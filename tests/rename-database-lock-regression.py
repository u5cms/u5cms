#!/usr/bin/env python3
"""Real Rename requests: bounded SQL waits, released mutex and exact-plan retry."""
from contextlib import contextmanager
import base64
import fcntl
import json
import os
from pathlib import Path
import select
import subprocess
import sys
import time
import urllib.error
import urllib.parse
import urllib.request


base, folder = sys.argv[1:]
site = Path(folder)
repo = Path(__file__).resolve().parent.parent
php = [os.environ.get("U5_TEST_PHP", "php")] + json.loads(os.environ.get("U5_TEST_PHP_ARGS", "[]"))
php += ["-d", "mysqli.default_port=" + os.environ["U5CMS_TEST_PORT"]]
checks = 0


def check(condition, message):
    global checks
    checks += 1
    if not condition:
        raise AssertionError(message)


def support(action, mode):
    result = subprocess.check_output(
        php + [str(repo / "tests/rename-web-support.php"), str(site), action, mode], text=True)
    return json.loads(result) if action == "snapshot" else None


client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor())
credentials = "auditadmin:" + os.environ["U5CMS_FIXTURE_PASSWORD"]
client.addheaders = [("Authorization", "Basic " + base64.b64encode(credentials.encode()).decode())]


def request(path, form=None):
    data = urllib.parse.urlencode(form).encode() if form is not None else None
    try:
        with client.open(urllib.request.Request(base + path, data=data), timeout=15) as response:
            return response.status, response.read().decode("cp1252")
    except urllib.error.HTTPError as error:
        return error.code, error.read().decode("cp1252")


helper = site.parent / "rename-database-guard.php"
helper.write_text(r'''<?php
$_SERVER['REQUEST_URI']='/';$_SERVER['HTTP_HOST']='fixture.invalid';
require $argv[1].'/config.php';
if ($host!=='127.0.0.1' || !preg_match('/^u5rename_web_[a-f0-9]+$/D',$db)) exit(1);
$connection=new mysqli($host,$username,$password,$db);
if ($argv[2]==='settings') {
    echo json_encode($connection->query('SELECT @@GLOBAL.lock_wait_timeout,@@GLOBAL.innodb_lock_wait_timeout')->fetch_row());
} elseif ($argv[2]==='set') {
    foreach (array(3,4) as $index) if (!ctype_digit($argv[$index])) exit(2);
    $connection->query('SET GLOBAL lock_wait_timeout='.(int)$argv[3].', GLOBAL innodb_lock_wait_timeout='.(int)$argv[4]);
} elseif ($argv[2]==='waiters') {
    echo $connection->query("SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE DB=DATABASE() AND ID<>CONNECTION_ID() AND STATE LIKE '%lock%'")->fetch_row()[0];
} else {
    if ($argv[2]==='table') $connection->query('LOCK TABLES resources_log WRITE');
    elseif ($argv[2]==='row') {
        $connection->begin_transaction();
        $statement=$connection->prepare('SELECT name FROM resources WHERE name=? FOR UPDATE');
        $statement->bind_param('s',$argv[3]);$statement->execute();$statement->get_result()->fetch_all();
    } else exit(3);
    echo "ready\n";fflush(STDOUT);fgets(STDIN);
    if ($argv[2]==='table') $connection->query('UNLOCK TABLES');
    else $connection->rollback();
}
''')


def native(action, *args):
    output = subprocess.check_output(php + [str(helper), str(site), action] + list(map(str, args)), text=True)
    if action == "settings":
        try:
            json.loads(output)
        except ValueError as error:
            raise RuntimeError("The disposable SQL helper returned: " + repr(output)) from error
    return output


