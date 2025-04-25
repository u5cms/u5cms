<?php 
ignore_user_abort(true);set_time_limit(3600); 

require_once('connect.inc.php');

$sql_a="SELECT * FROM formdata WHERE formname='getaloginstep1' AND status<5";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}

$num_a = mysql_num_rows($result_a);

if ($num_a>0) {

die('You cannot manage intranet users manually as long as there are users for automatic inscription in <a href="formdata2.php?n=getaloginstep1&s=1">getaloginstep1</a> with status 1, 2, 3 or 4!');

}
?>

