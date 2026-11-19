"""Actual save without browser children, named indexing and competing locks.

Uses a private copied CMS and a disposable database; never production.
"""
from pathlib import Path
from contextlib import contextmanager
import base64, fcntl, http.cookiejar, json, os, re, select, shutil, socket
import subprocess, tempfile, time, urllib.error, urllib.parse, urllib.request
import sys
sys.path.insert(0,str(Path(os.environ.get('U5CMS_INDEX_SOURCE_ROOT',str(Path(__file__).resolve().parent.parent))).resolve()/'tests'))
from fixture_environment import ensure_fixture_environment

ensure_fixture_environment()
root=Path(os.environ.get('U5CMS_INDEX_SOURCE_ROOT',str(Path(__file__).resolve().parent.parent)))
php=[os.environ.get('U5_TEST_PHP','php')]+json.loads(os.environ.get('U5_TEST_PHP_ARGS','[]'))
php+=['-d','mysqli.default_port='+os.environ['U5CMS_TEST_PORT'],'-d','display_errors=0','-d','opcache.enable_cli=0']
checks=0
def check(ok,label):
    global checks
    checks+=1
    if not ok: raise AssertionError(label)

with tempfile.TemporaryDirectory(prefix='u5-save-index-') as directory:
    tmp=Path(directory);site=tmp/'site'
    shutil.copytree(root,site,ignore=shutil.ignore_patterns('.git','qa'))
    setup=subprocess.run(php+[str(root/'tests/rename-web-fixture.php'),str(site)],capture_output=True,text=True,check=True)
    database=json.loads(setup.stdout)['database']
    helper=tmp/'query.php'
    helper.write_text('''<?php
require $argv[1].'/config.php';
if ($host!=='127.0.0.1' || !preg_match('/^u5rename_web_[a-f0-9]+$/D',$db)) exit(1);
$c=new mysqli($host,$username,$password,$db);
if ($argv[2]==='hold') {
 $c->query('LOCK TABLES resources WRITE');echo "ready\\n";fflush(STDOUT);fgets(STDIN);$c->query('UNLOCK TABLES');
} elseif ($argv[2]==='waiters') {
 echo $c->query("SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE DB=DATABASE() AND ID<>CONNECTION_ID() AND STATE LIKE 'Waiting for%lock'")->fetch_row()[0];
} else {
 $r=$c->query($argv[2]);echo json_encode($r===true ? true : $r->fetch_all(MYSQLI_ASSOC));
}
''')
    def sql(query):
        return json.loads(subprocess.check_output(php+[str(helper),str(site),query],text=True))
    def row(): return sql("SELECT * FROM resources WHERE name='saveindextest' AND deleted=0")[0]
    sql("INSERT INTO resources(name,typ,deleted,hidden,lastmut,operator,content_1,title_1) VALUES('saveindextest','p',0,0,100,'original','OLD_INDEX_TOKEN','Original title')")
    with socket.socket() as s:s.bind(('127.0.0.1',0));port=s.getsockname()[1]
    origin='http://127.0.0.1:'+str(port)
    server=subprocess.Popen(php+['-S','127.0.0.1:'+str(port),'-t',str(site)],cwd=site,stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
    opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
    opener.addheaders=[('Authorization','Basic '+base64.b64encode(('auditadmin:'+os.environ['U5CMS_FIXTURE_PASSWORD']).encode()).decode())]
    def request(path,data=None):
        req=urllib.request.Request(origin+path,data=urllib.parse.urlencode(data).encode() if data is not None else None)
        try:
            with opener.open(req,timeout=12) as r:return r.status,r.read().decode('cp1252')
        except urllib.error.HTTPError as e:return e.code,e.read().decode('cp1252')
    state=site/'fileversions';marker=state/'lastindex.txt';dirty=state/'lastsave.txt'
    try:
        for _ in range(100):
            try:
                with socket.create_connection(('127.0.0.1',port),.1):break
            except OSError:time.sleep(.05)
        status,form=request('/u5admin/rename.php?name=saveindextest')
        check(status==200,'authenticated backend reachable')
        token=re.search(r'name="u5scrtytknfrfrms"[^>]*value="([^"]+)"',form).group(1)
        marker.write_text('100');dirty.unlink(missing_ok=True)
        post={'page':'saveindextest','coco':'100','u5scrtytknfrfrms':token,'ishomepage':'0','hidden':'0','logins':''}
        for n in range(1,6):
            post['content_'+str(n)]='NEW_SAVE_TOKEN_'+str(n)
            post['title_'+str(n)]='Human title '+str(n)
            post['desc_'+str(n)]='';post['key_'+str(n)]=''
        with socket.socket() as sk: sk.bind(('127.0.0.1',0)); second=sk.getsockname()[1]
        indexFile=site/'indexer.php'
        text=indexFile.read_text(encoding='latin1')
        hook=os.environ.get('U5_AUDIT_INDEX_HOOK',"define('U5CMS_INDEX_REQUEST', true);")
        assert text.count(hook)==1
        indexFile.write_text(text.replace(hook,hook+"file_put_contents(__DIR__.'/ready-test','1'); for($i=0;$i<200 && !file_exists(__DIR__.'/continue-test');$i++) usleep(50000);"),encoding='latin1')
        server2=subprocess.Popen(php+['-S','127.0.0.1:'+str(second),'-t',str(site)],cwd=site,stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
        try:
            for _ in range(100):
                try:
                    with socket.create_connection(('127.0.0.1',second),.1):break
                except OSError:time.sleep(.05)
            from concurrent.futures import ThreadPoolExecutor
            with ThreadPoolExecutor(1) as pool:
                job=pool.submit(urllib.request.urlopen,'http://127.0.0.1:'+str(second)+'/indexer.php?n=saveindextest')
                for _ in range(120):
                    if (site/'ready-test').exists(): break
                    time.sleep(.05)
                assert (site/'ready-test').exists()
                try:
                    start=time.monotonic()
                    status,body=request('/u5admin/savepage.php?i=i1',post)
                    elapsed=time.monotonic()-start
                    fields=[row()['content_'+str(n)] for n in range(1,6)]
                    print(json.dumps({'save_http':status,'elapsed':round(elapsed,3),'five_saved':all(fields[n-1]==post['content_'+str(n)] for n in range(1,6)),'rename_pending':bool(list(state.glob('resource-rename-*.pending.php')))}),flush=True)
                finally:(site/'continue-test').touch()
                with job.result(timeout=15) as response: assert response.status==200
        finally: server2.terminate();server2.wait(timeout=10)
        returned=row()
        assert fields==[returned['content_'+str(n)] for n in range(1,6)]
        expected=int(os.environ.get('U5_AUDIT_EXPECT_HTTP','200'))
        assert status==expected,(status,expected)
        assert all((fields[n-1]==post['content_'+str(n)])==(expected==200) for n in range(1,6))
        assert not list(state.glob('resource-rename-*.pending.php'))
        if expected==200:
            status,_=request('/indexer.php?n=saveindextest')
            assert status==200
            indexed=row()
            assert all(post['content_'+str(n)] in indexed['search_'+str(n)] for n in range(1,6))
    finally:
        server.terminate();server.wait(timeout=10)
        subprocess.run(php+[str(root/'tests/rename-web-fixture.php'),'cleanup',database],check=True)
