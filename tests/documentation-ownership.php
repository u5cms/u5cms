<?php
// A disposable SQL exercise: compare access policy with unchanged stored owners.
if (PHP_SAPI !== 'cli' || !getenv('U5CMS_TEST_PORT')) exit(2);
$_SERVER['REQUEST_URI'] = '/';
require dirname(__DIR__) . '/mysql.php';
require dirname(__DIR__) . '/myfunctions.inc.php';
require dirname(__DIR__) . '/u5admin/u5idn.inc.php';
require dirname(__DIR__) . '/onlymine.inc.php';
ini_set('mysqli.default_port', getenv('U5CMS_TEST_PORT'));
mysql_connect('127.0.0.1', getenv('U5CMS_TEST_USER') ?: 'root', getenv('U5CMS_TEST_PASSWORD') ?: '');
mysql_select_db(getenv('U5CMS_TEST_DB') ?: 'test');
mysql_query('CREATE TEMPORARY TABLE formdata(id INT PRIMARY KEY,formname VARCHAR(64),authuser VARCHAR(255),status INT) CHARACTER SET utf8mb4');
mysql_query("INSERT INTO formdata VALUES (1,'applications','alice@office.example',1),(2,'applications','bob@office.example',1),(3,'applications','charlie@sub.office.example',1),(4,'applications','dana@other.example',1),(5,'different','alice@office.example',1)");
function documentationRows($where='') {
    $result=mysql_query("SELECT id,authuser FROM formdata WHERE formname='applications' AND status=1".$where.' ORDER BY id');
    if (!$result) throw new RuntimeException(mysql_error());
    $rows=array();while($row=mysql_fetch_assoc($result))$rows[]=$row;return $rows;
}
function documentationIds() {
    return array_map(function($row){return (int)$row['id'];},documentationRows(u5onlymineSelfSql('applications')));
}
function documentationCase($name,$expected) {
    $actual=documentationIds();
    if ($actual!==$expected) throw new RuntimeException($name.': unexpected record selection');
    $GLOBALS['documentationCases'][]=array('case'=>$name,'visible_ids'=>$actual,'passed'=>true);
}
$original=hash('sha256',json_encode(documentationRows()));
$documentationCases=array();$unknown='ok';$_SERVER['PHP_AUTH_USER']='alice@office.example';
$u5onlyminescope='user';$u5onlymineadmindomains=array();
documentationCase('Alice in personal scope',array(1));
$u5onlyminescope='domain';documentationCase('Alice shares the exact organisation domain',array(1,2));
$_SERVER['PHP_AUTH_USER']='charlie@sub.office.example';documentationCase('A subdomain remains separate',array(3));
$_SERVER['PHP_AUTH_USER']='administrator@central.example';documentationCase('A central-sounding name grants no additional access',array());
$u5onlymineadmindomains=array('central.example');documentationCase('An explicitly configured central domain spans organisations',array(1,2,3,4));
$_SERVER['PHP_AUTH_USER']='administrator@sub.central.example';documentationCase('The central allowance does not include subdomains',array());
$unknown='unknown';$_SERVER['PHP_AUTH_USER']='administrator@central.example';documentationCase('An unverified identity cannot use the central allowance',array());
$unknown='ok';$_SERVER['PHP_AUTH_USER']='alice@office.example';$u5onlyminescope='user';documentationCase('Returning to personal scope restores the original selection',array(1));
if (hash('sha256',json_encode(documentationRows()))!==$original)throw new RuntimeException('The policy exercise changed stored owners');
$documentationCases[]=array('case'=>'Policy changes did not rewrite stored owners','passed'=>true);
echo json_encode(array('checks'=>count($documentationCases),'cases'=>$documentationCases),JSON_PRETTY_PRINT)."\n";
