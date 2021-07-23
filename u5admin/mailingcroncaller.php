<?php require('connect.inc.php')?><!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>mailingcroncaller</title>
<?php require('backendcss.php'); ?></head>
</head>

<body>
<?php
if($serialmailmethod>1)$plural='s';
else $plural='';
if($serialmailmethod>1)$plural2='are';
else $plural2='is';
?>
Every time <?php echo str_replace('u5admin/mailingcroncaller.php','',$scripturi)?>mailingcron.php is called, <?php echo $serialmailmethod ?> mail<?php echo $plural ?> of the serial mail stack <?php echo $plural2 ?> being sent. So there must be either a cronjob calling mailingcron.php on a regular basis or leave the page at hand open until everything is sent.
<hr />
<iframe frameborder="0" width="1000" height="1000" name="msend"></iframe>
<script>
function mailsend() {
msend.location.href='../mailingcron.php';	
setTimeout("mailsend()",10000);
}
setTimeout("mailsend()",777);
</script>
</body>
</html>