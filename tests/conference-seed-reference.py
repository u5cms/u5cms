#!/usr/bin/env python3
"""Preserve seeded conference pages, access and form controls across evolution.

Run the original application first. Compare semantic form contracts in German,
English and French for anonymous users, authors, reviewers and both backend roles.
Additional forms/fields are allowed; losing or changing an existing contract is not.
The global fsearch navigation form has deliberately evolved from JavaScript URL
construction to native GET submission. Its behavior has a separate real-browser
search suite; it is excluded from this conference business-form comparison.
"""
import argparse,collections,copy,importlib.util,json,pathlib,tempfile,urllib.parse
from html.parser import HTMLParser

HERE=pathlib.Path(__file__).resolve().parent
spec=importlib.util.spec_from_file_location('conference_workflow',HERE/'conference-workflow-regression.py')
conference=importlib.util.module_from_spec(spec);spec.loader.exec_module(conference)
ROLES={'anonymous':None,'author':conference.USERS[0],'reviewer':conference.USERS[1],
       'organizer':'conferenceadmin','editor':'conferenceeditor'}

class Forms(HTMLParser):
    def __init__(self,path,port):
        super().__init__(convert_charrefs=True);self.path=path;self.port=port;self.forms=[];self.form=None;self.select=None
    def handle_starttag(self,tag,attrs):
        a=dict(attrs)
        if tag=='form':
            if a.get('name')=='fsearch':self.form=None;return
            action=urllib.parse.urljoin('http://127.0.0.1:'+str(self.port)+'/'+self.path,a.get('action') or self.path)
            parsed=urllib.parse.urlsplit(action)
            origin='<LOCAL>' if parsed.hostname=='127.0.0.1' else parsed.netloc
            query=urllib.parse.parse_qsl(parsed.query,keep_blank_values=True)
            query=[(k,v) for k,v in query if k!='u5scrtytknfrfrms']
            self.form={'action':origin+parsed.path+'?'+urllib.parse.urlencode(query),
                       'method':a.get('method','get').lower(),'target':a.get('target') or '_self','controls':[]}
            self.forms.append(self.form)
        if self.form is not None and tag in ['input','textarea','select','button'] and a.get('name'):
            control={'name':a['name'],'tag':tag,'type':a.get('type','text' if tag=='input' else '').lower(),
                     'required':'required' in a,'multiple':'multiple' in a,'disabled':'disabled' in a}
            self.form['controls'].append(control)
            if tag=='select':self.select=control;control['options']=[]
        if tag=='option' and self.select is not None and 'value' in a:self.select['options'].append(a['value'])
    def handle_endtag(self,tag):
        if tag=='form':self.form=None
        if tag=='select':self.select=None

def exercise(source,work,names=None):
    work.mkdir();site=conference.Conference(source,work)
    try:
        upgrade=site.request('u5admin/zr.php',user='conferenceadmin')
        if upgrade['status']!=200 or 'Fatal error' in upgrade['body']:raise AssertionError('Conference upgrade did not complete')
        available=sorted(r['name'] for r in site.sql("SELECT name FROM resources WHERE typ='p' AND deleted!=1")['rows'])
        if names is None:names=available
        missing=sorted(set(names)-set(available))
        if missing:raise AssertionError('Seed pages removed: '+repr(missing))
        observed={}
        for language in ['de','en','fr']:
            for role,user in ROLES.items():
                for name in names:
                    path='index.php?'+urllib.parse.urlencode({'c':name,'l':language})
                    response=site.request(path,user=user)
                    if response['status']>=500 or 'Fatal error' in response['body']:raise AssertionError('Page abort: '+repr((language,role,name,response['status'])))
                    if role=='author' and (response['status']!=200 or '</html>' not in response['body'].lower()):raise AssertionError('Incomplete authorized seed page: '+name)
                    if role=='anonymous' and name in ['!uploadabstract','!reviewprocess','!reviewscore','!registerforconf'] and response['status']!=401:raise AssertionError('Protected conference page is exposed: '+name)
                    parser=Forms(path,site.port);parser.feed(response['body'])
                    observed[language+'/'+role+'/'+name]={'status':response['status'],'forms':parser.forms}
        for language in ['de','en','fr']:
            for page,required in [('!uploadabstract',{'authors_mandatory','title_mandatory','abstract_mandatory'}),
                                  ('!registerforconf',{'Firstname_mandatory','Name_mandatory','Contract_mandatory'})]:
                controls={c['name'] for form in observed[language+'/author/'+page]['forms'] for c in form['controls']}
                if not required.issubset(controls):raise AssertionError('Required seed form controls were not observed: '+language+'/'+page)
        return names,observed
    finally:site.close()

