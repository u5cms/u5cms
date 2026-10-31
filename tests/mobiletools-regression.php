<?php
require dirname(__DIR__).'/mysql.php';
require dirname(__DIR__).'/u5admin/codeassets-migrate.inc.php';
$checks=0;$fault=null;$parallel=null;$databases=array();$roots=array();
function mobileCheck($value,$label) { global $checks;$checks++;if (!$value) throw new RuntimeException('FAILED: '.$label); }
function u5CodeMigrationTestCheckpoint($phase) {
    global $fault,$parallel;
    if ($phase==='mobiletools:planned' && $parallel) { $callback=$parallel;$parallel=null;$callback(); }
    if ($phase===$fault) { $fault=null;throw new RuntimeException('test interruption '.$phase); }
}
ini_set('mysqli.default_port',(string)(int)getenv('U5CMS_TEST_PORT'));
if (!getenv('U5CMS_TEST_PORT') || !mysql_connect('127.0.0.1',getenv('U5CMS_TEST_USER') ?: 'root',getenv('U5CMS_TEST_PASSWORD') ?: '')) throw new RuntimeException('Use a disposable MariaDB and U5CMS_TEST_PORT.');
function mobileFixture($seed,$charset='utf8mb4') {
    global $databases,$roots;
    $db='u5mobile_'.bin2hex(random_bytes(6));$databases[]=$db;
    u5CodeMigrationQuery('CREATE DATABASE `'.$db.'`');mysql_select_db($db);
    $link=mysql_ensure_link(null);
    if (!mysqli_multi_query($link,file_get_contents(dirname(__DIR__).'/'.$seed))) throw new RuntimeException(mysqli_error($link));
    do {if ($result=mysqli_store_result($link))mysqli_free_result($result);}while(mysqli_more_results($link)&&mysqli_next_result($link));
    if(mysqli_errno($link))throw new RuntimeException(mysqli_error($link));
    u5CodeMigrationQuery('SET NAMES latin1');
    u5CodeMigrationQuery('ALTER TABLE resources MODIFY content_1 MEDIUMTEXT CHARACTER SET '.$charset);
    $root=sys_get_temp_dir().'/u5-mobile-'.bin2hex(random_bytes(6));$roots[]=$root;
    mkdir($root.'/r',0700,true);mkdir($root.'/fileversions',0700,true);
    foreach(glob(dirname(__DIR__).'/r/*.tpl') as $file)copy($file,$root.'/r/'.basename($file));
    // Simulate a site already migrated by the earlier branch, but with its own mobile source.
    u5CodeAssetsMigrateLocked($root);
    if (is_file($root.'/fileversions/codeassets-12.11.2.done.php')===false && function_exists('u5CodeMigrationReadGuarded'))u5CodeAssetsMigrateLocked($root,'12.11.2');
    $source="window.siteSpecific='\xDCber \x80 &amp;';\r\nfunction customSiteMobile() {}\r\n";
    $columns=u5CodeMigrationColumns('resources');$raw=u5CodeMigrationFileText($source,u5CodeMigrationCharset($columns['content_1']));
    u5CodeMigrationQuery('UPDATE resources SET content_1='.u5CodeMigrationValue($raw).", content_2=NULL, content_3='' WHERE name='mobile!js'");
    file_put_contents($root.'/r/mobile!js.tpl',$source);
    u5CodeAssetExport($root);
    return array($root,$raw,$source);
}
function mobileRead($name='mobile!js') {
    $row=u5CodeMigrationRows('SELECT CAST(content_1 AS BINARY) AS source, content_2,content_3 FROM resources WHERE name='.u5CodeMigrationValue($name));return $row[0];
}
function mobileFailure($callback,$label) {
    $failed=false;try {$callback();}catch(RuntimeException $e){$failed=true;}
    mobileCheck($failed,$label);
}
try {
 foreach (array('u5standard.sql','u5conference.sql') as $seed) {
  foreach(array('utf8mb4','latin1') as $charset) {
   list($root,$raw,$source)=mobileFixture($seed,$charset);
   u5CodeAssetsMigrate($root);$row=mobileRead();
   mobileCheck(strpos($row['source'],$raw)===0,$seed.' '.$charset.': exact customized database prefix');
   mobileCheck(substr_count($row['source'],'/* u5CMS mobile controls enhancement 2026-10-31 */')===1,'one addendum');
   mobileCheck($row['content_2']===null&&$row['content_3']==='','NULL and empty variants retained');
   mobileCheck(file_get_contents($root.'/r/mobile!js.tpl')===u5MobileToolsAppend($source),'custom template retained and appended');
   mobileCheck(strpos(file_get_contents($root.'/r/mobile.js'),'u5-mobile-tools-enhancement-style')!==false,'actual export enhanced');
   $before=$row;$tpl=file_get_contents($root.'/r/mobile!js.tpl');u5CodeAssetsMigrate($root);
   mobileCheck(mobileRead()===$before&&file_get_contents($root.'/r/mobile!js.tpl')===$tpl,'repetition is idempotent');
   mobileCheck(is_file($root.'/fileversions/mobiletools-2026-10-31.done.php')&&!is_file($root.'/fileversions/mobiletools-2026-10-31.pending.php'),'completed guarded checkpoint');
   $historySource=u5MobileToolsHistoryRow(array('content_1'=>$raw),u5CodeMigrationColumns('resources'))['content_1'];
   $history=u5CodeMigrationRows('SELECT COUNT(*) AS n FROM resources_log WHERE name=\'mobile!js\' AND BINARY content_1='.u5CodeMigrationValue($historySource));
   mobileCheck((int)$history[0]['n']>0,'original source in history');
  }
  foreach(array('mobiletools:planned','mobiletools:database','mobiletools:templates','mobiletools:exported') as $phase) {
   list($root,$raw)=mobileFixture($seed);$fault=$phase;
   mobileFailure(function()use($root){u5CodeAssetsMigrate($root);},'interrupt '.$phase);
   mobileCheck(is_file($root.'/fileversions/mobiletools-2026-10-31.pending.php'),'interrupted plan retained');
   u5CodeAssetsMigrate($root);
   mobileCheck(substr_count(mobileRead()['source'],'/* u5CMS mobile controls enhancement 2026-10-31 */')===1,'resume '.$phase);
  }
  list($root,$raw)=mobileFixture($seed);
  $parallel=function(){u5CodeMigrationQuery("UPDATE resources SET content_1=CONCAT(content_1,' concurrent edit') WHERE name='mobile!js'");};
  mobileFailure(function()use($root){u5CodeAssetsMigrate($root);},'concurrent database edit denied');
  mobileCheck(strpos(mobileRead()['source'],'concurrent edit')!==false,'concurrent database edit kept');
  u5CodeMigrationQuery('UPDATE resources SET content_1='.u5CodeMigrationValue($raw)." WHERE name='mobile!js'");u5CodeAssetsMigrate($root);
  list($root,$raw,$source)=mobileFixture($seed);
  $parallel=function()use($root){file_put_contents($root.'/r/mobile!js.tpl','concurrent template edit');};
  mobileFailure(function()use($root){u5CodeAssetsMigrate($root);},'concurrent template edit denied');
  mobileCheck(file_get_contents($root.'/r/mobile!js.tpl')==='concurrent template edit'&&mobileRead()['source']===$raw,'concurrent template and untouched DB preserved');
  file_put_contents($root.'/r/mobile!js.tpl',$source);u5CodeAssetsMigrate($root);
 }
 list($root,$raw)=mobileFixture('u5standard.sql');
 u5CodeMigrationQuery("ALTER TABLE resources ADD mobile_test_generated INT AS (LENGTH(content_1)) STORED, ADD mobile_test_auto TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
 u5CodeMigrationQuery("UPDATE resources SET mobile_test_auto='2001-01-01 00:00:00' WHERE name='mobile!js'");
 $fault='mobiletools:database';mobileFailure(function()use($root){u5CodeAssetsMigrate($root);},'interrupt with database-managed columns');
 u5CodeAssetsMigrate($root);
 mobileCheck(substr_count(mobileRead()['source'],'/* u5CMS mobile controls enhancement 2026-10-31 */')===1,'generated and automatic columns permit exact retry');
 list($root,$raw)=mobileFixture('u5standard.sql');
 u5CodeAssetsRename($root,'mobile!js','site-mobile!js');
 u5CodeAssetsMigrate($root);
 mobileCheck(strpos(mobileRead('site-mobile!js')['source'],'u5-mobile-tools-enhancement-style')!==false,'renamed mapped mobile role upgraded');
 mobileCheck(strpos(file_get_contents($root.'/r/site-mobile!js.tpl'),'u5-mobile-tools-enhancement-style')!==false,'renamed reset template upgraded');
 mobileCheck(u5MobileToolsAppend('/* u5-mobile-tools-enhancement-style */')==='/* u5-mobile-tools-enhancement-style */','original AGOV enhancement not duplicated');
 echo 'Mobile tools database: '.$checks." checks passed (both seeds, charsets, custom sources/templates, history, interruptions, retry and concurrent edits).\n";
} finally {
 foreach($databases as $db)mysql_query('DROP DATABASE IF EXISTS `'.$db.'`');
 foreach($roots as $root) {
  $files=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);
  foreach($files as $file){if($file->isDir())rmdir($file->getPathname());else unlink($file->getPathname());}rmdir($root);
 }
}
