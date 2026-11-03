<?php
/* Real original/new restore control under a constrained disposable server. */
if (PHP_SAPI!=='cli' || !getenv('U5CMS_TEST_PORT')) throw new RuntimeException('Disposable MariaDB required.');
$_SERVER['REQUEST_URI']='/';$_SERVER['HTTP_HOST']='fixture.invalid';
$root=dirname(__DIR__);require $root.'/mysql.php';require $root.'/u5admin/databasebackup.inc.php';
ini_set('mysqli.default_port',(string)(int)getenv('U5CMS_TEST_PORT'));
$host='127.0.0.1';$user=getenv('U5CMS_TEST_USER') ?: 'root';$password=getenv('U5CMS_TEST_PASSWORD') ?: '';
function packetConnect() {global $host,$user,$password;$link=mysql_connect($host,$user,$password,true);if(!$link)throw new RuntimeException('Fixture connection failed.');return $link;}
function packetQuery($sql,$link) {$result=mysql_query($sql,$link);if($result===false)throw new RuntimeException('SQL '.mysql_errno($link).': '.mysql_error($link),mysql_errno($link));return $result;}
function packetCheck($condition,$label) {global $checks;if(!$condition)throw new RuntimeException($label);$checks++;}
function packetId($name) {return chr(96).str_replace(chr(96),chr(96).chr(96),$name).chr(96);}
function packetSnapshot($link,$table,$columns) {
    $select=array();foreach($columns as$column)$select[]='SHA2(CAST('.packetId($column).' AS BINARY),256)';
    $rows=array();$result=packetQuery('SELECT '.implode(',',$select).' FROM '.packetId($table).' ORDER BY 1',$link);
    while($row=mysql_fetch_row($result))$rows[]=serialize($row);sort($rows);return $rows;
}
function packetRestore($files,$target,$limit,$admin,$reconnect=false,$resetDatabase=true) {
    packetQuery('SET GLOBAL max_allowed_packet='.(int)$limit,$admin);
    if ($resetDatabase) {
        packetQuery('DROP DATABASE IF EXISTS '.packetId($target),$admin);packetQuery('CREATE DATABASE '.packetId($target).' CHARACTER SET latin1',$admin);
    }
    $link=packetConnect();mysql_select_db($target,$link);$maximum=0;$statements=0;$reconnected=false;
    try {
        packetCheck((int)mysql_fetch_row(packetQuery('SELECT @@max_allowed_packet',$link))[0]===$limit,'Restore did not use the requested packet limit.');
        $schema=substr($files[0],-3)==='.gz' ? gzdecode(file_get_contents($files[0])) : file_get_contents($files[0]);
        if(!mysqli_multi_query($link,$schema))throw new RuntimeException('Actual structure restore failed: '.mysqli_error($link));
        do {if($result=mysqli_store_result($link))mysqli_free_result($result);}while(mysqli_more_results($link)&&mysqli_next_result($link));
        if(mysqli_errno($link))throw new RuntimeException('Actual structure restore failed: '.mysqli_error($link));
        $compressed=substr($files[1],-3)==='.gz';$handle=$compressed ? gzopen($files[1],'rb') : fopen($files[1],'rb');
        try {
            // This fixture's generated data statements each occupy one line.
            // Execute actual dump statements separately, as an SQL client does.
            while(($sql=$compressed ? gzgets($handle) : fgets($handle))!==false) {
                if(trim($sql)==='' || strpos(ltrim($sql),'--')===0)continue;
                $maximum=max($maximum,strlen($sql));packetQuery($sql,$link);
                if ($reconnect && ++$statements%3===0) {
                    $before=(int)mysql_fetch_row(packetQuery('SELECT CONNECTION_ID()',$link))[0];
                    mysql_close($link);$link=packetConnect();mysql_select_db($target,$link);
                    if (!$reconnected) {
                        packetCheck((int)mysql_fetch_row(packetQuery('SELECT CONNECTION_ID()',$link))[0]!==$before,'Resume did not open a fresh database session.');
                        $reconnected=true;
                    }
                    // A resumed SQL importer reapplies the dump's session
                    // header. No fragment data may depend on that session.
                    foreach (array('SET NAMES utf8mb4',"SET sql_mode='NO_AUTO_VALUE_ON_ZERO'",'SET FOREIGN_KEY_CHECKS=0',"SET time_zone='+00:00'") as$header)packetQuery($header,$link);
                }
            }
        } finally {if($compressed)gzclose($handle);else fclose($handle);}
        if ($reconnect) packetCheck($reconnected,'Restore did not exercise a connection change.');
        return array($link,$maximum);
    } catch(Throwable $error) {mysql_close($link);throw $error;}
}
$checks=0;$admin=packetConnect();$originalPacket=(int)mysql_fetch_row(packetQuery('SELECT @@GLOBAL.max_allowed_packet',$admin))[0];
$prefix='u5backup_packet_'.bin2hex(random_bytes(6));$source=$prefix.'_source';$target=$prefix.'_target';
$directory=sys_get_temp_dir().'/'.$prefix;mkdir($directory,0700);$input=null;$links=array();
try {
    packetQuery('SET GLOBAL max_allowed_packet=33554432',$admin);$input=packetConnect();
    packetQuery('CREATE DATABASE '.packetId($source).' CHARACTER SET utf8mb4',$admin);mysql_select_db($source,$input);
    packetQuery("SET time_zone='+00:00'",$input);
    packetQuery('CREATE TABLE probe(a MEDIUMTEXT,b MEDIUMTEXT,c MEDIUMTEXT) ENGINE=MyISAM',$input);
    packetQuery("INSERT INTO probe VALUES(REPEAT('a',8*1024*1024),REPEAT('b',8*1024*1024),REPEAT('c',8*1024*1024))",$input);
    $expected=packetSnapshot($input,'probe',array('a','b','c'));
    $process=proc_open(array('git','show','9d366253417822c279692767b89dcf1ff0388c5c:u5admin/backupdb.php'),array(1=>array('pipe','w'),2=>array('pipe','w')),$pipes,$root);
    $original=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
    if(proc_close($process)!==0)throw new RuntimeException($error);
    if(!preg_match('/function get_content\(\$table, \$link\).*?return \$content;\s*\}/s',$original,$match))throw new RuntimeException('Original backup function is missing.');
    eval(str_replace('function get_content','function packetOriginalContent',$match[0]));
    $legacy=packetOriginalContent('probe',$input);
    packetCheck(strlen($legacy)<33554432,'The original control must fit the constrained server.');
    packetQuery('CREATE DATABASE '.packetId($target).' CHARACTER SET latin1',$admin);$restore=packetConnect();mysql_select_db($target,$restore);
    packetQuery('CREATE TABLE probe LIKE '.packetId($source).'.probe',$restore);packetQuery($legacy,$restore);unset($legacy);
    packetCheck(packetSnapshot($restore,'probe',array('a','b','c'))===$expected,'Original Main control did not restore the 24 MiB row exactly.');mysql_close($restore);
    $largeFiles=u5CreateDatabaseBackup($source,$directory,false,$input);
    list($restore,$maximum)=packetRestore($largeFiles,$target,33554432,$admin,true);$links[]=$restore;
    packetCheck(packetSnapshot($restore,'probe',array('a','b','c'))===$expected,'New dump failed the same valid 32 MiB restore.');
    packetCheck($maximum<1048576,'Large rows still produce oversized individual statements.');
    packetCheck(mysql_num_rows(packetQuery('SHOW TABLES',$restore))===1,'A completed large-row restore left a staging table.');
    // CONCAT can return NULL with only a warning on non-strict servers. The
    // dump must raise an INSERT error, rather than store NULL or truncated data.
    try {packetRestore($largeFiles,$target,1048576,$admin);throw new RuntimeException('Undersized restore unexpectedly succeeded.');}
    catch(RuntimeException $error) {packetCheck($error->getCode()===1242,'Incomplete assembly did not fail before INSERT: '.$error->getMessage());}
    $verify=packetConnect();mysql_select_db($target,$verify);$links[]=$verify;
    packetCheck((int)mysql_fetch_row(packetQuery('SELECT COUNT(*) FROM probe',$verify))[0]===0,'An undersized restore stored an incomplete row.');
    list($retry,$maximum)=packetRestore($largeFiles,$target,33554432,$admin,true,false);$links[]=$retry;
    packetCheck(packetSnapshot($retry,'probe',array('a','b','c'))===$expected,'Retry after an interrupted restore lost data.');
    packetCheck(mysql_num_rows(packetQuery('SHOW TABLES',$retry))===1,'Retry failed to clean its previous staging table.');
    packetQuery('DROP TABLE probe',$input);
    packetQuery("CREATE TABLE typed(id INT PRIMARY KEY COMMENT 'ENGINE=MEMORY is only a comment', unicode_text LONGTEXT CHARACTER SET utf8mb4, legacy_text MEDIUMTEXT CHARACTER SET latin1, bytes MEDIUMBLOB, nullable TEXT NULL, empty_value VARBINARY(8), amount DECIMAL(32,10), moment TIMESTAMP NULL, bits BIT(12), json_value JSON) ENGINE=InnoDB",$input);
    packetQuery("INSERT INTO typed VALUES(1,CONCAT(REPEAT(_utf8mb4 X'c3a4f09f8cb7',131072),'tail'),REPEAT(_latin1 X'80e9',131072),REPEAT(X'0001ff3b275c',131072),NULL,X'',123456789012345.1234567890,'2020-01-02 03:04:05',b'101011110000',JSON_OBJECT('note',REPEAT('j',200000)))",$input);
    $columns=array('id','unicode_text','legacy_text','bytes','nullable','empty_value','amount','moment','bits','json_value');$typed=packetSnapshot($input,'typed',$columns);
    foreach(array(false,true) as$compressed) {
        $files=u5CreateDatabaseBackup($source,$directory,$compressed,$input);
        list($restore,$maximum)=packetRestore($files,$target,1048576,$admin,true);$links[]=$restore;
        packetCheck(packetSnapshot($restore,'typed',$columns)===$typed,'Packet assembly changed Unicode, Latin-1, binary, NULL, empty or typed values.');
        packetCheck($maximum<1048576,'Typed dump exceeds its tested packet budget.');
        packetCheck(mysql_num_rows(packetQuery('SHOW TABLES',$restore))===1,'Typed restore left a staging table.');
    }
    echo 'Database backup packet contracts: '.$checks." checks passed (actual original control, 32 MiB restore, 1 MiB typed plain/gzip restore, reconnect, fail-before-write, retry and cleanup).\n";
} finally {
    mysql_query('SET GLOBAL max_allowed_packet='.$originalPacket,$admin);
    foreach($links as$link)mysql_close($link);
    mysql_query('DROP DATABASE IF EXISTS '.packetId($target),$admin);mysql_query('DROP DATABASE IF EXISTS '.packetId($source),$admin);
    if($input)mysql_close($input);mysql_close($admin);
    foreach(glob($directory.'/*') as$file)unlink($file);rmdir($directory);
}
