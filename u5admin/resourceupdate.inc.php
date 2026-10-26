<?php
function u5ResourceForUpdate($name,$raw=false) {
    $select='*';$encodings=array();
    if ($raw) {
        require_once __DIR__.'/codeassets-migrate.inc.php';
        $columns=u5CodeMigrationColumns('resources');$select=u5CodeMigrationSelect($columns)['select'];
        foreach ($columns as $field=>$column) if ($column['Collation']) $encodings[$field]=u5CodeMigrationCharset($column);
    }
    $result=mysql_query("SELECT ".$select." FROM resources WHERE deleted!=1 AND name='".mysql_real_escape_string($name)."' LIMIT 1");
    if ($result===false) throw new RuntimeException('Cannot read the current resource.');
    $row=mysql_fetch_array($result,MYSQL_ASSOC);
    if (!$row || $row['deleted']==1) throw new RuntimeException('The resource no longer exists.');
    if ($raw) $row["\0u5_encodings"]=$encodings;
    return $row;
}

function u5ResourceSqlValue($value,$charset='latin1') {
    if ($value===null) return 'NULL';
    if (!preg_match('/^[a-z0-9_]+$/iD',$charset)) throw new RuntimeException('Invalid resource character set.');
    return '_'.$charset." X'".bin2hex((string)$value)."'";
}

function u5UpdateResource($name, $changes, $row, $revision,$inputEncodings=array()) {
    global $checksaveversionconflictinbackend;
    if (!is_numeric($revision)) throw new RuntimeException('Missing editor revision.');
    $original=$row;$encodings=$row["\0u5_encodings"] ?? array();
    if ($encodings) {
        $columns=u5CodeMigrationColumns('resources');
        foreach ($changes as $field=>&$value) if (isset($encodings[$field])) $value=u5CodeMigrationConvertExact($value,$inputEncodings[$field] ?? 'latin1',$encodings[$field]);
        unset($value);u5CodeMigrationValidateRow('resources',$row,array_merge($row,$changes),$columns);
    }
    for ($attempt=0;$attempt<3;$attempt++) {
        $check=$checksaveversionconflictinbackend!='none' &&
            ($checksaveversionconflictinbackend!='foreign' || trim(u5flatidnlower($row['operator']))!=trim(u5flatidnlower($_SERVER['PHP_AUTH_USER'])));
        if ($row['deleted']!=$original['deleted'] || ($check && (int)$revision<(int)$row['lastmut'])) break;
        $stamp=max(time(),(int)$row['lastmut']+1);
        $new=array_merge($row,$changes);
        $new['operator']=u5flatidnlower($_SERVER['PHP_AUTH_USER']);
        $new['ip']=$_SERVER['REMOTE_ADDR'];
        $new['lastmut']=$stamp;
        if ($encodings) { $new["\0u5_encodings"]=$encodings;$new["\0u5_encodings"]['operator']='latin1';$new["\0u5_encodings"]['ip']='latin1'; }
        $set=array();
        foreach (array_merge($changes,array('operator'=>$new['operator'],'ip'=>$new['ip'],'lastmut'=>$stamp)) as $field=>$value) {
            if (!preg_match('/^(content_[1-5]|title_[1-5]|desc_[1-5]|key_[1-5]|logins|hidden|typ|operator|ip|lastmut)$/',$field) || !is_scalar($value)) throw new RuntimeException('Invalid resource field: '.$field.' ('.gettype($value).').');
            $set[]='`'.$field.'`='.u5ResourceSqlValue($value,in_array($field,array('operator','ip','lastmut'),true) ? 'latin1' : ($encodings[$field] ?? 'latin1'));
        }
        for ($language=1;$language<=5;$language++) $set[]='search_'.$language.'=NULL';
        $previous=$row['lastmut']===null ? 'lastmut IS NULL' : 'lastmut='.(int)$row['lastmut'];
        $sql="UPDATE resources SET ".implode(',',$set)." WHERE name='".mysql_real_escape_string($name)."' AND deleted=".(int)$original['deleted'].' AND '.$previous.' LIMIT 1';
        if (mysql_query($sql)===false) throw new RuntimeException('Update failed. The current resource has been kept.');
        if (mysql_affected_rows()==1) return array('saved'=>true,'stamp'=>$stamp,'record'=>$new,'current'=>$row);
        try { $row=u5ResourceForUpdate($name,(bool)$encodings); }
        catch (RuntimeException $error) { break; }
    }
    $record=array_merge($original,$changes);
    $record['lastmut']=time();
    return array('saved'=>false,'stamp'=>$record['lastmut'],'record'=>$record,'current'=>$row);
}

function u5LogResourceAttempt($result) {
    $record=$result['record'];
    $fields=array('name','ishomepage','logins','hidden','ip','lastmut','deleted','typ');
    for ($language=1;$language<=5;$language++) foreach (array('content','title','desc','key') as $field) $fields[]=$field.'_'.$language;
    $fields[]='operator';
    $record['operator']=$_SERVER['PHP_AUTH_USER'].($result['saved'] ? '' : '(!)');
    $encodings=$record["\0u5_encodings"] ?? array();$encodings['operator']='latin1';
    $values=array();
    foreach ($fields as $field) $values[]=u5ResourceSqlValue($record[$field] ?? '',$encodings[$field] ?? 'latin1');
    if (mysql_query('INSERT INTO resources_log (`'.implode('`,`',$fields).'`) VALUES ('.implode(',',$values).')')===false) throw new RuntimeException('History write failed. Your editor contents have been kept.');
}

function u5ResourceJsString($value) {
    return "'".str_replace(array("\\","'","\r","\n","<"),array("\\\\","\\'","\\r","\\n","\\x3c"),$value)."'";
}
?>
