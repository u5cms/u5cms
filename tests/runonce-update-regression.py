"""Exercise a deployed update, preserved records and the actual CSS editor."""
import base64
import http.client
import json
import os
from pathlib import Path
import re
import secrets
import shutil
import socket
import subprocess
import sys
import tempfile
import time
import urllib.parse

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / 'tests'))
from code_editor_form import CodeForm, check_code_editor


class FrontendForm(CodeForm):
    def handle_starttag(self, tag, attrs):
        if tag == 'form':
            attrs = [(key, 'form1' if key == 'name' and value == 'u5form' else value) for key, value in attrs]
        super().handle_starttag(tag, attrs)

PHP = [os.environ.get('U5_TEST_PHP', 'php'), '-d', 'mysqli.default_port=' + os.environ['U5CMS_TEST_PORT'],
       '-d', 'opcache.enable=0', '-d', 'display_errors=0']
if 'U5_RUNONCE_SCHEMA' not in os.environ:
    for schema in ['current', 'legacy', 'partial']:
        subprocess.run([sys.executable, str(Path(__file__).resolve())],
                       env=dict(os.environ, U5_RUNONCE_SCHEMA=schema), check=True)
    raise SystemExit(0)
os.environ['U5CMS_FIXTURE_PASSWORD'] = secrets.token_urlsafe(36)

