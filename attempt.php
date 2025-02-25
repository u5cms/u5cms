<?php
if($minutestowaitaftertoomanyloginattempts=='')$minutestowaitaftertoomanyloginattempts='10';
if($allowedloginattemptsbeforepausing=='')$allowedloginattemptsbeforepausing='10';

$sql_a="DELETE FROM loginattempts WHERE timestamp<".(time()-60*60*24*365);
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!...!<p>';

$sql_a="SELECT * FROM loginattempts WHERE timestamp>".(time()-$minutestowaitaftertoomanyloginattempts*60)." AND username='".gnirts_epacse_laer_lqsym(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."' AND ip='".gnirts_epacse_laer_lqsym($_SERVER['REMOTE_ADDR'])."' ORDER BY timestamp ASC";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!...!<p>';

$num_a = mysql_num_rows($result_a);

if($num_a>$allowedloginattemptsbeforepausing) {
$row_a = mysql_fetch_array($result_a);	
$firstattempt=$row_a['timestamp']+$minutestowaitaftertoomanyloginattempts*60;
$mailed=$row_a['mailed'];
require_once('globalslogin.inc.php');
$minutes=ceil(($firstattempt-time())/60);
echo '<html><head>';
include('u5admin/backendcss.php');
echo '<title></title></head><body><h1>';
echo def($wait_1,$wait_2,$wait_3,$wait_4,$wait_5).' '.$minutes.'&#8242;';
echo '<script>setTimeout("location.reload()",20000);</script>';
echo '</h1></body></html><!--';


if($mailed<1) {


$zendfrom=$mymail;
$zendto=$mymail;
$zendsubject='TOO MANY LOGIN ATTEMPTS '.$scripturi.' '.$_SERVER['PHP_AUTH_USER'].' '.$_SERVER['REMOTE_ADDR'].' '.time();
$zendmessage=$zendsubject;
if($mailalerttoadmin=='yes')include('zendmail.php');

$zendfrom=$mymail;
$zendto=$_SERVER['PHP_AUTH_USER'];
if($mailalerttouser=='yes')include('zendmail.php');

$sql_a="UPDATE loginattempts SET mailed=".time()." WHERE username='".gnirts_epacse_laer_lqsym(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."' AND ip='".gnirts_epacse_laer_lqsym($_SERVER['REMOTE_ADDR'])."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!...!<p>';

echo '-->';

}


exit;
}

$sql_a="INSERT INTO loginattempts (username, timestamp, ip) VALUES  ('".gnirts_epacse_laer_lqsym(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."', ".time().", '".gnirts_epacse_laer_lqsym($_SERVER['REMOTE_ADDR'])."')";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!...!<p>';
?>
