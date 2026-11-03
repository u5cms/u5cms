#!/usr/bin/env python3
"""Fresh conference seed, real HTTP workflow and documented :dat contracts.

Use only a disposable TCP MariaDB (U5CMS_TEST_PORT). Expected values are
fixture/domain values, not output copied from the renderer under test.
"""
import argparse, base64, datetime, hashlib, html, importlib.util, json, os
import pathlib, re, shutil, socket, subprocess, tempfile, time
import urllib.error, urllib.parse, urllib.request

HERE=pathlib.Path(__file__).resolve().parent
spec=importlib.util.spec_from_file_location('reference',HERE/'main-reference-regression.py')
reference=importlib.util.module_from_spec(spec);spec.loader.exec_module(reference)
PHP=os.environ.get('U5_TEST_PHP','php')
PASSWORD='conference-password'
USERS=['author@example.invalid','reviewer-a@example.invalid','reviewer-b@example.invalid','outsider@example.invalid']

class Conference(reference.Site):
    def __init__(self,source,work):
        self.label=source.name;self.root=work/'site';self.database='u5reference_'+os.urandom(7).hex()
        self.server=None;self.log=None
        shutil.copytree(source,self.root,ignore=shutil.ignore_patterns('.git','config.php','__pycache__'))
        (self.root/'fileversions').mkdir(exist_ok=True)
        self.db(action='create',seed=str(source/'u5conference.sql'))
        self.config=(source/'config.php.sample').read_bytes()
        self.settings={'host':'127.0.0.1','username':os.environ.get('U5CMS_TEST_USER','root'),
          'password':os.environ.get('U5CMS_TEST_PASSWORD',''),'db':self.database,
          'mymail':'conference@example.invalid','usesessioninsteadofbasicauth':'no',
          'blockautoincluderefresherforhtaccessandindex':'yes','cssremoteurlsifnecessary':'',
          'doublepasswordmailing':'no','requireturnstileonfrontendformsave':'no'}
        self.write_config()
        sha='{SHA}'+base64.b64encode(hashlib.sha1(PASSWORD.encode()).digest()).decode()
        self.sql('DELETE FROM accounts')
        self.sql("INSERT INTO accounts(id,email,pw,accadmin) VALUES(1,'conferenceadmin',"+reference.literal(sha)+",1)")
        self.sql("INSERT INTO accounts(email,pw,accadmin) VALUES('conferenceeditor',"+reference.literal(sha)+",0)")
        logins=''.join('?'+user+':'+PASSWORD+';\n' for user in USERS)
        self.sql('UPDATE resources SET logins='+reference.literal(logins)+" WHERE logins!=''")
        with socket.socket() as probe:probe.bind(('127.0.0.1',0));self.port=probe.getsockname()[1]
        self.log=(work/'server.log').open('w+')
        self.server=subprocess.Popen([PHP,'-d','mysqli.default_port='+os.environ['U5CMS_TEST_PORT'],
          '-d','sendmail_path=/bin/false','-S','127.0.0.1:'+str(self.port),'-t',str(self.root)],
          cwd=self.root,stdout=self.log,stderr=self.log)
        for _ in range(100):
            try:
                with socket.create_connection(('127.0.0.1',self.port),timeout=.2):break
            except OSError:
                if self.server.poll() is not None:raise RuntimeError('PHP stopped')
                time.sleep(.03)
        else:raise RuntimeError('PHP readiness timeout')

    def request(self,path,data=None,user=None,cookies=None):
        headers={}
        if user:headers['Authorization']='Basic '+base64.b64encode((user+':'+PASSWORD).encode()).decode()
        if cookies:headers['Cookie']=cookies
        if data is not None:
            if isinstance(data,dict):data=list(data.items())
            data=urllib.parse.urlencode([(k,v.encode('cp1252') if isinstance(v,str) else v) for k,v in data],doseq=True).encode()
        request=urllib.request.Request('http://127.0.0.1:'+str(self.port)+'/'+path,data=data,headers=headers)
        try:response=urllib.request.urlopen(request,timeout=30)
        except urllib.error.HTTPError as error:response=error
        return {'status':response.status,'body':response.read().decode('cp1252'),'headers':dict(response.headers)}

    def dat(self,args,query='',user=None,raw=False):
        command='['+'|'.join(args)+':dat]'
        self.sql("DELETE FROM resources WHERE name='datprobe'")
        self.resource('datprobe',[('[h:]<div id="dat-result">'+command+'</div>[:h]').encode('cp1252')])
        result=self.request('index.php?c=datprobe&l=en'+query,user=user)
        match=re.search(r'<div id="dat-result">(.*?)</div>',result['body'],re.S)
        if not match:raise AssertionError('Incomplete Dat response: '+result['body'][-1200:])
        return match.group(1) if raw else html.unescape(match.group(1))

