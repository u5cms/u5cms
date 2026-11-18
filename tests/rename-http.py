#!/usr/bin/env python3
"""Real authenticated requests and before/after database/file checks; no browser claim."""
from pathlib import Path
import base64,http.cookiejar,json,os,re,subprocess,sys,urllib.request,urllib.parse,urllib.error
from fixture_environment import fixture_password_for
base,site=sys.argv[1:];site=Path(site);repo=Path(__file__).resolve().parent.parent
php=[os.environ.get('U5_TEST_PHP','php')]+json.loads(os.environ.get('U5_TEST_PHP_ARGS','[]'))
checks=0
def check(value,message):
    global checks
    checks+=1
    if not value:raise AssertionError(message)
def support(action,mode):
    p=subprocess.run(php+[str(repo/'tests/rename-web-support.php'),str(site),action,mode],capture_output=True,text=True)
    check(p.returncode==0,'fixture '+action+': '+p.stderr+p.stdout)
    return json.loads(p.stdout) if action=='snapshot' else None
def client(user='auditadmin',password=None,basic=True):
    opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
    if basic:opener.addheaders=[('Authorization','Basic '+base64.b64encode((user+':'+(password or os.environ['U5CMS_FIXTURE_PASSWORD'])).encode()).decode())]
    return opener
def request(opener,path,form=None):
    req=urllib.request.Request(base+path,data=urllib.parse.urlencode(form).encode() if form is not None else None)
    try:
        with opener.open(req,timeout=30) as response:return response.status,response.read().decode('cp1252')
    except urllib.error.HTTPError as error:return error.code,error.read().decode('cp1252')
def token(opener,name):
    status,body=request(opener,'/u5admin/rename.php?name='+name)
    check(status==200,'rename form status')
    match=re.search(r'name="u5scrtytknfrfrms"[^>]*value="([^"]+)"',body)
    check(match is not None,'actual CSRF token in form')
    return match.group(1)
