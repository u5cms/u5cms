"""Exercise normal editor and upload paths, followed by direct Apache authorisation."""
from pathlib import Path
from contextlib import contextmanager
import base64, http.cookiejar, json, os, re, shutil, socket, subprocess, tempfile, time
import urllib.error, urllib.parse, urllib.request
from fixture_environment import ensure_fixture_environment
ensure_fixture_environment()
repo=Path(__file__).resolve().parent.parent
php=os.environ.get('U5_TEST_PHP','php')
auth='Basic '+base64.b64encode(('auditadmin:'+os.environ['U5CMS_FIXTURE_PASSWORD']).encode()).decode()
def freeport():
    with socket.socket() as listener:
        listener.bind(('127.0.0.1',0))
        return listener.getsockname()[1]
@contextmanager
def server(command,site,port,env=None):
    with tempfile.TemporaryFile() as log:
        process=subprocess.Popen(command,cwd=site,stdout=log,stderr=log,env=env)
        try:
            for _ in range(100):
                if process.poll() is not None:
                    log.seek(0);raise RuntimeError(log.read().decode(errors='replace')[-1000:])
                try:
                    with socket.create_connection(('127.0.0.1',port),.2): break
                except OSError: time.sleep(.05)
            yield
        finally:
            process.terminate()
            process.wait(timeout=10)
def fetch(port,path,headers=None,data=None,opener=None):
    req=urllib.request.Request('http://127.0.0.1:'+str(port)+path,headers=headers or {},data=data)
    try:
        with (opener or urllib.request.build_opener()).open(req,timeout=15) as response:
            return response.status,response.read(),response.headers
    except urllib.error.HTTPError as err:
        return err.code,err.read(),err.headers
def check(value,label):
    if not value: raise AssertionError(label)
    print(label)
with tempfile.TemporaryDirectory(prefix='u5-http-acl-') as tmp:
    tmp=Path(tmp);site=tmp/'site'
    shutil.copytree(repo,site,ignore=shutil.ignore_patterns('.git','qa'))
    setup=[php,str(repo/'tests/rename-web-fixture.php')]
    db=json.loads(subprocess.check_output(setup+[str(site)]))['database']
    try:
        with (site/'config.php').open('ab') as config:config.write(b"\n<?php $u5allowbasicauthtoprotectedfilesindirr='yes'; ?>\n" if (site/'config.php').read_bytes().rstrip().endswith(b'?>') else b"\n$u5allowbasicauthtoprotectedfilesindirr='yes';\n")
        port=freeport()
        opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
        opener.addheaders=[('Authorization',auth)]
        router=tmp/'router.php'
        router.write_text("""<?php $p=realpath($_SERVER['DOCUMENT_ROOT'].parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH));
if (!$p || !is_file($p)) return false;
if (substr($p,-4)==='.php') {chdir(dirname($p));require $p;return;}return false;""")
        with server([php,'-d','display_errors=0','-d','mysqli.default_port='+os.environ['U5CMS_TEST_PORT'],'-S','127.0.0.1:'+str(port),'-t',str(site),str(router)],site,port):
            def get(path):return fetch(port,path,opener=opener)
            token=re.search(rb'name="u5scrtytknfrfrms"[^>]*value="([^"]+)"',get('/u5admin/rename.php?name=homepage')[1]).group(1).decode()
            def post(path,values):
                values=dict(values,u5scrtytknfrfrms=token)
                status,body,_=fetch(port,path,data=urllib.parse.urlencode(values).encode(),opener=opener)
                check(status==200 and b'Fatal error' not in body,path.split('?')[0])
            post('/u5admin/new2.php?typ=p',{'name':'aclpage'})
            post('/u5admin/new2.php?typ=f',{'name':'aclfile'})
            def save(logins,content):
                form={'page':'aclpage','name':'aclpage','coco':'2147483646','hidden':'0','ishomepage':'0','logins':logins}
                for lang in range(1,6):
                    for f in ('content','title','key','desc'): form[f+'_'+str(lang)]=''
                form['content_5']=content
                post('/u5admin/savepage.php?i=i1',form)
            save('?reader:example;','[label:aclfile?view=1]')
            boundary='u5-provision-test-boundary'
            part=lambda name,value:('--'+boundary+'\r\nContent-Disposition: form-data; name="'+name+'"\r\n\r\n'+value+'\r\n').encode()
            data=part('name','aclfile')+part('u5scrtytknfrfrms',token)+('--'+boundary+'\r\nContent-Disposition: form-data; name="userfile"; filename="sample.pdf"\r\nContent-Type: application/pdf\r\n\r\n%PDF-1.4 fixture\r\n--'+boundary+'--\r\n').encode()
            upload_form=get('/u5admin/sendfile.php?name=aclfile&l=_de&typ=f')[1]
            action=re.search(rb'action="(sendfile2\.php[^"]+)"',upload_form).group(1).decode().replace('&amp;','&')
            result=fetch(port,'/u5admin/'+action,{'Content-Type':'multipart/form-data; boundary='+boundary},data,opener)
            file=site/'r/aclfile/aclfile_de.pdf';pw=site/'r/aclfile/.htpasswd'
            check(result[0]==200 and file.is_file() and 'reader:' in pw.read_text(),'first multilingual HTTP upload inherits earlier private reference')
            save('','[aclfile]')
            check(not pw.exists(),'public edit publishes file permissions')
            save('?reader:example;','[aclfile]')
            check('reader:' in pw.read_text(),'withdrawal of public access applied by successful save')
            post('/u5admin/copy2.php?name=aclpage',{'name':'aclcopy'})
            check('reader:' in pw.read_text(),'copy retains group')
            # Leave the file private for the actual web server below.
        apache=os.environ.get('U5_TEST_APACHE')
        if apache:
            apache_port=freeport()
            mods=Path(os.environ.get('U5_TEST_APACHE_MODULES','/usr/lib/apache2/modules'))
            conf=tmp/'apache.conf'
            modules={'mpm_prefork':'mpm_prefork','authz_core':'authz_core','authz_host':'authz_host','access_compat':'access_compat','authn_core':'authn_core','auth_basic':'auth_basic','authn_file':'authn_file','authz_user':'authz_user'}
            conf.write_text('\n'.join([f'ServerRoot "{tmp}"',f'PidFile "{tmp}/http.pid"',f'ErrorLog "{tmp}/apache.log"','ServerName localhost',f'Listen 127.0.0.1:{apache_port}',f'DocumentRoot "{site}"']+
                [f'LoadModule {key}_module "{mods}/mod_{value}.so"' for key,value in modules.items()]+[f'<Directory "{site}">','AllowOverride All','Require all granted','</Directory>']))
            with server([apache,'-X','-f',str(conf)],site,apache_port):
                status,body,_=fetch(apache_port,'/r/aclfile/aclfile_de.pdf')
                check(status==401 and b'%PDF' not in body,'Apache enforces private file on direct URL')
                reader='Basic '+base64.b64encode(b'reader:example').decode()
                status,body,_=fetch(apache_port,'/r/aclfile/aclfile_de.pdf',{'Authorization':reader})
                check(status==200 and body.startswith(b'%PDF'),'Apache serves the file to an authorised reader')
    finally:
        subprocess.run(setup+['cleanup',db],check=True)
