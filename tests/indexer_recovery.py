"""Exercise partial full rebuilds on the disposable HTTP/database fixture."""
import fcntl
import json
import subprocess
import time
import urllib.error


def verify_recovery(site, php, get):
    directory = site / 'fileversions'
    progress = directory / 'indexer-progress.php'
    marker = directory / 'lastindex.txt'
    saved = directory / 'lastsave.txt'
    # The fixture normally disables public maintenance. Enable its normal entry point.
    with (site / 'config.php').open('a') as config:
        config.write("\n$blockautoincluderefresherforhtaccessandindex='';\n")
        config.write("$waitsecondstillindexingsincelastsave=1800;\n")
        config.write("$requiregooglerecaptchaonfrontendformsave='yes';\n")
    for endpoint in ('formsave.php', 'paypal.php'):
        assert b'Turnstile verification is missing' in get('/' + endpoint)

    def sql(statement):
        code = '''
        require $argv[1].'/config.php';
        if ($host !== '127.0.0.1' || !preg_match('/^u5rename_web_[a-f0-9]+$/D', $db)) exit(1);
        $conn = new mysqli($host, $username, $password, $db);
        $result = $conn->query(stream_get_contents(STDIN));
        if ($result instanceof mysqli_result) echo $result->fetch_row()[0];
        '''
        return subprocess.check_output(php + ['-r', code, str(site)], input=statement, text=True)

    def checkpoint():
        return json.loads(progress.read_text().split('\n', 1)[1])

    def due():
        state = checkpoint()
        state['next'] = 0
        progress.write_text('<?php exit; ?>\n' + json.dumps(state))

    def homepage():
        return get('/index.php?c=start&l=de')

    def step():
        due()
        try:
            return get('/indexer.php?u5indexcontinue=1', quiet=True)['state']
        except urllib.error.HTTPError as error:
            assert error.code == 500
            return 'error'

    assert not progress.exists()
    assert get('/indexer.php', quiet=True)['state'] == 'wait'
    assert b'indexer.php?u5indexcontinue=1' not in homepage()
    due()
    assert b'indexer.php?u5indexcontinue=1' in homepage()
    saved.write_text(str(int(time.time())))
    assert b'indexer.php?u5indexcontinue=1' in homepage()
    with (directory/'indexerrunning.txt').open('r+') as lock:
        fcntl.flock(lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
        assert get('/indexer.php?u5indexcontinue=1', quiet=True)['state'] == 'busy'
    # Two errors enclose a good row. The first repair must not publish the watermark.
    for suffix in ('a', 'b', 'c', 'd'):
        sql("INSERT INTO resources(name,typ,content_1,search_1) VALUES "
            f"('zzaudit{suffix}','p','recovery {suffix}','old')")
    # Start a new manual pass so the new rows fall inside its fixed upper bound.
    for _ in range(100):
        if not progress.exists():
            break
        assert step() in ('wait', 'done')
    assert not progress.exists()
    original = marker.read_bytes()
    sql("CREATE TRIGGER audit_index_error BEFORE UPDATE ON resources FOR EACH ROW "
        "BEGIN IF NEW.name IN ('zzaudita','zzauditc') THEN SIGNAL SQLSTATE '45000' "
        "SET MESSAGE_TEXT='controlled resource error'; END IF; END")
    assert get('/indexer.php', quiet=True)['state'] == 'wait'
    failures = 0
    for _ in range(100):
        failures += step() == 'error'
        assert marker.read_bytes() == original
        state = checkpoint()
        if state.get('retryRound') == 1:
            break
    else:
        raise AssertionError('Unrelated resources could not finish')
    assert failures == 2
    assert sql("SELECT search_1 FROM resources WHERE name='zzauditd'").find('recovery d') >= 0
    assert 895 <= state['next'] - time.time() <= 901
    sql('DROP TRIGGER audit_index_error')
    sql("CREATE TRIGGER audit_index_error BEFORE UPDATE ON resources FOR EACH ROW "
        "BEGIN IF NEW.name='zzauditc' THEN SIGNAL SQLSTATE '45000' "
        "SET MESSAGE_TEXT='remaining resource error'; END IF; END")
    for _ in range(30):
        step()
        assert marker.read_bytes() == original
        if checkpoint().get('retryRound') == 2:
            break
    else:
        raise AssertionError('Second repair range was not scheduled')
    assert 'recovery a' in sql("SELECT search_1 FROM resources WHERE name='zzaudita'")
    assert 3595 <= checkpoint()['next'] - time.time() <= 3601
    sql('DROP TRIGGER audit_index_error')
    for _ in range(30):
        if step() == 'done':
            break
    assert not progress.exists()
    assert 'recovery c' in sql("SELECT search_1 FROM resources WHERE name='zzauditc'")
    assert get('/indexer.php?u5indexcontinue=1', quiet=True)['state'] == 'idle'
    print('Public resume, busy result, legacy form policy and two independent row failures recovered.')

    # A completion timestamp from a machine with a fast clock must not suppress work.
    now = int(time.time())
    saved.write_text(str(now - 7200))
    marker.write_text(str(now + 86400))
    assert b'indexer.php?u5indexauto=1' in homepage()
    assert get('/indexer.php?u5indexauto=1', quiet=True)['state'] == 'wait'
    for _ in range(100):
        if step() == 'done':
            break
    assert not progress.exists() and 0 < int(marker.read_text()) <= time.time()
    assert get('/indexer.php?u5indexauto=1', quiet=True)['state'] == 'idle'

    # Keep one old failure open and edit rows on both sides of its retry interval.
    # Hold the 24-hour retry deadline in the future; only advance the edit probe.
    saved.write_text('100')
    original = marker.read_bytes()
    sql("CREATE TRIGGER audit_index_error BEFORE UPDATE ON resources FOR EACH ROW "
        "BEGIN IF NEW.name='zzauditc' THEN SIGNAL SQLSTATE '45000' "
        "SET MESSAGE_TEXT='controlled resource error'; END IF; END")
    assert get('/indexer.php', quiet=True)['state'] == 'wait'
    for _ in range(100):
        step()
        if 'checkEditsAfter' in checkpoint():
            break
    state = checkpoint()
    assert 'checkEditsAfter' in state and bytes.fromhex(state['upper']) == b'zzauditc'
    def wake_edit_probe(state):
        state['next'] = int(time.time()) + 86300
        state['checkEditsAfter'] = 0
        progress.write_text('<?php exit; ?>\n' + json.dumps(state))
    wake_edit_probe(state)
    assert b'indexer.php?u5indexcontinue=1' in homepage()
    probe = get('/indexer.php?u5indexcontinue=1', quiet=True)
    assert probe['state'] == 'wait' and 895000 <= probe['delay'] <= 902000
    assert checkpoint()['upper'] == state['upper'] and checkpoint()['next'] > time.time() + 86000
    assert b'indexer.php?u5indexcontinue=1' not in homepage()
    sql("UPDATE resources SET content_1='edited before hole u5earlyword', logins='', hidden=0 WHERE name='zzaudita'")
    sql("INSERT INTO resources(name,typ,content_1,search_1) VALUES "
        "('zzauditz','p','new after hole u5laterword','old')")
    sql("UPDATE resources SET hidden=0, logins='' WHERE name='zzauditz'")
    saved.write_text(str(int(time.time())))
    wake_edit_probe(checkpoint())
    assert b'indexer.php?u5indexcontinue=1' in homepage()
    assert get('/indexer.php?u5indexcontinue=1', quiet=True)['state'] == 'wait'
    for _ in range(100):
        # Simulate continuing editing during the sweep. This must not rewind it.
        saved.write_text(str(int(time.time())))
        step()
        assert marker.read_bytes() == original
        if 'checkEditsAfter' in checkpoint():
            break
    assert 'checkEditsAfter' in checkpoint()
    assert 'edited before hole' in sql("SELECT search_1 FROM resources WHERE name='zzaudita'")
    assert 'new after hole' in sql("SELECT search_1 FROM resources WHERE name='zzauditz'")
    for word, expected, other in [('u5earlyword', b'zzaudita', b'zzauditz'),
                                  ('u5laterword', b'zzauditz', b'zzaudita')]:
        results = get('/index.php?c=_search&l=de&q=' + word)
        assert expected in results and other not in results, (word, expected, other)
    assert marker.read_bytes() == original
    sql('DROP TRIGGER audit_index_error')
    for _ in range(100):
        if step() == 'done':
            break
    assert not progress.exists()
    print('Clock rollback, bounded idle probes and fresh content around a persistent failure passed.')
