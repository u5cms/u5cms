<?php
/* CLI-only setup for a disposable legacy installation. No migration is invoked. */
if (PHP_SAPI!=='cli' || strlen((string)getenv('U5CMS_FIXTURE_PASSWORD'))<32) exit(1);
require dirname(__DIR__).'/mysql.php';
require dirname(__DIR__).'/u5admin/resource-rename.inc.php';
ini_set('mysqli.default_port',(string)(int)getenv('U5CMS_TEST_PORT'));
mysql_connect('127.0.0.1',getenv('U5CMS_TEST_USER') ?: 'root',getenv('U5CMS_TEST_PASSWORD') ?: '');
if (($argv[1] ?? '')==='cleanup') {
    if (!preg_match('/^u5rename_web_[a-f0-9]+$/D',$argv[2] ?? '')) exit(2);
    u5CodeMigrationQuery('DROP DATABASE `'.$argv[2].'`');exit;
}
$root=$argv[1];$database='u5rename_web_'.bin2hex(random_bytes(6));
u5CodeMigrationQuery('CREATE DATABASE `'.$database.'` CHARACTER SET utf8mb4');mysql_select_db($database);
$link=mysql_ensure_link(null);
$seed=str_replace('SET storage_engine=MYISAM','SET default_storage_engine=MYISAM',file_get_contents(dirname(__DIR__).'/u5standard.sql'));
if (!mysqli_multi_query($link,$seed)) throw new RuntimeException(mysqli_error($link));
do {if ($result=mysqli_store_result($link)) mysqli_free_result($result);} while (mysqli_more_results($link) && mysqli_next_result($link));
if (mysqli_errno($link)) throw new RuntimeException(mysqli_error($link));
u5CodeMigrationQuery('SET NAMES latin1');
u5CodeMigrationQuery("UPDATE accounts SET email='auditadmin',pw=".u5CodeMigrationValue('{SHA}'.base64_encode(sha1(getenv('U5CMS_FIXTURE_PASSWORD'),true))).',accadmin=1 WHERE id=1');
$config=file_get_contents($root.'/config.php.sample');
$settings=array('host'=>'127.0.0.1','username'=>getenv('U5CMS_TEST_USER') ?: 'root','password'=>getenv('U5CMS_TEST_PASSWORD') ?: '',
    'db'=>$database,'mymail'=>'fixture@example.invalid','usesessioninsteadofbasicauth'=>'no');
foreach ($settings as $variable=>$value) $config=preg_replace('/\$'.preg_quote($variable,'/').'\s*=\s*[\'\"][^\'\"]*[\'\"]\s*;/', '\$'.$variable.'='.var_export($value,true).';',$config,1);
$config.="\n\$blockautoincluderefresherforhtaccessandindex='yes';\n\$backup_send_mail=false;\n\$archiveRqHIADRI='yes';\n\$cssRqHIADRI='yes';\n";
file_put_contents($root.'/config.php',$config);
if (function_exists('u5CodeAssetsMigrate')) u5CodeAssetsMigrate($root);
u5CodeAssetExport($root);
$export=function_exists('u5CodeTemplatePath') ? basename(u5CodeTemplatePath($root)) : 'htmltemplate.css';
echo json_encode(array('database'=>$database,'export'=>$export));
