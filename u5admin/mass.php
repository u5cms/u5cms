<?php 
require_once('connect.inc.php');
trxlog('status '.$_POST['newstatus'].': '.str_replace('\'','',$_POST['rowaid']));
$sql_a="UPDATE formdata SET lastmut='".time()."', status=".mysql_real_escape_string($_POST['newstatus'])." WHERE id IN (".($_POST['rowaid']).")";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}
header("Location: formdata2.php?".$_SERVER['QUERY_STRING']."&".time());
?>