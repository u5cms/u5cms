<?php
// Run with a local MariaDB fixture. Only temporary tables are used.
if (PHP_SAPI !== 'cli') exit;
require_once __DIR__.'/../onlymine.inc.php';
require_once __DIR__.'/../mysql.php';
$_SERVER['REQUEST_URI'] = '/';
require_once __DIR__.'/../myfunctions.inc.php';
require_once __DIR__.'/../u5admin/u5idn.inc.php';

function checkOnlymine($actual, $expected, $case) {
    if ($actual !== $expected) throw new RuntimeException($case . ': ' . var_export($actual, true));
}
function u5FormWriteRecord($form) {
    checkOnlymine($form, 'test', 'canonical form name passed to writer');
    $GLOBALS['fixtureWrites']++;
    return true;
}
function matchingIds() {
    $result = mysql_query("SELECT id FROM formdata WHERE formname='test' AND status=1".u5onlymineSelfSql('test')." ORDER BY id");
    $ids = array();
    while ($row = mysql_fetch_row($result)) $ids[] = (int)$row[0];
    return $ids;
}
$port = getenv('U5CMS_TEST_PORT');
if (!$port) throw new RuntimeException('Set U5CMS_TEST_PORT for a local MariaDB fixture.');
ini_set('mysqli.default_port', $port);
mysql_connect('127.0.0.1', getenv('U5CMS_TEST_USER') ?: 'root', getenv('U5CMS_TEST_PASSWORD') ?: '');
mysql_select_db(getenv('U5CMS_TEST_DB') ?: 'test');
mysql_query('SET NAMES latin1');
mysql_query('CREATE TEMPORARY TABLE formdata(id INT PRIMARY KEY, formname VARCHAR(64), authuser VARCHAR(255), status INT) CHARACTER SET utf8mb4');
mysql_query("INSERT INTO formdata VALUES (1,'test','anna@amt.example',1),(2,'test','beat@AMT.EXAMPLE',1),(3,'test','anna@sub.amt.example',1),(4,'test','anna@other.example',1),(5,'test','invalid',1),(6,'other','anna@amt.example',1),(7,'test','anna@amt.example',6)");
mysql_query('CREATE TEMPORARY TABLE resources(name VARCHAR(64),deleted INT,content_1 TEXT,content_2 TEXT,content_3 TEXT,content_4 TEXT,content_5 TEXT)');
mysql_query("INSERT INTO resources VALUES ('test',0,'aa','bb','','',''),('other',0,'other','','','','')");
$_GET = array('n'=>'test','c'=>'other','id'=>'2','a'=>'0');
$_SERVER['PHP_AUTH_USER']='anna@amt.example';
$unknown = 'ok';
checkOnlymine(matchingIds(), array(1), 'default user scope');
checkOnlymine(u5onlymineOwnsRequestedRecord(), false, 'default colleague');
checkOnlymine(u5onlymineFormRules(), 'other', 'legacy display-page rules');
$u5onlyminescope='typo';
checkOnlymine(matchingIds(), array(1), 'unknown scope uses user matching');
$u5onlyminescope='domain';
checkOnlymine(u5onlymineFormRules(), 'aabb', 'domain scope checks the record form');
checkOnlymine(u5onlymineHasMarker('SELF_test','SELF_'), true, 'marker at start');
checkOnlymine(matchingIds(), array(1,2), 'exact domain list');
checkOnlymine(u5onlymineOwnsRequestedRecord(), true, 'colleague record');
foreach (array(3,4,5,6,7) as $id) {
    $_GET['id']=(string)$id;
    checkOnlymine(u5onlymineOwnsRequestedRecord(), false, 'foreign, invalid, other form, or archived record '.$id);
}
$_GET['id']='2';
foreach (array('add'=>'ADD','edit'=>'EDIT','del'=>'DELETE') as $file=>$operation) {
    $cmdstring='SELF_TO'.$operation.'ITEMS_LOGINS_SAME_AS_[members]';
    require __DIR__.'/../check'.$file.'self.inc.php';
}
$unknown='unknown';
checkOnlymine(matchingIds(), array(), 'unvalidated identity');
checkOnlymine(u5onlymineOwnsRequestedRecord(), false, 'unvalidated action');
$unknown='ok';
$_SERVER['PHP_AUTH_USER']='lea@office.example';
checkOnlymine(u5onlymineCanWriteAll(), false, 'no general administrator by default');
$u5onlymineadmindomains=array('office.example');
$unknown='unknown';
checkOnlymine(u5onlymineCanWriteAll(), false, 'unverified central identity');
checkOnlymine(matchingIds(), array(), 'unverified central list');
$unknown='ok';
$_SERVER['PHP_AUTH_USER']='lea@sub.office.example';
checkOnlymine(u5onlymineCanWriteAll(), false, 'central subdomain');
$_SERVER['PHP_AUTH_USER']='lea@OFFICE.EXAMPLE';
checkOnlymine(u5onlymineCanWriteAll(), true, 'central exact domain');
checkOnlymine(matchingIds(), array(1,2,3,4,5), 'central administration');
$_SERVER['PHP_AUTH_USER']='invalid';
checkOnlymine(matchingIds(), array(), 'invalid identity');
checkOnlymine(u5onlymineRemotepart('x@y@z'), '', 'invalid address');
if (function_exists('idn_to_ascii')) {
    checkOnlymine(u5onlymineRemotepart('a@b'.chr(252).'ro.example'), 'xn--bro-hoa.example', 'IDN stored bytes');
    checkOnlymine(u5onlymineDomainHash('a@b&#252;ro.example'), hash('sha256','xn--bro-hoa.example'), 'IDN template hash');
}
checkOnlymine(u5FormUsesSiteWriter(array('content_1'=>'standard')), false, 'ordinary form');
checkOnlymine(u5FormUsesSiteWriter(array('content_5'=>'<!-- u5cms-form-writer -->')), true, 'declaration in fifth language');
checkOnlymine(u5FormRunSiteWriter('other', false), false, 'standard write');
$fixtureWrites=0;
checkOnlymine(u5FormRunSiteWriter('test', true), true, 'site writer');
checkOnlymine($fixtureWrites, 1, 'exactly one write');
echo "Onlymine ownership and form dispatch passed.\n";
