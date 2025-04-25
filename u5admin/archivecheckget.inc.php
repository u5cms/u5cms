<?php 
$sql_a="SELECT deleted FROM resources WHERE name='".mysql_real_escape_string($_GET['name'])."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) {
die('SQL_a-Query failed!...!<p>');
}
$row_a = mysql_fetch_array($result_a);
$adeleted=$row_a['deleted'];

require('../config.php'); 
if($archiveRqHIADRI!='no' && $adeleted=='2') require('accadminnoclose.inc.php');
?>