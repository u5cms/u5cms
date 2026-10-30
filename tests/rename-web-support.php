<?php
/* CLI-only fixture management in the disposable web installation. */
if (PHP_SAPI!=='cli') {http_response_code(404);exit;}
$root=$argv[1];$action=$argv[2];$mode=$argv[3] ?? 'basic';
$_SERVER['REQUEST_URI']='/';$_SERVER['HTTP_HOST']='fixture.invalid';
require $root.'/config.php';require $root.'/mysql.php';require $root.'/u5admin/resource-rename.inc.php';
ini_set('mysqli.default_port',(string)(int)getenv('U5CMS_TEST_PORT'));mysql_connect($host,$username,$password);mysql_select_db($db);u5CodeMigrationQuery('SET NAMES latin1');
$old='renameold'.$mode;$new='renamenew'.$mode;$reference='renameref'.$mode;
if ($action==='seed') {
    $body='[[[['.$old.']]]]'."\n".'[Go to page:'.$old.'] [go:]'.$old.'[:go] [lo:]'.$old.'[:lo]';
    foreach (array($old=>$body,$reference=>'[[['.$old.']]]'."\n".'[Go to page:'.$old.']') as $name=>$content) {
        $columns=array('name','typ','deleted','hidden','lastmut','operator');$values=array(u5CodeMigrationValue($name),"'p'",'0','0','100',"'original'");
        foreach (range(1,5) as $language) foreach (array('content','title','desc','key') as $prefix) {$columns[]=$prefix.'_'.$language;$values[]=u5CodeMigrationValue($prefix==='content' ? $content : ($prefix==='title' ? 'Human '.$name : '[Label:'.$old.']'));}
        u5CodeMigrationQuery('INSERT INTO resources (`'.implode('`,`',$columns).'`) VALUES ('.implode(',',$values).')');
        u5CodeMigrationQuery('INSERT INTO resources_log (`'.implode('`,`',$columns).'`) VALUES ('.implode(',',$values).')');
    }
    u5CodeMigrationQuery('INSERT INTO formdata (formname,datacsv) VALUES ('.u5CodeMigrationValue($old).','.u5CodeMigrationValue('submitted [label:'.$old.']').')');
    u5CodeMigrationQuery('INSERT INTO accounts (email,pw,accadmin) VALUES (\'renamewriter\','.u5CodeMigrationValue('{SHA}'.base64_encode(sha1('writer-password',true))).',0) ON DUPLICATE KEY UPDATE accadmin=0');
    mkdir($root.'/r/'.$old);file_put_contents($root.'/r/'.$old.'/'.$old.'_en.txt','original language bytes');
    file_put_contents($root.'/r/'.$old.'/'.$old.'_100.jpg','original album bytes');
    file_put_contents($root.'/r/'.$old.'/.htpasswd','private-member:{SHA}opaque');
    file_put_contents($root.'/r/'.$old.'/.htaccess',"Deny from all\n");
} elseif ($action==='archive') {
    u5CodeMigrationQuery('UPDATE resources SET deleted=2 WHERE name='.u5CodeMigrationValue($old));
    u5CodeMigrationQuery('INSERT INTO resources (name,typ,deleted,lastmut,operator) VALUES ('.u5CodeMigrationValue($old).",'p',1,2000000000,'deleted version')");
} elseif ($action==='codepending') {
    $codeOld='renamearchived!css';$codeNew='renamedarchived!css';
    u5CodeMigrationQuery('INSERT INTO resources (name,typ,deleted,lastmut,operator,content_1) VALUES ('.u5CodeMigrationValue($codeNew).",'c',2,100,'original','body{}')");
    $before=u5CodeMigrationRows('SELECT '.u5CodeMigrationSelect(u5CodeMigrationColumns('resources'))['select'].' FROM resources WHERE name='.u5CodeMigrationValue($codeNew).' AND deleted=2')[0];$after=$before;$before['name']=$codeOld;
    $updates=u5CodeMigrationStartChanges();u5CodeMigrationQueueChange($root,$updates,array('table'=>'resources','before'=>$before,'after'=>$after));$updates=u5CodeMigrationFinishChanges($root,$updates);
    $pending=$root.'/fileversions/codeasset-rename-'.sha1($codeOld."\0".$codeNew).'.pending.php';
    u5CodeAssetAtomicWrite($pending,u5CodeMigrationEncodeJournal(array('version'=>'12.11.0','updates'=>$updates,'files'=>array())));
} elseif ($action==='snapshot') {
    $out=array();foreach (array('resources','resources_log') as $table) {
        $where='name IN ('.u5CodeMigrationValue($old).','.u5CodeMigrationValue($new).','.u5CodeMigrationValue($reference).')';
        $out[$table]=u5CodeMigrationRows('SELECT '.u5CodeMigrationSelect(u5CodeMigrationColumns($table))['select'].' FROM '.$table.' WHERE '.$where.' ORDER BY name,deleted,lastmut');
    }
    $out['formdata']=u5CodeMigrationRows('SELECT formname,datacsv FROM formdata WHERE formname IN ('.u5CodeMigrationValue($old).','.u5CodeMigrationValue($new).')');
    echo json_encode($out,JSON_THROW_ON_ERROR);
} else throw new RuntimeException('Unknown fixture action.');
