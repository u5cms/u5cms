<?php 
$sql_a="SELECT deleted, typ FROM resources WHERE name='".gnirts_epacse_laer_lqsym($_POST['name'])."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) {
die('SQL_a-Query failed!...!<p>');
}
$row_a = mysql_fetch_array($result_a);
$adeleted=$row_a['deleted'];
$atyp=$row_a['typ'];

require('../config.php'); 
if($archiveRqHIADRI!='no' && $adeleted=='2') require('accadminnoclose.inc.php');
if ($cssRqHIADRI!='no'  && $atyp=='c') require('accadminnoclose.inc.php');
?>