@contextmanager
def held(kind, old):
    process = subprocess.Popen(php + [str(helper), str(site), kind, old],
                               stdin=subprocess.PIPE, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    try:
        if not select.select([process.stdout], [], [], 10)[0] or process.stdout.readline() != b"ready\n":
            raise RuntimeError("The disposable blocker did not acquire its SQL lock")
        yield
    finally:
        process.stdin.close()
        try:
            process.wait(timeout=10)
        except subprocess.TimeoutExpired:
            process.kill()
            process.wait(timeout=5)
            raise
        error = process.stderr.read().decode()
        process.stdout.close()
        process.stderr.close()
        check(process.returncode == 0, "SQL blocker released its transaction: " + error)


probe = site / "u5admin/rename-lock-probe.php"
probe.write_text('''<?php
if (isset($_GET['limited'])) define('U5CMS_RESOURCE_RENAME_REQUEST',true);
require 'connect.inc.php';
echo json_encode(mysql_fetch_row(mysql_query('SELECT @@SESSION.lock_wait_timeout,@@SESSION.innodb_lock_wait_timeout')));
''')


def limits(limited=False):
    code, body = request("/u5admin/rename-lock-probe.php" + ("?limited=1" if limited else ""))
    check(code == 200, "Authenticated connection probe succeeded")
    return list(map(int, json.loads(body)))


def csrf(old):
    import re
    code, body = request("/u5admin/rename.php?name=" + old)
    check(code == 200, "Actual Rename form opened")
    match = re.search(r'name="u5scrtytknfrfrms"[^>]*value="([^"]+)"', body)
    check(match is not None, "Actual Rename form supplied a CSRF token")
    return match.group(1)


def rename(old, new, token):
    return request("/u5admin/rename2.php?name=" + old,
                   {"name": new, "ulinks": "yes", "u5scrtytknfrfrms": token})


def check_mutex():
    lock = site / "fileversions/codeassets-12.11.0.lock.php"
    check(lock.is_file(), "Rename entered the coordinated mutation section")
    with lock.open() as file:
        fcntl.flock(file, fcntl.LOCK_EX | fcntl.LOCK_NB)
    check(True, "No kernel mutation lock remained after the failed request")


original = json.loads(native("settings"))
try:
    try:
        urllib.request.urlopen(base + "/u5admin/renaming.php", timeout=10).close()
    except urllib.error.HTTPError as error:
        check(error.code == 401, "Recovery overview requires backend authentication")
    else:
        raise AssertionError("The recovery overview accepted an unauthenticated request")
    native("set", 20, 20)
    check(limits() == [20, 20], "Ordinary backend connection keeps the server wait policy")
    check(limits(True) == [5, 5], "Rename connection caps both SQL lock-wait classes")
    check(limits() == [20, 20], "Rename does not change other connections or global settings")

    for mode, kind, seconds in [("sqltable", "table", 5), ("sqlrow", "row", 5), ("sqlstrict", "table", 1)]:
        if seconds == 1:
            native("set", 1, 1)
            check(limits(True) == [1, 1], "Rename preserves stricter server wait limits")
        support("seed", mode)
        old, new = "renameold" + mode, "renamenew" + mode
        before = support("snapshot", mode)
        token = csrf(old)
        if kind == "row":
            # MVCC permits planning; the later guarded UPDATE is actually blocked.
            # Files have already moved when the SQL wait expires.
            alter = site.parent / "rename-innodb-fixture.php"
            alter.write_text("<?php $_SERVER['REQUEST_URI']='/';$_SERVER['HTTP_HOST']='fixture.invalid'; require $argv[1].'/config.php'; $c=new mysqli($host,$username,$password,$db); $c->query('ALTER TABLE resources ENGINE=InnoDB');")
            subprocess.run(php + [str(alter), str(site)], check=True)
        with held(kind, old):
            started = time.monotonic()
            code, body = rename(old, new, token)
            elapsed = time.monotonic() - started
            check(code == 500 and "Retry rename" in body, "SQL timeout reports failure and offers exact retry")
            check(seconds - 0.5 <= elapsed < seconds + 4, "SQL timeout returned within its wait budget: " + str(elapsed))
            check(native("waiters").strip() == "0", "No SQL statement remained waiting after PHP returned")
            check_mutex()
            pending = list((site / "fileversions").glob("resource-rename-*.pending.php"))
            if kind == "row":
                check(len(pending) == 1, "Failure after file publication retained its immutable Rename plan")
                check((site / "r" / new / (new + "_en.txt")).read_bytes() == b"original language bytes",
                      "Row lock was reached after the original file bytes had moved")
                code, body = request("/u5admin/savepage.php", {"name": new, "page": new, "u5scrtytknfrfrms": token})
                check(code == 409 and "did not change" in body and 'href="renaming.php"' in body,
                      "An unfinished Rename protects source writers and links to recovery")
                partial = support("snapshot", mode)
                code, body = request("/u5admin/renaming.php")
                check(code == 200 and "Retry rename" in body and "rename2.php?name=" + old in body,
                      "The saved Rename is recoverable without the original result window")
                import re
                recovered_token = re.search(r'name="u5scrtytknfrfrms"[^>]*value="([^"]+)"', body)
                check(recovered_token is not None and 'value="' + new + '"' in body,
                      "Recovery supplies the original target and a current CSRF token")
                check(support("snapshot", mode) == partial, "Opening recovery does not change the partially applied source")
                token = recovered_token.group(1)
                code, body = request("/u5admin/rename2.php?name=" + old, {"name": new, "ulinks": "yes"})
                check(code == 403 and support("snapshot", mode) == partial,
                      "Recovery still rejects a mutating request without its CSRF token")
            else:
                check(not pending, "Failure before planning leaves no unfinished Rename plan")
        if kind == "table":
            check(support("snapshot", mode) == before, "Pre-plan SQL timeout preserved all source, history and form rows")
        code, body = rename(old, new, token)
        check(code == 200 and "Rename has not completed" not in body, "The same authenticated request completes after releasing the SQL blocker")
        after = support("snapshot", mode)
        row = next(row for row in after["resources"] if row["name"] == new)
        check(row["operator"] == "R original", "Retry advances the source revision exactly once")
        for language in range(1, 6):
            check("[Go to page:" + new + "]" in row["content_" + str(language)], "Retry completes language " + str(language))
        check(after["formdata"][0]["formname"] == new, "Retry completes form ownership")
        check(not list((site / "fileversions").glob("resource-rename-*.pending.php")), "Completed retry removes the pending writer barrier")
        code, body = request("/u5admin/renaming.php")
        check(code == 200 and "No unfinished resource rename is recorded" in body,
              "Recovery overview clears after successful completion")
        check_mutex()
        check(rename(old, new, token)[0] == 200 and support("snapshot", mode) == after, "A repeated completed request is idempotent")
        print(f"Rename {kind} blocker: {elapsed:.2f}s; released mutex and SQL; exact retry completed.", flush=True)
    print(f"Rename database lock checks: {checks} passed.", flush=True)
finally:
    native("set", *original)
    probe.unlink(missing_ok=True)
    helper.unlink(missing_ok=True)
