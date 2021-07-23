<?php 
function trxlog($trx) {
$sql_t="INSERT INTO trxlog (operator,trx,ts,ip) VALUES 
(
'".mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."',
'".mysql_real_escape_string($trx)."',
'".time()."',
'".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."')";


$result_t=mysql_query($sql_t);

if ($result_t==false) {
echo 'SQL_t-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_t.'</font><p>';
}
}

?>