<?php require('connect.inc.php')?><!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>&#19904;<?php echo $_GET['id']?> mailjob info</title>
<?php require('backendcss.php'); ?></head>
<body>
<button onclick="self.close()">back</button>
<?php
if($serialmailmethod>1)$plural='s';
else $plural='';
if($serialmailmethod>1)$plural2='are';
else $plural2='is';

$sql_a="SELECT * FROM mailingcron WHERE mailingid='".mysql_real_escape_string($_GET['id'])."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$row_a = mysql_fetch_array($result_a);
if($row_a['numa']>$row_a['nextmail']) echo '<hr>
Still sending. <b>'.$row_a['nextmail'].'&#9993;</b> sent until now (see list below). <b>'.($row_a['numa']-$row_a['nextmail']).'&#9993;</b> pending. Sending is timed according to <b>config.php</b> of your u5CMS installation where the value <b>$serialmailmethod='. $serialmailmethod.'</b> is set.
<br /><br />
<b>$serialmailmethod='. $serialmailmethod.'</b> means:<br />Every time '. str_replace('u5admin/mailinfo.php','',$scripturi).'mailingcron.php is called, '. $serialmailmethod .' mail'.$plural.' of the serial mail stack '.$plural2.' being sent. So there must be either a cronjob calling mailingcron.php on a regular basis or you surf to <a target="_blank" href="'. str_replace('mailinfo.php','',$scripturi) .'mailingcroncaller.php">'. str_replace('mailinfo.php','',$scripturi).'mailingcroncaller.php</a> and leave that page open until everything is sent.
<hr>';

$sql_a="SELECT * FROM mailing WHERE id='".mysql_real_escape_string($_GET['id'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$row_a = mysql_fetch_array($result_a);

$num_a = mysql_num_rows($result_a);

if($row_a['mailsent']==0) die('<h3>Sending mails from mailjob &#19904;'.$_GET['id'].' has not been started until now.</h3>');

echo '<h1>Mailjob &#19904;'.$_GET['id'].' execution initiated '.date('Y-m-d H:i:s',$row_a['mailsent']).' by '.$row_a['mailsentop'].'</h1>';
echo '<h2>Recipients (to, excl. cc/bcc)</h2>';
echo '<small><small>'.str_replace(',',', ',substr($row_a['mailsentto'],0,-1)).'</small></small>';
echo '<h2>Content</h2>';
echo $row_a['mailsentts'];
?>
<script>
setTimeout("location.reload()",1000*60*10);
</script>
</body>
</html>
