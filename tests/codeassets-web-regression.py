#!/usr/bin/env python3
"""A real PHP frontend and browser regression, using a disposable local MariaDB."""
from pathlib import Path
import json, os, shutil, socket, subprocess, tempfile, time, urllib.request

REPO = Path(__file__).resolve().parent.parent
PHP = [os.environ.get('U5_TEST_PHP', 'php')] + json.loads(os.environ.get('U5_TEST_PHP_ARGS', '[]'))
with tempfile.TemporaryDirectory(prefix='u5-codeassets-web-') as temporary:
    work = Path(temporary)
    site = work/'site'
    shutil.copytree(REPO, site, ignore=shutil.ignore_patterns('.git','tests'))
    setup = subprocess.run(PHP + [str(REPO/'tests/codeassets-web-fixture.php'),str(site)], capture_output=True, text=True, check=True)
    database = json.loads(setup.stdout)['database']
    router = site/'router-fixture.php'
    router.write_text('''<?php
header('X-Content-Type-Options: nosniff');
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$file=realpath(__DIR__.$path);
if ($file===false || strpos($file,__DIR__.'/')!==0 && $file!==__DIR__) { http_response_code(404);return; }
if (is_dir($file)) $file=rtrim($file,'/').'/index.php';
if (!is_file($file)) { http_response_code(404);return; }
if (substr($file,-4)==='.php') { chdir(dirname($file));require $file;return; }
$mime=array('js'=>'text/javascript','css'=>'text/css','png'=>'image/png','jpg'=>'image/jpeg','gif'=>'image/gif','svg'=>'image/svg+xml','woff'=>'font/woff','woff2'=>'font/woff2');
$extension=strtolower(pathinfo($file,PATHINFO_EXTENSION));
header('Content-Type: '.($mime[$extension] ?? 'application/octet-stream'));readfile($file);
''')
    with socket.socket() as probe:
        probe.bind(('127.0.0.1',0));port=probe.getsockname()[1]
    base='http://127.0.0.1:'+str(port)
    with (work/'server.log').open('w+') as log:
        server = subprocess.Popen(PHP + ['-d','mysqli.default_port='+os.environ['U5CMS_TEST_PORT'], '-d','upload_max_filesize=24M','-d','post_max_size=64M','-S','127.0.0.1:'+str(port),'-t',str(site),str(router)],cwd=site,stdout=log,stderr=log)
        try:
            for attempt in range(100):
                try:
                    urllib.request.urlopen(base+'/?c=codefixture&l=en',timeout=2).close();break
                except OSError:
                    if server.poll() is not None:raise RuntimeError('PHP server stopped')
                    time.sleep(.05)
            else:raise RuntimeError('PHP server did not start')
            for file, mime in [('r/mobile.js','text/javascript'),('r/mobilebase.js','text/javascript'),('r/base.css','text/css')]:
                with urllib.request.urlopen(base+'/'+file,timeout=5) as response:
                    assert response.headers.get_content_type()==mime
                    assert response.headers.get('X-Content-Type-Options')=='nosniff'
            subprocess.run(['node',str(REPO/'tests/codeassets-browser.cjs'),base,str(site)],check=True)
        except Exception:
            log.flush();print((work/'server.log').read_text()[-12000:]);raise
        finally:
            server.terminate();server.wait(timeout=10)
            subprocess.run(PHP+[str(REPO/'tests/codeassets-web-fixture.php'),'cleanup',database],check=True)
