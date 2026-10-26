<?php
require dirname(__DIR__).'/mysql.php';
require dirname(__DIR__).'/u5admin/codeassets-migrate.inc.php';
ini_set('mysqli.default_port',(string)(int)getenv('U5CMS_TEST_PORT'));
if (!mysql_connect('127.0.0.1',getenv('U5CMS_TEST_USER') ?: 'root',getenv('U5CMS_TEST_PASSWORD') ?: '')) throw new RuntimeException('Cannot connect to the test server.');
if (($argv[1] ?? '')==='cleanup') {
    $name=$argv[2] ?? '';
    if (!preg_match('/^u5asset_web_[a-f0-9]+$/D',$name)) throw new RuntimeException('Invalid test database name.');
    for ($attempt=0;$attempt<4;$attempt++) {
        if (mysql_query('DROP DATABASE IF EXISTS `'.$name.'`')!==false) exit;
        if ($attempt===3) throw new RuntimeException('Cannot clean disposable database: '.mysql_error());
        usleep(100000);
    }
}
$root=$argv[1];$name='u5asset_web_'.bin2hex(random_bytes(6));
u5CodeMigrationQuery('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4');mysql_select_db($name);
$link=mysql_ensure_link(null);
if (!mysqli_multi_query($link,file_get_contents(dirname(__DIR__).'/u5standard.sql'))) throw new RuntimeException(mysqli_error($link));
do { if ($result=mysqli_store_result($link)) mysqli_free_result($result); } while (mysqli_more_results($link) && mysqli_next_result($link));
if (mysqli_errno($link)) throw new RuntimeException(mysqli_error($link));
u5CodeMigrationQuery('SET NAMES latin1');
u5CodeMigrationQuery('ALTER TABLE resources MODIFY content_1 MEDIUMTEXT CHARACTER SET utf8mb4');
u5CodeMigrationQuery("UPDATE accounts SET email='auditadmin',pw=".u5CodeMigrationValue('{SHA}'.base64_encode(sha1('audit-password',true))).',accadmin=1 WHERE id=1');
$body=<<<'HTML'
[h:]
<img alt="fixture" width="1" height="1" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==">
<form id="proof-form" name="proof-form">
<input id="token-proof" name="token_mandatory" type="hidden" value="">
<input id="level-proof" name="level_mandatory" type="hidden" value="">
<input id="answer-proof" name="cliving_mandatory" type="text" value="">
<script>
window.proofToken=document.getElementById('token-proof');
window.proofEvents=0;
document.getElementById('proof-form').addEventListener('proof',function(){window.proofEvents++;});
window.proofToken.value='kept-token';
document.getElementById('level-proof').value='kept-level';
document.getElementById('answer-proof').value='99';
</script>
<script src="r/accent.js"></script>
<label>Optional private upload<script src="upload"></script></label>
<label>Required private upload<script src="upload_mandatory"></script></label>
<label>Optional public upload<script src="Pupload"></script></label>
<label>Required public upload<script src="Pupload_mandatory"></script></label>
</form>
[:h]
HTML;
$columns=array('name','typ','deleted','hidden');$values=array("'codefixture'","'p'",'0','0');
foreach (range(1,5) as $language) { $columns[]='content_'.$language;$values[]='_latin1 '.u5CodeMigrationValue($body); }
u5CodeMigrationQuery('INSERT INTO resources (`'.implode('`,`',$columns).'`) VALUES ('.implode(',',$values).')');
foreach (array('upload!css'=>'body { color: rgb(1, 2, 3); }','accent!js'=>"window.assetGreeting='\xDCber \x80 &amp;';") as $code=>$source) {
    u5CodeMigrationQuery('INSERT INTO resources (name,content_1,typ,deleted,hidden,lastmut) VALUES ('.u5CodeMigrationValue($code).',_latin1 '.u5CodeMigrationValue($source).",'c',0,0,0)");
}
$config=file_get_contents($root.'/config.php.sample');
$settings=array('host'=>'127.0.0.1','username'=>getenv('U5CMS_TEST_USER') ?: 'root','password'=>getenv('U5CMS_TEST_PASSWORD') ?: '',
    'db'=>$name,'mymail'=>'fixture@example.invalid','allowuseruploads'=>'yes','allowuserPuploads'=>'yes','usesessioninsteadofbasicauth'=>'no','checksaveversionconflictinbackend'=>'all');
