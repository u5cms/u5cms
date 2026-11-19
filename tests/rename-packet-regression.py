"""Actual multilingual Rename and guarded recovery with a 4 MiB SQL packet.

Run only on the dedicated disposable test database instance: the original global
packet setting is restored in finally. No production installation is used.
"""
from pathlib import Path
import base64,hashlib,json,os,re,shutil,socket,subprocess,tempfile,time
import urllib.error,urllib.parse,urllib.request
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
with tempfile.TemporaryDirectory(prefix='u5-rename-packet-') as directory:
    tmp=Path(directory);site=tmp/'site';shutil.copytree(root,site,ignore=shutil.ignore_patterns('.git','qa'))
    database=json.loads(subprocess.check_output(php+[str(root/'tests/rename-web-fixture.php'),str(site)],text=True))['database']
    helper=tmp/'query.php';helper.write_text('''<?php
$_SERVER['REQUEST_URI']='/';$_SERVER['HTTP_HOST']='fixture.invalid';require $argv[1].'/config.php';
if ($host!=='127.0.0.1' || !preg_match('/^u5rename_web_[a-f0-9]+$/D',$db)) exit(1);
$c=new mysqli($host,$username,$password,$db);$c->set_charset('utf8mb4');$r=$c->query($argv[2]);echo json_encode($r===true ? true : $r->fetch_all(MYSQLI_ASSOC));
''')
    def sql(query):return json.loads(subprocess.check_output(php+[str(helper),str(site),query],text=True))
    original_packet=int(sql('SELECT @@GLOBAL.max_allowed_packet AS n')[0]['n']);server=None;log=(tmp/'server.log').open('w')
    try:
        sql('SET GLOBAL max_allowed_packet=4194304')
        sql("INSERT INTO resources(name,typ,deleted,hidden,lastmut,operator) VALUES('packetold','p',0,0,100,'original'),('packetref','p',0,0,100,'original')")
        bodies={n:'Language '+str(n)+' '+('x'*350000)+' [packetold] [[packetold]] &amp; literal' for n in range(1,6)}
        for n in range(1,6):sql("UPDATE resources SET content_"+str(n)+"=CONCAT('Language "+str(n)+" ',REPEAT('x',350000),' [packetold] [[packetold]] &amp; literal') WHERE name='packetref'")
        fields=','.join(['name','typ','deleted','hidden','lastmut','operator']+['content_'+str(n) for n in range(1,6)])
        sql('INSERT INTO resources_log ('+fields+') SELECT '+fields+" FROM resources WHERE name='packetref'")
        check(int(sql('SELECT @@max_allowed_packet AS n')[0]['n'])==4194304,'dedicated fixture really uses a 4 MiB SQL packet')
        with socket.socket() as probe:probe.bind(('127.0.0.1',0));port=probe.getsockname()[1]
        origin='http://127.0.0.1:'+str(port);auth='Basic '+base64.b64encode(('auditadmin:'+os.environ['U5CMS_FIXTURE_PASSWORD']).encode()).decode()
        env=dict(os.environ);env.pop('PHP_CLI_SERVER_WORKERS',None)
        server=subprocess.Popen(php+['-S','127.0.0.1:'+str(port),'-t',str(site)],cwd=site,env=env,stdout=log,stderr=log)
        def request(path,form=None):
            req=urllib.request.Request(origin+path,data=urllib.parse.urlencode(form).encode() if form is not None else None,headers={'Authorization':auth})
            try:
                with urllib.request.urlopen(req,timeout=30) as response:return response.status,response.read().decode('cp1252')
            except urllib.error.HTTPError as error:return error.code,error.read().decode('cp1252')
        for _ in range(100):
            try:code,form=request('/u5admin/rename.php?name=packetold');break
            except OSError:time.sleep(.05)
        else:raise AssertionError('HTTP fixture did not start')
        token=re.search(r'name="u5scrtytknfrfrms"[^>]*value="([^"]+)"',form).group(1)
        def rename(old,new):return request('/u5admin/rename2.php?name='+old,{'name':new,'ulinks':'yes','u5scrtytknfrfrms':token})
        def conserved(target,reference='packetref',expected_bodies=None,old='packetold'):
            if expected_bodies is None:expected_bodies=bodies
            for table in ['resources','resources_log']:
                fields=','.join('SHA2(CAST(content_'+str(n)+' AS BINARY),256) AS h'+str(n) for n in range(1,6))
                rows=sql('SELECT '+fields+' FROM '+table+" WHERE name='"+reference+"'")
                check(bool(rows),'large reference row retained in '+table)
                for n in range(1,6):
                    expected=hashlib.sha256(expected_bodies[n].replace('['+old+'] [['+old+']]','['+target+'] [['+old+']]').encode()).hexdigest()
                    check(all(row['h'+str(n)]==expected for row in rows),'exact large language '+str(n)+' source/history conservation in '+table)
        for old,new in [('packetold','packetnew'),('packetnew','packetold')]:
            code,body=rename(old,new)
            check(code==200 and 'has not completed' not in body,'actual large multilingual Rename succeeds: '+old+' to '+new+'; '+body[-500:])
            conserved(new)
        if (site/'codeassets.inc.php').exists():
            code_old='packetcssold!css';code_new='packetcssnew!css'
            sql("INSERT INTO resources(name,typ,deleted,hidden,lastmut,operator,content_1) VALUES('"+code_old+"','c',0,0,100,'original','body{}'),('packetcref','p',0,0,100,'original','')")
            code_bodies={n:'Code language '+str(n)+' '+('y'*350000)+' ['+code_old+'] [['+code_old+']] &amp; literal' for n in range(1,6)}
            for n in range(1,6):sql("UPDATE resources SET content_"+str(n)+"=CONCAT('Code language "+str(n)+" ',REPEAT('y',350000),' ["+code_old+"] [["+code_old+"]] &amp; literal') WHERE name='packetcref'")
            sql('INSERT INTO resources_log ('+fields+') SELECT '+fields+" FROM resources WHERE name='packetcref'")
            code,body=rename(code_old,code_new)
            check(code==200 and 'has not completed' not in body,'actual typed-code Rename updates large multilingual dependants')
            conserved(code_new,'packetcref',code_bodies,code_old)
            check((site/'r/packetcssnew.css').is_file(),'typed-code Rename publishes its matching file')
        # Inject a direct, uncoordinated database edit after the immutable plan
        # was saved. The large-value digest must reject even a one-byte change.
        flag=tmp/'interfere';flag.touch();path=site/'u5admin/resource-rename.inc.php';original=path.read_bytes()
        hook='u5RenameStorageTestCheckpoint' if (site/'u5admin/resource-rename-support.inc.php').exists() else 'u5CodeMigrationTestCheckpoint'
        injection=("\nfunction "+hook+"($phase) {if($phase==='rename:planned' && is_file("+repr(str(flag))+") && mysql_query(\"UPDATE resources SET content_1=CONCAT(content_1,'!') WHERE name='packetref'\")===false) throw new RuntimeException('Fixture interference failed.');}\n").encode()
        path.write_bytes(original.replace(b'<?php',b'<?php'+injection,1))
        code,body=rename('packetold','packetnew')
        check(code==500 and 'changed during migration' in body,'large snapshot guard refuses a later one-byte edit')
        check(bool(list((site/'fileversions').glob('resource-rename-*.pending.php'))),'failed large Rename retains its recovery plan')
        check(sql("SELECT RIGHT(content_1,1) AS tail FROM resources WHERE name='packetref'")[0]['tail']=='!','uncoordinated source edit is not overwritten')
        flag.unlink();path.write_bytes(original)
        sql("UPDATE resources SET content_1=LEFT(content_1,CHAR_LENGTH(content_1)-1) WHERE name='packetref'")
        code,body=rename('packetold','packetnew')
        check(code==200 and 'has not completed' not in body,'exact large Rename resumes after explicit source reconciliation')
        conserved('packetnew')
        check(not list((site/'fileversions').glob('resource-rename-*.pending.php')),'completed large retry leaves no pending recovery plan')
        print('Rename packet checks:',checks,'passed (4 MiB SQL packets, five large languages, history, round trip, one-byte conflict and exact recovery).')
    except Exception:
        log.flush();print((tmp/'server.log').read_text()[-2500:]);raise
    finally:
        if server:server.terminate();server.wait(timeout=10)
        log.close();sql('SET GLOBAL max_allowed_packet='+str(original_packet))
        subprocess.run(php+[str(root/'tests/rename-web-fixture.php'),'cleanup',database],check=True)
