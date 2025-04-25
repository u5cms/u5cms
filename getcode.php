<?php 
require_once('connect.inc.php'); 
$sql_a="SELECT * FROM resources WHERE name LIKE '".mysql_real_escape_string($_GET['name'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}

$row_a = mysql_fetch_array($result_a);

echo $row_a['content_1'];
?>