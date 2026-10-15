<?php
function u5ResourceForUpdate($name) {
    $result=mysql_query("SELECT * FROM resources WHERE deleted!=1 AND name='".mysql_real_escape_string($name)."' LIMIT 1");
    if ($result===false) throw new RuntimeException('Cannot read the current resource.');
    $row=mysql_fetch_array($result,MYSQL_ASSOC);
    if (!$row || $row['deleted']==1) throw new RuntimeException('The resource no longer exists.');
    return $row;
}

function u5UpdateResource($name, $changes, $row, $revision) {
    global $checksaveversionconflictinbackend;
    if (!is_numeric($revision)) throw new RuntimeException('Missing editor revision.');
    $original=$row;
    for ($attempt=0;$attempt<3;$attempt++) {
        $check=$checksaveversionconflictinbackend!='none' &&
            ($checksaveversionconflictinbackend!='foreign' || trim(u5flatidnlower($row['operator']))!=trim(u5flatidnlower($_SERVER['PHP_AUTH_USER'])));
        if ($row['deleted']!=$original['deleted'] || ($check && (int)$revision<(int)$row['lastmut'])) break;
        $stamp=max(time(),(int)$row['lastmut']+1);
        $new=array_merge($row,$changes);
        $new['operator']=u5flatidnlower($_SERVER['PHP_AUTH_USER']);
        $new['ip']=$_SERVER['REMOTE_ADDR'];
        $new['lastmut']=$stamp;
        $set=array();
        foreach (array_merge($changes,array('operator'=>$new['operator'],'ip'=>$new['ip'],'lastmut'=>$stamp)) as $field=>$value) {
            if (!preg_match('/^(content_[1-5]|title_[1-5]|desc_[1-5]|key_[1-5]|logins|hidden|typ|operator|ip|lastmut)$/',$field) || !is_scalar($value)) throw new RuntimeException('Invalid resource field.');
            $set[]='`'.$field."`='".mysql_real_escape_string($value)."'";
        }
        for ($language=1;$language<=5;$language++) $set[]='search_'.$language.'=NULL';
        $previous=$row['lastmut']===null ? 'lastmut IS NULL' : 'lastmut='.(int)$row['lastmut'];
        $sql="UPDATE resources SET ".implode(',',$set)." WHERE name='".mysql_real_escape_string($name)."' AND deleted=".(int)$original['deleted'].' AND '.$previous.' LIMIT 1';
        if (mysql_query($sql)===false) throw new RuntimeException('Update failed. The current resource has been kept.');
        if (mysql_affected_rows()==1) return array('saved'=>true,'stamp'=>$stamp,'record'=>$new,'current'=>$row);
        $result=mysql_query("SELECT * FROM resources WHERE deleted!=1 AND name='".mysql_real_escape_string($name)."' LIMIT 1");
        if ($result===false) throw new RuntimeException('Cannot recheck the current resource.');
        $row=mysql_fetch_array($result,MYSQL_ASSOC);
        if (!$row) break;
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
    $values=array();
    foreach ($fields as $field) $values[]="'".mysql_real_escape_string($record[$field] ?? '')."'";
    if (mysql_query('INSERT INTO resources_log (`'.implode('`,`',$fields).'`) VALUES ('.implode(',',$values).')')===false) throw new RuntimeException('History write failed. Your editor contents have been kept.');
}

function u5ResourceJsString($value) {
    return "'".str_replace(array("\\","'","\r","\n","<"),array("\\\\","\\'","\\r","\\n","\\x3c"),$value)."'";
}
?>
