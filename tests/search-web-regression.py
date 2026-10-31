#!/usr/bin/env python3
"""Real PHP/MariaDB/browser integration tests in a disposable u5CMS installation."""
from pathlib import Path
import base64
import json
import os
import shutil
import socket
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request

REPO = Path(__file__).resolve().parent.parent
PHP = [os.environ.get('U5_TEST_PHP', 'php')] + json.loads(os.environ.get('U5_TEST_PHP_ARGS', '[]'))
PORT = os.environ.get('U5CMS_TEST_PORT', '33361')
CHECKS = 0


def check(condition, message):
    global CHECKS
    if not condition:
        raise AssertionError(message)
    CHECKS += 1


def php_fixture(*arguments):
    result = subprocess.run(PHP + [str(REPO / 'tests/search-web-fixture.php'), *arguments], capture_output=True, text=True)
    if result.returncode:
        raise RuntimeError(result.stdout + result.stderr)
    return result.stdout


with tempfile.TemporaryDirectory(prefix='u5-search-web-') as temporary:
    work = Path(temporary)
    site = work / 'site'
    shutil.copytree(REPO, site, ignore=shutil.ignore_patterns('.git', 'tests'))
    default_template = (site / 'r/htmltemplate.html').read_bytes()
    default_templates = {name: default_template for name in ('htmltemplate.css', 'htmltemplate.html')}
    setup = json.loads(php_fixture(str(site)))
    database = setup['database']
    router = site / 'router-search-fixture.php'
    router.write_text('''<?php
header('X-Content-Type-Options: nosniff');
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$file=realpath(__DIR__.$path);
if ($file===false || (strpos($file,__DIR__.'/')!==0 && $file!==__DIR__)) { http_response_code(404);return; }
if (is_dir($file)) $file=rtrim($file,'/').'/index.php';
if (!is_file($file)) { http_response_code(404);return; }
if (substr($file,-4)==='.php') { chdir(dirname($file));require $file;return; }
$mime=array('js'=>'text/javascript','json'=>'application/json','css'=>'text/css','png'=>'image/png','jpg'=>'image/jpeg','gif'=>'image/gif','svg'=>'image/svg+xml');
header('Content-Type: '.($mime[strtolower(pathinfo($file,PATHINFO_EXTENSION))] ?? 'application/octet-stream'));
readfile($file);
''', encoding='ascii')
    if os.environ.get('U5CMS_PHP9_AUDIT'):
        router.write_text('<?php require '+json.dumps(str(REPO/'tests/php9-guard.php'))+'; ?>\n'+router.read_text())
    if os.environ.get('U5CMS_TEST_HTTP_PORT'):
        port = int(os.environ['U5CMS_TEST_HTTP_PORT'])
    else:
        with socket.socket() as probe:
            probe.bind(('127.0.0.1', 0))
            port = probe.getsockname()[1]
    base = 'http://127.0.0.1:' + str(port)

    def request(path, credentials=None):
        headers = {}
        if credentials:
            headers['Authorization'] = 'Basic ' + base64.b64encode(':'.join(credentials).encode()).decode()
        req = urllib.request.Request(base + path, headers=headers)
        try:
            response = urllib.request.urlopen(req, timeout=20)
        except urllib.error.HTTPError as error:
            response = error
        with response:
            return response.status, dict(response.headers), response.read().decode('cp1252', errors='replace')

    def query(value, scope='_search', language='de', direct=False):
        path = '/u5sys._searchsi.php' if direct else '/index.php'
        return path + '?' + urllib.parse.urlencode({'c': scope, 'l': language, 'qv': '2', 'q': value})

    with (work / 'server.log').open('w+') as log:
        # Config/source fixtures change between requests; execute their current bytes.
        server = subprocess.Popen(PHP + ['-d','opcache.enable=0','-d', 'mysqli.default_port=' + PORT, '-d', 'output_buffering=4096', '-d', 'display_errors=0', '-S', '127.0.0.1:' + str(port), '-t', str(site), str(router)], cwd=site, stdout=log, stderr=log)
        try:
            for attempt in range(100):
                try:
                    request('/index.php?c=_search&l=de')
                    break
                except OSError:
                    if server.poll() is not None:
                        raise RuntimeError('PHP HTTP server stopped during startup')
                    time.sleep(.05)
            else:
                raise RuntimeError('PHP HTTP server did not start')
            status, _, body = request('/indexer.php')
            check(status == 200 and 'doneaudio' in body, 'The real full indexer completes over HTTP')
            check((site / 'fileversions/search-index-v2.txt').read_text().strip() == '2', 'Full indexing publishes its format marker')
            check(not (site / 'fileversions/search-index-pending.txt').exists(), 'Full indexing clears pending state')

            status, _, body = request(query('SecretNeedle'))
            check(status == 200 and 'Protected sample' in body, 'Public policy permits a protected title result')
            check('u5-search-protected' in body and 'privatepayload' not in body, 'Public result does not expose protected body text')
            status, headers, body = request(query('SecretNeedle', '_searchsi', direct=True))
            check(status == 401 and any(key.lower() == 'www-authenticate' for key in headers), 'Intranet search challenges even without a protected ! sentinel')
            check('privatepayload' not in body and 'u5-search-result' not in body, 'Unauthenticated intranet request cannot render results')
            status, _, body = request(query('SecretNeedle', '_searchsi', direct=True), (setup['username'], 'wrong-password'))
            check(status == 401 and 'privatepayload' not in body, 'Incorrect credentials cannot read protected snippets')
            status, _, body = request(query('SecretNeedle', '_searchsi', direct=True), (setup['username'], setup['password']))
            check(status == 200 and 'privatepayload' in body, 'A valid account can use authenticated intranet search without a sentinel')

            for value in ('x' * 513, '"unfinished', ' '.join('term' + str(i) for i in range(17))):
                status, _, body = request(query(value))
                check(status == 200 and 'role="alert"' in body and 'class="u5-search-result"' not in body, 'Invalid or oversized query gives a bounded user-facing error')

            for path in ('/f.php', '/f.php?f=single', '/f.php?f%5B%5D=invalid',
                         '/f.php?f=r/downloadfixture/downloadfixture_de.txt&s%5B%5D=invalid',
                         '/f.php?f=r/downloadfixture/downloadfixture_de.txt&t%5B%5D=invalid'):
                check(request(path)[0] == 400, 'Malformed download paths are rejected before string/path processing: ' + path)
            for suffix in ('', '&t=123', '?t=123'):
                status, _, payload = request('/f.php?f=r/downloadfixture/downloadfixture_de.txt' + suffix)
                check(status == 200 and payload == 'Actual download fixture payload.', 'Download works with absent, explicit or legacy embedded time parameter: ' + suffix)

            subprocess.run(['node', str(REPO / 'tests/search-web-browser.cjs'), base, json.dumps(setup)], check=True)

            fixture_templates = {name: (site / 'r' / name).read_bytes() for name in default_templates}
            try:
                for name, content in default_templates.items():
                    (site / 'r' / name).write_bytes(content)
                subprocess.run(['node', str(REPO / 'tests/search-web-browser.cjs'), base, json.dumps(setup), 'presentation'], check=True)
            finally:
                for name, content in fixture_templates.items():
                    (site / 'r' / name).write_bytes(content)

            config = site / 'config.php'
            original_config = config.read_bytes()
            # Add the intranet sentinel only after the no-sentinel authentication
            # regression has run, then index it through the real per-resource path.
            php_fixture('add-intranet', database)
            status, _, body = request('/indexer.php?n=%21fixture')
            check(status == 200 and 'could not be updated' not in body, 'A new intranet resource can be indexed individually')
            status, _, body = request(query('IntranetNeedle'))
            check('Intranet sample' not in body and 'intranetpayload' not in body, 'Public search excludes intranet resources by default')
            try:
                config.write_bytes(original_config + b"\n$automaticallyskipintranetlogin='yes';\n")
                status, _, body = request(query('IntranetNeedle', '_searchsi', direct=True))
                check(status == 200 and 'intranetpayload' in body, 'Explicit intranet login opt-out opens intranet-prefixed content')
                status, _, body = request(query('SecretNeedle', '_searchsi', direct=True))
                check(status == 200 and 'Protected sample' in body and 'u5-search-protected' in body and 'privatepayload' not in body,
                      'Intranet login opt-out preserves ordinary protected preview policy')

                config.write_bytes(original_config + b"\n$automaticallyskipintranetlogin='yes';\n$doesfindpasswordprotectedcontent='no';\n$doesshowpreviewofsuchcontent='yes';\n")
                status, _, body = request(query('IntranetNeedle', '_searchsi', direct=True))
                check(status == 200 and 'intranetpayload' in body, 'Intranet login opt-out still permits intranet resources when ordinary protected search is disabled')
                for scope in ('_search', '_searchsi'):
                    status, _, body = request(query('SecretNeedle', scope, direct=scope == '_searchsi'))
                    check(status == 200 and 'Protected sample' not in body and 'privatepayload' not in body,
                          'Disabled protected search excludes ordinary protected results in ' + scope)
                    status, _, body = request(query('privatepaylod', scope, direct=scope == '_searchsi'))
                    check(status == 200 and 'privatepayload' not in body,
                          'Spelling suggestions cannot expose excluded protected vocabulary in ' + scope)
            finally:
                config.write_bytes(original_config)

            try:
                config.write_bytes(original_config + b"\n$searchenginesqladditionalbooland='AND u5_search_fixture_missing_column=1';\n")
                status, _, body = request(query('cafe'))
                check('role="alert"' in body and 'class="u5-search-result"' not in body, 'Database query errors render an unavailable message')
                check('u5_search_fixture_missing_column' not in body and 'SELECT name' not in body and 'SQLSTATE' not in body, 'Database failure does not expose SQL or database diagnostics')
            finally:
                config.write_bytes(original_config)
            php_fixture('empty', database)
            started = time.monotonic()
            status, _, body = request(query('unfindable'))
            check(status == 200 and 'Keine Treffer' in body and 'class="u5-search-result"' not in body, 'An empty searchable resource set returns an ordinary no-result page')
            check(time.monotonic() - started < 5, 'An empty search set terminates promptly')
            php_fixture('restore', database)
            print(f'Search HTTP/MariaDB checks: {CHECKS} passed.')
        except Exception:
            log.flush()
            print((work / 'server.log').read_text(errors='replace')[-16000:])
            if os.environ.get('U5CMS_TEST_KEEP_FAILED_SITE'):
                destination = Path(os.environ['U5CMS_TEST_KEEP_FAILED_SITE'])
                shutil.copytree(site, destination, dirs_exist_ok=True)
            raise
        finally:
            server.terminate()
            server.wait(timeout=10)
            php_fixture('cleanup', database)
