<?php
/* Independent schema/type conservation around the bounded large-row SQL format. */
if(PHP_SAPI!=='cli' || !getenv('U5CMS_TEST_PORT'))throw new RuntimeException('Disposable database required.');
$_SERVER['REQUEST_URI']='/';$_SERVER['HTTP_HOST']='fixture.invalid';
$root=dirname(__DIR__);require $root.'/mysql.php';require $root.'/u5admin/databasebackup.inc.php';
ini_set('mysqli.default_port',(string)(int)getenv('U5CMS_TEST_PORT'));
function schemaConnect(){return mysql_connect('127.0.0.1',getenv('U5CMS_TEST_USER') ?: 'root',getenv('U5CMS_TEST_PASSWORD') ?: '',true);}
function schemaQuery($sql,$link){$r=mysql_query($sql,$link);if($r===false)throw new RuntimeException('Fixture SQL '.mysql_errno($link).': '.mysql_error($link));return $r;}
function schemaId($s){return '`'.str_replace('`','``',$s).'`';}
function schemaCheck($ok,$label){global $checks;if(!$ok)throw new RuntimeException($label);$checks++;}
function schemaSnapshot($name,$link){
    mysql_select_db($name,$link);schemaQuery('SET NAMES utf8mb4',$link);schemaQuery("SET time_zone='+00:00'",$link);$state=[];$tables=[];
    $r=schemaQuery('SHOW FULL TABLES',$link);while($row=mysql_fetch_row($r))$tables[]=$row[0];
    foreach($tables as$table){
        $ddl=mysql_fetch_row(schemaQuery('SHOW CREATE TABLE '.schemaId($table),$link))[1];$cols=[];
        $r=schemaQuery('SHOW FULL COLUMNS FROM '.schemaId($table),$link);while($c=mysql_fetch_assoc($r)){
            $field=schemaId($c['Field']);if(preg_match('/^(float|double|real)\b/i',$c['Type']))$field='('.$field.'+0e0)';$cols[]='CAST('.$field.' AS BINARY)';
        }
        $hashes=[];$r=mysql_unbuffered_query('SELECT '.implode(',',$cols).' FROM '.schemaId($table),$link);
        if($r===false)throw new RuntimeException('Cannot inspect fixture data.');
        while($row=mysql_fetch_row($r))$hashes[]=hash('sha256',serialize($row));mysql_free_result($r);sort($hashes);
        $state[$table]=['ddl'=>$ddl,'rows'=>$hashes];
    }
    ksort($state);return $state;
}
function schemaImport($file,$link){
    $compressed=substr($file,-3)==='.gz';$h=$compressed?gzopen($file,'rb'):fopen($file,'rb');$statement='';
    try{
        while(($line=$compressed?gzgets($h):fgets($h))!==false){
            // The generated fixture contains no semicolons/newlines in its DDL
            // string constants. Hex-encoded data cannot contain SQL delimiters.
            if($statement==='' && (trim($line)==='' || strpos(ltrim($line),'--')===0))continue;
            $statement.=$line;
            if(substr(rtrim($statement),-1)===';'){schemaQuery($statement,$link);$statement='';}
        }
        schemaCheck(trim($statement)==='','All generated statements were consumed.');
    }finally{if($compressed)gzclose($h);else fclose($h);}
}
$checks=0;$admin=schemaConnect();$packet=(int)mysql_fetch_row(schemaQuery('SELECT @@GLOBAL.max_allowed_packet',$admin))[0];
$prefix='u5backup_schema_'.bin2hex(random_bytes(6));$source=$prefix.'_source';$target=$prefix.'_target';$directory=sys_get_temp_dir().'/'.$prefix;mkdir($directory,0700);$input=null;$restore=null;
try{
    schemaQuery('SET GLOBAL max_allowed_packet=33554432',$admin);$input=schemaConnect();
    schemaQuery('CREATE DATABASE '.schemaId($source).' CHARACTER SET utf8mb4',$admin);mysql_select_db($source,$input);schemaQuery('SET NAMES utf8mb4',$input);schemaQuery("SET time_zone='+00:00'",$input);schemaQuery("SET sql_mode='NO_AUTO_VALUE_ON_ZERO'",$input);
    $table='quoted`雪';$payload='bytes`part';$unicode='unicode`雪';
    schemaQuery('CREATE TABLE '.schemaId($table).' (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,'.schemaId($payload).' MEDIUMBLOB,'.schemaId($unicode)." MEDIUMTEXT CHARACTER SET utf8mb4, hidden_value VARCHAR(12) INVISIBLE, category ENUM('zero','one'), flags SET('a','b'), exact_value DECIMAL(38,18), fraction DOUBLE, precise_time DATETIME(6), generated_hash BINARY(32) AS (UNHEX(SHA2(".schemaId($payload).',256))) PERSISTENT, generated_length INT AS (OCTET_LENGTH('.schemaId($payload).')) VIRTUAL) ENGINE=Aria',$input);
    $fields='id,'.schemaId($payload).','.schemaId($unicode).',hidden_value,category,flags,exact_value,fraction,precise_time';
    schemaQuery('INSERT INTO '.schemaId($table).' ('.$fields.") VALUES (0,REPEAT(X'0001ff275c',120000),REPEAT(_utf8mb4 X'f09f8cb7c3a4',100000),'invisible','zero','a,b',12345678901234567890.123456789012345678,1.2345678901234567,'2020-01-02 03:04:05.123456'),(18446744073709551615,NULL,_utf8mb4 X'','zero','one','',0,0,NULL)",$input);
    schemaQuery('CREATE TABLE empty_probe (id INT PRIMARY KEY, value VARBINARY(12) DEFAULT NULL) ENGINE=InnoDB',$input);
    $expected=schemaSnapshot($source,$input);schemaCheck(count($expected)===2 && count($expected[$table]['rows'])===2,'Positive schema fixture was not populated.');
    foreach([false,true]as$compressed){
        $files=u5CreateDatabaseBackup($source,$directory,$compressed,$input);
        schemaCheck(schemaSnapshot($source,$input)===$expected,'Export mutated its source schema or data.');
        schemaQuery('SET GLOBAL max_allowed_packet=1048576',$admin);schemaQuery('CREATE DATABASE '.schemaId($target).' CHARACTER SET latin1',$admin);$restore=schemaConnect();mysql_select_db($target,$restore);
        foreach($files as$file)schemaImport($file,$restore);
        $actual=schemaSnapshot($target,$restore);
        schemaCheck(array_keys($actual)===array_keys($expected),'Restore lost tables or left staging behind.');
        foreach($expected as$name=>$state){schemaCheck($actual[$name]['ddl']===$state['ddl'],'Quoted identifiers, generated/invisible columns, indexes, defaults, engine or charset changed.');schemaCheck($actual[$name]['rows']===$state['rows'],'Generated, invisible, Unicode, binary, unsigned, decimal, floating, enum/set, temporal or NULL/empty values changed.');}
        mysql_close($restore);$restore=null;schemaQuery('DROP DATABASE '.schemaId($target),$admin);schemaQuery('SET GLOBAL max_allowed_packet=33554432',$admin);
    }
    echo 'Database backup schema contracts: '.$checks." checks passed (Aria/InnoDB, quoted Unicode identifiers, generated/invisible columns, exact types, NULL/empty and plain/gzip under 1 MiB).\n";
}finally{
    if($restore)mysql_close($restore);schemaQuery('SET GLOBAL max_allowed_packet='.$packet,$admin);mysql_query('DROP DATABASE IF EXISTS '.schemaId($target),$admin);mysql_query('DROP DATABASE IF EXISTS '.schemaId($source),$admin);
    if($input)mysql_close($input);mysql_close($admin);foreach(glob($directory.'/*')as$file)unlink($file);rmdir($directory);
}
