#!/usr/bin/env python3
"""Actual legacy backend forms, authentication, renderer, failure and retry."""
from pathlib import Path
import json,os,shutil,socket,subprocess,tempfile,time,urllib.request
from fixture_environment import ensure_fixture_environment
ensure_fixture_environment()
REPO=Path(__file__).resolve().parent.parent
PHP=[os.environ.get('U5_TEST_PHP','php')]+json.loads(os.environ.get('U5_TEST_PHP_ARGS','[]'))
with tempfile.TemporaryDirectory(prefix='u5-rename-web-') as tmp:
    work=Path(tmp);site=work/'site'
    shutil.copytree(REPO,site,ignore=shutil.ignore_patterns('.git','tests'))
    setup=subprocess.run(PHP+[str(REPO/'tests/rename-web-fixture.php'),str(site)],capture_output=True,text=True)
    if setup.returncode:raise RuntimeError(setup.stdout+setup.stderr)
    fixture=json.loads(setup.stdout);database=fixture['database']
    os.environ['U5_RENAME_TEST_EXPORT']=fixture['export']
    router=site/'router-fixture.php'
    router.write_text('''<?php
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);$file=realpath(__DIR__.$path);
if ($file===false || (strpos($file,__DIR__.'/')!==0 && $file!==__DIR__)) {http_response_code(404);return;}
if (is_dir($file)) $file.='/index.php';
if (substr($file,-4)==='.php') {chdir(dirname($file));require $file;return;}
return false;
''')
    with socket.socket() as probe:probe.bind(('127.0.0.1',0));port=probe.getsockname()[1]
    base='http://127.0.0.1:'+str(port)
    with (work/'server.log').open('w+') as log:
        server=subprocess.Popen(PHP+['-d','mysqli.default_port='+os.environ['U5CMS_TEST_PORT'],'-S','127.0.0.1:'+str(port),'-t',str(site),str(router)],cwd=site,stdout=log,stderr=log)
        try:
            for attempt in range(100):
                try:urllib.request.urlopen(base+'/r/'+fixture['export'],timeout=1).close();break
                except OSError:
                    if server.poll() is not None:raise RuntimeError('PHP server stopped')
                    time.sleep(.05)
            else:raise RuntimeError('PHP server did not start')
            if os.environ.get('U5CMS_RENAME_HTTP_ONLY'):
                subprocess.run([os.sys.executable,str(REPO/'tests/rename-http.py'),base,str(site)],check=True)
            else:
                subprocess.run(['node',str(REPO/'tests/rename-browser.cjs'),base,str(site)],check=True)
        except Exception:
            log.flush();print((work/'server.log').read_text()[-7000:]);raise
        finally:
            server.terminate();server.wait(timeout=10)
            subprocess.run(PHP+[str(REPO/'tests/rename-web-fixture.php'),'cleanup',database],check=True)
