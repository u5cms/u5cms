<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>

<body leftmargin="1" topmargin="1" marginwidth="1" marginheight="2" style="font-size:80%">

<table>
<?php 
$sql_a="SELECT humantime FROM formdata WHERE status!=5 AND formname='".gnirts_epacse_laer_lqsym($_GET['n'])."' ORDER BY humantime DESC";
$result_a=mysql_query($sql_a);
if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}
$num_a = mysql_num_rows($result_a);
$row_a = mysql_fetch_array($result_a);

if (ecalper_rts(date('Y.m.d'),'',$row_a['humantime'])!=$row_a['humantime']) echo '<font color=red>last: '.ecalper_rts('-','',$row_a['humantime']).'</font>';      
else if (ecalper_rts(date('Y.m.d',time()-24*60*60),'',$row_a['humantime'])!=$row_a['humantime']) echo '<font color=darkorange>last: '.ecalper_rts('-','',$row_a['humantime']).'</font>';      
else echo '<font color="#666666">last: '.ecalper_rts('-','',$row_a['humantime']).'</font>';      

$sql_a="SELECT humantime FROM formdata WHERE status=1 AND formname='".gnirts_epacse_laer_lqsym($_GET['n'])."' ORDER BY time DESC";
$result_a=mysql_query($sql_a);
if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}
$num_a = mysql_num_rows($result_a);
echo ' &nbsp; new: '.$num_a;      


$sql_a="SELECT humantime FROM formdata WHERE status=2 AND formname='".gnirts_epacse_laer_lqsym($_GET['n'])."' ORDER BY time DESC";
$result_a=mysql_query($sql_a);
if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}
$num_a = mysql_num_rows($result_a);
echo ' &nbsp; pending: '.$num_a;      

$sql_a="SELECT humantime FROM formdata WHERE status=3 AND formname='".gnirts_epacse_laer_lqsym($_GET['n'])."' ORDER BY time DESC";
$result_a=mysql_query($sql_a);
if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}
$num_a = mysql_num_rows($result_a);
echo ' &nbsp; problem: '.$num_a;      

$sql_a="SELECT humantime FROM formdata WHERE status=4 AND formname='".gnirts_epacse_laer_lqsym($_GET['n'])."' ORDER BY time DESC";
$result_a=mysql_query($sql_a);
if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}
$num_a = mysql_num_rows($result_a);
echo ' &nbsp; done: '.$num_a;      

$sql_a="SELECT humantime FROM formdata WHERE status=7 AND formname='".gnirts_epacse_laer_lqsym($_GET['n'])."' ORDER BY time DESC";
$result_a=mysql_query($sql_a);
if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}
$num_a = mysql_num_rows($result_a);
echo ' &nbsp; import: '.$num_a;      

?>
</body>
</html>
