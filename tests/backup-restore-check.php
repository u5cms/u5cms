<?php
/* Imports actual fixture backups into a separate random database, never into the source. */
$_SERVER['REQUEST_URI']='/';$_SERVER['HTTP_HOST']='fixture.invalid';
$root=$argv[1];require $root.'/config.php';require_once $root.'/mysql.php';
ini_set('mysqli.default_port',getenv('U5CMS_TEST_PORT'));
$link=mysql_connect($host,$username,$password,true);if (!$link) throw new RuntimeException('Fixture connection failed.');
mysql_query('SET NAMES utf8mb4',$link);
$source=$db;$target='u5asset_restore_'.bin2hex(random_bytes(6));$checks=0;
function backupCheck($condition,$label) { global $checks;if (!$condition) throw new RuntimeException($label);$checks++; }
function backupQuery($sql) { global $link;$result=mysql_query($sql,$link);if ($result===false) throw new RuntimeException(mysql_error($link));return $result; }
function backupId($name) { return chr(96).str_replace(chr(96),chr(96).chr(96),$name).chr(96); }
function backupSnapshot($database,$source=false) {
    $state=array();mysql_select_db($database);$result=backupQuery('SHOW FULL TABLES');
    $tables=array();while ($row=mysql_fetch_row($result)) if ($row[1]==='BASE TABLE') $tables[]=$row[0];
    foreach ($tables as $table) {
        $create=mysql_fetch_row(backupQuery('SHOW CREATE TABLE '.backupId($table)))[1];
        $tail='';
        if ($table==='trxlog') {
            // The endpoint records its successful completion after exporting the snapshot.
            $create=preg_replace('/ AUTO_INCREMENT=[0-9]+/','',$create);
            if ($source) {
                $last=mysql_fetch_assoc(backupQuery('SELECT id,trx FROM trxlog ORDER BY id DESC LIMIT 1'));
                if ($last && strpos($last['trx'],'backup ')===0) $tail=' WHERE id!='.(int)$last['id'];
            }
        }
        $columns=array();$result=backupQuery('SHOW FULL COLUMNS FROM '.backupId($table));while ($column=mysql_fetch_assoc($result)) {
            $field=backupId($column['Field']);
            if (preg_match('/^(float|double|real)/i',$column['Type'])) $field='('.$field.'+0e0)';
            $columns[]='CAST('.$field.' AS BINARY)';
        }
        $hashes=array();$result=mysql_unbuffered_query('SELECT '.implode(',',$columns).' FROM '.backupId($table).$tail);
        while ($row=mysql_fetch_row($result)) $hashes[]=hash('sha256',serialize($row));
        mysql_free_result($result);sort($hashes);$state[$table]=array('create'=>$create,'rows'=>$hashes);
    }
    ksort($state);return $state;
}
try {
    $pairedRoot=$argv[3] ?? null;
    $before=$pairedRoot ? null : backupSnapshot($source,true);backupQuery('CREATE DATABASE '.backupId($target).' CHARACTER SET latin1');mysql_select_db($target);
    $directory=$root.'/fileversions/_dbbackup';$files=glob($directory.'/*_structure_*');
    backupCheck((bool)$files,'No structure dump was produced.');usort($files,function($a,$b){return filemtime($b)<=>filemtime($a);});$structure=$argv[2] ?? $files[0];$data=str_replace('_structure_','_data_',$structure);
    foreach (array($structure,$data) as $file) {
        $sql=substr($file,-3)==='.gz' ? gzdecode(file_get_contents($file)) : file_get_contents($file);
        backupCheck(is_string($sql),'Cannot decode the actual database backup.');
        if (!mysqli_multi_query($link,$sql)) throw new RuntimeException('Actual backup cannot be imported: '.mysqli_error($link));
        do { if ($result=mysqli_store_result($link)) mysqli_free_result($result); } while (mysqli_more_results($link) && mysqli_next_result($link));
        if (mysqli_errno($link)) throw new RuntimeException('Actual backup cannot be imported: '.mysqli_error($link));
    }
    $after=backupSnapshot($target);if ($before!==null) backupCheck(array_keys($after)===array_keys($before),'Backup lost tables.');
    foreach ($before ?? array() as $table=>$state) {
        backupCheck($state['create']===$after[$table]['create'],'Backup changed schema/engine/charset/index/default/comment for '.$table);
        backupCheck($state['rows']===$after[$table]['rows'],'Backup changed original bytes, NULLs, Unicode or generated values in '.$table);
    }
    if ($pairedRoot) {
        require_once $root.'/u5admin/codeassets-migrate.inc.php';
        $name=u5CodeTemplateName($pairedRoot);
        backupCheck($name==='audit-site!layout!html','Restored role mapping lost the custom layout name.');
        backupCheck(mysql_num_rows(backupQuery("SELECT name FROM resources WHERE name='".mysql_real_escape_string($name)."' AND typ='c' AND deleted!=1"))===1,'Restored DB and restored files disagree about the active layout.');
        u5CodeAssetExport($pairedRoot);$template=u5CodeTemplatePath($pairedRoot);
        backupCheck(is_file($template),'Restored code cannot be exported.');
        backupCheck(strpos(file_get_contents($template),'main-audit custom reset baseline')===false,'Restore unexpectedly applied a layout reset.');
        $count=mysql_result(backupQuery('SELECT COUNT(*) FROM resources_log'),0);
        mkdir($pairedRoot.'/u5admin',0700,true);file_put_contents($pairedRoot.'/u5admin/connect.inc.php','<?php');
        symlink($root.'/u5admin/codeassets-migrate.inc.php',$pairedRoot.'/u5admin/codeassets-migrate.inc.php');
        $reset=file_get_contents($root.'/u5admin/reactivateinitialu5cmslayout.php');
        $reset=preg_replace('/^die\([^\n]+\);\n/m','',$reset,1,$removed);backupCheck($removed===1,'Test-only layout-reset guard was not found.');
        file_put_contents($pairedRoot.'/u5admin/restore-reset.php',$reset);$cssRqHIADRI='no';$_GET['iswear']='iknowthatthisoverwritesthecurrentlayout';
        $cwd=getcwd();chdir($pairedRoot.'/u5admin');ob_start();try {include $pairedRoot.'/u5admin/restore-reset.php';}finally{ob_end_clean();chdir($cwd);}
        backupCheck(strpos(file_get_contents($template),'main-audit custom reset baseline')!==false,'Actual layout reset did not use its restored custom baseline.');
        backupCheck((int)mysql_result(backupQuery('SELECT COUNT(*) FROM resources_log'),0)>(int)$count,'Layout reset lost its source history.');
        backupCheck(u5CodeTemplateName($pairedRoot)===$name,'Layout reset changed its restored role mapping.');
    }
    echo json_encode(array('checks'=>$checks,'tables'=>count($after),'status'=>'restored'));
} finally { mysql_query('DROP DATABASE IF EXISTS '.backupId($target),$link); }
