#!/usr/bin/env python3
"""Import both real seeds and inspect their default desktop/mobile pages."""
from pathlib import Path
import json, os, shutil, socket, subprocess, tempfile, time, urllib.request
repo=Path(__file__).resolve().parent.parent
php=[os.environ.get('U5_TEST_PHP','php')]+json.loads(os.environ.get('U5_TEST_PHP_ARGS','[]'))
for seed in ['u5standard.sql','u5conference.sql']:
 with tempfile.TemporaryDirectory(prefix='u5-default-design-') as tmp:
    work=Path(tmp);site=work/'site'
    shutil.copytree(repo,site,ignore=shutil.ignore_patterns('.git','tests'))
    (site/'tests').mkdir()
    fixture=site/'tests/default-design-fixture.php'
    fixture.write_bytes((repo/'tests/codeassets-web-fixture.php').read_bytes().replace(b"'/u5standard.sql'",("'/"+seed+"'").encode()))
    setup=subprocess.run(php+[str(fixture),str(site)],capture_output=True,text=True)
    if setup.returncode:raise RuntimeError(setup.stdout+setup.stderr)
    database=json.loads(setup.stdout)['database']
    # Compare actual imported C-object source and its real export with the reset baseline.
    parity=site/'tests/default-design-parity.php'
    parity.write_text('''<?php
require dirname(__DIR__).'/connect.inc.php';
$checks=0;
foreach(array('base','layout','style','navleft','navtop','navleftsubtop','form','table') as $name){
 $object=$name.'!css';$result=mysql_query("SELECT CAST(CONVERT(content_1 USING utf8mb4) AS BINARY) AS body FROM resources WHERE name='".$object."'");$row=mysql_fetch_array($result);
 $template=file_get_contents(dirname(__DIR__).'/r/'.$object.'.tpl');
 if($row['body']!==u5CodeAssetUtf8($template))throw new RuntimeException('Imported CSS differs: '.$object);
 $checks++;$export=file_get_contents(dirname(__DIR__).'/r/'.$name.'.css');
 $normalized=preg_replace('/u5v=[0-9]+/','u5v=STAMP',$export);
 $expected=preg_replace('/u5v=[0-9]+/','u5v=STAMP',"\\xEF\\xBB\\xBF".u5CodeAssetOutput($row['body'],121500));
 if($normalized!==$expected)throw new RuntimeException('Export differs: '.$object);
 $checks++;
}
echo "Actual seeded C-objects, exports and reset sources: $checks checks passed.\\n";
''')
    subprocess.run(php+['-d','mysqli.default_port='+os.environ['U5CMS_TEST_PORT'],str(parity)],cwd=site,check=True)
    router=site/'default-design-router.php'
    router.write_text('''<?php
header('X-Content-Type-Options: nosniff');$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);$file=realpath(__DIR__.$path);
if(!$file||strpos($file,__DIR__)!==0){http_response_code(404);return;}if(is_dir($file))$file.='/index.php';
if(!is_file($file)){http_response_code(404);return;}if(substr($file,-4)==='.php'){chdir(dirname($file));require $file;return;}
$types=array('js'=>'text/javascript','css'=>'text/css','svg'=>'image/svg+xml','png'=>'image/png','jpg'=>'image/jpeg','gif'=>'image/gif');
header('Content-Type: '.($types[pathinfo($file,PATHINFO_EXTENSION)]??'application/octet-stream'));readfile($file);
''')
    with socket.socket() as s:s.bind(('127.0.0.1',0));port=s.getsockname()[1]
    base='http://127.0.0.1:'+str(port)
    with (work/'server.log').open('w+') as log:
        server=subprocess.Popen(php+['-d','opcache.enable=0','-d','mysqli.default_port='+os.environ['U5CMS_TEST_PORT'],'-S','127.0.0.1:'+str(port),'-t',str(site),str(router)],cwd=site,stdout=log,stderr=log)
        try:
            for attempt in range(100):
                try:urllib.request.urlopen(base+'/?c=codefixture',timeout=2).close();break
                except OSError:time.sleep(.05)
            else:raise RuntimeError('CMS did not start')
            subprocess.run(['node',str(repo/'tests/default-design-browser.cjs'),base,seed[:-4]],check=True)
        except Exception:
            log.flush();print((work/'server.log').read_text()[-4000:]);raise
        finally:
            server.terminate();server.wait(timeout=10)
            subprocess.run(php+[str(repo/'tests/codeassets-web-fixture.php'),'cleanup',database],check=True)