config=site/'config.php';original=config.read_bytes()
try:
    status,_=request(client(basic=False),'/u5admin/rename.php?name=demo')
    check(status==401,'unauthenticated backend denied')
    for mode in ['basic','session']:
        config.write_bytes(re.sub(rb"\$usesessioninsteadofbasicauth\s*=\s*'(?:yes|no)'",b"$usesessioninsteadofbasicauth='"+(b'no' if mode=='basic' else b'yes')+b"'",original))
        support('seed',mode);old='renameold'+mode;new='renamenew'+mode
        admin=client(basic=mode=='basic')
        if mode=='session':request(admin,'/loginsave.php?u=u5admin/rename.php',{'u':'auditadmin','p':os.environ['U5CMS_FIXTURE_PASSWORD']})
        csrf=token(admin,old);before=support('snapshot',mode)
        status,_=request(admin,'/u5admin/rename2.php?name='+old,{'name':new,'ulinks':'yes'})
        check(status==403,'missing CSRF denied '+mode);check(support('snapshot',mode)==before,'CSRF preserves all rows')
        status,_=request(admin,'/u5admin/rename3.php?name='+old+'&newname='+new+'&ulinks=yes')
        check(status==410,'history-only endpoint retired');check(support('snapshot',mode)==before,'retired endpoint preserves all rows')
        status,body=request(admin,'/u5admin/rename2.php?name='+old,{'name':new,'ulinks':'yes','u5scrtytknfrfrms':csrf})
        check(status==200 and 'Rename has not completed' not in body,'actual rename succeeds '+mode)
        state=support('snapshot',mode);row=next(x for x in state['resources'] if x['name']==new)
        check(row['operator']=='R original','one revision marker')
        for language in range(1,6):
            check('[[[['+old+']]]]' in row['content_'+str(language)],'heading preserved '+str(language))
            check('[Go to page:'+new+']' in row['content_'+str(language)],'link updated '+str(language))
            check('[Go to page:'+new+']' in next(x for x in state['resources_log'] if x['name']==new)['content_'+str(language)],'history complete before response '+str(language))
        check(state['formdata'][0]['formname']==new,'form ownership follows')
        check(state['formdata'][0]['datacsv']=='submitted [label:'+old+']','form payload remains opt-in')
        check((site/'r'/new/(new+'_en.txt')).read_bytes()==b'original language bytes','language file bytes')
        check((site/'r'/new/(new+'_100.jpg')).read_bytes()==b'original album bytes','album bytes')
        check((site/'r'/new/'.htpasswd').read_text()==os.environ['U5CMS_FIXTURE_BODY'],'membership bytes')
        status,front=request(admin,'/?c=renameref'+mode+'&l=en')
        check(status==200 and old in front and 'c='+new in front,'real renderer resolves heading and target')
        status,_=request(admin,'/u5admin/rename2.php?name='+old,{'name':new,'ulinks':'yes','u5scrtytknfrfrms':csrf})
        check(status==200 and support('snapshot',mode)==state,'completed retry is idempotent')
    config.write_bytes(original);support('seed','failure');support('archive','failure')
    writer=client('renamewriter',fixture_password_for('rename-writer'));old='renameoldfailure';new='renamenewfailure'
    csrf=token(writer,old);before=support('snapshot','failure')
    status,_=request(writer,'/u5admin/rename2.php?name='+old,{'name':new,'ulinks':'yes','u5scrtytknfrfrms':csrf})
    check(status==403 and support('snapshot','failure')==before,'archive HIADRI denial preserves source')
    admin=client();csrf=token(admin,old)
    status,body=request(admin,'/u5admin/checkco.php?name='+old+'&coco=100&changes=1')
    check('msgconflict=' not in body,'conflict poll ignores newer deleted tombstone')
    export=site/'r'/os.environ.get('U5_RENAME_TEST_EXPORT','htmltemplate.css');saved=site/'r/htmltemplate.fixture';export.rename(saved);export.mkdir()
    try:
        status,body=request(admin,'/u5admin/rename2.php?name='+old,{'name':new,'ulinks':'yes','u5scrtytknfrfrms':csrf})
        check(status==500 and 'Retry rename' in body,'publication failure offers exact retry')
        state=support('snapshot','failure');check(any(x['name']==new for x in state['resources']),'checkpoint covers moved source')
        status,body=request(admin,'/u5admin/meta2.php?typ=p',{'name':new,'u5scrtytknfrfrms':csrf})
        check(status==409 and 'did not change' in body,'coordinated metadata writer blocks pending rename')
        for route in ['savepage.php','new2.php','copy2.php','delete2.php','archiveunarchive2.php','coding2.php','sendfile2.php','sendfilea2.php','deletefile.php','deletefilea.php','albumsort.php','cleanlog.php','defineloginglobals2.php','definelanguages2.php','ishomepage2.php','kill2.php','phperror.php','updateintranet.php']:
            code,blocked=request(admin,'/u5admin/'+route,{'name':new,'page':new,'u5scrtytknfrfrms':csrf})
            check(code==409 and 'did not change' in blocked,'pending rename blocks writer '+route)
        check(support('snapshot','failure')==state,'all blocked writers preserve source/history/form data')
        lowcsrf=token(writer,old)
        status,_=request(writer,'/u5admin/rename2.php?name='+old,{'name':new,'ulinks':'yes','u5scrtytknfrfrms':lowcsrf})
        check(status==403 and support('snapshot','failure')==state,'retry retains archive rights after source has moved')
    finally:export.rmdir();saved.rename(export)
    status,body=request(admin,'/u5admin/rename2.php?name='+old,{'name':new,'ulinks':'yes','u5scrtytknfrfrms':csrf})
    check(status==200 and 'Rename has not completed' not in body,'administrator completes exact retry')
    check(next(x for x in support('snapshot','failure')['resources'] if x['name']==new)['deleted']=='2','archive state retained')
    print('Rename HTTP checks:',checks,'passed (Basic/session, CSRF, HIADRI, real renderer, files/history, failure/retry).')
finally:config.write_bytes(original)
