"""Exercise the shipped form handlers using connection-private MariaDB tables."""
import json
import os
from pathlib import Path
import shutil
import subprocess
import tempfile

ROOT = Path(__file__).resolve().parents[1]
PHP = os.environ.get('U5_TEST_PHP', 'php')

with tempfile.TemporaryDirectory(prefix='u5-form-') as tmp:
    site = Path(tmp)
    for folder in ['u5admin', 'fileversions', 'mail', 'simplesaml']:
        (site / folder).mkdir()
    for name in ['formsave.php', 'editformsave.php', 'delformsave.php', 'formdata-write-lock.inc.php', 'form-evidence.inc.php',
                 'onlymine.inc.php', 'mysql.php', 'mail/addresses.php', 'u5admin/formdata2csv.php',
                 'u5admin/formdata.php', 'u5admin/formdata2.php', 'u5admin/formdatamail.php', 'request.inc.php', 'u5admin/cleanuseruploads.php', 'u5admin/formdata_n.php','u5admin/formdata_h.php',
                 'u5admin/formdata.query.php','u5admin/formdata.intro.php','u5admin/formdata.filterlabel.php', 'u5admin/formdatacookies.inc.php']:
        if (ROOT / name).exists():
            shutil.copy(ROOT / name, site / name)
    shutil.copy(ROOT / 'docs/form-evidence-schema.sql', site / 'schema.sql')
    shutil.copytree(ROOT / 'mail/idn', site / 'mail/idn')
    shutil.copytree(ROOT / 'mail/normalizer', site / 'mail/normalizer')
    for name in ['render.inc.php', 'loginformsave.inc.php', 'keyfocus.inc.php', 'ob.php', 'config.php',
                 'saveversionconflict.inc.php', 'editreadrights.inc.php', 'delreadrights.inc.php',
                 'san.inc.php', 'u5admin/connect.inc.php', 'u5admin/accadmin.inc.php', 'u5admin/backendcss.php', 'u5admin/mailing.inc.php', 'u5admin/t1.php']:
        (site / name).write_text('<?php\n')
    (site / 'simplesaml/_include.php').write_text("""<?php
namespace SimpleSAML;
class Session {
    static function getSessionFromRequest() { return new self(); }
    function cleanup() {}
}
namespace SimpleSAML\\Auth;
class Simple {
    function __construct($source) {}
    function isAuthenticated() { return $GLOBALS['scenario'] !== 'expired'; }
    function getAuthData($key) { return ['id'=>'response-fixture','issuer'=>'issuer-fixture']; }
    function getAttributes() { return ['mail'=>['sender@example.org']]; }
}
""")
    (site / 'connect.inc.php').write_text("""<?php
require_once __DIR__.'/mysql.php';
require_once __DIR__.'/onlymine.inc.php';
function checked($sql) {
    $r=mysql_query($sql);
    if ($r===false) throw new RuntimeException(mysql_error());
    return $r;
}
function tnuoc($x) { return is_countable($x) ? count($x) : 0; }
function u5flatidnlower($x) { return strtolower($x); }
function htmlXentities($x) { return htmlentities($x ?? ''); }
function htmlXspecialchars($x) { return htmlspecialchars($x ?? ''); }
function def(...$v) { return $v[0]; }
function render($v) { return $v; }
function u5FormWriteRecord($form) { file_put_contents('writer.txt',$form); return true; }
$host=getenv('U5CMS_TEST_HOST') ?: '127.0.0.1';
$port=getenv('U5CMS_TEST_PORT') ?: '3306';
mysql_connect($host.':'.$port,getenv('U5CMS_TEST_USER') ?: 'root',getenv('U5CMS_TEST_PASSWORD') ?: '');
mysql_select_db(getenv('U5CMS_TEST_DB') ?: 'test');
mysql_set_charset('latin1');
checked('CREATE TEMPORARY TABLE formdata(id INT AUTO_INCREMENT PRIMARY KEY, formname VARCHAR(120), headcsv MEDIUMTEXT,
 datacsv MEDIUMTEXT,time INT,ip TEXT,authuser TEXT,humantime TEXT,lastmut INT,status INT DEFAULT 1,notes TEXT) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4');
checked('CREATE TEMPORARY TABLE resources(name VARCHAR(120),deleted INT,content_1 TEXT,content_2 TEXT,content_3 TEXT,content_4 TEXT,content_5 TEXT)');
if (!str_starts_with($scenario,'legacy-') && !in_array($scenario,['off','writer','unset','custom-edit','custom-edit-explicit','ordinary-monofill'],true)) {
    checked('ALTER TABLE formdata ADD sender_evidence_json MEDIUMTEXT NULL');
    $ddl=substr(file_get_contents('schema.sql'),strpos(file_get_contents('schema.sql'),'CREATE TABLE'));
    if ($scenario!=='no-journal') checked(str_replace('CREATE TABLE','CREATE TEMPORARY TABLE',$ddl));
    checked('CREATE TEMPORARY TABLE saml_response_archive(id INT,response_id TEXT,authsource TEXT,idp_entity_id TEXT,
         attributes_json TEXT,saml_response_b64 TEXT,saml_response_sha256 TEXT)');
    $xml='<p:Response xmlns:p="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:s="urn:oasis:names:tc:SAML:2.0:assertion" ID="response-fixture"><s:Assertion><s:AttributeStatement><s:Attribute Name="mail"><s:AttributeValue>sender@example.org</s:AttributeValue></s:Attribute></s:AttributeStatement></s:Assertion></p:Response>';
    $raw=base64_encode($xml);
    $attr=json_encode(['mail'=>['sender@example.org']]);
    $digest=$scenario==='digest-error' ? str_repeat('0',64) : hash('sha256',$raw);
    checked("INSERT INTO saml_response_archive VALUES (7,'response-fixture','sp-fixture','issuer-fixture','$attr','$raw','$digest')");
}
$html='<form name="u5form"><input name="youremail"><input name="message_mandatory"><input type="hidden" name="ouremail" value="owner@example.org"><input type="hidden" name="em2me" value="on"><input type="hidden" name="em2cu" value="on"></form>';
if (in_array($scenario,['ordinary-monofill','protected-monofill'],true)) {
    $html .= '<!-- ifrmonofill -->';
    $allowmultiu5formloginsonfrontendpages='yes';
}
if ($scenario==='writer' || $scenario==='protected-writer') $html .= '<!-- u5cms-form-writer -->';
if ($scenario==='custom-edit' || $scenario==='custom-edit-explicit') $html .= '<input name="ed2cu"><input name="frstsvrwnspgldtmstp">';
checked("INSERT INTO resources VALUES ('caseform',0,'".mysql_real_escape_string($html)."','','','','')");
""")
    (site / 'mail/delivery.php').write_text("""<?php
function u5mail_send($message) {
    file_put_contents(getcwd().'/mails.jsonl',json_encode($message)."\\n",FILE_APPEND);
    return $GLOBALS['scenario']!=='uncertain';
}
""")
    (site / 'zendmail.php').write_text("""<?php
file_put_contents(getcwd().'/mails.jsonl',json_encode(['to'=>[$zendto],'body'=>$zendmessage])."\\n",FILE_APPEND);""")
    (site / 'worker.php').write_text("""<?php
error_reporting(E_ERROR | E_PARSE);
ob_start();
$scenario=$argv[1];
$formSenderEvidenceRequired='saml-archive-v1';
if ($scenario==='unset'||$scenario==='writer'||$scenario==='custom-edit') unset($formSenderEvidenceRequired);
if (str_starts_with($scenario,'legacy-')) unset($formSenderEvidenceRequired);
if (in_array($scenario,['off','custom-edit-explicit','ordinary-monofill'],true)) $formSenderEvidenceRequired='no';
if ($scenario==='old-mode') $formSenderEvidenceRequired='yes';
$formSenderEvidenceSigningKey=str_repeat('synthetic-key-',4);
$u5samlsspconfigdir=__DIR__;
$u5samlsspauthsourcekey='sp-fixture';
$u5samlmockauth='no';
$mymail='admin@example.org';
$formEvidenceInternalRecipients=$scenario==='unlisted' ? [$mymail] : ['owner@example.org'];
$mfwhereclause='status<5';
$scripturi='https://site.example/formsave.php';
$_SERVER['REMOTE_ADDR']='127.0.0.1';
$_SERVER['HTTP_HOST']='site.example';
$_SERVER['PHP_AUTH_USER']=$scenario==='wrong-account' ? 'wrong@example.org' : 'sender@example.org';
$_GET=['n'=>'caseform','l'=>'de'];
$_POST=['youremail'=>$scenario==='changed' ? 'other@example.org' : 'sender@example.org','message_mandatory'=>'A message',
    'ouremail'=>'untrusted@example.org','em2me'=>'off','em2cu'=>'on'];
if ($scenario==='missing') unset($_POST['youremail']);
if ($scenario==='custom-edit'||$scenario==='custom-edit-explicit') $_POST['ed2cu']='on';
$u5Initial=$_POST;
register_shutdown_function(function(){
    $rows=$events=[];
    if (function_exists('mysql_query')) {
        $r=mysql_query('SELECT * FROM formdata ORDER BY id');
        while ($r && $v=mysql_fetch_assoc($r)) $rows[]=$v;
        $r=mysql_query('SELECT * FROM form_evidence_mail');
        while ($r && $v=mysql_fetch_assoc($r)) $events[]=$v;
    }
    file_put_contents(__DIR__.'/results.json',json_encode([$rows,$events,http_response_code(),function_exists('u5EvidenceIdentity')],JSON_INVALID_UTF8_SUBSTITUTE));
});
require 'formsave.php';
if ($scenario==='custom-edit'||$scenario==='custom-edit-explicit') { $_POST=$u5Initial; require 'formsave.php'; }
if ($scenario==='edit-delete') {
    $row=mysql_fetch_assoc(checked('SELECT * FROM formdata LIMIT 1'));
    foreach (['editformsave.php','delformsave.php'] as $entry) {
        $_GET+=['id'=>$row['id'],'ti'=>$row['time'],'hu'=>$row['humantime'],'st'=>1,'a'=>1];
        $_POST=['message_mandatory'=>'Amended message'];
        require $entry;
    }
}
if (str_contains($scenario,'view-')) {
    $entry = substr($scenario,strpos($scenario,'view-')+5).'.php';
    $_GET=['n'=>'caseform','f'=>'','o'=>'','s'=>0];
    $formdataRqHIADRI='yes';
    $keywords=[];
    chdir('u5admin'); ob_start(); require $entry;
    file_put_contents(__DIR__.'/view.html',ob_get_clean()); chdir('..');
}
if (str_ends_with($scenario,'cleanup')) {
    $upload=__DIR__.'/fileversions/useruploads';
    if (!is_dir($upload)) mkdir($upload);
    file_put_contents($upload.'/old.dat','fixture');
    touch($upload.'/old.dat',time()-40*86400);
    chdir('u5admin'); require 'cleanuseruploads.php'; chdir('..');
}
if (in_array($scenario,['off','unset','changed','edit-delete'],true)) {
    $_GET=['n'=>'caseform','s'=>$scenario==='edit-delete'?5:0,'f'=>''];
    chdir('u5admin');
    ob_start(); require 'formdata2csv.php'; file_put_contents(__DIR__.'/export.csv',ob_get_clean()); chdir('..');
}
""")
    for scenario in ['unset','off','writer','custom-edit','custom-edit-explicit','same','changed','missing','expired',
                     'digest-error','wrong-account','old-mode','no-journal','unlisted','protected-writer','uncertain','edit-delete','legacy-cleanup','secure-cleanup',
                     'legacy-view-formdata_n','legacy-view-formdata_h','legacy-view-formdata2','legacy-view-formdatamail',
                     'secure-view-formdata2','secure-view-formdatamail','ordinary-monofill','protected-monofill']:
        for name in ['results.json','mails.jsonl','export.csv','writer.txt']:
            (site / name).unlink(missing_ok=True)
        run = subprocess.run([PHP, 'worker.php', scenario], cwd=site, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        assert run.returncode == 0, (scenario, run.stderr[-1500:], run.stdout[-400:])
        rows, events, status, loaded = json.loads((site / 'results.json').read_text())
        mails = [json.loads(s) for s in (site / 'mails.jsonl').read_text().splitlines()] if (site / 'mails.jsonl').exists() else []
        if 'view-' in scenario:
            html = (site/'view.html').read_bytes()
            assert b'caseform' in html
            assert (b'Sender evidence' in html)==scenario.startswith('secure-')
            if scenario.startswith('secure-'):
                assert b'verified' in html
        if scenario.endswith('cleanup'):
            assert (site/'fileversions/useruploads/old.dat').exists()==scenario.startswith('secure-')
        if scenario == 'protected-monofill':
            assert status == 409 and not rows and not mails and not events, (scenario, status, len(rows))
        elif scenario in ['expired','digest-error','wrong-account','old-mode','no-journal','unlisted','protected-writer']:
            assert not rows and not events and not mails and status in [403,409,503], (scenario,status)
        elif scenario.startswith('legacy-') or scenario in ['unset','off','writer','custom-edit','custom-edit-explicit','ordinary-monofill']:
            expected = 0 if scenario=='writer' else 2 if scenario.startswith('custom-edit') else 1
            assert len(rows)==expected and not loaded and not events, (scenario,len(rows),loaded)
            if scenario=='writer':
                assert (site/'writer.txt').read_text()=='caseform'
            if scenario in ['unset','off']:
                export = (site / 'export.csv').read_bytes()
                assert b'Status;Notes;Authuser;' in export and b'Sender evidence;' not in export
        else:
            assert len(rows)==(3 if scenario=='edit-delete' else 1), (scenario,len(rows))
            envelope = json.loads(rows[0]['sender_evidence_json'])
            record = envelope['record']
            assert all(row['sender_evidence_json']==rows[0]['sender_evidence_json'] for row in rows)
            expected = 'changed' if scenario=='changed' else 'missing' if scenario=='missing' else 'match'
            assert record['identity']['comparison']==expected
            assert len(events)==2*len(mails) and len(mails)==(2 if scenario=='missing' else 3), (scenario,len(events),len(mails))
            assert {e['event_state'] for e in events}=={'started','unconfirmed' if scenario=='uncertain' else 'accepted'}
            for message in mails:
                assert 'untrusted@example.org' not in message['to']
                assert record['id'] in message['body'] and 'PHA6UmVzcG9uc2U' not in message['body']
                if message['to'][0] not in ['admin@example.org','owner@example.org']:
                    assert 'A message' not in message['body'] and 'Authenticated sender' not in message['body']
            if scenario in ['changed','edit-delete']:
                assert b'Sender evidence;' in (site/'export.csv').read_bytes()
                assert b'"verified"' in (site/'export.csv').read_bytes()
            if scenario=='uncertain':
                assert b'Mail delivery is unconfirmed' in run.stdout
        print(scenario, 'OK', flush=True)
