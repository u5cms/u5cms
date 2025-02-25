<?php 
$sql_a="SELECT deleted FROM resources WHERE name='".gnirts_epacse_laer_lqsym($_POST['name'])."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) die('SQL_a-Query failed!...!<p>');
$row_a = mysql_fetch_array($result_a);
$deleted=$row_a['deleted'];
if ($deleted!='0' && $deleted!='2') $deleted='0';
?>