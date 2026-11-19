#!/usr/bin/env python3
"""Additional pass 1: original-main contracts under combined configuration switches.

Uses independent HTTP clients, real Basic/session logins and fresh native SQL
fixtures. Records semantic observations, including completion after early output.
No message is sent and no production configuration is read.
"""
import argparse, base64, http.cookiejar, importlib.util, json, pathlib, re
import tempfile, urllib.error, urllib.parse, urllib.request

HERE=pathlib.Path(__file__).resolve().parent
spec=importlib.util.spec_from_file_location('original_reference',HERE/'main-reference-regression.py')
reference=importlib.util.module_from_spec(spec);spec.loader.exec_module(reference)

class Client:
    def __init__(self,site,user='referenceadmin',session=False):
        self.site=site;self.user=user;self.session=session
        self.cookies=http.cookiejar.CookieJar()
        self.opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.cookies))
        if session:
            self.request('loginsave.php?u=u5admin/',{'u':user,'p':reference.REFERENCE_PASSWORD})
            assert {'u','p'}.issubset({c.name for c in self.cookies}), 'Real session login did not issue credentials'
    def request(self,path,data=None,cookie=None):
        headers={}
        if self.user and not self.session:
            headers['Authorization']='Basic '+base64.b64encode((self.user+':'+reference.REFERENCE_PASSWORD).encode()).decode()
        if cookie:headers['Cookie']=cookie
        payload=None if data is None else urllib.parse.urlencode(data).encode()
        req=urllib.request.Request('http://127.0.0.1:'+str(self.site.port)+'/'+path,data=payload,headers=headers)
        try:response=self.opener.open(req,timeout=30)
        except urllib.error.HTTPError as error:response=error
        return response.status,response.read().decode('cp1252')

PROFILES=[
    ('default',{},'referenceadmin'),
    ('ordinary-open-data',{'formdataRqHIADRI':'no'},'referenceeditor'),
    ('ordinary-restricted-data',{'formdataRqHIADRI':'yes'},'referenceeditor'),
    ('checked-form-edits',{'enableformfieldcheckerforedits':'yes'},'referenceadmin'),
    ('php-archive-only',{'executephp':'inarchiveonly'},'referenceadmin'),
    ('php-disabled',{'executephp':'nowhere'},'referenceadmin'),
    ('quote-handling',{'quotehandling':'on'},'referenceadmin'),
    ('cookie-session',{'usesessioninsteadofbasicauth':'yes'},'referenceadmin'),
    ('fastcgi-auth',{'autoresolvefastcgiproblems':'yes'},'referenceadmin'),
    ('explicit-open-backend',{'automaticallyskipbackendlogin':'yes','formdataRqHIADRI':'no'},None),
]

