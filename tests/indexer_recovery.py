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
