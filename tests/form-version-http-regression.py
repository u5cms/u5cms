"""Observe stale shared-form submissions using the shipped HTTP handlers."""
import base64
import concurrent.futures
import html.parser
import http.client
import json
import os
from pathlib import Path
import re
import secrets
import shutil
import socket
import subprocess
import tempfile
import time
import urllib.parse

root = Path(__file__).resolve().parents[1]
application = Path(os.environ.get('U5_AUDIT_APPLICATION', root))
php = [os.environ.get('U5_TEST_PHP', 'php'), '-d', 'mysqli.default_port=' + os.environ['U5CMS_TEST_PORT']]
os.environ['U5CMS_FIXTURE_PASSWORD'] = secrets.token_urlsafe(36)

class Form(html.parser.HTMLParser):
    def __init__(self):
        super().__init__()
        self.inside = False
        self.action = None
        self.fields = {}

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag == 'form':
            self.inside = attrs.get('name') == 'u5form'
            if self.inside:
                self.action = attrs['action']
        if self.inside and tag == 'input' and attrs.get('name'):
            self.fields[attrs['name']] = attrs.get('value', '')

    def handle_endtag(self, tag):
        if tag == 'form':
            self.inside = False

with tempfile.TemporaryDirectory(prefix='u5-mfs-second-') as temporary:
    site = Path(temporary) / 'site'
    shutil.copytree(application, site, ignore=shutil.ignore_patterns('.git', 'tests'))
    fixture = json.loads(subprocess.check_output(php + [str(root / 'tests/rename-web-fixture.php'), str(site)], cwd=site, text=True))
    config = site / 'config.php'
    with config.open('a') as output:
        output.write("\n$usesessioninsteadofbasicauth='no';\n$checksaveversionconflictinfrontendforms='all';\n")
    form = re.search(r'^### `timepreference`\n\n```text\n(.*?)\n```', (root / 'docs/forms/manage-entries.md').read_text(), re.M | re.S).group(1)
    form = form.replace('[mf]', '[mfs]').replace('timepreference', 'sharedsecond')
    setup = '''
        $_SERVER['HTTP_HOST']='localhost'; $_SERVER['REQUEST_URI']='/';
        require 'connect.inc.php';
        foreach (['bob@example.org','charlie@example.org'] as $email) {
            if (!mysql_query("INSERT INTO accounts(email,pw,accadmin) SELECT '$email',pw,0 FROM accounts WHERE id=1")) throw new RuntimeException(mysql_error());
        }
        $form=mysql_real_escape_string($argv[1]);
        if (!mysql_query("INSERT INTO resources(name,typ,content_1,content_2,title_1,title_2,logins,lastmut) VALUES('sharedsecond','p','$form','$form','Shared','Shared','?',1)")) throw new RuntimeException(mysql_error());
    '''
    subprocess.run(php + ['-r', setup, form], cwd=site, check=True, stdout=subprocess.DEVNULL)
    with socket.socket() as probe:
        probe.bind(('127.0.0.1', 0))
        port = probe.getsockname()[1]
    router = Path(temporary) / 'router.php'
    router.write_text("<?php $_SERVER['HTTPS']='on'; return false;\n")
    environment = dict(os.environ, PHP_CLI_SERVER_WORKERS='2')
    log = (Path(temporary) / 'php.log').open('wb')
    server = subprocess.Popen(php + ['-S', '127.0.0.1:' + str(port), '-t', str(site), str(router)], cwd=site, env=environment, stdout=log, stderr=log, start_new_session=True)

    def request(path, user='bob@example.org', fields=None):
        connection = http.client.HTTPConnection('127.0.0.1', port, timeout=15)
        auth = base64.b64encode((user + ':' + os.environ['U5CMS_FIXTURE_PASSWORD']).encode()).decode()
        headers = {'Authorization': 'Basic ' + auth}
        body = None
        if fields is not None:
            body = urllib.parse.urlencode(fields, encoding='cp1252', errors='xmlcharrefreplace')
            headers['Content-Type'] = 'application/x-www-form-urlencoded'
        try:
            connection.request('POST' if fields is not None else 'GET', path, body, headers)
            response = connection.getresponse()
            content = response.read().decode('cp1252', errors='replace')
            assert response.status == 200, (path, response.status, content[:300])
            assert 'SQL_a-Query failed' not in content and 'SQL_b-Query failed' not in content, content[:500]
            return content
        finally:
            connection.close()

    def snapshot():
        script = "$_SERVER['HTTP_HOST']='localhost';$_SERVER['REQUEST_URI']='/';require 'connect.inc.php';$r=mysql_query(\"SELECT id,status,authuser,lastmut,HEX(headcsv) AS head,HEX(datacsv) AS data FROM formdata WHERE formname='sharedsecond' ORDER BY id\");$rows=[];while($a=mysql_fetch_assoc($r))$rows[]=$a;echo json_encode($rows);"
        return json.loads(subprocess.check_output(php + ['-r', script], cwd=site, text=True))

    try:
        for _ in range(100):
            try:
                with socket.create_connection(('127.0.0.1', port), timeout=1):
                    break
            except OSError:
                time.sleep(.05)
        parsed = Form()
        rendered = request('/index.php?c=sharedsecond&l=en')
        parsed.feed(rendered)
        assert parsed.action and 'formsave.php' in parsed.action, rendered[-2000:]
        assert 'ifrmonofillshared' in rendered
        assert 'firstsaverwins' in parsed.fields and 'venue_mandatory' in parsed.fields, parsed.fields
        action = urllib.parse.urlsplit(urllib.parse.urljoin('http://localhost/', parsed.action))
        action = action.path + '?' + action.query
        initial = dict(parsed.fields, venue_mandatory='Original Genève', firstsaverwins='')
        assert 'You cannot save your data' not in request(action, fields=initial)
        findings = []
        for number in range(6):
            # Begin just after a real second boundary. Neither application time nor SQL is mocked.
            while time.time() % 1 > .08:
                time.sleep(.002)
            stamp_source = request('/fv.php')
            stamp = re.search(r"(?:firstsaverwins|field).value='([0-9]+)'", stamp_source).group(1)
            transport = request('/monofillshared.php?n=sharedsecond')
            version = re.search(r'\+":([a-f0-9]{64})"', transport)
            if (site / 'formdata-version.inc.php').exists():
                assert version is not None, transport
            opening = stamp + (':' + version.group(1) if version else '')
            before = snapshot()
            first = dict(parsed.fields, venue_mandatory='Ada Genève ' + str(number), firstsaverwins=opening)
            stale = dict(parsed.fields, venue_mandatory='Ben Tokyo ' + str(number), firstsaverwins=opening)
            response_a = request(action, fields=first)
            state_a = snapshot()
            response_b = request(action, 'charlie@example.org', stale)
            state_b = snapshot()
            active_a = [r for r in state_a if int(r['status']) == 1]
            active_b = [r for r in state_b if int(r['status']) == 1]
            assert len(active_a) == len(active_b) == 1, (state_a, state_b)
            assert 'You cannot save your data' not in response_a, response_a[-1500:]
            if str(active_a[0]['lastmut']) != stamp or str(active_b[0]['lastmut']) != stamp:
                print('OUTSIDE_SECOND', json.dumps({'stamp': stamp, 'first': active_a[0]['lastmut'], 'second': active_b[0]['lastmut']}), flush=True)
                continue
            rejected = 'You cannot save your data' in response_b
            assert rejected and state_a == state_b, (response_b[-1600:], state_a, state_b)
            refreshed = request('/monofillshared.php?n=sharedsecond', 'charlie@example.org')
            current = re.search(r'\+":([a-f0-9]{64})"', refreshed).group(1)
            retry = dict(stale, firstsaverwins=stamp + ':' + current)
            assert 'You cannot save your data' not in request(action, 'charlie@example.org', retry)
            assert [r for r in snapshot() if int(r['status']) == 1][0]['authuser'] == 'charlie@example.org'

            result = {'revision': os.environ.get('U5_AUDIT_REVISION', 'working-tree'), 'case': number, 'stamp': stamp, 'conflict': rejected, 'first_author': active_a[0]['authuser'], 'final_author': active_b[0]['authuser'], 'new_rows': len(state_b) - len(before), 'unchanged_after_stale': state_a == state_b}
            print('OBSERVATION', json.dumps(result), flush=True)
            findings.append(result)
        assert len(findings) >= 3, 'Fewer than three measured same-second pairs'
        assert all(r['conflict'] and r['unchanged_after_stale'] for r in findings), 'A stale same-second shared submission replaced the first save'
        for mode, second_user, conflict in [
            ('all', 'bob@example.org', True),
            ('foreign', 'bob@example.org', False),
            ('foreign', 'charlie@example.org', True),
            ('none', 'charlie@example.org', False),
        ]:
            with config.open('a') as output:
                output.write("\\n$checksaveversionconflictinfrontendforms='" + mode + "';\\n")
            stamp = str(int(time.time()))
            transport = request('/monofillshared.php?n=sharedsecond')
            version = re.search(r'\+":([a-f0-9]{64})"', transport).group(1)
            fields = dict(parsed.fields, firstsaverwins=stamp + ':' + version, venue_mandatory='Policy ' + mode)
            assert 'You cannot save your data' not in request(action, fields=fields)
            before = snapshot()
            assert ('You cannot save your data' in request(action, second_user, dict(fields, venue_mandatory='Second ' + mode))) == conflict
            assert (before == snapshot()) == conflict
            print('POLICY', json.dumps({'mode': mode, 'second_user': second_user, 'conflict': conflict}), flush=True)
        with config.open('a') as output:
            output.write("\\n$checksaveversionconflictinfrontendforms='all';\\n")
        stamp = str(int(time.time()))
        transport = request('/monofillshared.php?n=sharedsecond')
        version = re.search(r'\+":([a-f0-9]{64})"', transport).group(1)
        fields = dict(parsed.fields, firstsaverwins=stamp + ':' + version, venue_mandatory='')
        before = snapshot()
        request(action, fields=fields)
        assert before == snapshot(), 'A rejected empty required field changed storage'
        fields['venue_mandatory'] = 'Resumed Genève &#26481;&#20140;'
        assert 'You cannot save your data' not in request(action, fields=fields)
        before = snapshot()
        assert 'You cannot save your data' in request(action, fields=fields), 'A duplicate submission bypassed all-mode conflict detection'
        assert snapshot() == before
        print('PASS invalid required field, resumption and duplicate submission', flush=True)
        transport = request('/monofillshared.php?n=sharedsecond')
        version = re.search(r'\+":([a-f0-9]{64})"', transport).group(1)
        fields = dict(parsed.fields, firstsaverwins=str(int(time.time())) + ':' + version, venue_mandatory='Parallel')
        before = snapshot()
        with concurrent.futures.ThreadPoolExecutor(max_workers=2) as workers:
            responses = list(workers.map(lambda user: request(action, user, fields), ['bob@example.org', 'charlie@example.org']))
        assert sum('You cannot save your data' in response for response in responses) == 1, responses
        after = snapshot()
        assert len(after) == len(before) + 1 and len([r for r in after if int(r['status']) == 1]) == 1
        print('PASS two independent PHP workers, exactly one accepted shared edit', flush=True)
    finally:
        import signal
        os.killpg(server.pid, signal.SIGTERM)
        server.wait(timeout=10)
        subprocess.run(php + [str(root / 'tests/rename-web-fixture.php'), 'cleanup', fixture['database']], check=True)