def compare(expected,actual):
    failures={}
    for key,before in expected.items():
        after=actual.get(key)
        if after is None or before['status']!=after['status']:
            failures[key]={'expected_status':before['status'],'actual_status':None if after is None else after['status']};continue
        for form in before['forms']:
            candidates=[f for f in after['forms'] if all(f[k]==form[k] for k in ['action','method','target'])]
            wanted=collections.Counter(json.dumps(c,sort_keys=True) for c in form['controls'])
            if not any(not (wanted-collections.Counter(json.dumps(c,sort_keys=True) for c in f['controls'])) for f in candidates):
                failures.setdefault(key,[]).append({'lost_form_contract':form})
    return failures

def verify_oracle():
    parsed=Forms('index.php?c=conference',12345)
    parsed.feed('<form name="fsearch"><input name="q"></form><form action="formsave.php" method="post"><input name="title_mandatory"></form>')
    if len(parsed.forms)!=1 or parsed.forms[0]['controls'][0]['name']!='title_mandatory':raise AssertionError('Business form parser did not observe its fixture')
    form={'action':'<LOCAL>/formsave.php?n=conference','method':'post','target':'saver',
          'controls':[{'name':'title_mandatory','tag':'input','type':'text','required':False,'multiple':False,'disabled':False},
                      {'name':'submissiontype','tag':'select','type':'','required':False,'multiple':False,'disabled':False,'options':['paper','poster']}]}
    original={'page':{'status':200,'forms':[form]}}
    added=copy.deepcopy(original);added['page']['forms'][0]['controls'].append({'name':'optional_new_control'})
    if compare(original,added):raise AssertionError('Additive controls must preserve the existing contract')
    mutations=[]
    for key,value in [('method','get'),('target','_self'),('action','<LOCAL>/other.php')]:
        changed=copy.deepcopy(original);changed['page']['forms'][0][key]=value;mutations.append(changed)
    changed=copy.deepcopy(original);changed['page']['status']=401;mutations.append(changed)
    changed=copy.deepcopy(original);changed['page']['forms']=[];mutations.append(changed)
    changed=copy.deepcopy(original);changed['page']['forms'][0]['controls'].pop();mutations.append(changed)
    changed=copy.deepcopy(original);changed['page']['forms'][0]['controls'][0]['required']=True;mutations.append(changed)
    changed=copy.deepcopy(original);changed['page']['forms'][0]['controls'][1]['options'].pop();mutations.append(changed)
    mutations.append({})
    if any(not compare(original,changed) for changed in mutations):raise AssertionError('Lost or changed seed form contract was accepted')
    return len(mutations)

def main():
    parser=argparse.ArgumentParser(description=__doc__);parser.add_argument('--baseline',type=pathlib.Path,required=True);parser.add_argument('--target',action='append',default=[]);parser.add_argument('--output',type=pathlib.Path,required=True);args=parser.parse_args()
    args.output.parent.mkdir(exist_ok=True,parents=True);report={'method':'Original seed executes first; preserve conference page access and business forms; allow additive features; navigation search has its own browser suite.','baseline':str(args.baseline),'oracle_mutations':verify_oracle(),'results':[]}
    with tempfile.TemporaryDirectory(prefix='u5-seed-reference-') as temporary:
        work=pathlib.Path(temporary);names,expected=exercise(args.baseline.resolve(),work/'original')
        report['original_cases']=len(expected);report['seed_pages']=len(names);args.output.write_text(json.dumps(report,indent=2));print('Original conference seed contracts frozen: '+str(len(expected))+' cases.',flush=True)
        for number,value in enumerate(args.target):
            label,path=value.split('=',1)
            try:
                _,actual=exercise(pathlib.Path(path).resolve(),work/('target-'+str(number)),names)
                record={'label':label,'cases':len(actual),'differences':compare(expected,actual)}
            except Exception as error:record={'label':label,'error':str(error)}
            report['results'].append(record);args.output.write_text(json.dumps(report,indent=2));print(json.dumps({'label':label,'cases':record.get('cases'),'differences':len(record.get('differences',{})),'error':record.get('error')}),flush=True)
    return int(any(r.get('differences') or r.get('error') for r in report['results']))

if __name__=='__main__':raise SystemExit(main())
