<?php
$sql_a="DELETE FROM resources WHERE name='".mysql_real_escape_string($_POST['name'])."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) die('SQL_a-Query failed!...!<p>');
?>