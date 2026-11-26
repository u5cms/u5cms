"""Real concurrent saves and Rename, including a killed PHP Rename worker."""
from pathlib import Path
from concurrent.futures import ThreadPoolExecutor
import base64, fcntl, hashlib, http.cookiejar, json, os, re, select, shutil
import signal, socket, subprocess, tempfile, time, urllib.error, urllib.parse, urllib.request
from fixture_environment import ensure_fixture_environment
from code_editor_form import check_code_editor

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

with tempfile.TemporaryDirectory(prefix='u5-live-rename-') as directory:
    tmp=Path(directory);site=tmp/'site';state=site/'fileversions'
    shutil.copytree(root,site,ignore=shutil.ignore_patterns('.git','qa'))
    database=json.loads(subprocess.check_output(php+[str(root/'tests/rename-web-fixture.php'),str(site)],text=True))['database']
    control=tmp/'control';control.mkdir()
    helper=tmp/'query.php'
    helper.write_text('''<?php
require $argv[1].'/config.php';
if ($host!=='127.0.0.1' || !preg_match('/^u5rename_web_[a-f0-9]+$/D',$db)) exit(1);
$c=new mysqli($host,$username,$password,$db);$r=$c->query($argv[2]);
echo json_encode($r===true ? true : $r->fetch_all(MYSQLI_ASSOC));
''')
    def sql(query):return json.loads(subprocess.check_output(php+[str(helper),str(site),query],text=True))
    sql("INSERT INTO resources(name,typ,deleted,hidden,lastmut,operator,content_1,title_1) VALUES('activitypage','p',0,0,100,'original','ORIGINAL','Original title')")
    router=tmp/'router.php'
    router.write_text('''<?php
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
if ($path==='/u5admin/rename2.php') {
 function u5CodeMigrationTestCheckpoint($phase) {
  $control=getenv('U5_ACTIVITY_CONTROL');
  if ($phase!=='rename:templates' || !is_file($control.'/hold')) return;
  file_put_contents($control.'/ready',(string)getmypid());
  $until=microtime(true)+15;
  while (microtime(true)<$until) {clearstatcache(true,$control.'/hold');if (!is_file($control.'/hold')) break;usleep(20000);}
 }
}
$file=realpath(getenv('U5_ACTIVITY_SITE').$path);
if (!$file) {http_response_code(404);return;}
if (is_dir($file)) $file.='/index.php';
if (substr($file,-4)==='.php') {chdir(dirname($file));require $file;return;}
return false;
''')
    with socket.socket() as probe:probe.bind(('127.0.0.1',0));port=probe.getsockname()[1]
    base='http://127.0.0.1:'+str(port)
    env=dict(os.environ,PHP_CLI_SERVER_WORKERS='4',U5_ACTIVITY_SITE=str(site),U5_ACTIVITY_CONTROL=str(control))
    log=(tmp/'server.log').open('w')
    server=subprocess.Popen(php+['-S','127.0.0.1:'+str(port),'-t',str(site),str(router)],env=env,stdout=log,stderr=log,start_new_session=True)
    opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
    opener.addheaders=[('Authorization','Basic '+base64.b64encode(('auditadmin:'+os.environ['U5CMS_FIXTURE_PASSWORD']).encode()).decode())]
    def request(path,form=None,origin=None):
        req=urllib.request.Request((origin or base)+path,data=urllib.parse.urlencode(form,encoding='cp1252',errors='xmlcharrefreplace').encode() if form is not None else None)
        try:
            with opener.open(req,timeout=20) as response:return response.status,response.read().decode('cp1252')
        except urllib.error.HTTPError as error:return error.code,error.read().decode('cp1252')
    def row(name='activitypage'):return sql("SELECT * FROM resources WHERE name='"+name+"' AND deleted=0")[0]
    def save(text,revision=None,path='/u5admin/savepage.php?i=i1',name='activitypage',origin=None):
        current=row(name);form={'page':name,'name':name,'coco':current['lastmut'] if revision is None else revision,'u5scrtytknfrfrms':token,'ishomepage':'0','hidden':'0','logins':''}
        for language in range(1,6):
            for field in ['content','title','desc','key']:form[field+'_'+str(language)]=current[field+'_'+str(language)] or ''
        form['content_1']=text
        started=time.monotonic();code,body=request(path,form,origin)
        return code,body,time.monotonic()-started
    writer=None
    def reachable():
        try:
            with socket.create_connection(('127.0.0.1',port),.1):return True
        except OSError:return False
    wait_for(reachable,'HTTP fixture')
    try:
        check_code_editor(site, request, sql)
        code,form=request('/u5admin/rename.php?name=activitypage')
        check(code==200,'authenticated editor reachable')
        token=re.search(r'name="u5scrtytknfrfrms"[^>]*value="([^"]+)"',form).group(1)
        with (state/'codeassets-12.11.0.lock.php').open('a+') as old, (state/'indexerrunning.txt').open('a+') as index:
            fcntl.flock(old,fcntl.LOCK_EX|fcntl.LOCK_NB);fcntl.flock(index,fcntl.LOCK_EX|fcntl.LOCK_NB)
            code,body,elapsed=save('SAVE_DURING_INDEX_AND_BACKUP')
            check(code==200 and 'saved activitypage' in body and elapsed<2,'real save stays prompt under index and backup mutexes')
            check(row()['content_1']=='SAVE_DURING_INDEX_AND_BACKUP','save bytes persisted under background locks')
        config=site/'config.php';original_config=config.read_bytes()
        try:
            config.write_bytes(original_config+b"\n$checksaveversionconflictinbackend='all';\n")
            sql("UPDATE resources SET lastmut="+str(int(time.time())+1)+" WHERE name='activitypage'")
            previous=row()['lastmut'];code,body,_=save('REVISION_FIRST',previous)
            first=row();check(code==200 and int(first['lastmut'])>int(previous),'accepted save advances revision even within the same second')
            check(str(first['lastmut']) in body,'server acknowledges actual stored revision')
            code,body,_=save('REVISION_STALE',previous)
            check(code==200 and 'CONFLICT' in body and row()['content_1']=='REVISION_FIRST','stale second editor cannot silently overwrite first save')
            check(bool(sql("SELECT name FROM resources_log WHERE name='activitypage' AND content_1='REVISION_STALE' AND operator LIKE '%(!)'")),'stale submission is retained in text versions')
            config.write_bytes(original_config+b"\n$checksaveversionconflictinbackend='none';\n")
            code,body,_=save('POLICY_NONE',previous)
            check(code==200 and row()['content_1']=='POLICY_NONE','configured overwrite policy remains supported')
            config.write_bytes(original_config+b"\n$checksaveversionconflictinbackend='all';\n")
            page_impl=site/'u5admin/savepage.php';page_source=page_impl.read_bytes()
            if b'u5SaveResource(' in page_source:
                save_impl=site/'u5admin/resource-save.inc.php';needle=b'$set=array();$guard=array();'
            elif b'u5UpdateResource(' in page_source:
                save_impl=site/'u5admin/resourceupdate.inc.php';needle=b'for ($attempt=0;$attempt<3;$attempt++) {'
            else:
                save_impl=page_impl;needle=b'for ($u5Attempt=0;$u5Attempt<3;$u5Attempt++) {'
            original_impl=save_impl.read_bytes()
            barrier=b"$u5Control=getenv('U5_ACTIVITY_CONTROL');file_put_contents($u5Control.'/cas-ready-'.getmypid(),'1');$u5Until=microtime(true)+10;while(microtime(true)<$u5Until){clearstatcache(true,$u5Control.'/cas-go');if(is_file($u5Control.'/cas-go'))break;usleep(10000);}"
            check(original_impl.count(needle)==1,'actual source write barrier has one insertion point')
            save_impl.write_bytes(original_impl.replace(needle,barrier+needle))
            with socket.socket() as second_probe:second_probe.bind(('127.0.0.1',0));second_port=second_probe.getsockname()[1]
            second_env=dict(env);second_env.pop('PHP_CLI_SERVER_WORKERS',None)
            second_server=subprocess.Popen(php+['-S','127.0.0.1:'+str(second_port),'-t',str(site),str(router)],env=second_env,stdout=log,stderr=log,start_new_session=True)
            second_origin='http://127.0.0.1:'+str(second_port)
            def second_ready():
                try:return request('/u5admin/renaming.php',origin=second_origin)[0]==200
                except OSError:return False
            try:
                wait_for(second_ready,'independent second HTTP server')
                revision=row()['lastmut']
                with ThreadPoolExecutor(max_workers=2) as pool:
                    jobs=[pool.submit(save,text,revision,origin=origin) for text,origin in [('CONCURRENT_A',base),('CONCURRENT_B',second_origin)]]
                    wait_for(lambda:len(list(control.glob('cas-ready-*')))==2,'two real writers read same revision')
                    (control/'cas-go').touch()
                    results=[job.result(timeout=10) for job in jobs]
                check(all(result[0]==200 for result in results),'both submissions receive an explicit result')
                check(sum('CONFLICT' in result[1] for result in results)==1,'atomic same-resource race reports exactly one conflict')
                check(row()['content_1'] in ['CONCURRENT_A','CONCURRENT_B'],'one complete source version remains active')
                check(len(sql("SELECT name FROM resources_log WHERE name='activitypage' AND content_1 IN ('CONCURRENT_A','CONCURRENT_B')"))==2,'both concurrent submissions survive in history')
                for marker in control.glob('cas-ready-*'):
                    marker.unlink()
                (control/'cas-go').unlink()
                with ThreadPoolExecutor(max_workers=1) as pool:
                    pending=pool.submit(save,'EDITOR_DRAFT',row()['lastmut'],origin=base)
                    wait_for(lambda:any(control.glob('cas-ready-*')),'editor has read the original row')
                    sql("UPDATE resources SET content_1='REVISED_TEXT' WHERE name='activitypage'")
                    (control/'cas-go').touch()
                    status,body,_=pending.result(timeout=10)
                check(status==200 and 'CONFLICT' in body and row()['content_1']=='REVISED_TEXT',
                      'an edit with the same timestamp survives a competing editor save')
                for policy in ['none','foreign']:
                    config.write_bytes(original_config+("\n$checksaveversionconflictinbackend='"+policy+"';\n").encode())
                    for marker in control.glob('cas-ready-*'):marker.unlink()
                    (control/'cas-go').unlink()
                    revision=row()['lastmut'];values=['OVERWRITE_'+policy+'_A','OVERWRITE_'+policy+'_B']
                    with ThreadPoolExecutor(max_workers=2) as pool:
                        jobs=[pool.submit(save,text,revision,origin=origin) for text,origin in zip(values,[base,second_origin])]
                        wait_for(lambda:len(list(control.glob('cas-ready-*')))==2,'concurrent configured overwrite policy')
                        (control/'cas-go').touch();results=[job.result(timeout=10) for job in jobs]
                    check(all(result[0]==200 and 'CONFLICT' not in result[1] for result in results),'concurrent '+policy+' policy preserves permitted overwrite behaviour')
                    check(row()['content_1'] in values and len(sql("SELECT name FROM resources_log WHERE name='activitypage' AND content_1 IN ('"+values[0]+"','"+values[1]+"')"))==2,'both '+policy+' submissions persist in history with one complete active version')
            finally:
                save_impl.write_bytes(original_impl);second_server.terminate();second_server.wait(timeout=10)

        finally:config.write_bytes(original_config)
        unicode_text='Grüezi € &amp; &#x1F600;'
        code,body,_=save(unicode_text)
        check(code==200 and row()['content_1']==unicode_text,'actual Windows-1252 form preserves accents, euro and entities in UTF-8 DB')
        for table in ['resources','resources_log']:sql('ALTER TABLE '+table+' MODIFY content_2 MEDIUMTEXT CHARACTER SET latin1')
        code,body,_=save(unicode_text,path='/u5admin/meta2.php?typ=p')
        check(code==200 and row()['content_1']==unicode_text,'metadata save preserves real form bytes with mixed column charsets')
        code_name='activity!css' if (site/'codeassets.inc.php').exists() else 'cssactivity'
        sql("INSERT INTO resources(name,typ,deleted,lastmut,operator) VALUES('"+code_name+"','c',0,100,'original')")
        css='body{color:red} /* '+unicode_text+' */'
        code,body,_=save(css,path='/u5admin/coding2.php?typ=c',name=code_name)
        check(code==200 and row(code_name)['content_1']==css,'actual code save and export retain bytes and do not require global write mutex: '+repr((code,row(code_name)['content_1'],body[-600:])))
        sql("INSERT INTO resources(name,typ,deleted,lastmut,operator) VALUES('cafépage','p',0,100,'original')")
        code,body,_=save('Legacy name: Grüezi',name='cafépage')
        check(code==200 and row('cafépage')['content_1']=='Legacy name: Grüezi','non-ASCII legacy resource names retain connection-charset lookup and save')
        for table in ['resources','resources_log']:sql('ALTER TABLE '+table+' MODIFY lastmut BIGINT')
        sql("UPDATE resources SET lastmut=3000000000 WHERE name='cafépage'")
        code,body,_=save('Configured revision capacity',name='cafépage')
        check(code==200 and int(row('cafépage')['lastmut'])==3000000001,'save respects enlarged revision column without an artificial signed-INT gate')
        dirty=state/'lastsave.txt';dirty.unlink(missing_ok=True);dirty.mkdir()
        try:
            code,body,_=save('SOURCE_SURVIVES_MARKER_ERROR')
            check(code==200 and row()['content_1']=='SOURCE_SURVIVES_MARKER_ERROR','ancillary index marker failure does not reject real source save')
        finally:dirty.rmdir()
        code,body,_=save('SEARCH_MARKER_RESTORED')
        check(code==200 and dirty.is_file(),'next save repairs index dirty tracking after marker path is restored')
        hold=tmp/'writer.php'
        hold.write_text("<?php require $argv[1].'/u5admin/resource-mutation.inc.php';echo \"ready\\n\";fflush(STDOUT);fgets(STDIN);")
        writer=subprocess.Popen(php+[str(hold),str(site)],stdin=subprocess.PIPE,stdout=subprocess.PIPE,stderr=subprocess.PIPE)
        check(bool(select.select([writer.stdout],[],[],5)[0]) and writer.stdout.readline()==b'ready\n','another writer really owns a shared gate')
        code,body,elapsed=save('CONCURRENT_WRITER_SAVE')
        check(code==200 and elapsed<2 and row()['content_1']=='CONCURRENT_WRITER_SAVE','ordinary writer does not serialize another actual save')
        code,_=request('/indexer.php?n=activitypage')
        check(code==409,'indexer yields to active source writer')
        writer.kill();writer.wait(timeout=5);writer=None
        code,_=request('/indexer.php?n=activitypage')
        check(code==200,'killed writer leaves no persistent index blocker')
        for mode,crash in [('live',False),('crash',True)]:
            subprocess.run(php+[str(root/'tests/rename-web-support.php'),str(site),'seed',mode],check=True,capture_output=True)
            old='renameold'+mode;new='renamenew'+mode
            (state/'lastsave.txt').write_text('100')
            (control/'hold').touch();(control/'ready').unlink(missing_ok=True)
            with ThreadPoolExecutor(max_workers=1) as pool:
                future=pool.submit(request,'/u5admin/rename2.php?name='+old,{'name':new,'ulinks':'yes','u5scrtytknfrfrms':token})
                wait_for(lambda:(control/'ready').is_file(),'actual Rename checkpoint')
                check(int((state/'lastsave.txt').read_text())>100,'Rename marks search dirty before an interrupted publication can occur')
                code,body=request('/u5admin/renaming.php')
                check(code==200 and 'Rename in progress' in body and old in body and new in body and 'Elapsed:' in body,'live status names actual source/target and elapsed time')
                before=row();code,body,elapsed=save('MUST_NOT_SAVE_DURING_RENAME')
                check(code==409 and old in body and new in body and 'Phase:' in body and elapsed<2,'live Rename alone promptly explains refused save')
                check(row()==before,'refused save preserves exact source row')
                code,_=request('/indexer.php?n=activitypage');check(code==409,'index work yields to genuine Rename')
                if crash:os.kill(int((control/'ready').read_text()),signal.SIGKILL)
                (control/'hold').unlink()
                try:
                    result=future.result(timeout=10)
                    check(not crash and result[0]==200,'normal Rename completes successfully')
                except (OSError,ValueError):check(crash,'killed Rename connection ends')
            if crash:
                with (state/'resource-rename.lock.php').open('r') as gate:
                    fcntl.flock(gate,fcntl.LOCK_SH|fcntl.LOCK_NB)
                    check(True,'kernel released killed Rename gate before any HTTP fixture restart')
                # PHP's development-server parent also accepts requests. If it
                # was the killed worker, restart this test HTTP pool only. Keep
                # every CMS file, journal, lock inode and DB row untouched.
                try:os.killpg(server.pid,signal.SIGTERM)
                except ProcessLookupError:pass
                server.wait(timeout=10)
                wait_for(lambda:not reachable(),'old disposable HTTP pool stopping')
                server=subprocess.Popen(php+['-S','127.0.0.1:'+str(port),'-t',str(site),str(router)],env=env,stdout=log,stderr=log,start_new_session=True)
                def replacement_ready():
                    try:return request('/u5admin/renaming.php')[0]==200
                    except OSError:return False
                wait_for(replacement_ready,'replacement HTTP fixture after SIGKILL')
            code,body=request('/u5admin/renaming.php')
            check('Rename in progress' not in body,'finished or killed process cannot appear as live Rename')
            plans=list(state.glob('resource-rename-*.pending.php'))
            hashes={p:hashlib.sha256(p.read_bytes()).hexdigest() for p in plans}
            check(bool(plans)==crash,'only interrupted Rename retains recovery plan')
            code,body,elapsed=save('AFTER_RENAME_'+mode)
            check(code==200 and elapsed<2 and row()['content_1']=='AFTER_RENAME_'+mode,'save immediately available after normal completion or SIGKILL')
            check(all(hashlib.sha256(p.read_bytes()).hexdigest()==h for p,h in hashes.items()),'ordinary save leaves immutable recovery plan untouched')
            if crash:
                check('These are recovery plans, not locks' in body or 'saved activitypage' in body,'save result available with recovery plan')
                code,body=request('/u5admin/rename2.php?name='+old,{'name':new,'ulinks':'yes','u5scrtytknfrfrms':token})
                check(code==200 and 'Files, references and history have completed' in body,'killed Rename resumes exact plan and finishes')
                check(not list(state.glob('resource-rename-*.pending.php')),'successful recovery finalizes plan')
        print('Resource activity checks:',checks,'passed (real concurrent writers, background locks, live Rename, SIGKILL, status and exact recovery).')
    except Exception:
        log.flush();print((tmp/'server.log').read_text()[-2500:]);raise
    finally:
        if writer:writer.kill();writer.wait(timeout=5)
        try:os.killpg(server.pid,signal.SIGTERM)
        except ProcessLookupError:pass
        server.wait(timeout=10);log.close()
        subprocess.run(php+[str(root/'tests/rename-web-fixture.php'),'cleanup',database],check=True)
