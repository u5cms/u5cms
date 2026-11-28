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
php = [os.environ.get('U5_TEST_PHP', 'php'), '-d', 'opcache.enable=0', '-d', 'mysqli.default_port=' + os.environ['U5CMS_TEST_PORT']]
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
    callback_form = form.replace('value="sharedsecond"', 'value="savedInPlace()"')
    callback_form = callback_form.replace('[fo:]', '[fo:][h:]<input type="hidden" name="id_mandatory" value="101">[:h]', 1)
    callback_form = callback_form.replace('[:h]\n[mfs]', '<input name="I_service" type="hidden" value="Question">\n<input name="N_service" value="draft">\n<input name="S_service" type="hidden" value="Draft">\n<input name="O_service" type="hidden" value="Help">\n<input name="userupload1" type="hidden" value="">\n[:h]\n[mfs]')
    setup = '''
        $_SERVER['HTTP_HOST']='localhost'; $_SERVER['REQUEST_URI']='/';
        require 'connect.inc.php';
        foreach (['bob@example.org','charlie@example.org'] as $email) {
            if (!mysql_query("INSERT INTO accounts(email,pw,accadmin) SELECT '$email',pw,0 FROM accounts WHERE id=1")) throw new RuntimeException(mysql_error());
        }
        $form=mysql_real_escape_string($argv[1]);
        if (!mysql_query("INSERT INTO resources(name,typ,content_1,content_2,title_1,title_2,logins,lastmut) VALUES('sharedsecond','p','$form','$form','Shared','Shared','?',1)")) throw new RuntimeException(mysql_error());
        foreach (['personalcallback','personalappendcallback','sharedcallback','writercallback'] as $name) {
            $source=$argv[2];
            if (strpos($name,'personal')===0) $source=str_replace('[mfs]','[mf]',$source);
            if ($name==='personalappendcallback') $source=str_replace('[mf]','',$source).'[mf]';
            if ($name==='writercallback') $source.='<!-- u5cms-form-writer -->';
            $source=mysql_real_escape_string($source);
            if (!mysql_query("INSERT INTO resources(name,typ,content_1,content_2,title_1,title_2,logins,lastmut) VALUES('$name','p','$source','$source','Callback','Callback','?',1)")) throw new RuntimeException(mysql_error());
        }
    '''
    subprocess.run(php + ['-r', setup, form, callback_form], cwd=site, check=True, stdout=subprocess.DEVNULL)
    # A synthetic, declared site writer uses real SQL and leaves the CMS to
    # perform validation, conflict checks and confirmation. No site code/data.
    with config.open('a') as output:
        output.write('''
if (!function_exists('u5FormWriteRecord')) {
    function u5FormWriteRecord($form) {
        if ($form!=='writercallback') return false;
        global $head,$data,$row_b;
        $name=mysql_real_escape_string($form);
        $values="headcsv='".mysql_real_escape_string($head)."',datacsv='".mysql_real_escape_string($data)."',lastmut=".time();
        if (is_array($row_b) && $row_b['id']) {
            return mysql_query("UPDATE formdata SET $values WHERE id=".(int)$row_b['id']." AND formname='$name'")!==false;
        }
        $owner=mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']));
        return mysql_query("INSERT INTO formdata SET formname='$name',authuser='$owner',time=".time().",ip='127.0.0.1',humantime='fixture',status=1,notes='',$values")!==false;
    }
}
''')
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

    def snapshot(name='sharedsecond'):
        assert re.fullmatch('[a-z]+', name)
        script = "$_SERVER['HTTP_HOST']='localhost';$_SERVER['REQUEST_URI']='/';require 'connect.inc.php';$r=mysql_query(\"SELECT id,status,authuser,lastmut,HEX(headcsv) AS head,HEX(datacsv) AS data FROM formdata WHERE formname='" + name + "' ORDER BY id\");$rows=[];while($a=mysql_fetch_assoc($r))$rows[]=$a;echo json_encode($rows);"
        return json.loads(subprocess.check_output(php + ['-r', script], cwd=site, text=True))

    def stored_fields(row):
        # HEX exposes the fixture's UTF-8 column bytes before SET NAMES latin1
        # would convert them for the ordinary PHP read path.
        head = bytes.fromhex(row['head']).decode('utf-8').split(';')
        data = bytes.fromhex(row['data']).decode('utf-8').split(';')
        return {key[1:].replace(',.', ';'): value[1:].replace(',.', ';')
                for key, value in zip(head, data) if key}

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
            stamp = re.search(r"firstsaverwins.value='([0-9]+)'", stamp_source).group(1)
            transport = request('/monofillshared.php?n=sharedsecond')
            version = re.search(r'version.value="([a-f0-9]{64})"', transport)
            if (site / 'formdata-version.inc.php').exists():
                assert version is not None, transport
            opening = stamp
            token = version.group(1) if version else ''
            before = snapshot()
            first = dict(parsed.fields, venue_mandatory='Ada Genève ' + str(number), firstsaverwins=opening, u5cms_formversion=token)
            stale = dict(parsed.fields, venue_mandatory='Ben Tokyo ' + str(number), firstsaverwins=opening, u5cms_formversion=token)
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
            current = re.search(r'version.value="([a-f0-9]{64})"', refreshed).group(1)
            retry = dict(stale, firstsaverwins=stamp, u5cms_formversion=current)
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
                output.write("\n$checksaveversionconflictinfrontendforms='" + mode + "';\n")
            stamp = str(int(time.time()))
            transport = request('/monofillshared.php?n=sharedsecond')
            version = re.search(r'version.value="([a-f0-9]{64})"', transport).group(1)
            fields = dict(parsed.fields, firstsaverwins=stamp, u5cms_formversion=version, venue_mandatory='Policy ' + mode)
            assert 'You cannot save your data' not in request(action, fields=fields)
            before = snapshot()
            response = request(action, second_user, dict(fields, venue_mandatory='Second ' + mode))
            assert ('You cannot save your data' in response) == conflict, (mode, second_user, conflict, response[-1800:])
            assert (before == snapshot()) == conflict
            print('POLICY', json.dumps({'mode': mode, 'second_user': second_user, 'conflict': conflict}), flush=True)
        with config.open('a') as output:
            output.write("\n$checksaveversionconflictinfrontendforms='all';\n")
        stamp = str(int(time.time()))
        transport = request('/monofillshared.php?n=sharedsecond')
        version = re.search(r'version.value="([a-f0-9]{64})"', transport).group(1)
        fields = dict(parsed.fields, firstsaverwins=stamp, u5cms_formversion=version, venue_mandatory='')
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
        version = re.search(r'version.value="([a-f0-9]{64})"', transport).group(1)
        fields = dict(parsed.fields, firstsaverwins=str(int(time.time())), u5cms_formversion=version, venue_mandatory='Parallel')
        before = snapshot()
        with concurrent.futures.ThreadPoolExecutor(max_workers=2) as workers:
            responses = list(workers.map(lambda user: request(action, user, fields), ['bob@example.org', 'charlie@example.org']))
        assert sum('You cannot save your data' in response for response in responses) == 1, responses
        after = snapshot()
        assert len(after) == len(before) + 1 and len([r for r in after if int(r['status']) == 1]) == 1
        print('PASS two independent PHP workers, exactly one accepted shared edit', flush=True)

        # The same prefill helper also serves deletion screens. Its transport
        # metadata must be consumed before the source-field check and storage.
        active = next(r for r in after if int(r['status']) == 1)
        delete_url = '/formdatadel.php?n=sharedsecond&id=' + str(active['id']) + '&l=en'
        delete_page = request(delete_url, 'auditadmin')
        delete_form = Form()
        delete_form.feed(delete_page)
        delete_action = '/' + re.search(r"document\.u5form\.action='([^']+)'", delete_page).group(1).lstrip('/')
        loader = '/formdataedit2.php?n=sharedsecond&id=' + str(active['id']) + '&l=en'
        loaded_delete = request(loader, 'auditadmin')
        loaded_version = re.search(r'version.value="([a-f0-9]{64})"', loaded_delete).group(1)
        stale_delete = dict(delete_form.fields, venue_mandatory='Parallel',
                            firstsaverwins=str(int(time.time())), u5cms_formversion=loaded_version)

        # Save a newer response after the deletion form was opened. The stale
        # deletion must preserve that response. The row fingerprint must cover
        # deletion as well as editing and ordinary frontend submissions.
        latest = request('/monofillshared.php?n=sharedsecond')
        latest_version = re.search(r'version.value="([a-f0-9]{64})"', latest).group(1)
        changed = dict(parsed.fields, firstsaverwins=str(int(time.time())),
                       u5cms_formversion=latest_version, venue_mandatory='Newer before delete')
        assert 'You cannot save your data' not in request(action, fields=changed)
        before_delete = snapshot()
        response = request(delete_action, 'auditadmin', stale_delete)
        assert 'You cannot save your data' in response, response[-1800:]
        assert snapshot() == before_delete, 'A stale deletion changed the newer response'
        print('PASS stale versioned deletion preserves the newer response', flush=True)

        # Reopening the actual deletion loader permits the deliberate deletion.
        refreshed = request(loader, 'auditadmin')
        refreshed_version = re.search(r'version.value="([a-f0-9]{64})"', refreshed).group(1)
        delete_fields = dict(delete_form.fields, venue_mandatory='Newer before delete',
                             firstsaverwins=str(int(time.time())), u5cms_formversion=refreshed_version)
        response = request(delete_action, 'auditadmin', delete_fields)
        assert 'ERROR: Data cannot be saved' not in response, response[-1800:]
        assert 'You cannot save your data' not in response, response[-1800:]
        deleted = snapshot()
        assert len(deleted) == len(before_delete) + 1
        assert not [r for r in deleted if int(r['status']) == 1]
        assert len([r for r in deleted if int(r['status']) == 5]) == 1
        assert all('u5cms_formversion' not in bytes.fromhex(r['head']).decode('latin1') for r in deleted)
        print('PASS refreshed deletion, retained former version and transport metadata excluded from storage', flush=True)

        upload_link = 'https://fixture.example/fileversions/useruploads/kept-document.pdf?download=1&key=synthetic'
        for name in ['personalcallback','personalappendcallback','sharedcallback','writercallback']:
            page = Form()
            page.feed(request('/index.php?c=' + name + '&l=en&id=101'))
            assert page.action and 'id_mandatory' in page.fields
            personal = name.startswith('personal')
            append_only = name == 'personalappendcallback'
            assert ('ed2cu' in page.fields) == (not append_only)
            loader = '/monofill.php' if personal else '/monofillshared.php'

            def loaded(user='bob@example.org', key=101):
                content = request(loader + '?n=' + name + '&id=' + str(key), user)
                version = re.search(r'version.value="([a-f0-9]{64})"', content).group(1)
                action = '/' + page.action.lstrip('/')
                if personal:
                    action += '&o=' + re.search(r"action\+'&o=([^']+)'", content).group(1)
                return action, version, content

            action, version, _ = loaded()
            fields = dict(page.fields, id_mandatory='101', venue_mandatory='First in place',
                          firstsaverwins=str(int(time.time())), u5cms_formversion=version,
                          userupload1=upload_link, N_service='review', S_service='Review')
            first_response = request(action, fields=fields)
            assert 'You cannot save your data' not in first_response, first_response[-1800:]
            assert 'parent.savedInPlace()' in first_response, first_response[-1000:]
            returned = re.search(r"u5form\.u5cms_formversion\.value='([a-f0-9]*)'", first_response).group(1)
            _, current, transport = loaded()
            assert returned == current, (name, 'Confirmation did not refresh the loaded row version', returned, current)
            assert 'kept-document.pdf?download=1&amp;key=synthetic' in transport

            # Continue using the version returned to the still-open page.
            fields.update(venue_mandatory='Second in place', u5cms_formversion=returned)
            second_response = request(action, fields=fields)
            assert 'You cannot save your data' not in second_response, second_response[-1800:]
            current_rows = [r for r in snapshot(name) if int(r['status']) == 1]
            assert len(current_rows) == (2 if append_only else 1), (name, current_rows)
            saved = stored_fields(max(current_rows, key=lambda row: int(row['id'])))
            assert next(iter(saved)) == 'id_mandatory' and saved['id_mandatory'] == '101', (name, saved)
            assert saved['venue_mandatory'] == 'Second in place'
            assert saved['userupload1'] == upload_link
            assert [saved[key] for key in ['I_service','N_service','S_service','O_service']] == ['Question','review','Review','Help']
            assert 'u5cms_formversion' not in saved

            # A second tab saves after the callback. The first tab must keep
            # precise conflict protection instead of falling back to seconds.
            returned_second = re.search(r"u5form\.u5cms_formversion\.value='([a-f0-9]*)'", second_response).group(1)
            _, latest_version, _ = loaded()
            assert returned_second == latest_version
            other_editor = 'bob@example.org' if personal else 'charlie@example.org'
            independent_action, independent_version, _ = loaded(other_editor)
            independent = dict(fields, venue_mandatory='Independent edit',
                               firstsaverwins=str(int(time.time())), u5cms_formversion=independent_version)
            assert 'You cannot save your data' not in request(independent_action, other_editor, independent)
            before_stale_callback = snapshot(name)
            stale_callback = dict(fields, venue_mandatory='Stale callback edit',
                                  firstsaverwins=str(int(time.time())), u5cms_formversion=returned_second)
            assert 'You cannot save your data' in request(action, fields=stale_callback)
            assert snapshot(name) == before_stale_callback

            # A different key starts blank. MF also keeps two users independent;
            # MFS deliberately exposes the shared saved response to both users.
            _, _, other_key = loaded(key=102)
            assert 'kept-document.pdf' not in other_key
            other_action, other_version, other_transport = loaded('charlie@example.org')
            assert ('kept-document.pdf' in other_transport) == (not personal)
            if personal:
                other = dict(page.fields, id_mandatory='101', venue_mandatory='Charlie only',
                             firstsaverwins=str(int(time.time())), u5cms_formversion=other_version)
                assert 'You cannot save your data' not in request(other_action, 'charlie@example.org', other)
                active_rows = [r for r in snapshot(name) if int(r['status']) == 1]
                assert {r['authuser'] for r in active_rows} == {'bob@example.org','charlie@example.org'}
                assert 'Independent edit' in loaded()[2] and 'Charlie only' not in loaded()[2]
            print('PASS ' + name + ': callback continuity, key separation, ownership and retained upload/dynamic fields', flush=True)
    finally:
        import signal
        os.killpg(server.pid, signal.SIGTERM)
        server.wait(timeout=10)
        subprocess.run(php + [str(root / 'tests/rename-web-fixture.php'), 'cleanup', fixture['database']], check=True)
