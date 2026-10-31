<?php
/* Reproducible comparison with the actual committed legacy SQL functions (not a model). */
if (PHP_SAPI!=='cli' || !getenv('U5CMS_TEST_PORT')) die("Disposable MariaDB required.\n");
require dirname(__DIR__).'/mysql.php';require dirname(__DIR__).'/u5admin/resource-rename.inc.php';
ini_set('mysqli.default_port',(string)(int)getenv('U5CMS_TEST_PORT'));mysql_connect('127.0.0.1',getenv('U5CMS_TEST_USER') ?: 'root',getenv('U5CMS_TEST_PASSWORD') ?: '');
$database='u5rename_bench_'.bin2hex(random_bytes(6));u5CodeMigrationQuery('CREATE DATABASE `'.$database.'` CHARACTER SET utf8mb4');mysql_select_db($database);
$link=mysql_ensure_link(null);mysqli_multi_query($link,file_get_contents(dirname(__DIR__).'/u5standard.sql'));do {if ($r=mysqli_store_result($link))mysqli_free_result($r);}while(mysqli_more_results($link)&&mysqli_next_result($link));
if (mysqli_errno($link))throw new RuntimeException(mysqli_error($link));u5CodeMigrationQuery('SET NAMES latin1');
$alsorenamelinksinformdatadatacsv='yes';
foreach (array('rename2.php'=>array('sere13'),'rename3.php'=>array('sere13b','loginglobals')) as $path=>$names) {
    $process=proc_open(array('git','show','acdd8d6cf362c93803e94897336d347e26d1dba6:u5admin/'.$path),array(1=>array('pipe','w'),2=>array('pipe','w')),$pipes,dirname(__DIR__));
    $code=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);if(proc_close($process)!==0)throw new RuntimeException($error);
    foreach ($names as $name) {
        if (!preg_match('~function '.preg_quote($name,'~').'\(.*?\n\}~s',$code,$match))throw new RuntimeException('Missing legacy function.');
        // Legacy benchmark inputs are arrays; the removed historical alias was count() here.
        $match[0]=str_replace('tnuoc(', 'count(', $match[0]);
        eval(preg_replace('/\b(sere13b|sere13|loginglobals)\(/','legacy_$1(',$match[0]));
    }
}
function benchQuestions() {return (int)u5CodeMigrationRows("SHOW SESSION STATUS LIKE 'Questions'")[0]['Value'];}
function benchTexts($table,$fields) {
    $hash=hash_init('sha256');$query=array();foreach ($fields as $field)$query[]='CAST(`'.$field.'` AS BINARY) `'.$field.'`';
    $result=mysql_unbuffered_query('SELECT '.implode(',',$query).' FROM '.$table.' ORDER BY name,lastmut');
    while ($row=mysql_fetch_array($result,MYSQL_ASSOC))hash_update($hash,serialize($row));mysql_free_result($result);return hash_final($hash);
}
function benchRemove($root) {if(is_link($root)||!is_dir($root)){@unlink($root);return;}foreach(scandir($root)as$file)if($file!=='.'&&$file!=='..')benchRemove($root.'/'.$file);rmdir($root);}
$roots=array();$report=array();$old='benchmarkold';$new='benchmarknew';$fields=array();foreach(range(1,5)as$l)foreach(array('content','title','desc','key')as$f)$fields[]=$f.'_'.$l;
$options=array('links'=>true,'manual'=>true,'formdata'=>true,'template'=>'htmltemplate!html','languages'=>array('de','en','fr','it','rm'));
try {
 foreach (array(array('sparse',5000,25000,100),array('dense',2000,5000,1)) as list($label,$resources,$history,$stride)) {
    foreach (array('resources','resources_log','formdata','loginglobals') as $table)u5CodeMigrationQuery('TRUNCATE TABLE '.$table);
    $root=sys_get_temp_dir().'/u5-rename-bench-'.bin2hex(random_bytes(6));$roots[]=$root;mkdir($root.'/r/'.$old,0700,true);mkdir($root.'/fileversions');
    $base=str_repeat('Ordinary multilingual site content &amp; literal documentation. ',10);
    $reference=' [Label:'.$old.'] [:::'.$old.'?l=en] [[['.$old.']]] |'.$old.'| r/'.$old.'/'.$old.'_en.jpg';
    foreach (array('resources'=>$resources,'resources_log'=>$history) as $table=>$count) {
        for ($start=0;$start<$count;$start+=100) {
            $values=array();for ($i=$start;$i<min($start+100,$count);$i++) {
                $name=$i===0 ? $old : 'resource'.sprintf('%08d',$i);$text=$base.($i%$stride===0 ? $reference : ' no dependencies');
                $values[]='('.u5CodeMigrationValue($name).','.u5CodeMigrationValue($text).','.u5CodeMigrationValue($text).",'p',0,100,'original')";
            }
            u5CodeMigrationQuery('INSERT INTO '.$table.' (name,content_1,title_1,typ,deleted,lastmut,operator) VALUES '.implode(',',$values));
        }
    }
    foreach (array('resources','resources_log','formdata','loginglobals') as $table) {u5CodeMigrationQuery('DROP TABLE IF EXISTS saved_'.$table);u5CodeMigrationQuery('CREATE TABLE saved_'.$table.' LIKE '.$table);u5CodeMigrationQuery('INSERT INTO saved_'.$table.' SELECT * FROM '.$table);}
    $fileNames=array();foreach(range(100,999) as$n)$fileNames[]=$old.'_'.$n.'.jpg';foreach($options['languages']as$l)$fileNames[]=$old.'_'.$l.'.pdf';foreach(range(0,49)as$n)$fileNames[]='unrelated-'.$n.'.txt';
    foreach($fileNames as$file)file_put_contents($root.'/r/'.$old.'/'.$file,str_repeat('source bytes ',80));
    echo "Benchmark $label: $resources resources, $history history rows, stride $stride, ".count($fileNames)." files\n";flush();
    $q=benchQuestions();$start=microtime(true);
    foreach (u5RenameReplacements($old,$new,$options) as $search=>$replace) {legacy_sere13(implode(',',$fields),$search,$replace);legacy_sere13b(implode(',',$fields),$search,$replace);}
    $legacySql=microtime(true)-$start;$legacyQueries=benchQuestions()-$q;
    $expected=array('resources'=>benchTexts('resources',$fields),'resources_log'=>benchTexts('resources_log',$fields));
    $start=microtime(true);rename($root.'/r/'.$old,$root.'/r/'.$new);$probes=0;
    $handle=opendir($root.'/r/'.$new);
    while(false!==($file=readdir($handle))) {
        $extension=pathinfo($file,PATHINFO_EXTENSION);
        foreach(array_merge($options['languages'],range(100,999))as$suffix) {
            $from=$root.'/r/'.$new.'/'.$old.'_'.$suffix.'.'.$extension;$probes++;
            if(file_exists($from))rename($from,$root.'/r/'.$new.'/'.$new.'_'.$suffix.'.'.$extension);
        }
    }closedir($handle);$legacyFiles=microtime(true)-$start;
    foreach(array('resources','resources_log','formdata','loginglobals')as$table){u5CodeMigrationQuery('TRUNCATE TABLE '.$table);u5CodeMigrationQuery('INSERT INTO '.$table.' SELECT * FROM saved_'.$table);}
    // Match the migration-created index present on upgraded sites for guarded history lookups.
    u5CodeMigrationHistorySchema();
    rename($root.'/r/'.$new,$root.'/r/'.$old);foreach(scandir($root.'/r/'.$old)as$file)if(strpos($file,$new.'_')===0)rename($root.'/r/'.$old.'/'.$file,$root.'/r/'.$old.'/'.$old.substr($file,strlen($new)));
    $q=benchQuestions();$start=microtime(true);$plan=u5ResourceRename($root,$old,$new,$options);$elapsed=microtime(true)-$start;$newQueries=benchQuestions()-$q;
    foreach($expected as$table=>$hash)if(benchTexts($table,$fields)!==$hash)throw new RuntimeException('Text/reference conservation failure in '.$table);
    foreach($fileNames as$file){$target=isset($plan['files']['files'][$file])?$plan['files']['files'][$file]['to']:$file;if(file_get_contents($root.'/r/'.$new.'/'.$target)!==str_repeat('source bytes ',80))throw new RuntimeException('File conservation failure.');}
    $report[]=array('fixture'=>$label,'resources'=>$resources,'history'=>$history,'dependency_stride'=>$stride,'files'=>count($fileNames),'legacy_reference_sql_seconds'=>round($legacySql,4),'legacy_file_seconds'=>round($legacyFiles,4),'legacy_queries'=>$legacyQueries,'legacy_file_probes'=>$probes,'new_complete_seconds'=>round($elapsed,4),'new_queries'=>$newQueries,'changed_rows'=>$plan['updates']['count'],'speedup'=>round(($legacySql+$legacyFiles)/$elapsed,2),'peak_php_bytes'=>memory_get_peak_usage(true),'conservation'=>'all 20 resource/history text columns and every file verified');
    echo json_encode(end($report),JSON_PRETTY_PRINT)."\n";flush();
 }
 echo "Rename benchmark: ".count($report)." fixtures passed.\n";
 if(getenv('U5CMS_RENAME_BENCHMARK_JSON'))file_put_contents(getenv('U5CMS_RENAME_BENCHMARK_JSON'),json_encode($report,JSON_PRETTY_PRINT)."\n");
} finally {u5CodeMigrationQuery('DROP DATABASE `'.$database.'`');foreach($roots as$root)benchRemove($root);}
