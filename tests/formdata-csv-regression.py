#!/usr/bin/env python3
"""Exercise the shipped CSV export with legacy, empty and hostile form values."""

from pathlib import Path
import json
import os
import re
import secrets
import shutil
import subprocess
import tempfile


ROOT = Path(os.environ.get('U5CSV_SOURCE_ROOT', Path(__file__).resolve().parents[1]))
PHP = [os.environ.get('U5_TEST_PHP', 'php')] + json.loads(os.environ.get('U5_TEST_PHP_ARGS', '[]'))
DATABASE = 'u5csv_' + secrets.token_hex(8)
ROWS = 1024

runner = r'''<?php
$host=getenv('U5CMS_TEST_HOST') ?: '127.0.0.1';
$port=(int)(getenv('U5CMS_TEST_PORT') ?: '3306');
$user=getenv('U5CMS_TEST_USER') ?: 'root';
$password=getenv('U5CMS_TEST_PASSWORD') ?: '';
$database=getenv('U5CSV_TEST_DB');
$connection=new mysqli($host,$user,$password,null,$port);
if ($connection->connect_error) throw new RuntimeException($connection->connect_error);
$connection->query('DROP DATABASE IF EXISTS `'.$database.'`');
if (!$connection->query('CREATE DATABASE `'.$database.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_bin')) throw new RuntimeException($connection->error);
$connection->select_db($database);
$connection->query('CREATE TABLE formdata (id INT AUTO_INCREMENT PRIMARY KEY, formname VARCHAR(120), headcsv MEDIUMTEXT, datacsv MEDIUMTEXT, time INT, ip TEXT, authuser TEXT, humantime TEXT, lastmut INT, status INT, notes TEXT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
$insert=$connection->prepare('INSERT INTO formdata(formname,headcsv,datacsv,time,ip,authuser,humantime,lastmut,status,notes) VALUES (?,?,?,?,?,?,?,?,?,?)');
$form='auditform';$head='=Equals;+Plus;@At;-Minus;Safe;Empty;Unicode;';
$time=1700000000;$human='2023.11.14 22:13:20';$last=1700000000;$status=1;
for ($i=0;$i<1024;$i++) {
    if ($i%2) {
        // This is the historical storage convention after the form handler
        // replaces embedded semicolons with ",.".
        $data='&#61,.1+1;&#43,.SUM(1,2);&#64,.SUM(1,1);&#45,.2+3;ordinary;;Fran&ccedil,.ais &amp,. résumé;';
        $notes='&#61,.HYPERLINK(&quot,.https://example.invalid&quot,.)';
    } else {
        $data="\t=1+1; +SUM(1,2);\n@SUM(1,1);\r-2+3;ordinary;;Grüezi € 漢字;";
        $notes='=HYPERLINK("https://example.invalid","old row")';
    }
    $ip=$i%4===0 ? '+441234' : '127.0.0.1';
    $auth=$i%4===1 ? '@account' : ($i%4===2 ? '-2+3' : '=account');
    $insert->bind_param('sssisssiis',$form,$head,$data,$time,$ip,$auth,$human,$last,$status,$notes);
    if (!$insert->execute()) throw new RuntimeException($insert->error);
}
$connection->close();
$_GET=['n'=>'auditform','s'=>1,'f'=>''];$_COOKIE=['fdbool'=>'or','fdorder'=>'yes'];
chdir(__DIR__.'/u5admin');ob_start();require 'formdata2csv.php';$csv=ob_get_clean();chdir(__DIR__);
$connection=new mysqli($host,$user,$password,null,$port);
$connection->query('DROP DATABASE `'.$database.'`');$connection->close();
echo $csv;
'''

connect = r'''<?php
require_once dirname(__DIR__).'/mysql.php';
function tnuoc($value){return is_countable($value)?count($value):0;}
function htmlXspecialchars($value){return htmlspecialchars($value??'',ENT_QUOTES,'UTF-8');}
$host=getenv('U5CMS_TEST_HOST') ?: '127.0.0.1';
$port=getenv('U5CMS_TEST_PORT') ?: '3306';
$user=getenv('U5CMS_TEST_USER') ?: 'root';
$password=getenv('U5CMS_TEST_PASSWORD') ?: '';
mysql_connect($host.':'.$port,$user,$password);mysql_select_db(getenv('U5CSV_TEST_DB'));
'''

config = "<?php\n$formdataRqHIADRI='no';\n$formSenderEvidenceRequired='no';\n"
san = "<?php\nif(!function_exists('htmlXspecialchars')){function htmlXspecialchars($v){return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8');}}\n"

with tempfile.TemporaryDirectory(prefix='u5-csv-export-') as folder:
    site = Path(folder)
    (site / 'u5admin').mkdir()
    for name in ['mysql.php', 'u5admin/formdata2csv.php']:
        shutil.copy2(ROOT / name, site / name)
    (site / 'u5admin/connect.inc.php').write_text(connect)
    (site / 'config.php').write_text(config)
    (site / 'san.inc.php').write_text(san)
    (site / 'run.php').write_text(runner)
    environment = dict(os.environ, U5CSV_TEST_DB=DATABASE)
    result = subprocess.run(PHP + [str(site / 'run.php')], env=environment, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    if result.returncode:
        raise RuntimeError('PHP fixture failed:\n' + result.stderr.decode(errors='replace') + result.stdout.decode(errors='replace'))

if not result.stdout.startswith(b'\xef\xbb\xbf'):
    raise AssertionError('CSV export lost its UTF-8 BOM')
csv = result.stdout.decode('utf-8-sig')
lines = [line for line in csv.splitlines() if line]
headers = [line for line in lines if line.startswith('Status;')]
data = [line for line in lines if not line.startswith('Status;')]
if len(headers) != 1 or len(data) != ROWS:
    raise AssertionError(f'unexpected export shape: {len(headers)} headers, {len(data)} rows')
cells = [cell for line in lines for cell in line.split(';')]
unsafe = [cell for cell in cells if re.match(r'^[\x00-\x20]*[=+@-]', cell)]
if unsafe:
    raise AssertionError(f'{len(unsafe)} spreadsheet formula cells remain, first: {unsafe[:4]!r}')
for expected in [";'=", ";'+", ";'@", ";'-", 'Grüezi € 漢字', 'Français & résumé', 'ordinary;;']:
    if expected not in csv:
        raise AssertionError('export changed or omitted fixture value: ' + repr(expected))
print(f'CSV export checks passed: {ROWS} old/current multilingual rows, empty cells and all formula prefixes protected.')
