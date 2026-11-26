<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
// Disposable connection-private SQL tables and synthetic data only.
error_reporting(E_ERROR | E_PARSE);
ini_set('default_charset', 'Windows-1252');
$scenario = $argv[1];
require 'mysql.php';
mysql_connect((getenv('U5CMS_TEST_HOST') ?: '127.0.0.1').':'.(getenv('U5CMS_TEST_PORT') ?: '3306'), getenv('U5CMS_TEST_USER') ?: 'root', getenv('U5CMS_TEST_PASSWORD') ?: '');
mysql_select_db(getenv('U5CMS_TEST_DB') ?: 'test');
mysql_set_charset('latin1');
function evolutionQuery($sql) {
    $result = mysql_query($sql);
    if ($result === false) throw new RuntimeException(mysql_error());
    return $result;
}
function tnuoc($value) { return is_countable($value) ? count($value) : 0; }
function u5flatidnlower($value) { return strtolower((string)$value); }
function htmlXspecialchars($value) { return htmlspecialchars($value ?? ''); }
function htmlXentities($value) { return htmlentities($value ?? ''); }
function render($value) { return $value; }
function def(...$values) { foreach ($values as $value) if ($value !== '') return $value; return ''; }
function pwdhsh($value) { return hash('sha256', $value); }
evolutionQuery('CREATE TEMPORARY TABLE formdata(id INT AUTO_INCREMENT PRIMARY KEY, formname VARCHAR(120), headcsv MEDIUMTEXT, datacsv MEDIUMTEXT, time INT, ip TEXT, authuser TEXT, humantime TEXT, lastmut INT, status INT DEFAULT 1, notes TEXT) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4');
evolutionQuery('CREATE TEMPORARY TABLE resources(name VARCHAR(120),deleted INT,content_1 MEDIUMTEXT,content_2 MEDIUMTEXT,content_3 MEDIUMTEXT,content_4 MEDIUMTEXT,content_5 MEDIUMTEXT)');
if (is_file('onlymine.inc.php')) require 'onlymine.inc.php';
$mfwhereclause = 'status<5';
$checksaveversionconflictinfrontendforms = 'all';
$allowmultiu5formloginsonfrontendpages = 'yes';
$formSenderEvidenceRequired = $scenario === 'explicit-off' ? 'no' : null;
$scripturi = 'https://fixture.example/formsave.php';
$mymail = 'owner@example.org';
$_SERVER = array('REMOTE_ADDR'=>'127.0.0.1','HTTP_HOST'=>'fixture.example','PHP_AUTH_USER'=>'reader@example.org');
$_GET = array('n'=>'complexform','l'=>'de');
$post = array('dossiertyp_mandatory'=>'VOID','name_mandatory'=>"M\xfcller &amp; Co; \"alpha\"",'youremail_mandatory'=>'reader@example.org','addmails_mandatory'=>"deputy@example.org\nsecond@example.org",'amt_mandatory'=>'TEST','pers_mandatory'=>'Synthetic contact','dossier_mandatory'=>'synthetic-record','message'=>"Line one; line two\r\n&#339;uvre \x80",'ouremail'=>'owner@example.org','thankssubject'=>'Fixture receipt','thankstext'=>'Opening','thanksgreetings'=>'Closing','thanks'=>'saved','in2my'=>'off','em2me'=>'off','em2cu'=>'off','nt2me'=>'','nt2cu'=>'');
if ($scenario === 'mail') { $post['in2my']='on'; $post['em2me']='on'; $post['em2cu']='on'; }
if ($scenario === 'exclude-columns') { $post['nt2me']='message,pers_mandatory'; $post['nt2cu']='message'; }
if ($scenario === 'callback') $post['thanks']='formSaved()';
if (in_array($scenario,array('replace','conflict','foreign','monofill','shared'),true)) {
    $post['ed2cu']='on'; $post['firstsaverwins']='1700000001';
}
$html = '<form>'.implode('',array_map(function($key){return '<input name="'.$key.'">';},array_keys($post))).'</form>';
if ($scenario === 'monofill') $html.='<!-- ifrmonofill -->';
if ($scenario === 'shared') $html.='<!-- ifrmonofillshared -->';
foreach (array('complexform','secondstep') as $form) evolutionQuery("INSERT INTO resources VALUES ('$form',0,'".mysql_real_escape_string($html)."','','','','')");
if (in_array($scenario,array('replace','conflict','foreign','monofill','shared','admin-edit','admin-delete'),true)) {
    $author = in_array($scenario,array('foreign','shared'),true) ? 'other@example.org' : 'reader@example.org';
    evolutionQuery("INSERT INTO formdata VALUES (7,'complexform','old-head','old-data',1700000000,'127.0.0.1','$author','2023.11.14 22:13:20',1700000000,2,'retain this note')");
}
if ($scenario === 'conflict') $post['firstsaverwins']='1699999999';
if ($scenario === 'blank') $post['name_mandatory']='   ';
if ($scenario === 'invalid-email') $post['youremail_mandatory']='invalid';
if ($scenario === 'unexpected-field') $post['not_in_source']='rejected';
if ($scenario === 'empty-post') $post=array();
$capture = array();
register_shutdown_function(function() use (&$capture) {
    $rows=array();
    $result=evolutionQuery('SELECT * FROM formdata ORDER BY id');
    while ($row=mysql_fetch_assoc($result)) {
        unset($row['time'],$row['humantime'],$row['lastmut']);
        $row['ip']=preg_replace('/[0-9]{4}\.[0-9]{2}\.[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/','<timestamp>',$row['ip']);
        $rows[]=array_map(function($value){return $value === null ? null : base64_encode((string)$value);},$row);
    }
    $html=ob_get_clean();
    $summary=array('rows'=>$rows,'mail'=>$capture,'conflict'=>strpos($html,'You cannot save your data')!==false,'field_error'=>strpos($html,'ERROR: Data cannot be saved')!==false,'callback'=>strpos($html,'parent.formSaved')!==false,'completed'=>strpos($html,'function firstsaverwins')!==false);
    file_put_contents('result.json',json_encode($summary,JSON_THROW_ON_ERROR));
});
ob_start();
$_POST=$post;
if ($scenario==='admin-edit' || $scenario==='admin-delete') {
    $_GET += array('id'=>'7','ti'=>'1700000000','hu'=>'2023.11.14 22:13:20','st'=>'2','a'=>'1');
    require $scenario==='admin-edit' ? 'editformsave.php' : 'delformsave.php';
} else {
    require 'formsave.php';
    if ($scenario==='repeat') { $_POST=$post; require 'formsave.php'; }
    if ($scenario==='two-steps') { $_POST=$post; $_GET['n']='secondstep'; require 'formsave.php'; }
}
