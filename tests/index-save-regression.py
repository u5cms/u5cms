"""Actual save without browser children, named indexing and competing locks.

Uses a private copied CMS and a disposable database; never production.
"""
from pathlib import Path
from contextlib import contextmanager
import base64, fcntl, http.cookiejar, json, os, re, select, shutil, socket
import subprocess, tempfile, time, urllib.error, urllib.parse, urllib.request
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
    def request(path,data=None,headers=None):
        req=urllib.request.Request(origin+path,data=urllib.parse.urlencode(data).encode() if data is not None else None,headers=headers or {})
        try:
            with opener.open(req,timeout=12) as r:return r.status,r.read().decode('cp1252')
        except urllib.error.HTTPError as e:return e.code,e.read().decode('cp1252')
    @contextmanager
    def held_file(path):
        with path.open('a+') as f:
            fcntl.flock(f,fcntl.LOCK_EX|fcntl.LOCK_NB)
            yield
            fcntl.flock(f,fcntl.LOCK_UN)
    @contextmanager
    def held_table():
        p=subprocess.Popen(php+[str(helper),str(site),'hold'],stdin=subprocess.PIPE,stdout=subprocess.PIPE)
        try:
            check(bool(select.select([p.stdout],[],[],5)[0]) and p.stdout.readline()==b'ready\n','real table blocker ready')
            yield
        finally:p.stdin.close();p.wait(timeout=10)
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
        status,body=request('/u5admin/savepage.php?i=i1',post)
        check(status==200 and 'saved saveindextest' in body,'real editor save completed')
        # urllib never requests child frames: this models closing the result
        # window after receiving the saved response and before iframe execution.
        check(dirty.is_file() and int(dirty.read_text())>=int(time.time())-10,'source save marks index dirty without browser children')
        saved=row()
        check(all(saved['content_'+str(n)]=='NEW_SAVE_TOKEN_'+str(n) for n in range(1,6)),'all five source fields persisted')
        check(marker.read_text()=='100','save does not declare a complete full index')
        # A rapid second save can share the first save's integer timestamp.
        post['coco']=saved['lastmut']
        post['content_1']='LATEST_SAVE_TOKEN_1'
        status,body=request('/u5admin/savepage.php?i=i1',post)
        check(status==200 and row()['content_1']=='LATEST_SAVE_TOKEN_1','rapid second source save persists')
        status,_=request('/indexer.php?n=saveindextest')
        check(status==200,'named index succeeds')
        indexed=row()
        check('LATEST_SAVE_TOKEN_1' in indexed['search_1'] and all('NEW_SAVE_TOKEN_'+str(n) in indexed['search_'+str(n)] for n in range(2,6)),'named index renders latest saved versions in all five languages')
        check(marker.read_text()=='100','named index retains full-index watermark')
        for lockname in ['indexerrunning.txt','resource-rename.lock.php']:
            with held_file(state/lockname):
                status,body=request('/indexer.php?n=saveindextest')
                check(status==409 and 'u5indexretry=1' in body,'named request defers at '+lockname)
                check(row()==indexed,'busy named request changes no resource')
                status,body=request('/indexer.php?n=saveindextest',headers={'X-U5-Background-Work':'1'})
                quiet=json.loads(body)
                check(status==409 and quiet['state']=='wait' and quiet['delay']==10000 and quiet['next']=='?n=saveindextest&u5indexretry=1','quiet named retry retains the same lock policy at '+lockname)
            with held_file(state/lockname):
                status,body=request('/indexer.php?n=saveindextest&u5indexretry=3')
                check(status==409 and '<script>' not in body,'browser retries bounded at '+lockname)
                status,body=request('/indexer.php?n=saveindextest&u5indexretry=3',headers={'X-U5-Background-Work':'1'})
                check(status==409 and json.loads(body)['state']=='pending','quiet named retries are bounded at '+lockname)
        with held_file(state/'codeassets-12.11.0.lock.php'):
            post['coco']=indexed['lastmut'];post['content_1']='SAVE_DURING_MAINTENANCE'
            started=time.monotonic();status,body=request('/u5admin/savepage.php?i=i1',post)
            check(status==200 and 'saved saveindextest' in body and time.monotonic()-started<2,'maintenance mutex does not delay or refuse source save')
            check(row()['content_1']=='SAVE_DURING_MAINTENANCE','save under maintenance mutex persists exactly')
            status,_=request('/indexer.php?n=saveindextest')
            check(status==200,'maintenance mutex does not hold indexing hostage')
        indexed=row()
        with held_file(state/'resource-rename.lock.php'):
            post['coco']=indexed['lastmut'];post['content_1']='MUST_NOT_REPLACE'
            status,body=request('/u5admin/savepage.php?i=i1',post)
            check(status==409 and 'editor contents remain available' in body and row()==indexed,'only live Rename gate refuses competing save without losing data')
            status,body=request('/indexer.php')
            check(status==409 and 'u5indexcontinue' in body,'full batch yields to live Rename')
        progress=json.loads((state/'indexer-progress.php').read_text().split('\n',1)[1])
        check(progress['failures']==0 and progress['next']>int(time.time()) and marker.read_text()=='100','Rename contention pauses full work without success or failure backoff')
        pending=state/'resource-rename-synthetic.pending.php'
        pending.write_text('<?php exit; ?>\n')
        try:
            post['coco']=indexed['lastmut'];post['content_1']='SAVE_WITH_RECOVERY_PLAN'
            status,body=request('/u5admin/savepage.php?i=i1',post)
            check(status==200 and row()['content_1']=='SAVE_WITH_RECOVERY_PLAN','idle recovery plan does not block ordinary save')
            status,_=request('/indexer.php?n=saveindextest')
            check(status==200 and 'SAVE_WITH_RECOVERY_PLAN' in row()['search_1'],'idle recovery plan does not block current-source indexing')
            check(pending.read_text()=='<?php exit; ?>\n','save/index preserves recovery plan bytes')
            indexed=row()
        finally:pending.unlink()
        with held_table():
            started=time.monotonic();status,_=request('/indexer.php?n=saveindextest');elapsed=time.monotonic()-started
            check(status==500 and 3.5<elapsed<8,'named SQL wait is bounded on database server')
            waiters=subprocess.check_output(php+[str(helper),str(site),'waiters'],text=True)
            check(waiters=='0','named failure leaves no waiting SQL statement')
        for lockname in ['indexerrunning.txt','resource-rename.lock.php']:
            with held_file(state/lockname):check(True,'failure releases '+lockname)
        check(marker.read_text()=='100','failure retains full watermark')
        status,_=request('/indexer.php?n=saveindextest')
        check(status==200 and row()==indexed,'named retry is safe after SQL failure')
        # Editing a populated multilingual page must fit into a normal SQL packet
        # even when the existing text and the replacement are both substantial.
        packet=int(sql('SELECT @@max_allowed_packet AS n')[0]['n'])
        field_bytes=min(1150000,packet//12)
        for generation in ['A','B']:
            expected=[]
            for n in range(1,6):
                value=generation+str(n)
                expected.append(value)
                post['content_'+str(n)]=value+('x'*field_bytes)
            post['coco']=sql("SELECT lastmut FROM resources WHERE name='saveindextest' AND deleted=0")[0]['lastmut']
            status,body=request('/u5admin/savepage.php?i=i1',post)
            columns=','.join('LEFT(content_'+str(n)+',2) AS lang'+str(n) for n in range(1,6))
            actual=sql('SELECT '+columns+" FROM resources WHERE name='saveindextest' AND deleted=0")[0]
            check(status==200 and 'saved saveindextest' in body and [actual['lang'+str(n)] for n in range(1,6)]==expected,'large five-language save '+generation)
        print('Save/index coordination checks:',checks,'passed (real save without child frames, five languages, competing kernel locks, pending Rename, bounded SQL wait and retry).')
    finally:
        server.terminate();server.wait(timeout=10)
        subprocess.run(php+[str(root/'tests/rename-web-fixture.php'),'cleanup',database],check=True)
