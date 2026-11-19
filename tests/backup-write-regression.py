"""Real backup endpoints yield to source changes; editor writes never wait."""
from pathlib import Path
from concurrent.futures import ThreadPoolExecutor
import base64, hashlib, http.cookiejar, json, os, re, shutil, signal, socket, subprocess, tempfile, time
import urllib.error, urllib.parse, urllib.request
from fixture_environment import ensure_fixture_environment

ensure_fixture_environment()
root=Path(__file__).resolve().parent.parent
php=[os.environ.get('U5_TEST_PHP','php')]+json.loads(os.environ.get('U5_TEST_PHP_ARGS','[]'))
php+=['-d','mysqli.default_port='+os.environ['U5CMS_TEST_PORT'],'-d','opcache.enable=0','-d','display_errors=0']
checks=0
def check(ok,label):
    global checks
    checks+=1
    if not ok:raise AssertionError(label)
def wait_for(predicate,label):
    until=time.monotonic()+10
    while time.monotonic()<until:
        if predicate():return
        time.sleep(.025)
    raise AssertionError('Timed out: '+label)

with tempfile.TemporaryDirectory(prefix='u5-backup-write-') as directory:
    tmp=Path(directory);site=tmp/'site'
    shutil.copytree(root,site,ignore=shutil.ignore_patterns('.git','qa'))
    database=json.loads(subprocess.check_output(php+[str(root/'tests/rename-web-fixture.php'),str(site)],text=True))['database']
    helper=tmp/'query.php'
    helper.write_text('''<?php
require $argv[1].'/config.php';
if ($host!=='127.0.0.1' || !preg_match('/^u5rename_web_[a-f0-9]+$/D',$db)) exit(1);
$c=new mysqli($host,$username,$password,$db);$r=$c->query($argv[2]);echo json_encode($r===true ? true : $r->fetch_all(MYSQLI_ASSOC));
''')
    def sql(query):return json.loads(subprocess.check_output(php+[str(helper),str(site),query],text=True))
    sql("INSERT INTO resources(name,typ,deleted,lastmut,operator,content_1) VALUES('backuppage','p',0,100,'original','BEFORE')")
    log=(tmp/'server.log').open('w');servers=[];origins=[]
    # Two independent HTTP servers guarantee that the actual save runs while
    # the actual backup endpoint is paused before publishing its new snapshot.
    for _ in range(2):
        with socket.socket() as probe:probe.bind(('127.0.0.1',0));port=probe.getsockname()[1]
        env=dict(os.environ);env.pop('PHP_CLI_SERVER_WORKERS',None)
        servers.append(subprocess.Popen(php+['-S','127.0.0.1:'+str(port),'-t',str(site)],cwd=site,env=env,stdout=log,stderr=log))
        origins.append('http://127.0.0.1:'+str(port))
    auth='Basic '+base64.b64encode(('auditadmin:'+os.environ['U5CMS_FIXTURE_PASSWORD']).encode()).decode()
    def request(path,form=None,origin=None):
        req=urllib.request.Request((origin or origins[0])+path,data=urllib.parse.urlencode(form).encode() if form is not None else None,headers={'Authorization':auth})
        try:
            with urllib.request.urlopen(req,timeout=20) as response:return response.status,response.read().decode('cp1252')
        except urllib.error.HTTPError as error:return error.code,error.read().decode('cp1252')
    def ready(origin):
        try:return request('/u5admin/rename.php?name=backuppage',origin=origin)[0]==200
        except OSError:return False
    try:
        for origin in origins:wait_for(lambda:ready(origin),'independent HTTP fixture')
        code,form=request('/u5admin/rename.php?name=backuppage')
        token=re.search(r'name="u5scrtytknfrfrms"[^>]*value="([^"]+)"',form).group(1)
        zip_available=subprocess.check_output(php+['-r',"echo class_exists('ZipArchive') ? 'yes' : 'no';"],text=True)=='yes'
        check(zip_available or os.environ.get('U5CMS_ALLOW_NO_ZIP_BACKUP_TEST')=='1','real ZIP support is required unless the local test boundary is explicit')
        routes=['backupdb.php']+(['backupfiles.php'] if zip_available else [])
        generation=site/'fileversions/resource-write-generation.php'
        generation.unlink(missing_ok=True);generation.mkdir()
        try:
            current=sql("SELECT * FROM resources WHERE name='backuppage' AND deleted=0")[0]
            post={'name':'backuppage','page':'backuppage','coco':current['lastmut'],'u5scrtytknfrfrms':token,'ishomepage':'0','hidden':'0','logins':''}
            for language in range(1,6):
                for field in ['content','title','desc','key']:post[field+'_'+str(language)]=current[field+'_'+str(language)] or ''
            post['content_1']='SOURCE_WITH_UNWRITABLE_GENERATION'
            code,_=request('/u5admin/savepage.php?i=i1',post)
            check(code==200 and sql("SELECT content_1 FROM resources WHERE name='backuppage'")[0]['content_1']==post['content_1'],'source save remains available if ancillary generation publication fails')
            code,body=request('/u5admin/backupdb.php')
            check(code==500 and 'Cannot track a consistent backup' in body,'backup fails explicitly when it cannot track a consistent source')
        finally:generation.rmdir()
        for route in routes:
            path=site/'u5admin'/route;original=path.read_bytes()
            check(b'u5ResourceBackupFinish' in original,'actual backup endpoint checks source generation: '+route)
            code,body=request('/u5admin/'+route)
            check(code==200 and 'Source content changed' not in body,'quiescent backup succeeds: '+route)
            output=site/'fileversions'
            def outputs():
                return list((output/'_dbbackup').glob('*')) if route=='backupdb.php' else list(output.glob('*_FilesExclCMSsys.zip'))
            check(bool(outputs()),'actual backup output exists: '+route)
            previous={str(file):hashlib.sha256(file.read_bytes()).hexdigest() for file in outputs()}
            ready_file=tmp/'backup-ready';go_file=tmp/'backup-go'
            barrier=("file_put_contents("+repr(str(ready_file))+",'1');$u5Until=microtime(true)+10;while(microtime(true)<$u5Until){clearstatcache(true,"+repr(str(go_file))+");if(is_file("+repr(str(go_file))+"))break;usleep(10000);}").encode()
            # Only the temporary fixture gets a deterministic pre-publication barrier.
            path.write_bytes(original.replace(b'u5ResourceBackupFinish(',barrier+b'u5ResourceBackupFinish(',1))
            try:
                with ThreadPoolExecutor(max_workers=1) as pool:
                    future=pool.submit(request,'/u5admin/'+route,None,origins[1])
                    wait_for(ready_file.is_file,'actual backup output ready')
                    current=sql("SELECT * FROM resources WHERE name='backuppage' AND deleted=0")[0]
                    post={'name':'backuppage','page':'backuppage','coco':current['lastmut'],'u5scrtytknfrfrms':token,'ishomepage':'0','hidden':'0','logins':''}
                    for language in range(1,6):
                        for field in ['content','title','desc','key']:post[field+'_'+str(language)]=current[field+'_'+str(language)] or ''
                    post['content_1']='DURING_'+route
                    start=time.monotonic();code,saved=request('/u5admin/savepage.php?i=i1',post)
                    check(code==200 and 'saved backuppage' in saved and time.monotonic()-start<2,'real save remains prompt during '+route)
                    check(sql("SELECT content_1 FROM resources WHERE name='backuppage'")[0]['content_1']==post['content_1'],'source change persisted during backup')
                    go_file.touch();code,body=future.result(timeout=15)
                check(code==409 and 'Source content changed during the backup' in body,'changed backup explains refusal instead of publishing success')
                check(previous=={str(file):hashlib.sha256(file.read_bytes()).hexdigest() for file in outputs()},'rejected backup preserves every previous valid backup byte and publishes no mixed snapshot')
            finally:
                path.write_bytes(original);ready_file.unlink(missing_ok=True);go_file.unlink(missing_ok=True)
            code,body=request('/u5admin/'+route)
            check(code==200 and bool(outputs()),'quiescent retry succeeds after source change: '+route)
        print('Backup/write checks:',checks,'passed (actual database'+('/ZIP' if zip_available else '; ZIP unavailable locally')+' backup, concurrent authenticated save, previous backup preservation and retry).')
    except Exception:
        log.flush();print((tmp/'server.log').read_text()[-3500:]);raise
    finally:
        for server in servers:server.terminate();server.wait(timeout=10)
        log.close();subprocess.run(php+[str(root/'tests/rename-web-fixture.php'),'cleanup',database],check=True)