with tempfile.TemporaryDirectory(prefix='u5-runonce-update-') as directory:
    tmp = Path(directory)
    site = tmp / 'site'
    shutil.copytree(ROOT, site, ignore=shutil.ignore_patterns('.git', 'tests'))
    fixture = json.loads(subprocess.check_output(PHP + [str(ROOT / 'tests/rename-web-fixture.php'), str(site)], text=True))
    with (site / 'config.php').open('ab') as output:
        output.write(b"\n$checksaveversionconflictinfrontendforms='all';\n")
    helper = tmp / 'query.php'
    helper.write_text('''<?php
require $argv[1].'/config.php';
if ($host!=='127.0.0.1' || !preg_match('/^u5rename_web_[a-f0-9]+$/D',$db)) exit(1);
$c=new mysqli($host,$username,$password,$db); $c->set_charset('utf8mb4');
$r=$c->query($argv[2]); if ($r===false) throw new RuntimeException($c->error);
echo json_encode($r===true ? true : $r->fetch_all(MYSQLI_ASSOC));
''')

    def sql(query):
        return json.loads(subprocess.check_output(PHP + [str(helper), str(site), query], text=True))

    words = 'content_d content_e content_f title_d title_e title_f key_d key_e key_f desc_d desc_e desc_f search_d search_e search_f'
    value = 'Reference values: ' + words + ' – Müller & Co; 123'
    # Current business text may mention legacy column names without becoming
    # migration input. Keep both an active record and an archived version.
    encoded = '·' + value.replace(';', ',.') + ';'
    quoted = encoded.replace("'", "''")
    for status in [1, 6]:
        sql("INSERT INTO formdata(formname,headcsv,datacsv,time,ip,status,notes,lastmut,authuser,humantime) "
            "VALUES('updateprobe','·message;','" + quoted + "',100,'127.0.0.1'," + str(status) + ",'retain note',100,'fixture@example.invalid','fixture')")

    def records():
        return sql("SELECT id,status,HEX(headcsv) h,HEX(datacsv) d,HEX(notes) n,lastmut,authuser FROM formdata WHERE formname='updateprobe' ORDER BY id")

    schema = os.environ['U5_RUNONCE_SCHEMA']
    legacy = schema == 'legacy'
    if schema == 'partial':
        # An unrelated old column can require an upgrade even on a database
        # whose language columns and current form records are already modern.
        sql('ALTER TABLE mailing DROP COLUMN mailtested')
    if legacy:
        sql("UPDATE formdata SET headcsv='·content_d;' WHERE formname='updateprobe'")
        for number, letter in [(1, 'd'), (2, 'e'), (3, 'f')]:
            sql('ALTER TABLE resources CHANGE content_' + str(number) + ' content_' + letter + ' MEDIUMTEXT')
    before = records()
    expected = json.loads(json.dumps(before))
    if legacy:
        for row in expected:
            for field in ['h', 'd']:
                data = bytes.fromhex(row[field])
                for base in ['content', 'title', 'key', 'desc', 'search']:
                    for letter, number in [('d', '1'), ('e', '2'), ('f', '3')]:
                        data = data.replace((base + '_' + letter).encode(), (base + '_' + number).encode())
                row[field] = data.hex().upper()
    for name, marker in [('updatepersonal', 'mf'), ('updateshared', 'mfs')]:
        source = '''[fo:][fi:][le:]Update acceptance[:le][h:]
<input type="hidden" name="thanks" value="savedInPlace()">
<input type="hidden" name="firstsaverwins" value="">
<input type="hidden" name="in2my" value="off">
<input type="hidden" name="em2cu" value="off">
<input type="hidden" name="em2me" value="off">
<input name="message_mandatory"><button type="submit">Save</button>
<!-- TOEDITITEMS_LOGINS_SAME_AS_[''' + name + '''] -->
<!-- TODELETEITEMS_LOGINS_SAME_AS_[''' + name + '''] -->
[:h][''' + marker + '''][:fi][:fo]'''
        field = 'content_d' if legacy else 'content_1'
        sql("INSERT INTO resources(name,typ," + field + ",logins,lastmut) VALUES('" + name + "','p','" + source.replace("'", "''") + "','?',1)")
    config_bytes = (site / 'config.php').read_bytes()
    favicon_bytes = b'fixture-favicon-keep-exactly\x00\xff'
    (site / 'favicon.ico').write_bytes(favicon_bytes)
    supplied_runonce = (site / 'r/runonce.php').read_bytes()
    router = tmp / 'router.php'
    router.write_text('''<?php
$_SERVER['HTTPS']='on';
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$file=realpath(getenv('U5_RUNONCE_SITE').$path);
if (!$file) {http_response_code(404);return;}
if (is_dir($file)) $file.='/index.php';
if (substr($file,-4)==='.php') {chdir(dirname($file));require $file;return;}
return false;
''')
    with socket.socket() as probe:
        probe.bind(('127.0.0.1', 0))
        port = probe.getsockname()[1]
    log = (tmp / 'http.log').open('wb')
    server = subprocess.Popen(PHP + ['-S', '127.0.0.1:' + str(port), '-t', str(site), str(router)],
                              cwd=site, env=dict(os.environ, U5_RUNONCE_SITE=str(site)), stdout=log, stderr=log)

    def request(path, fields=None):
        c = http.client.HTTPConnection('127.0.0.1', port, timeout=30)
        headers = {'Authorization': 'Basic ' + base64.b64encode(('auditadmin:' + os.environ['U5CMS_FIXTURE_PASSWORD']).encode()).decode()}
        body = None
        if fields is not None:
            headers['Content-Type'] = 'application/x-www-form-urlencoded'
            body = urllib.parse.urlencode(fields, encoding='cp1252', errors='xmlcharrefreplace')
        try:
            c.request('POST' if fields is not None else 'GET', path, body, headers)
            response = c.getresponse()
            return response.status, response.read().decode('cp1252', errors='replace')
        finally:
            c.close()

    try:
        for _ in range(100):
            try:
                with socket.create_connection(('127.0.0.1', port), timeout=.1):
                    break
            except OSError:
                time.sleep(.03)
        results = []
        for run in [1, 2]:
            # A later package supplies runonce.php again. It must still be safe.
            (site / 'r/runonce.php').write_bytes(supplied_runonce)
            status, page = request('/u5admin/zr.php')
            results.append({'run': run, 'http': status, 'query_error': 'Query did not work!' in page or 'Query failed!' in page,
                            'runonce_removed': not (site / 'r/runonce.php').exists(),
                            'business_records_preserved': records() == expected,
                            'config_preserved': (site / 'config.php').read_bytes() == config_bytes,
                            'favicon_preserved': (site / 'favicon.ico').read_bytes() == favicon_bytes})
            if run == 1:
                # New business data entered after a successful old-schema
                # migration must survive the next supplied runonce script.
                sql("INSERT INTO formdata(formname,headcsv,datacsv,time,ip,status,notes,lastmut,authuser,humantime) "
                    "VALUES('updateprobe','·message;','" + quoted + "',101,'127.0.0.1',1,'new after upgrade',101,'fixture@example.invalid','fixture')")
                expected.append(records()[-1])
        check_code_editor(site, request, sql)
        for name, loader in [('updatepersonal', 'monofill.php'), ('updateshared', 'monofillshared.php')]:
            status, html = request('/index.php?c=' + name + '&l=en')
            form = FrontendForm(html)
            assert status == 200 and form.action and 'ed2cu' in form.fields
            action = urllib.parse.urlsplit(urllib.parse.urljoin('/index.php', form.action))
            action = action.path + '?' + action.query

            def loaded():
                status, content = request('/' + loader + '?n=' + name)
                assert status == 200
                token = re.search(r'version.value="([a-f0-9]{64})"', content).group(1)
                target = action
                if loader == 'monofill.php':
                    target += '&o=' + re.search(r"action\+'&o=([^']+)'", content).group(1)
                return target, token, content

            target, token, _ = loaded()
            fields = dict(form.fields, message_mandatory='Original after update',
                          firstsaverwins=str(int(time.time())), u5cms_formversion=token)
            status, saved = request(target, fields)
            assert status == 200 and 'parent.savedInPlace()' in saved and 'You cannot save your data' not in saved
            target, token, content = loaded()
            assert 'Original after update' in content
            fields.update(message_mandatory='Changed after update', u5cms_formversion=token)
            stale = dict(fields, message_mandatory='Stale edit must be rejected')
            status, changed = request(target, fields)
            assert status == 200 and 'You cannot save your data' not in changed
            active = sql("SELECT * FROM formdata WHERE formname='" + name + "' AND status=1")
            assert len(active) == 1 and 'Changed after update' in active[0]['datacsv']
            status, rejected = request(target, stale)
            assert status == 200 and 'You cannot save your data' in rejected
            assert sql("SELECT * FROM formdata WHERE formname='" + name + "' AND status=1") == active

            record_id = str(active[0]['id'])
            status, delete_page = request('/formdatadel.php?n=' + name + '&id=' + record_id + '&l=en')
            assert status == 200
            delete_form = FrontendForm(delete_page)
            delete_action = '/' + re.search(r"document\.u5form\.action='([^']+)'", delete_page).group(1).lstrip('/')
            status, delete_loader = request('/formdataedit2.php?n=' + name + '&id=' + record_id + '&l=en')
            assert status == 200
            delete_version = re.search(r'version.value="([a-f0-9]{64})"', delete_loader).group(1)
            delete_fields = dict(delete_form.fields, message_mandatory='Changed after update',
                                 firstsaverwins=str(int(time.time())), u5cms_formversion=delete_version)
            status, deleted = request(delete_action, delete_fields)
            assert status == 200 and 'ERROR: Data cannot be saved' not in deleted and 'You cannot save your data' not in deleted
            rows = sql("SELECT status,headcsv,datacsv FROM formdata WHERE formname='" + name + "'")
            assert sorted(int(row['status']) for row in rows) == [5, 6, 6]
            assert all('u5cms_formversion' not in row['headcsv'] for row in rows)
        print(json.dumps({'source': os.environ.get('U5_AUDIT_REVISION', 'working-tree'), 'runs': results,
                          'schema': schema,
                          'css_actual_form_save_export_history_empty_stale': 'PASS',
                          'post_update_mf_mfs_create_load_edit_stale_delete_history': 'PASS'}, ensure_ascii=False), flush=True)
        assert all(r['http'] == 200 and not r['query_error'] and r['runonce_removed'] for r in results), results
        assert all(r['config_preserved'] and r['favicon_preserved'] for r in results), results
        assert all(r['business_records_preserved'] for r in results), 'An update rewrote existing business text or history'
    finally:
        server.terminate()
        server.wait(timeout=10)
        log.close()
        subprocess.run(PHP + [str(ROOT / 'tests/rename-web-fixture.php'), 'cleanup', fixture['database']], check=True)
