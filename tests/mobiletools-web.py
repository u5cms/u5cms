#!/usr/bin/env python3
"""Real disposable CMS/SQL/browser path for the mobile-controls backport."""
from pathlib import Path
import json, os, shutil, socket, subprocess, tempfile, time, urllib.request
repo=Path(__file__).resolve().parent.parent
php=[os.environ.get('U5_TEST_PHP','php')]+json.loads(os.environ.get('U5_TEST_PHP_ARGS','[]'))
with tempfile.TemporaryDirectory(prefix='u5-mobiletools-web-') as tmp:
    work=Path(tmp);site=work/'site'
    shutil.copytree(repo,site,ignore=shutil.ignore_patterns('.git','tests'))
    setup=subprocess.run(php+[str(repo/'tests/codeassets-web-fixture.php'),str(site)],capture_output=True,text=True,check=True)
    database=json.loads(setup.stdout)['database']
    router=site/'mobiletools-router.php'
    router.write_text('''<?php
header('X-Content-Type-Options: nosniff');
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);$file=realpath(__DIR__.$path);
if (!$file || strpos($file,__DIR__)!==0) {http_response_code(404);return;}
if (is_dir($file)) $file.='/index.php';
if (!is_file($file)) {http_response_code(404);return;}
if (substr($file,-4)==='.php') {chdir(dirname($file));require $file;return;}
$types=array('js'=>'text/javascript','css'=>'text/css','svg'=>'image/svg+xml','png'=>'image/png','jpg'=>'image/jpeg','gif'=>'image/gif');
header('Content-Type: '.($types[pathinfo($file,PATHINFO_EXTENSION)] ?? 'application/octet-stream'));readfile($file);
''')
    with socket.socket() as s:s.bind(('127.0.0.1',0));port=s.getsockname()[1]
    base='http://127.0.0.1:'+str(port)
    with (work/'server.log').open('w+') as log:
        server=subprocess.Popen(php+['-d','opcache.enable=0','-d','mysqli.default_port='+os.environ['U5CMS_TEST_PORT'],'-S','127.0.0.1:'+str(port),'-t',str(site),str(router)],cwd=site,stdout=log,stderr=log)
        try:
            for attempt in range(100):
                try:urllib.request.urlopen(base+'/?c=codefixture',timeout=2).close();break
                except OSError:time.sleep(.05)
            else:raise RuntimeError('CMS fixture did not start')
            subprocess.run(['node',str(repo/'tests/mobiletools-browser.cjs'),base],check=True)
        except Exception:
            log.flush();print((work/'server.log').read_text()[-5000:]);raise
        finally:
            server.terminate();server.wait(timeout=10)
            subprocess.run(php+[str(repo/'tests/codeassets-web-fixture.php'),'cleanup',database],check=True)
