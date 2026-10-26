<?php
function u5MainDiffCore() {
    $body='{"label":"'."\xDCber \x80".'","selector":"[cssbase]","html":"<p>[cssbase]</p>"}';
    $expected='{"label":"'."\xDCber \x80".'","selector":"[cssbase]","html":"<p>[base!css]</p>"}';
    foreach (array('html','json') as $format) sameAsset(u5CodeAssetReferences($body,array(),0,$format),$expected,'Latin1 JSON preserves selectors and rewrites embedded CMS HTML: '.$format);
    $bytes='';for ($value=0;$value<256;$value++) $bytes.=chr($value);
    sameAsset(u5CodeAssetLatin1(u5CodeAssetUtf8($bytes)),$bytes,'all Windows-1252 byte values survive an exact UTF8 round trip');
    $raw=u5CodeAssetLatin1(json_encode(array('value'=>u5CodeAssetUtf8($bytes),'html'=>'<p>[cssbase]</p>'),JSON_UNESCAPED_UNICODE));
    $after=u5CodeAssetReferences($raw);
    sameAsset(json_decode(u5CodeAssetUtf8($after),true)['value'],u5CodeAssetUtf8($bytes),'Latin1 JSON leaves control escapes, quotes and all 256 byte values intact');
    foreach (u5MainDiffUsageCases() as $text=>$expected) sameAsset(preg_match('~'.u5CodeAssetUsagePattern('demo_name!extra!css').'~i',str_replace('\\/','/',$text))===1,$expected,'flat code dependency boundaries: '.$text);
    require_once dirname(__DIR__).'/u5admin/databasebackup.inc.php';
    $directory=sys_get_temp_dir().'/u5-backup-failure-'.bin2hex(random_bytes(5));mkdir($directory,0700);
    $target=$directory.'/previous.sql';file_put_contents($target,'previous complete SQL backup');
    foreach (array(false,true) as $compressed) {
        if ($compressed && !function_exists('gzopen')) continue;
        $output=new U5DatabaseBackupOutput($directory,$target,$compressed);$output->append('replacement');$output->finish();
        $failed=false;try {$output->publish();}catch(RuntimeException $error){$failed=true;}
        checkAsset($failed,'SQL backup never overwrites an existing publication target');unset($output);
        sameAsset(file_get_contents($target),'previous complete SQL backup','failed SQL publication keeps its previous complete file');
        sameAsset(count(glob($directory.'/u5db_*')),0,'failed SQL publication cleans its staging file');
    }
    if (!class_exists('ZipArchive')) {
        require_once dirname(__DIR__).'/src/Zipper.php';
        $failed=false;try {new U5cms\Zipper($directory.'/missing.zip');}catch(RuntimeException $error){$failed=true;}
        checkAsset($failed,'missing PHP zip extension fails explicitly');
        sameAsset(count(glob($directory.'/u5zip_*')),0,'missing zip extension leaves no temporary files');
    }
    unlink($target);rmdir($directory);
    $_SERVER['REQUEST_URI']='/';require_once dirname(__DIR__).'/myfunctions.inc.php';
    $directory=sys_get_temp_dir().'/u5-path-boundary-'.bin2hex(random_bytes(5));mkdir($directory,0700);mkdir($directory.'/r');mkdir($directory.'/r-other');
    file_put_contents($directory.'/r/file.txt','inside');file_put_contents($directory.'/r-other/file.txt','outside');
    symlink($directory.'/r-other',$directory.'/r/escape');
    sameAsset(u5ProhibTravers($directory.'/r/file.txt?x=1',$directory.'/r'),realpath($directory.'/r/file.txt').'?x=1','media path accepts an existing file and retains its query');
    sameAsset(u5ProhibTravers($directory.'/r-other/file.txt',$directory.'/r'),'','media path rejects a sibling sharing the base prefix');
    sameAsset(u5ProhibTravers($directory.'/r/escape/file.txt',$directory.'/r'),'','media path rejects a symlink escaping the base');
    sameAsset(u5ProhibTravers($directory.'/r/file.txt',$directory.'/missing'),'','media path rejects a missing base');
    unlink($directory.'/r/escape');unlink($directory.'/r/file.txt');unlink($directory.'/r-other/file.txt');rmdir($directory.'/r');rmdir($directory.'/r-other');rmdir($directory);
}
function u5MainDiffUsageCases() {
    return array('<link href="r/demo_name!extra.css?x=1#f">'=>true,
        'https://other.invalid/cms/r/demo_name%21extra.css'=>true,
        '{"url":"r\\/demo_name%21extra.css"}'=>true,
        '@import "./demo_name!extra.css";'=>true,
        "@import\nurl(demo_name!extra.css);"=>true,
        'a{background:url("demo_name!extra.css")}'=>true,
        'import "./demo_name!extra.css";'=>true,
        'export {x} from "./demo_name!extra.css";'=>true,
        'import("./demo_name!extra.css")'=>true,
        'fetch("demo_name!extra.css")'=>true,
        'new URL("./demo_name!extra.css",import.meta.url)'=>true,
        'r/demo_name!extra.css.backup'=>false,'r/demo_name!extra.css/subfile'=>false,
        'r/demoXname!extra.css'=>false,'r/notdemo_name!extra.css'=>false,
        '@import "../demo_name!extra.css";'=>false,'plain demo_name!extra.css'=>false);
}
function u5MainDiffDatabase($legacy) {
    $root=assetFixture($legacy,'main-latin1-source');oldAssetFiles($root);
    u5CodeMigrationQuery('ALTER TABLE resources MODIFY content_1 MEDIUMTEXT CHARACTER SET latin1');
    u5CodeMigrationQuery('ALTER TABLE resources_log MODIFY content_1 MEDIUMTEXT CHARACTER SET latin1');
    u5CodeMigrationQuery('CREATE TABLE main_latin1_json(id INT PRIMARY KEY,body TEXT CHARACTER SET latin1) ENGINE=MyISAM');
    $before='{"label":"'."\xDCber \x80".'","selector":"[cssbase]","html":"<p>[cssbase]</p>"}';
    $expected=str_replace('<p>[cssbase]</p>','<p>[base!css]</p>',$before);
    u5CodeMigrationQuery('INSERT INTO main_latin1_json VALUES(1,'.u5CodeMigrationValue($before).')');
    u5CodeMigrationQuery("INSERT INTO resources(name,typ,deleted,content_1,lastmut) VALUES('latinsettings','c',0,".u5CodeMigrationValue($before).',1)');
    u5CodeAssetsMigrate($root);
    sameAsset(u5CodeMigrationRows('SELECT CAST(body AS BINARY) body FROM main_latin1_json')[0]['body'],$expected,'physical Latin1 columns preserve literal bytes during JSON migration');
    sameAsset(u5CodeMigrationCharset(u5CodeMigrationColumns('resources')['content_1']),'latin1','resource source column remains Latin1');
    sameAsset(u5CodeMigrationRows("SELECT CAST(content_1 AS BINARY) body FROM resources WHERE name='latinsettings!json'")[0]['body'],$expected,'physical Latin1 C source keeps its bytes and suffix');
    sameAsset(file_get_contents($root.'/r/latinsettings.json'),u5CodeAssetUtf8($expected),'Latin1 C JSON exports as valid UTF8 without entity decoding');
    // Simulate completed 12.11.1 with a still-unhandled Latin1 JSON inclusion.
    unlink($root.'/fileversions/codeassets-12.11.2.done.php');
    u5CodeMigrationQuery('UPDATE main_latin1_json SET body='.u5CodeMigrationValue($before));
    u5CodeAssetsMigrate($root);
    sameAsset(u5CodeMigrationRows('SELECT CAST(body AS BINARY) body FROM main_latin1_json')[0]['body'],$expected,'12.11.2 repairs remaining references in an already completed installation');
    $snapshot=assetSnapshot();u5CodeAssetsMigrate($root);sameAsset(assetSnapshot(),$snapshot,'12.11.2 repeat is byte-identical');
    // Reproduce the previous parser, then retain its real before/after journal.
    $oldSource=shell_exec('git show 16377863d62f0b9a53235fbfabf236d1b18a425c:codeassets.inc.php');
    checkAsset(is_string($oldSource) && strpos($oldSource,'function u5CodeAssetReferences')!==false,'previous parser is available for the recovery reproduction');
    $parser=$root.'/old-parser.php';file_put_contents($parser,$oldSource.';echo serialize(u5CodeAssetReferences(unserialize(base64_decode($argv[1]))));');
    $process=proc_open(array_merge(array(PHP_BINARY),json_decode(getenv('U5_TEST_PHP_ARGS') ?: '["-n"]',true),array($parser,base64_encode(serialize($before)))),array(1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
    $bad=unserialize(stream_get_contents($pipes[1]));$error=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
    sameAsset(proc_close($process),0,'previous Latin1 JSON parser reproduction: '.$error);
    checkAsset($bad!==$expected && strpos($bad,'"selector":"[base!css]"')!==false,'previous parser actually changed a literal JSON selector');
    u5CodeMigrationQuery('DELETE FROM main_latin1_json');
    $journal=u5CodeMigrationStartChanges();unset($journal['implementation']);
    for ($id=1;$id<=3;$id++) {
        u5CodeMigrationQuery('INSERT INTO main_latin1_json VALUES('.$id.','.u5CodeMigrationValue($bad).')');
        u5CodeMigrationQueueChange($root,$journal,array('table'=>'main_latin1_json','before'=>array('id'=>(string)$id,'body'=>$before),'after'=>array('id'=>(string)$id,'body'=>$bad)));
    }
    $journal=u5CodeMigrationFinishChanges($root,$journal);
    // A later user edit must keep precedence over the older backup.
    $edited=str_replace('"selector":"[base!css]"','"selector":"user-choice"',$bad);
    u5CodeMigrationQuery('UPDATE main_latin1_json SET body='.u5CodeMigrationValue($edited).' WHERE id=2');
    $marker=$root.'/fileversions/codeassets-12.11.0.done.php';$state=include $marker;
    $state['row_backup']=array('directory'=>$journal['directory'],'count'=>$journal['count']);
    file_put_contents($marker,'<?php return '.var_export($state,true).';');unlink($root.'/fileversions/codeassets-12.11.2.done.php');
    global $faultPhase;$faultPhase='row:0';
    expectAssetFailure(function() use($root){u5CodeAssetsMigrate($root);},'12.11.2 recovery can stop after its first database write');
    checkAsset(is_file($root.'/fileversions/codeassets-12.11.2.pending.php'),'12.11.2 keeps an immutable repair checkpoint');
    u5CodeAssetsMigrate($root);
    $recovered=u5CodeMigrationRows('SELECT id,CAST(body AS BINARY) body FROM main_latin1_json ORDER BY id');
    sameAsset($recovered[0]['body'],$expected,'untouched previous result recovers the original selector and migrates actual HTML');
    sameAsset($recovered[1]['body'],u5CodeAssetReferences($edited),'later user edits are preserved during recovery');
    sameAsset($recovered[2]['body'],$expected,'interrupted repair resumes all remaining rows');
    $snapshot=assetSnapshot();unlink($root.'/fileversions/codeassets-12.11.2.done.php');u5CodeAssetsMigrate($root);
    sameAsset(assetSnapshot(),$snapshot,'repair never replays old originals over its own new journal');
    // A 12.11.0 installation can need both follow-up checkpoints in one update.
    $state['row_backup']=array('directory'=>$journal['directory'],'count'=>$journal['count']);
    file_put_contents($marker,'<?php return '.var_export($state,true).';');
    u5CodeMigrationQuery('UPDATE main_latin1_json SET body='.u5CodeMigrationValue($bad).' WHERE id=1');
    unlink($root.'/fileversions/codeassets-12.11.1.done.php');unlink($root.'/fileversions/codeassets-12.11.2.done.php');
    u5CodeAssetsMigrate($root);
    sameAsset(u5CodeMigrationRows('SELECT CAST(body AS BINARY) body FROM main_latin1_json WHERE id=1')[0]['body'],$expected,'a combined older-release update applies guarded JSON recovery before marking both repairs complete');
    checkAsset(is_file($root.'/fileversions/codeassets-12.11.1.done.php') && is_file($root.'/fileversions/codeassets-12.11.2.done.php'),'combined update completes both patch checkpoints');
    require_once dirname(__DIR__).'/u5admin/codeassets-usage.inc.php';
    u5CodeMigrationQuery("INSERT INTO resources(name,typ,deleted) VALUES('usageprobe','p',0)");
    foreach (u5MainDiffUsageCases() as $text=>$expected) {
        u5CodeMigrationQuery("UPDATE resources SET content_1=_latin1 ".u5CodeMigrationValue($text)." WHERE name='usageprobe'");
        $matches=u5CodeMigrationRows("SELECT ".u5CodeAssetUsageSql('demo_name!extra!css')." AS found FROM resources WHERE name='usageprobe'");
        sameAsset((bool)$matches[0]['found'],$expected,'SQL flat dependency matching agrees with browser boundaries: '.$text);
    }
    $originalLink=mysql_ensure_link(null);
    $connected=mysql_connect('127.0.0.1',getenv('U5CMS_TEST_USER') ?: 'root',getenv('U5CMS_TEST_PASSWORD') ?: '');
    sameAsset($connected,$originalLink,'mysql compatibility reuses a live identical connection');
    $second=mysql_connect('127.0.0.1',getenv('U5CMS_TEST_USER') ?: 'root',getenv('U5CMS_TEST_PASSWORD') ?: '',true);
    checkAsset($second!==$originalLink,'mysql compatibility opens an independent requested connection');
    $result=mysql_query('SELECT 7 AS MixedCase,NULL AS empty_value',$second);
    sameAsset(mysql_result($result,0,0),'7','mysql_result supports field zero');
    sameAsset(mysql_result($result,0,'mixedcase'),'7','mysql_result supports case-insensitive field names');
    sameAsset(mysql_result($result,0,'empty_value'),null,'mysql_result distinguishes NULL from a missing field');
    sameAsset(mysql_result($result,0,'missing'),false,'mysql_result returns false for a missing field');
    sameAsset(mysql_query('SELECT nonexistent_field FROM nonexistent_table',$second),false,'SQL errors return false through the compatibility layer');
    mysql_close($second);sameAsset(mysql_ensure_link(null),$originalLink,'closing a backup connection restores the original CMS connection');
    sameAsset(mysql_result(mysql_query('SELECT @@character_set_client'),0),'latin1','an independent connection leaves CMS charset unchanged');
}
