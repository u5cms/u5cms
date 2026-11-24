"""Finish a real full rebuild and a named update in a disposable CMS."""
from pathlib import Path
import json, os, shutil, socket, subprocess, tempfile, time, urllib.request
from indexer_recovery import verify_recovery
from fixture_environment import ensure_fixture_environment

ensure_fixture_environment()
root = Path(__file__).resolve().parent.parent
php = [os.environ.get('U5_TEST_PHP', 'php')] + json.loads(os.environ.get('U5_TEST_PHP_ARGS', '[]'))
php += ['-d', 'mysqli.default_port=' + os.environ['U5CMS_TEST_PORT'], '-d', 'display_errors=0']
with tempfile.TemporaryDirectory(prefix='u5-index-http-') as temp:
    site = Path(temp)/'site'
    shutil.copytree(root, site, ignore=shutil.ignore_patterns('.git', 'tests'))
    setup = subprocess.check_output(php + [str(root/'tests/rename-web-fixture.php'), str(site)], cwd=site, text=True)
    fixture = json.loads(setup)
    database = fixture['database']
    with socket.socket() as probe:
        probe.bind(('127.0.0.1', 0))
        port = probe.getsockname()[1]
    origin = 'http://127.0.0.1:' + str(port)
    server = subprocess.Popen(php + ['-S', '127.0.0.1:' + str(port), '-t', str(site)], cwd=site,
                              stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    def get(path, quiet=False, browser=False):
        headers = {'X-U5-Background-Work': '1', 'Accept': 'application/json'} if quiet else {}
        if browser: headers = {'Accept': 'text/html', 'Sec-Fetch-Dest': 'document'}
        with urllib.request.urlopen(urllib.request.Request(origin + path, headers=headers), timeout=20) as response:
            assert response.status == 200
            body = response.read()
            if quiet:
                assert response.headers.get_content_type() == 'application/json'
                result = json.loads(body)
                assert result['u5work'] == 1 and b'<script>' not in body
                return result
            return body
    try:
        for attempt in range(60):
            try:
                get('/r/' + fixture['export'])
                break
            except OSError:
                time.sleep(.05)
        else:
            raise RuntimeError('Disposable HTTP server did not start')
        state = site/'fileversions/indexer-progress.php'
        marker = site/'fileversions/lastindex.txt'
        (site/'fileversions/indexerrunning.txt').write_text('0')
        (site/'fileversions/lastsave.txt').write_text(str(int(time.time()) - 3200))
        marker.write_text('100')
        bootstrap = get('/indexer.php?u5indexauto=1', browser=True)
        assert b'u5BackgroundWorkQueue' in bootstrap and b'<iframe' not in bootstrap
        assert not state.exists() and marker.read_text() == '100'
        body = get('/indexer.php?u5indexauto=1')
        batches = 1
        while state.exists() and batches < 1000:
            checkpoint = json.loads(state.read_text().split('\n', 1)[1])
            assert marker.read_text() == '100' and checkpoint['failures'] == 0
            # Only the disposable fixture skips the production ten-second rest.
            checkpoint['next'] = 0
            state.write_text('<?php exit; ?>\n' + json.dumps(checkpoint))
            body = get('/indexer.php?u5indexcontinue=1')
            batches += 1
        assert b'doneaudio' in body and not state.exists() and int(marker.read_text()) > 100
        body = get('/indexer.php?u5indexauto=1')
        assert not state.exists() and b'u5indexcontinue' not in body
        completed = marker.read_text()
        get('/indexer.php?n=start')
        assert not state.exists() and marker.read_text() == completed
        assert (site/'fileversions/indexerrunning.txt').read_text().strip() == '0'
        marker.write_text('100')
        reply = get('/indexer.php?u5indexauto=1', quiet=True)
        quiet_batches = 1
        while state.exists() and quiet_batches < 1000:
            assert reply['kind'] == 'index' and reply['state'] == 'wait'
            assert reply['next'] == '?u5indexcontinue=1' and reply['delay'] == 11000
            assert marker.read_text() == '100'
            # An early request waits without taking the next batch or clearing progress.
            early = get('/indexer.php?u5indexcontinue=1', quiet=True)
            assert early['state'] == 'wait' and early['delay'] > 0
            checkpoint = json.loads(state.read_text().split('\n', 1)[1])
            checkpoint['next'] = 0
            state.write_text('<?php exit; ?>\n' + json.dumps(checkpoint))
            reply = get('/indexer.php?u5indexcontinue=1', quiet=True)
            quiet_batches += 1
        assert reply['state'] == 'done' and reply['completed'] is True and not state.exists()
        completed = marker.read_text()
        assert get('/indexer.php?u5indexauto=1', quiet=True)['state'] == 'idle'
        assert get('/indexer.php?n=start', quiet=True)['state'] == 'done'
        assert marker.read_text() == completed
        access = get('/htaccess.php', quiet=True)
        assert access['kind'] == 'provision' and access['state'] == 'done'
        print(f'Real HTTP full index completed in {batches} requests; idle and named updates passed.')
        print(f'Quiet JSON full index completed in {quiet_batches} requests; bootstrap, waits, idle, named and provisioning responses passed.')
        verify_recovery(site, php, get)
    finally:
        server.terminate()
        server.wait(timeout=10)
        subprocess.run(php + [str(root/'tests/rename-web-fixture.php'), 'cleanup', database], check=True)
