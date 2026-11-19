<?php
/* Atomic, resource-specific optimistic writes; never reserve the whole CMS. */
require_once __DIR__.'/resource-rename-support.inc.php';
function u5SaveSnapshot($name) {
    if (!is_string($name) || $name==='') throw new RuntimeException('No resource selected.');
    $columns=u5RenameStorageColumns('resources');
    $rows=u5RenameStorageRows('SELECT '.u5RenameStorageSelect($columns)['select'].' FROM resources WHERE name='.u5RenameStorageValue($name).' AND deleted!=1 LIMIT 1');
    if (!$rows) throw new RuntimeException('The resource no longer exists. Submitted content has not replaced another resource.');
    return $rows[0];
}

function u5SaveResource($before,$changes,$stamp) {
    $set=array();$guard=array();
    foreach (array('name','ishomepage','logins','hidden','operator','ip','lastmut','deleted','typ') as $field) $guard[$field]=$before[$field];
    foreach (range(1,5) as $language) foreach (array('content','title','desc','key') as $prefix) $guard[$prefix.'_'.$language]=$before[$prefix.'_'.$language];
    foreach ($changes as $field=>$value) {
        if (!array_key_exists($field,$guard) || !is_scalar($value)) throw new RuntimeException('Invalid resource field.');
        // Submitted text uses the existing connection charset. Snapshot guards
        // use raw database bytes, including UTF-8 and legacy Latin-1 columns.
        $set[]=u5RenameStorageIdentifier($field)."='".mysql_real_escape_string((string)$value)."'";
    }
    $set[]='lastmut='.(int)$stamp;
    u5RenameStorageQuery('UPDATE resources SET '.implode(',',$set).' WHERE '.u5RenameStorageWhere($guard));
    return mysql_affected_rows()===1;
}

function u5SaveNextRevision($before) {
    $stamp=max(time(),(int)$before['lastmut']+1);
    if ($stamp>2147483647) throw new RuntimeException('The resource revision exceeds the legacy column capacity. Enlarge lastmut before retrying.');
    return $stamp;
}