def exercise(site):
    original_settings=dict(site.settings)
    response=site.request('u5admin/zr.php',user='referenceadmin')
    assert response['status']==200,'Actual upgrade failed'
    site.resource('modify!formdata2!php',[b'[h:]<div id="configuration-hook">CONFIGURATION-HOOK</div>[:h]'])
    saved=site.request('formsave.php?n=refform&l=de',dict(site.fields,name_mandatory='CONFIG-RECORD'))
    assert saved['status']==200 and len(site.rows())==1,'Public fixture form did not save'
    rowid=site.rows()[0]['id'];observations={}
    # Compare the same business record, including its deliberately fixed date.
    site.sql("UPDATE formdata SET time=1700000000,humantime='2023-11-14 22:13:20' WHERE id="+rowid)
    for profile,settings,user in PROFILES:
        site.settings=dict(original_settings,**settings);site.write_config()
        client=Client(site,user,settings.get('usesessioninsteadofbasicauth')=='yes')
        for lang in ['de','en','fr','it','es']:
            status,body=client.request('index.php?c=refpage&l='+lang)
            match=re.search(r'<div id="proof">(.*?)</div>',body,re.S)
            observations[profile+'/language/'+lang]={'status':status,'proof':match.group(1) if match else None,'complete':'</html>' in body.lower()}
        for page,marker in [('refphp','EXECUTED-REFERENCE'),('refarchive','EXECUTED-ARCHIVE')]:
            status,body=client.request('index.php?c='+page+'&l=de')
            mode=settings.get('executephp','onallpages')
            executed=marker in body and '<?php' not in body
            expected=mode=='onallpages' or mode=='inarchiveonly' and page=='refarchive'
            assert status==200 and executed==expected,profile+'/'+page
            observations[profile+'/execution/'+page]={'status':status,'executed':executed,'complete':'</html>' in body.lower()}
        status,body=client.request('reference-app.php?c[]=chosen&n[]=one&selection[]=0',cookie='application[panel]=open')
        app=json.loads(body)
        observations[profile+'/own-app']={'status':status,'query':app['query'],'application_cookie':app['cookies'].get('application')}
        for endpoint in ['formdata2.php','formdatamail.php']:
            status,body=client.request('u5admin/'+endpoint+'?n=refform')
            observations[profile+'/'+endpoint]={'denied':'You do not have enough rights' in body,
                'record':'CONFIG-RECORD' in body,'extension':'CONFIGURATION-HOOK' in body,
                'complete':'</html>' in body.lower(),'fatal':'Fatal error' in body}
        status,body=client.request('u5admin/formdata2csv.php?n=refform')
        observations[profile+'/csv']={'denied':'You do not have enough rights' in body,'record':'CONFIG-RECORD' in body,'payload':body if 'CONFIG-RECORD' in body else ''}
        for endpoint in ['formdataedit.php','formdataeditgen.php']:
            status,body=client.request(endpoint+'?n=refform&id='+rowid+'&a=1')
            denied='You do not have enough rights' in body
            if denied:assert status in [200,403] and 'generic formdata editor' not in body
            # Earlier hardening intentionally changed denial's transport status
            # from 200 to 403; compare the preserved denial contract explicitly.
            observations[profile+'/'+endpoint]={'status':'denied' if denied else status,'denied':denied,'generic':'generic formdata editor' in body,
                'original_form':'reference-form' in body,'complete':'</html>' in body.lower(),'fatal':'Fatal error' in body}
    site.settings=original_settings;site.write_config()
    return observations

def assert_original(facts):
    for profile,settings,user in PROFILES:
        for lang in ['de','en','fr','it','es']:
            assert facts[profile+'/language/'+lang]['proof'] and facts[profile+'/language/'+lang]['complete']
        app=facts[profile+'/own-app']
        assert app['status']==200 and app['query']['selection']==['0'] and app['application_cookie']=={'panel':'open'}
        for endpoint in ['formdata2.php','formdatamail.php']:
            row=facts[profile+'/'+endpoint]
            if profile=='ordinary-restricted-data':assert row['denied'] and not row['record'] and not row['extension']
            else:assert not row['denied'] and row['record'] and row['extension'] and row['complete'] and not row['fatal'],profile+'/'+endpoint
    assert facts['default/formdataeditgen.php']['generic'] and facts['default/formdataeditgen.php']['complete']
    assert facts['default/csv']['record']

def main():
    parser=argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--baseline',type=pathlib.Path,required=True)
    parser.add_argument('--target',action='append',default=[])
    parser.add_argument('--output',type=pathlib.Path,required=True)
    args=parser.parse_args();report={'pass':'Additional 1: combined configuration and independent application contracts','profiles':[p[0] for p in PROFILES],'results':[]}
    args.output.parent.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(prefix='u5-configuration-reference-') as temporary:
        work=pathlib.Path(temporary);original=reference.Site(args.baseline,args.baseline,work,'original')
        try:
            expected=exercise(original);report['original']=expected;args.output.write_text(json.dumps(report,indent=2));assert_original(expected)
        finally:original.close()
        for target in args.target:
            label,path=target.split('=',1);site=reference.Site(pathlib.Path(path),args.baseline,work,label)
            try:actual=exercise(site);record={'label':label,'cases':len(actual),'differences':reference.differences(expected,actual)}
            except Exception as error:record={'label':label,'error':str(error)}
            finally:site.close()
            report['results'].append(record);args.output.write_text(json.dumps(report,indent=2))
            print(json.dumps({'label':label,'cases':record.get('cases'),'differences':len(record.get('differences',{})),'error':record.get('error')}),flush=True)
    failed=any(item.get('differences') or item.get('error') for item in report['results'])
    print('Configuration reference cases:',len(expected)*(len(args.target)+1),'; comparison passed:',not failed)
    return int(failed)
if __name__=='__main__':raise SystemExit(main())
