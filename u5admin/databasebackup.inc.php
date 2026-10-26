<?php
/* Streaming, lossless SQL backups. This does not change the CMS connection charset. */
function u5BackupIdentifier($name) { return chr(96).str_replace(chr(96),chr(96).chr(96),$name).chr(96); }
function u5BackupQuery($sql,$link) {
    $result=mysql_query($sql,$link);
    if ($result===false) throw new RuntimeException('Database backup failed: '.mysql_error($link));
    return $result;
}
function u5BackupLiteralParts($column) {
    $type=strtolower($column['Type']);
    if ($type==='json') return array("CONVERT(X'","' USING utf8mb4)");
    if (!empty($column['Collation'])) {
        $charset=explode('_',$column['Collation'])[0];
        if (!preg_match('/^[a-z0-9]+$/iD',$charset)) throw new RuntimeException('Unsupported backup character set.');
        return array('_'.$charset." X'","'");
    }
    if (preg_match('/blob|binary|^bit\\b/i',$type)) return array("X'","'");
    if (preg_match('/^(?:tinyint|smallint|mediumint|int|bigint|decimal|numeric|float|double|real|year|date|datetime|timestamp|time)\\b/',$type)) return array("CONVERT(X'","' USING ascii)");
    throw new RuntimeException('Unsupported database backup type: '.$column['Type']);
}
function u5BackupWriteLiteral($output,$value,$column) {
    if ($value===null) { $output->append('NULL');return; }
    list($prefix,$suffix)=u5BackupLiteralParts($column);$output->append($prefix);
    for ($offset=0,$length=strlen($value);$offset<$length;$offset+=32768) $output->append(bin2hex(substr($value,$offset,32768)));
    $output->append($suffix);
}
class U5DatabaseBackupOutput {
    private $temporary;
    private $handle;
    private $compressed;
    private $target;
    public function __construct($directory,$target,$compressed) {
        $this->compressed=$compressed;$this->target=$target;
        $this->temporary=@tempnam($directory,'u5db_');
        if ($this->temporary===false || realpath(dirname($this->temporary))!==$directory) {
            if ($this->temporary!==false) @unlink($this->temporary);
            throw new RuntimeException('Cannot create a database backup in its destination directory.');
        }
        $this->handle=$compressed ? @gzopen($this->temporary,'wb9') : @fopen($this->temporary,'wb');
        if (!$this->handle) { @unlink($this->temporary);throw new RuntimeException('Cannot open the temporary database backup.'); }
    }
    public function append($text) {
        $length=strlen($text);$offset=0;
        while ($offset<$length) {
            $chunk=substr($text,$offset,65536);
            $written=$this->compressed ? @gzwrite($this->handle,$chunk) : @fwrite($this->handle,$chunk);
            if (!$written) throw new RuntimeException('Cannot write the database backup. Previous backups have been kept.');
            $offset+=$written;
        }
    }
    public function finish() {
        $ok=$this->compressed ? @gzclose($this->handle) : @fclose($this->handle);$this->handle=null;
        if (!$ok || !@chmod($this->temporary,0666 & ~umask())) throw new RuntimeException('Cannot finish the database backup.');
    }
    public function publish() {
        if ($this->handle || file_exists($this->target) || !@rename($this->temporary,$this->target)) throw new RuntimeException('Cannot publish the completed database backup.');
        $this->temporary=null;return $this->target;
    }
    public function __destruct() {
        if ($this->handle) { if ($this->compressed) @gzclose($this->handle);else @fclose($this->handle); }
        if ($this->temporary && is_file($this->temporary)) @unlink($this->temporary);
    }
}
function u5CreateDatabaseBackup($database,$directory,$compressed,$link) {
    $directory=realpath($directory);
    if ($directory===false || !is_dir($directory)) throw new RuntimeException('The database backup directory is unavailable.');
    if (!mysql_select_db($database,$link)) throw new RuntimeException('Cannot select the database to back up.');
    // This is a separate connection. Metadata is Unicode; table values are selected as raw bytes.
    u5BackupQuery('SET NAMES utf8mb4',$link);u5BackupQuery("SET time_zone='+00:00'",$link);
    // Retain an explicit AUTO_INCREMENT zero and dates allowed by older schemas.
    u5BackupQuery("SET sql_mode='NO_AUTO_VALUE_ON_ZERO'",$link);
    $tables=array();$result=u5BackupQuery('SHOW FULL TABLES',$link);
    while ($row=mysql_fetch_row($result)) {
        if ($row[1]!=='BASE TABLE') throw new RuntimeException('This database contains views. Use a full server dump for these custom objects; previous backups have been kept.');
        $tables[]=$row[0];
    }
    $stamp=date('Y-m-d_His').'_'.bin2hex(random_bytes(4));$prefix=preg_replace('/[^a-zA-Z0-9_-]/','_',$database);
    $extension=$compressed ? '.sql.gz' : '.sql';
    $structure=new U5DatabaseBackupOutput($directory,$directory.'/'.$prefix.'_structure_'.$stamp.$extension,$compressed);
    $data=new U5DatabaseBackupOutput($directory,$directory.'/'.$prefix.'_data_'.$stamp.$extension,$compressed);
    $published=array();
    try {
        $header="-- u5CMS database backup ".date('c')."\nSET NAMES utf8mb4;\nSET sql_mode='NO_AUTO_VALUE_ON_ZERO';\nSET FOREIGN_KEY_CHECKS=0;\nSET time_zone='+00:00';\n";
        $structure->append($header);$data->append($header);
        foreach ($tables as $table) {
            $identifier=u5BackupIdentifier($table);
            $create=mysql_fetch_row(u5BackupQuery('SHOW CREATE TABLE '.$identifier,$link))[1];
            $structure->append("\nDROP TABLE IF EXISTS ".$identifier.";\n".$create.";\n");
            $columns=array();$select=array();$result=u5BackupQuery('SHOW FULL COLUMNS FROM '.$identifier,$link);
            while ($column=mysql_fetch_assoc($result)) {
                if (preg_match('/(?:VIRTUAL|STORED|PERSISTENT) GENERATED/i',$column['Extra'])) continue;
                $field=u5BackupIdentifier($column['Field']);
                // FLOAT's ordinary text display can round away stored precision.
                if (preg_match('/^(float|double|real)\b/i',$column['Type'])) $field='('.$field.'+0e0)';
                $columns[$column['Field']]=$column;$select[]='CAST('.$field.' AS BINARY)';
            }
            if (!$columns) throw new RuntimeException('No writable backup columns in '.$table);
            $fields=implode(',',array_map('u5BackupIdentifier',array_keys($columns)));
            $result=mysql_unbuffered_query('SELECT '.implode(',',$select).' FROM '.$identifier,$link);
            if ($result===false) throw new RuntimeException('Cannot stream table '.$table);
            try {
                while ($row=mysql_fetch_row($result)) {
                    $data->append('INSERT INTO '.$identifier.' ('.$fields.') VALUES (');$index=0;
                    foreach ($columns as $column) {
                        if ($index) $data->append(',');
                        u5BackupWriteLiteral($data,$row[$index++],$column);
                    }
                    $data->append(");\n");
                }
                if (mysql_errno($link)) throw new RuntimeException('The database connection failed while backing up '.$table);
            } finally { mysql_free_result($result); }
        }
        $structure->append("\nSET FOREIGN_KEY_CHECKS=1;\n");$data->append("\nSET FOREIGN_KEY_CHECKS=1;\n");
        $structure->finish();$data->finish();
        $published[]=$structure->publish();$published[]=$data->publish();
        return $published;
    } catch (Throwable $error) {
        foreach ($published as $path) @unlink($path);
        throw $error;
    }
}
