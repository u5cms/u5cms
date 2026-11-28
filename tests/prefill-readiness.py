"""Disposable HTTP/browser diagnosis; application files remain unmodified."""
import json, os, secrets, shutil, signal, socket, subprocess, tempfile, time
from pathlib import Path
lab = Path(__file__).resolve().parent
application = Path(os.environ['U5_AUDIT_APPLICATION']).resolve()
php = ['php', '-d', 'opcache.enable=0', '-d', 'mysqli.default_port='+os.environ['U5CMS_TEST_PORT']]
os.environ['U5CMS_FIXTURE_PASSWORD'] = secrets.token_urlsafe(36)
with tempfile.TemporaryDirectory(prefix='u5-prefill-') as temp:
    site=Path(temp)/'site'
    shutil.copytree(application,site,ignore=shutil.ignore_patterns('.git','tests'))
    fixture_script=application/'tests/rename-web-fixture.php'
    if not fixture_script.exists(): fixture_script=lab/'rename-web-fixture.php'
    fixture=json.loads(subprocess.check_output(php+[str(fixture_script),str(site)],cwd=site,text=True))
    with (site/'config.php').open('a') as config:
        config.write("\n$usesessioninsteadofbasicauth='no';\n$checksaveversionconflictinfrontendforms='all';\n")
    source='''[fo:][fi:][h:]
<input type="hidden" name="thanks" value="prefillSaved()">
<input type="hidden" name="in2my" value="off">
<input type="hidden" name="em2cu" value="off">
<input type="hidden" name="em2me" value="off">
<input type="hidden" name="firstsaverwins" value="">
<label>Title <input name="title_mandatory" value="Fresh initial title"></label>
<label>Optional <input name="optional" value="Fresh initial optional"></label>
<button type="submit">Save</button>
<script>function prefillSaved(){document.body.dataset.saved='yes';}</script>
[:h][:fi][:fo]'''
    setup='''$_SERVER['HTTP_HOST']='localhost';$_SERVER['REQUEST_URI']='/';require 'connect.inc.php';
    $source=mysql_real_escape_string($argv[1]);
    if(!mysql_query("INSERT INTO resources(name,typ,content_1,content_2,title_1,title_2,lastmut) VALUES('prefillaudit','p','$source','$source','Prefill','Prefill',1)"))throw new RuntimeException(mysql_error());
    '''
    subprocess.run(php+['-r',setup,source],cwd=site,check=True,stdout=subprocess.DEVNULL)
    snapshot=Path(temp)/'snapshot.php'
    snapshot.write_text('''<?php $_SERVER['HTTP_HOST']='localhost';$_SERVER['REQUEST_URI']='/';require 'connect.inc.php';
    if(($argv[1]??'')==='reset'){
      mysql_query("DELETE FROM formdata WHERE formname='prefillaudit'");
      $head=mysql_real_escape_string("·title_mandatory;·optional;·thanks;");
      $data=mysql_real_escape_string("·Saved Genève O'Connor – Müller;·;·prefillSaved();");
      if(!mysql_query("INSERT INTO formdata(id,formname,headcsv,datacsv,time,ip,authuser,humantime,status,notes,lastmut) VALUES(900001,'prefillaudit','$head','$data',1700000000,'127.0.0.1','auditadmin','fixture',1,'retained',1700000000)"))throw new RuntimeException(mysql_error());
    }
    $r=mysql_query("SELECT id,status,authuser,lastmut,HEX(headcsv) AS head,HEX(datacsv) AS data FROM formdata WHERE formname='prefillaudit' ORDER BY id");$rows=[];while($a=mysql_fetch_assoc($r))$rows[]=$a;echo json_encode($rows);
    ''',encoding='utf-8')
    # The seed is intentionally encoded as the application expects from legacy PHP.
    snapshot.write_bytes(snapshot.read_text().encode('cp1252'))
    with socket.socket() as probe:
        probe.bind(('127.0.0.1',0));port=probe.getsockname()[1]
    router=Path(temp)/'router.php';router.write_text("<?php $_SERVER['HTTPS']='on';return false;\n")
    log=(Path(temp)/'php.log').open('wb')
    server=subprocess.Popen(php+['-S','127.0.0.1:'+str(port),'-t',str(site),str(router)],cwd=site,stdout=log,stderr=log,start_new_session=True)
    try:
        for _ in range(100):
            try:
                with socket.create_connection(('127.0.0.1',port),timeout=1):break
            except OSError:time.sleep(.05)
        env=dict(os.environ,U5_PREFILL_SITE=str(site),U5_PREFILL_SNAPSHOT=str(snapshot),U5_PREFILL_ORIGIN='http://127.0.0.1:'+str(port))
        subprocess.run(['node',str(lab/'prefill-readiness.cjs')],env=env,check=True)
    finally:
        os.killpg(server.pid,signal.SIGTERM);server.wait(timeout=10)
        subprocess.run(php+[str(fixture_script),'cleanup',fixture['database']],check=True)
