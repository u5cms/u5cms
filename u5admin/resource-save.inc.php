<?php
/* Atomic, resource-specific optimistic writes; never reserve the whole CMS. */
require_once __DIR__.'/codeassets-migrate.inc.php';
function u5SaveFields() {
    $fields=array('name','ishomepage','logins','hidden','operator','ip','lastmut','deleted','typ');
    foreach (range(1,5) as $language) foreach (array('content','title','desc','key') as $prefix) $fields[]=$prefix.'_'.$language;
    return $fields;
}

function u5SaveDigestSql() {
    // Compare original column bytes on the server. Fixed-length field hashes
    // distinguish encodings, while '-' denotes SQL NULL, distinct from empty.
    $parts=array();
    foreach (u5SaveFields() as $field) $parts[]="COALESCE(SHA2(CAST(".u5CodeMigrationIdentifier($field)." AS BINARY),256),'-')";
    return "SHA2(CONCAT_WS(':',".implode(',',$parts)."),256)";
}

function u5SaveSnapshot($name) {
    if (!is_string($name) || $name==='') throw new RuntimeException('No resource selected.');
    // Names/operators follow the established connection charset for lookup,
    // policy and display. The digest independently guards original DB bytes.
    $rows=u5CodeMigrationRows("SELECT name,deleted,typ,operator,lastmut,".u5SaveDigestSql()." AS edit_digest FROM resources WHERE name='".mysql_real_escape_string($name)."' AND deleted!=1 LIMIT 1");
    if (!$rows) throw new RuntimeException('The resource no longer exists. Submitted content has not replaced another resource.');
    return $rows[0];
}

function u5SaveResource($before,$changes,&$stamp) {
    global $checksaveversionconflictinbackend;
    $original=$before;
    $set=array();$guard=array();
    $fields=u5SaveFields();
    foreach ($changes as $field=>$value) {
        if (!in_array($field,$fields,true) || !is_scalar($value)) throw new RuntimeException('Invalid resource field.');
        // Submitted text uses the existing connection charset. Snapshot guards
        // use raw database bytes, including UTF-8 and legacy Latin-1 columns.
        $set[]=u5CodeMigrationIdentifier($field)."='".mysql_real_escape_string((string)$value)."'";
    }
    for ($attempt=0;$attempt<3;$attempt++) {
        $guard=array("name='".mysql_real_escape_string($before['name'])."'",'deleted='.(int)$before['deleted'],u5SaveDigestSql().'='.u5CodeMigrationValue($before['edit_digest']));
        u5CodeMigrationQuery('UPDATE resources SET '.implode(',',$set).',lastmut='.(int)$stamp.' WHERE '.implode(' AND ',$guard));
        if (mysql_affected_rows()===1) return true;
        // Immediate optimistic recheck, with no wait or queue. Only the
        // configured overwrite policy permits replacing a newer version.
        $before=u5SaveSnapshot($original['name']);
        if ($before['deleted']!==$original['deleted'] || $before['typ']!==$original['typ']) return false;
        $check=$checksaveversionconflictinbackend!='none' && ($checksaveversionconflictinbackend!='foreign' || trim(u5flatidnlower($before['operator']))!=trim(u5flatidnlower($_SERVER['PHP_AUTH_USER'])));
        if ($check) return false;
        $stamp=u5SaveNextRevision($before);
    }
    return false;
}

function u5SaveNextRevision($before) {
    // The actual column controls capacity, including sites with BIGINT
    // revisions. The conditional UPDATE keeps database errors explicit.
    return max(time(),(int)$before['lastmut']+1);
}