def values(row):
    keys=[v[1:] for v in row['headcsv'].split(';') if v]
    vals=[v[1:].replace(',.',';') for v in row['datacsv'].split(';') if v]
    return dict(zip(keys,vals))

def exercise(site):
    checks=[];failures=[]
    def check(label,condition):
        checks.append(label)
        if not condition:failures.append(label)
    response=site.request('u5admin/zr.php',user='conferenceadmin')
    check('backend upgrade',response['status']==200 and 'Fatal error' not in response['body'])
    check('conference examples installed',len(site.rows('!uploadabstract'))==4)
    names=[r['name'] for r in site.sql("SELECT name FROM resources WHERE typ='p' AND deleted!=1")['rows']]
    for name in names:
        result=site.request('index.php?c='+urllib.parse.quote(name)+'&l=en',user=USERS[0])
        check('seed page '+name,result['status']==200 and 'Fatal error' not in result['body'] and '</html>' in result['body'].lower())
    for name in ['!uploadabstract','!reviewprocess','!reviewscore','!registerforconf']:
        denied=site.request('index.php?c='+name+'&l=en')
        check('anonymous denied '+name,denied['status']==401)
    site.sql('DELETE FROM formdata')
    fields={'youremail':'','authors_mandatory':'(1)Müller, A.',
      'affiliations_mandatory':'(1)University; Institute','title_mandatory':'Conference Alpha',
      'abstract_mandatory':'ABSTRACT-ALPHA\r\nLine "two"; C:\\work & <b>literal</b>',
      'userupload1':'','submissiontype_mandatory':'fullpaper','correspondencetitle_mandatory':'Dr',
      'correspondencefirstname_mandatory':'Anna','correspondencename_mandatory':'Müller',
      'youremail_mandatory':USERS[0],'thanks':'thanks'}
    response=site.request('formsave.php?n=!uploadabstract&l=en',fields)
    check('anonymous submission cannot write',response['status']==401 and not site.rows('!uploadabstract'))
    site.request('formsave.php?n=!uploadabstract&l=en',dict(fields,title_mandatory=''),user=USERS[0])
    check('mandatory field cannot write',not site.rows('!uploadabstract'))
    response=site.request('formsave.php?n=!uploadabstract&l=en',fields,user=USERS[0])
    rows=site.rows('!uploadabstract')
    check('submission persisted',response['status']==200 and len(rows)==1)
    if not rows:raise AssertionError('No submitted abstract: '+response['body'][-2000:])
    row=rows[0];rid=row['id']
    check('submitted values round trip',all(values(row).get(k)==v for k,v in fields.items() if k!='thanks'))
    check('submission status and owner',row['status']=='1' and row['authuser']==USERS[0])
    before=site.rows('!uploadabstract')
    response=site.request('u5admin/statussave.php?id='+rid+'&status=4',user='conferenceeditor')
    check('restricted editor cannot change status',response['status']==403 and site.rows('!uploadabstract')==before)
    response=site.request('u5admin/notesave.php?id='+rid,{'note':'UNAUTHORIZED-ASSIGNMENT'},user='conferenceeditor')
    check('restricted editor cannot change assignments',response['status']==403 and site.rows('!uploadabstract')==before)
    token=hashlib.sha1((site.settings['username']+site.settings['password']+'conferenceeditor'+PASSWORD+datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%d')).encode()).hexdigest()
    response=site.request('u5admin/mass.php?n=!uploadabstract',{'rowaid':rid,'newstatus':'4','u5scrtytknfrfrms':token},user='conferenceeditor')
    check('restricted editor cannot change status in bulk',response['status']==403 and site.rows('!uploadabstract')==before)
    site.sql('UPDATE formdata SET status=1,notes=\'\' WHERE id='+rid)
    site.request('formsave.php?n=!uploadabstract&l=en',dict(fields,title_mandatory='Conference Beta',abstract_mandatory='ABSTRACT-BETA'),user=USERS[0])
    other=site.rows('!uploadabstract')[-1]['id']
    for number,record,user in [('P001',rid,USERS[1]),('P002',other,USERS[2])]:
        response=site.request('u5admin/notesave.php?id='+record,{'note':number+' |||\r\n('+user+')'},user='conferenceadmin')
        check('organizer assigns reviewer '+number,response['status']==200 and user in next(r for r in site.rows('!uploadabstract') if r['id']==record)['notes'])
    for user,want,absent in [(USERS[1],'Conference Alpha','Conference Beta'),(USERS[2],'Conference Beta','Conference Alpha'),(USERS[3],None,'Conference Alpha')]:
        response=site.request('index.php?c=!reviewprocess&l=en',user=user)
        check('review list assignment '+user,(want is None or want in response['body']) and absent not in response['body'])
    response=site.request('index.php?c=!reviewscore&id='+rid+'&l=en',user=USERS[1])
    check('assigned abstract displayed','ABSTRACT-ALPHA' in response['body'] and 'ABSTRACT-BETA' not in response['body'])
    for user in [USERS[2],USERS[3]]:
        response=site.request('index.php?c=!reviewscore&id='+rid+'&l=en',user=user)
        check('unassigned direct abstract hidden '+user,'ABSTRACT-ALPHA' not in response['body'])
    secret=hashlib.sha1(USERS[1].encode()).digest()
    owner_token='{SHA}'+base64.b64encode(secret).decode()
    score={'id_mandatory':rid,'title_mandatory':'Conference Alpha','abstract_mandatory':fields['abstract_mandatory'],
      'content_relevance_mandatory':'5','content_methodic_mandatory':'4','content_conclusion_mandatory':'3',
      'comments':'REVIEW-A; "quote"\r\n0','thanks':'!selfclose','in2my':'off','em2cu':'off','em2me':'off','ed2cu':'on'}
    # [mf] uses a separate real HTTP request and an identity-bound action token.
    response=site.request('monofill.php?n=!reviewscore&id='+rid,user=USERS[1])
    match=re.search(r"&o=([^']+)",response['body'])
    check('review edit identity token',match is not None)
    action='formsave.php?n=!reviewscore&l=en&o='+urllib.parse.quote(owner_token,safe='')
    site.request(action,score,user=USERS[1])
    reviews=site.rows('!reviewscore')
    check('review persisted',len(reviews)==1 and reviews[0]['authuser']==USERS[1] and values(reviews[0]).get('comments')==score['comments'])
    site.request(action,dict(score,comments='REVIEW-A-EDITED'),user=USERS[1])
    reviews=site.rows('!reviewscore');active=[r for r in reviews if r['status']=='1'];history=[r for r in reviews if r['status']=='6']
    check('review edit keeps one active version and history',len(active)==1 and len(history)==1 and values(active[0]).get('comments')=='REVIEW-A-EDITED' and values(history[0]).get('comments')==score['comments'])
    response=site.request('monofill.php?n=!reviewscore&id='+rid,user=USERS[2])
    check('other reviewer cannot load saved review','REVIEW-A-EDITED' not in response['body'])
    response=site.request('u5admin/formdata2.php?n=!reviewscore&s=0',user='conferenceeditor')
    check('ordinary backend account cannot read reviews','REVIEW-A-EDITED' not in response['body'])
    response=site.request('u5admin/formdata2.php?n=!reviewscore&s=0',user='conferenceadmin')
    check('admin sees reviews','REVIEW-A-EDITED' in response['body'])
    response=site.request('index.php?c=abstracts&l=en')
    check('new abstracts not public','Conference Alpha' not in response['body'] and 'Conference Beta' not in response['body'])
    response=site.request('u5admin/statussave.php?id='+rid+'&status=4',user='conferenceadmin')
    check('organizer accepts abstract',response['status']==200 and next(r for r in site.rows('!uploadabstract') if r['id']==rid)['status']=='4')
    response=site.request('index.php?c=abstracts&l=en')
    check('accepted abstract published with public notes','Conference Alpha' in response['body'] and 'P001' in response['body'] and 'Conference Beta' not in response['body'])
    check('private reviewer notes not published',USERS[1] not in response['body'])
    response=site.request('index.php?c=!reviewprocess&l=en',user=USERS[1])
    check('accepted abstract leaves pending review list','Conference Alpha' not in response['body'])
    registration={'youremail':'','Title_mandatory':'Dr','Firstname_mandatory':'Anna','Name_mandatory':'Müller',
      'Affiliation_mandatory':'University','Address_mandatory':'Bern; Switzerland','preconference_mandatory':'one half day workshop = add CHF 50',
      'mainconference_mandatory':'Early bird: until 30 June 2014 = add CHF 220.-','ParticipateDinner_mandatory':'No','Contract_mandatory':'agreed','thanks':'!paymenthowto'}
    site.request('formsave.php?n=!registerforconf&l=en',registration,user=USERS[0])
    registrations=site.rows('!registerforconf')
    check('registration persisted',len(registrations)==1 and values(registrations[0]).get('Address_mandatory')==registration['Address_mandatory'])
    # Independent :dat matrix, including the optional/short documented forms.
    site.sql("DELETE FROM formdata WHERE formname='dattest'")
    data=[('Alpha','one',1,'public-alpha ||| private-alpha',USERS[0]),('Beta','two',1,'('+USERS[1]+')',USERS[1]),('Gamma','zero',4,'public-gamma',USERS[0])]
    ids=[]
    for title,note,status,notes,owner in data:
        result=site.sql('INSERT INTO formdata(formname,headcsv,datacsv,status,notes,authuser) VALUES('+','.join(reference.literal(v) if isinstance(v,str) else str(v) for v in ['dattest','·title_mandatory;·note;·zero;','·'+title+';·'+note+';·0;',status,notes,owner])+')')
        ids.append(str(result['id']))
    def dat(label,args,expected,query='',user=None):
        try:actual=site.dat(args,query,user);check(label,actual==expected)
        except Exception as error:check(label,False);failures.append(label+': '+str(error))
    dat('short mandatory alias',['title*','dattest','ALL'],'AlphaBetaGamma')
    dat('status selection',['title_mandatory','dattest','ALL','','1'],'AlphaBeta')
    dat('literal id',['title*','dattest',ids[1]],'Beta')
    dat('URL id',['title*','dattest','?'],'Beta','&id='+ids[1])
    dat('own records',['title*','dattest','ALL','SELF'],'AlphaGamma',user=USERS[0])
    dat('review assignment Dat',['title*','dattest','ALL','(authuser)'],'Beta',user=USERS[1])
    dat('notes substring',['title*','dattest','ALL','public-'],'AlphaGamma')
    dat('notes private suffix hidden',['?notes','dattest',ids[0]],'public-alpha ')
    dat('LIKE filter',['title*','dattest','ALL','datacsv LIKE %Beta%'],'Beta')
    dat('URL LIKE and',['title*','dattest','ALL','?'],'Beta','&notes='+urllib.parse.quote('datacsv LIKE %Beta%&&%two%'))
    dat('URL LIKE or',['title*','dattest','ALL','?'],'AlphaBeta','&notes='+urllib.parse.quote('datacsv LIKE %Beta%||%Alpha%'))
    dat('static descending limit',['title*','dattest','ALL','','','','','','id DESC LIMIT 0,2'],'GammaBeta')
    dat('dynamic order',['title*','dattest','ALL','','','','','','?'],'GammaBetaAlpha','&sort='+urllib.parse.quote('ORDER BY id DESC'))
    dat('dynamic order with limit',['title*','dattest','ALL','','','','','','? LIMIT 0,2'],'GammaBeta','&sort='+urllib.parse.quote('ORDER BY id DESC'))
    dat('dynamic order absent',['title*','dattest','ALL','','','','','','?'],'AlphaBetaGamma')
    dat('no matches',['title*','dattest','ALL','absent'],'')
    dat('missing field',['notpresent','dattest',ids[0]],'')
    dat('literal zero',['zero','dattest',ids[0]],'0')
    dat('condition zero',['zero','dattest',ids[0],'','','','','','','','','0','ZERO'],'ZERO')
    dat('condition false',['zero','dattest',ids[0],'','','','','','','','','1','ONE'],'')
    dat('per field separators',['title*,note,zero','dattest',ids[0],'','','<b>','</b>',',@@/'],'<b>Alpha,one/0</b>')
    dat('recursive id variable',['title*','dattest',ids[0],'','','<a href="?id=$id">','</a>'],'<a href="?id='+ids[0]+'">Alpha</a>')
    site.sql('UPDATE formdata SET datacsv='+reference.literal('·<b>Bold</b> [b]Syntax[/]\r\nhttps://example.invalid;·two;·0;')+' WHERE id='+ids[1])
    for mode in ['0','1','2','3','4','5']:
        actual=site.dat(['title*','dattest',ids[1],'','','','','','','<br>','','','',mode],raw=True)
        check('HTML interpretation '+mode,('<b>Bold</b>' in actual)==(mode in ['2','3','5']))
        check('CMS interpretation '+mode,('class="bold"' in actual)==(mode in ['4','5']))
    actual=site.dat(['title*','dattest',ids[1],'','','','','','','<br>','','','','0!'],raw=True)
    check('linkification opt in','<a ' in actual and 'https://example.invalid' in actual)
    # Full WHERE remains an explicit administrator opt-in.
    site.settings['allowfullwhereasdatparameterd']='yes';site.write_config()
    dat('full WHERE enabled',['title*','dattest','ALL','WHERE status=4'],'Gamma')
    site.settings['allowfullwhereasdatparameterd']='no';site.write_config()
    try:site.dat(['title*','dattest','ALL','WHERE status=4']);check('full WHERE disabled',False)
    except AssertionError as error:check('full WHERE disabled','allowfullwhereasdatparameterd' in str(error))
    # Invalid SQL may be hidden by configuration, but must not abort the page.
    site.settings['showdatcommandsqlerrors']='no';site.write_config()
    dat('invalid sort controlled',['title*','dattest','ALL','','','','','','?'],'','&sort='+urllib.parse.quote('ORDER BY definitely_missing_column'))
    # The documented RFC quote mode is distinct from the historical import.
    if b'csv_escape' in (site.root/'u5admin/import2.php').read_bytes():
        import csv,io
        token=hashlib.sha1((site.settings['username']+site.settings['password']+'conferenceadmin'+PASSWORD+datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%d')).encode()).hexdigest()
        cases=['He said "hello"; C:\\work\\file','\\"quoted\\"','end\\','0','','Line one\r\nLine two']
        stream=io.StringIO(newline='');writer=csv.writer(stream,delimiter='\t',lineterminator='\r\n');writer.writerow(['Name','Comment'])
        for index,value in enumerate(cases):writer.writerow(['Row'+str(index),value])
        response=site.request('u5admin/import2.php',{'u5scrtytknfrfrms':token,'csv':stream.getvalue(),'csv_escape':'rfc4180','target':'rfcprobe','h':'2','m':'1','n':'1','fixnotes':'','fixauthuser':''},user='conferenceadmin')
        imported=site.rows('rfcprobe')
        check('RFC import row count',response['status']==200 and len(imported)==len(cases))
        check('RFC import preserves quotes backslashes zero and newlines',[values(r).get('Comment') for r in imported]==cases)
    # The same endpoints intentionally remain available to ordinary editors
    # when the deployment explicitly relaxes its form-data restriction.
    site.settings['formdataRqHIADRI']='no';site.write_config()
    response=site.request('u5admin/statussave.php?id='+rid+'&status=2',user='conferenceeditor')
    check('relaxed editor may change status',response['status']==200 and next(r for r in site.rows('!uploadabstract') if r['id']==rid)['status']=='2')
    response=site.request('u5admin/notesave.php?id='+rid,{'note':'ALLOWED'},user='conferenceeditor')
    check('relaxed editor may change notes',response['status']==200 and next(r for r in site.rows('!uploadabstract') if r['id']==rid)['notes']=='ALLOWED')
    return {'checks':len(checks),'failures':failures,'checked':checks}

def main():
    parser=argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--source',type=pathlib.Path,default=HERE.parent)
    parser.add_argument('--output',type=pathlib.Path)
    args=parser.parse_args()
    with tempfile.TemporaryDirectory(prefix='u5-conference-') as temporary:
        work=pathlib.Path(temporary);site=Conference(args.source.resolve(),work)
        try:report=exercise(site)
        except Exception:
            site.log.flush();print((work/'server.log').read_text(errors='replace')[-6500:]);raise
        finally:site.close()
    if args.output:args.output.write_text(json.dumps(report,indent=2))
    print(json.dumps(report,indent=2))
    return bool(report['failures'])

if __name__=='__main__':raise SystemExit(main())
