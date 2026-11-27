<?php
function u5FormDataWriteLockName() {
    $result=mysql_query('SELECT DATABASE()');
    if ($result===false) return false;
    $row=mysql_fetch_row($result);
    if (!is_array($row) || !isset($row[0])) return false;
    return 'u5-formdata-'.sha1((string)$row[0]);
}

function u5FormDataWriteLock() {
    $name=u5FormDataWriteLockName();
    if ($name===false) return false;
    $result=mysql_query("SELECT GET_LOCK('".mysql_real_escape_string($name)."',5)");
    if ($result===false) return false;
    $row=mysql_fetch_row($result);
    return is_array($row) && isset($row[0]) && (string)$row[0]==='1' ? $name : false;
}

function u5FormDataWriteUnlock($name) {
    if (!is_string($name) || $name==='') return false;
    $result=mysql_query("SELECT RELEASE_LOCK('".mysql_real_escape_string($name)."')");
    if ($result===false) return false;
    $row=mysql_fetch_row($result);
    return is_array($row) && isset($row[0]) && (string)$row[0]==='1';
}

function u5FormDataRequireWriteLock() {
    $name=u5FormDataWriteLock();
    if ($name!==false) return $name;
    http_response_code(409);
    exit('This form is being saved by another request. Please wait and try again.');
}
