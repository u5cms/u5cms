"""Exercise the shipped database backup and restore its SQL into a disposable DB."""
from pathlib import Path
import base64, gzip, hashlib, http.cookiejar, json, os, re, shutil, socket, subprocess, tempfile, time
import urllib.error, urllib.request

ROOT = Path(os.environ.get("U5_AUDIT_ROOT", Path(__file__).resolve().parent.parent)).resolve()
PHP = os.environ.get("U5_TEST_PHP", "php")
PORT = os.environ.get("U5CMS_TEST_PORT", "33791")
PASSWORD = os.environ.get("U5CMS_FIXTURE_PASSWORD", "u5-backup-restore-fixture-password-only")
PHP_CMD = [PHP, "-d", "mysqli.default_port=" + PORT, "-d", "opcache.enable=0", "-d", "display_errors=0"]
checks = 0

def check(value, label):
    global checks
    checks += 1
    if not value:
        raise AssertionError(label)

def wait_for(predicate, label):
    until = time.monotonic() + 10
    while time.monotonic() < until:
        if predicate():
            return
        time.sleep(.025)
    raise AssertionError("Timed out: " + label)

def read_sql(path):
    data = path.read_bytes()
    return gzip.decompress(data) if path.suffix == ".gz" else data

with tempfile.TemporaryDirectory(prefix="u5-backup-restore-") as directory:
    tmp = Path(directory)
    site = tmp / "site"
    shutil.copytree(ROOT, site, ignore=shutil.ignore_patterns(".git", "qa"))
    env = dict(os.environ)
    env.update({"U5CMS_FIXTURE_PASSWORD": PASSWORD, "U5CMS_TEST_PORT": PORT, "U5CMS_TEST_USER": "root", "U5CMS_TEST_PASSWORD": ""})
    database = json.loads(subprocess.check_output(PHP_CMD + [str(ROOT / "tests/rename-web-fixture.php"), str(site)], env=env, text=True))["database"]
    restored = "u5restore_" + hashlib.sha256(database.encode()).hexdigest()[:12]
    helper = tmp / "db.php"
    helper.write_text(r'''<?php
$c=new mysqli('127.0.0.1',getenv('U5CMS_TEST_USER') ?: 'root',getenv('U5CMS_TEST_PASSWORD') ?: '', '',(int)getenv('U5CMS_TEST_PORT'));
if($c->connect_errno)throw new RuntimeException($c->connect_error);$c->set_charset('utf8mb4');
$action=$argv[1];$db=$argv[2];
if(!preg_match('/^(u5rename_web|u5restore)_[a-f0-9]+$/D',$db))exit(2);
if($action==='query'){$c->select_db($db);$r=$c->query(base64_decode($argv[3]));if($r===true){echo 'true';exit;}if(!$r)throw new RuntimeException($c->error);echo json_encode($r->fetch_all(MYSQLI_ASSOC),JSON_UNESCAPED_UNICODE);exit;}
if($action==='restore'){$c->query('DROP DATABASE IF EXISTS `'.$db.'`');$c->query('CREATE DATABASE `'.$db.'` CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci');$c->select_db($db);$sql=file_get_contents($argv[3])."\n".file_get_contents($argv[4]);if(!$c->multi_query($sql))throw new RuntimeException($c->error);do{if($r=$c->store_result())$r->free();if($c->errno)throw new RuntimeException($c->error);}while($c->more_results()&&$c->next_result());if($c->errno)throw new RuntimeException($c->error);echo 'true';exit;}
if($action==='drop'){$c->query('DROP DATABASE IF EXISTS `'.$db.'`');echo 'true';exit;}
exit(3);
''', encoding="utf-8")

    def db(action, name, *args):
        return subprocess.check_output(PHP_CMD + [str(helper), action, name, *map(str, args)], env=env, text=True)
    def query(name, sql):
        return json.loads(db("query", name, base64.b64encode(sql.encode()).decode()))

    probe = "älter;vide – œuvre – Müller – 中文 – O'Brien – \\ path\nsecond line"
    query(database, "INSERT INTO formdata(formname,headcsv,datacsv,time,ip,status,notes,lastmut,authuser,humantime) VALUES('auditrestore','·leer;·mehrsprachig;·pfad;','·;·" + probe.replace("\\", "\\\\").replace("'", "\\'").replace("\n", "\\n") + ";',1,'2001:db8::1',1,'',2,'audit@example.invalid','old')")
    before_row = query(database, "SELECT HEX(headcsv) h,HEX(datacsv) d,HEX(notes) n,authuser FROM formdata WHERE formname='auditrestore'")[0]
    before_default = query(database, "SELECT COLUMN_DEFAULT d,IS_NULLABLE n,COLUMN_TYPE t FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='" + database + "' AND TABLE_NAME='resources' AND COLUMN_NAME='name'")[0]
    check(before_default["d"] in ("", "''"), "actual resources.name starts with an empty-string default")

    with socket.socket() as s:
        s.bind(("127.0.0.1", 0)); http_port = s.getsockname()[1]
    log = (tmp / "server.log").open("w")
    server = subprocess.Popen(PHP_CMD + ["-S", "127.0.0.1:" + str(http_port), "-t", str(site)], cwd=site, env=env, stdout=log, stderr=log)
    auth = "Basic " + base64.b64encode(("auditadmin:" + PASSWORD).encode()).decode()
    def request():
        req = urllib.request.Request("http://127.0.0.1:" + str(http_port) + "/u5admin/backupdb.php", headers={"Authorization": auth})
        try:
            with urllib.request.urlopen(req, timeout=30) as response:
                return response.status, response.read().decode("cp1252")
        except urllib.error.HTTPError as error:
            return error.code, error.read().decode("cp1252")
        except urllib.error.URLError:
            return 0, ""
    try:
        wait_for(lambda: request()[0] in (200, 409, 500), "PHP fixture")
        code, body = request()
        check(code == 200, "actual backup endpoint completes")
        backup_dir = site / "fileversions" / "_dbbackup"
        structures = list(backup_dir.glob("*_structure_*.sql*")); data_files = list(backup_dir.glob("*_data_*.sql*"))
        check(bool(structures) and bool(data_files), "actual structure and data backup files exist")
        structure = read_sql(max(structures,key=lambda p:p.stat().st_mtime_ns)); data = read_sql(max(data_files,key=lambda p:p.stat().st_mtime_ns))
        check(b"CREATE TABLE `resources`" in structure and b"INSERT INTO `formdata`" in data, "backup contains actual resources schema and form rows")
        name_line = next((line for line in structure.splitlines() if line.lstrip().startswith(b"`name` varchar(255)")), b"")
        check(b"NOT NULL" in name_line and b"DEFAULT ''" in name_line, "resources.name empty default is retained in SQL")
        structure_path = tmp / "structure.sql"; data_path = tmp / "data.sql"
        structure_path.write_bytes(structure); data_path.write_bytes(data)
        db("restore", restored, structure_path, data_path)
        after_default = query(restored, "SELECT COLUMN_DEFAULT d,IS_NULLABLE n,COLUMN_TYPE t FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='" + restored + "' AND TABLE_NAME='resources' AND COLUMN_NAME='name'")[0]
        after_row = query(restored, "SELECT HEX(headcsv) h,HEX(datacsv) d,HEX(notes) n,authuser FROM formdata WHERE formname='auditrestore'")[0]
        check(after_default == before_default, "restored resources.name schema matches")
        check(after_row == before_row, "old multilingual, empty and escaped form values restore byte-for-byte")
        print("Backup restore default checks:", checks, "passed (actual endpoint, SQL import, schema default and multilingual form bytes).")
    except Exception:
        log.flush()
        print((tmp / "server.log").read_text(errors="replace")[-3500:])
        raise
    finally:
        server.terminate(); server.wait(timeout=10); log.close()
        db("drop", restored)
        subprocess.run(PHP_CMD + [str(ROOT / "tests/rename-web-fixture.php"), "cleanup", database], env=env, check=True)