foreach ($settings as $variable=>$value) $config=preg_replace('/\$'.preg_quote($variable,'/').'\s*=\s*[\'\"][^\'\"]*[\'\"]\s*;/', '\$'.$variable.'='.var_export($value,true).';',$config,1);
$config.="\n\$blockautoincluderefresherforhtaccessandindex='yes';\n\$backup_send_mail=false;\n";
file_put_contents($root.'/config.php',$config);
u5CodeAssetsMigrate($root);
$source=getenv('U5CMS_TEST_SITE_SOURCE') ? file_get_contents(getenv('U5CMS_TEST_SITE_SOURCE')) : file_get_contents($root.'/r/mobile!js.tpl');
$source=u5CodeAssetReferences($source);
// The real AGOV variant uses its own domain for the common file; its update must follow that domain.
$source=str_replace("'r/mobilebase.js'","'https://info.agov.ch/r/mobilebase.js'",$source);
file_put_contents($root.'/fileversions/web-test-source.php',"<?php return ".var_export(base64_encode($source),true).";");
$mode=<<<'PHP'
<?php
require __DIR__.'/connect.inc.php';
$source=base64_decode(include __DIR__.'/fileversions/web-test-source.php');
$mobile=u5CodeAssetName('jsmobilespecific');$template=u5CodeTemplateName();
mysql_query("UPDATE resources SET content_1='".mysql_real_escape_string($source)."' WHERE name='".mysql_real_escape_string($mobile)."'");
mysql_query("UPDATE resources SET content_1=REPLACE(content_1,'r/mobile.js','https://info.agov.ch/r/mobile.js') WHERE name='".mysql_real_escape_string($template)."'");
u5CodeAssetExport(__DIR__);
header('Content-Type: application/json');echo '{"ready":true}';
PHP;
file_put_contents($root.'/fixture-mode.php',$mode);
// Test-only snapshot endpoint lives in the disposable installation, never in a release.
$snapshot=<<<'PHP'
<?php
require __DIR__.'/connect.inc.php';
$name=$_GET['asset'] ?? '';if (!preg_match('/^[a-zA-Z0-9_!-]+$/D',$name)) exit;
$result=mysql_query("SELECT name,typ,deleted,lastmut,CAST(CONVERT(content_1 USING utf8mb4) AS BINARY) content_1 FROM resources WHERE name='".mysql_real_escape_string($name)."'");
$rows=array();while($row=mysql_fetch_array($result,MYSQL_ASSOC))$rows[]=$row;
$result=mysql_query("SELECT CAST(CONVERT(content_1 USING utf8mb4) AS BINARY) content_1 FROM resources_log WHERE name='".mysql_real_escape_string($name)."'");
$history=array();while($row=mysql_fetch_array($result,MYSQL_ASSOC))$history[]=$row['content_1'];
header('Content-Type: application/json; charset=utf-8');echo json_encode(array('rows'=>$rows,'history'=>$history));
PHP;
file_put_contents($root.'/fixture-snapshot.php',$snapshot);
if (getenv('U5CMS_MAIN_AUDIT') || getenv('U5CMS_MAIN_AUDIT_ONLY')) {
    u5CodeMigrationQuery("CREATE TABLE audit_backup_data(id INT PRIMARY KEY, latin VARCHAR(255) CHARACTER SET latin1 DEFAULT 'default' COMMENT 'custom column', wide MEDIUMTEXT CHARACTER SET utf8mb4, payload BLOB, derived VARCHAR(255) AS (SUBSTRING(wide,1,30)) STORED, KEY latin_prefix(latin(25))) ENGINE=InnoDB");
    $wide="Unicode \xE4\xB8\xAD\xE6\x96\x87 \xF0\x9F\x8C\xB7 &amp; \"quoted\"";
    u5CodeMigrationQuery("INSERT INTO audit_backup_data(id,latin,wide,payload) VALUES (1,_latin1 ".u5CodeMigrationValue("\xDCber \x80 &amp;").",_utf8mb4 ".u5CodeMigrationValue($wide).",X'00ff27225c80')");
    u5CodeMigrationQuery("CREATE TABLE audit_backup_types(id INT AUTO_INCREMENT PRIMARY KEY, choice ENUM('a','quo''te') DEFAULT 'quo''te' COMMENT 'quote: '' and slash: \\\\', flags SET('a','b'), bits BIT(9), fixed BINARY(8), amount DECIMAL(25,7), exact_float FLOAT, exact_double DOUBLE, body JSON, happened TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP, zero_date DATE) ENGINE=MyISAM");
    u5CodeMigrationQuery("SET sql_mode='NO_AUTO_VALUE_ON_ZERO'");
    u5CodeMigrationQuery("INSERT INTO audit_backup_types VALUES(0,'quo''te','a,b',b'101010101',X'ff00275c',-12345678901234.1234567,3.141592653589793,1.2345678901234567,'{\"a\":\"raw &amp;\"}',NULL,'0000-00-00')");
}
echo json_encode(array('database'=>$name));
