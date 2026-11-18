"""Exercise a real HTTP index request against a locked disposable MariaDB table."""
from contextlib import contextmanager
import fcntl
import json
import os
from pathlib import Path
import select
import shutil
import socket
import subprocess
import tempfile
import time
import urllib.error
import urllib.request

from fixture_environment import ensure_fixture_environment

ensure_fixture_environment()
root = Path(__file__).resolve().parent.parent
php = [os.environ.get("U5_TEST_PHP", "php")] + json.loads(os.environ.get("U5_TEST_PHP_ARGS", "[]"))
php += ["-d", "mysqli.default_port=" + os.environ["U5CMS_TEST_PORT"], "-d", "display_errors=0"]
fixture = root / "tests/rename-web-fixture.php"
guard = """<?php
require $argv[1].'/config.php';
if ($host !== '127.0.0.1' || !preg_match('/^u5rename_web_[a-f0-9]+$/D', $db)) exit(1);
$conn = new mysqli($host,$username,$password,$db);
if ($argv[2] === 'hold') {
    $conn->query('LOCK TABLES resources WRITE');
    echo "ready\\n"; fflush(STDOUT);
    fgets(STDIN);
    $conn->query('UNLOCK TABLES');
} else {
    $query = "SELECT COUNT(*) FROM information_schema.PROCESSLIST
        WHERE DB=DATABASE() AND ID<>CONNECTION_ID() AND STATE LIKE 'Waiting for%lock'";
    echo $conn->query($query)->fetch_row()[0];
}
"""


@contextmanager
def held_table(script, site):
    process = subprocess.Popen(php + [str(script), str(site), "hold"],
                               stdin=subprocess.PIPE, stdout=subprocess.PIPE)
    try:
        if not select.select([process.stdout], [], [], 10)[0] or process.stdout.readline() != b"ready\n":
            raise RuntimeError("The fixture did not acquire its table lock")
        yield
    finally:
        process.stdin.close()
        process.wait(timeout=10)


def exercise(site, tmp):
    helper = tmp / "database-guard.php"
    helper.write_text(guard)
    with socket.socket() as probe:
        probe.bind(("127.0.0.1", 0))
        port = probe.getsockname()[1]
    origin = "http://127.0.0.1:" + str(port)
    state = site / "fileversions"
    (state / "lastindex.txt").write_text("100")
    (state / "lastsave.txt").write_text(str(int(time.time()) - 3600))
    (state / "indexerrunning.txt").write_text("0")
    server = subprocess.Popen(php + ["-S", "127.0.0.1:" + str(port), "-t", str(site)],
                              cwd=site, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    try:
        for attempt in range(40):
            try:
                with socket.create_connection(("127.0.0.1", port), timeout=0.25):
                    break
            except OSError:
                time.sleep(0.05)
        else:
            raise RuntimeError("The disposable HTTP server did not start")
        with held_table(helper, site):
            started = time.monotonic()
            try:
                urllib.request.urlopen(origin + "/indexer.php?u5indexauto=1", timeout=15).close()
            except urllib.error.HTTPError as error:
                assert error.code == 500, error.code
            else:
                raise AssertionError("The blocked index reported success")
            seconds = time.monotonic() - started
            assert seconds < 9, seconds
            waiters = subprocess.check_output(php + [str(helper), str(site), "pending"], text=True)
            assert waiters.strip() == "0", "A database statement remained blocked after PHP returned"
            assert (state / "lastindex.txt").read_text() == "100"
            checkpoint = json.loads((state / "indexer-progress.php").read_text().split("\n", 1)[1])
            assert checkpoint["next"] >= time.time() + 890
            with (state / "indexerrunning.txt").open() as file:
                fcntl.flock(file, fcntl.LOCK_EX | fcntl.LOCK_NB)
        print(f"Blocked full index stopped after {seconds:.2f}s. No outstanding SQL or file lock.")
    finally:
        server.terminate()
        server.wait(timeout=10)


with tempfile.TemporaryDirectory(prefix="u5-index-db-wait-") as folder:
    temp = Path(folder)
    site = temp / "site"
    shutil.copytree(root, site, ignore=shutil.ignore_patterns(".git", "tests"))
    setup = subprocess.check_output(php + [str(fixture), str(site)], cwd=site, text=True)
    database = json.loads(setup)["database"]
    try:
        exercise(site, temp)
    finally:
        subprocess.run(php + [str(fixture), "cleanup", database], check=True)
