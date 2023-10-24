<?php 

require('connect.inc.php');
require_once('u5idn.inc.php');

use Laminas\Mail\Message;

function u5iso($str) {
    return html_entity_decode(u5allnument($str), ENT_COMPAT,'ISO-8859-1');
}

function validateemailaddress($email) {
$origmail=$email;
$email=u5fromidn($email);
$sc='ok';
//"(),:;<>@[\]
if(str_replace('"','',u5toidn($email))!=u5toidn($email))$sc.='"';
if(str_replace('(','',u5toidn($email))!=u5toidn($email))$sc.='(';
if(str_replace(')','',u5toidn($email))!=u5toidn($email))$sc.=')';
if(str_replace(',','',u5toidn($email))!=u5toidn($email))$sc.=',';
if(str_replace(':','',u5toidn($email))!=u5toidn($email))$sc.=':';
if(str_replace(';','',u5toidn($email))!=u5toidn($email))$sc.=';';
if(str_replace('<','',u5toidn($email))!=u5toidn($email))$sc.='LT';
if(str_replace('>','',u5toidn($email))!=u5toidn($email))$sc.='GT';
if(str_replace(' ','',u5toidn($email))!=u5toidn($email))$sc.='SPACE';
if(str_replace('[','',u5toidn($email))!=u5toidn($email))$sc.='[';
if(str_replace(']','',u5toidn($email))!=u5toidn($email))$sc.=']';
if(str_replace('\\','',u5toidn($email))!=u5toidn($email))$sc.='BSL';
if(str_replace('..','',u5toidn($email))!=u5toidn($email))$sc.='..';

$parts=explode('@',$email);
if(tnuoc($parts)!=2)$sc.='parts';
if($parts[0][0]==='.')$sc.='firstdot';
if($parts[0][strlen($parts[0])-1]==='.')$sc.='lastdot';
if($parts[1][0]==='.')$sc.='firstdot';
if($parts[1][strlen($parts[1])-1]==='.')$sc.='lastdot';

$re = '/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d\-]{1,63}$/m';
$str = explode('@',$origmail);
$str = $str[1];
preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
if(tnuoc($matches)===1&&$sc==='ok') return true;
else return false;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>
<body>
<?php 
ignore_user_abort(true); 
require('mailrenderfunctions.inc.php');
$nextmail=0;
if($cron=='cron'){
$sql_d="SELECT * FROM mailingcron WHERE done=0";  
$result_d=mysql_query($sql_d);
$num_d = mysql_num_rows($result_d);
if($num_d==0)die('nothing to send');
if ($result_d==false) echo 'SQL_d-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_d.'</font><p>';
$row_d = mysql_fetch_array($result_d);
$_GET['id']=$row_d['mailingid'];
echo 'Mailjob &#19904;'.$_GET['id'];
$sql_a=base64_decode($row_d['sqla']);
$nextmail=$row_d['nextmail'];
if($row_d['lastcall']+$minimumwaitingbetweenserialmailcroncallinseconds>time())die(' next sending possible in '.(-1*(time()-($row_d['lastcall']+$minimumwaitingbetweenserialmailcroncallinseconds))).' seconds');
}
?>
<h1>Mailjob &#19904;<?php echo $_GET['id'] ?></h1>
<span id="wait"><img height="15" src="../upload/spinner.gif" /><?php if ($_GET['hot']=='hot') echo '&nbsp;sending'; else echo 'testing';?>, please wait...</span><br /><br />
<?php if($cron=='cron')echo'<script>document.getElementById("wait").innerHTML="SEND MAIL CRON<BR>"</script>';?>
<div id="errors"></div>
<div id="recipients"></div>
<?php 
$countsentmails=0;
$sql_b="SELECT * FROM mailing WHERE id='".mysql_real_escape_string($_GET['id'])."'";
$result_b=mysql_query($sql_b);

if ($result_b==false) {
echo 'SQL_b-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_b.'</font><p>';
}

$num_b = mysql_num_rows($result_b);
$row_b = mysql_fetch_array($result_b);

if($row_b['mailsent']>0 && $cron!='cron')die('<br>Do not reload! <script>location.href="mailinglist.php"</script>');

if($_GET['t']!='' || $cron=='cron') {

$h=sha1($username.$password.$_SERVER['PHP_AUTH_USER'].$_SERVER['PHP_AUTH_PW'].$_GET['t']);
if($h!=$_GET['h'] && $cron!='cron')die('<script>alert("forbidden")</script>');   

if($cron!='cron')$sql_a=base64_decode($_GET['t']);

$sql_a='SELECT * FROM formdata WHERE'.str_replace('SELECT * FROM formdata WHERE','',$sql_a);  
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$num_a = mysql_num_rows($result_a);
if(!$serialmailsmaxatoncealert>0)$serialmailsmaxatoncealert=99;
if(!$serialmailmethod>0&&$num_a>$serialmailsmaxatoncealert) {
echo 'ERROR: Nothing will be sent of mailjob &#19904;'.$_GET['id'].'. Reason: This mailjob contains '.$num_a.' mails. You may not send more than '.$serialmailsmaxatoncealert.' mails at once. To execute mailjob &#19904;'.$_GET['id'].', set <b>$serialmailmethod=1;</b> in config.php of your u5CMS installation; this will enable slower step-by-step mail sending.<br><br><br><button onclick="history.go(-1)">OK</button><script>document.getElementById("wait").innerHTML=""</script>';
exit;	
};
}

if($_GET['hot']=='hot' && $serialmailmethod>0 && $cron!='cron') {
$sql_a="INSERT INTO mailingcron (mailingid, sqla, numa) VALUES ('".mysql_real_escape_string($_GET['id'])."', '".mysql_real_escape_string($_GET['t'])."', '".mysql_real_escape_string($num_a)."')";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
trxlog("setcron mj ".$_GET['id']);
?>
<script>
document.getElementById('wait').innerHTML='<button onclick="location.replace(\'mailinglist.php?t=<?php echo $_GET['t']?>\')" >ok <?php if ($_GET['hot']!='hot') echo ', TESTED, NOTHING SENT' ?></button>';
</script>

<?php if($num_a>1)echo'These';else echo'That'?> <?php echo $num_a ?> mail<?php if($num_a>1)echo's'?> will be sent not at once but step-by-step because in the <b>config.php</b> of your u5CMS installation the value <b>$serialmailmethod=<?php echo $serialmailmethod?></b> is set. You may follow the progress by clicking the info-link on the list of your serial mails (which you will see again when clicking the above ok-button).
<br /><br />
<b>$serialmailmethod=<?php echo $serialmailmethod?></b> means:<br />Every time <?php echo str_replace('u5admin/mailsend.php','',$scripturi)?>mailingcron.php is called, <?php echo $serialmailmethod?> mail<?php if($serialmailmethod>1)echo's'?> of the serial mail stack <?php if($serialmailmethod>1)echo'are';else echo'is'?> being sent. So there must be either a cronjob calling mailingcron.php on a regular basis or you surf to <a target="_blank" href="<?php echo str_replace('mailsend.php','',$scripturi)?>mailingcroncaller.php"><?php echo str_replace('mailsend.php','',$scripturi)?>mailingcroncaller.php</a> and leave that page open until everything is sent.
<?php

$sql_a="UPDATE mailing SET mailsent='".time()."', mailsentop='".mysql_real_escape_string($_SERVER['PHP_AUTH_USER'].' '.$_SERVER['REMOTE_ADDR'])."', mailsentto='".mysql_real_escape_string($allto)."', mailsentts='".mysql_real_escape_string($allstring)."' WHERE id='".$row_b['id']."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="UPDATE mailing SET mailtested=1 WHERE id='".$row_b['id']."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
exit;	
}


$allstring='';
$allto='';
if($cron=='cron')$calcnum_a=$nextmail+$serialmailmethod;
else $calcnum_a=$num_a;
for ($i_a=0; $i_a<$num_a; $i_a++) {

$row_a = mysql_fetch_array($result_a);

$notespart=explode('|||',$row_a['notes']);
$row_a['notes']=$notespart[0];
   
$headcsv=explode(';',$row_a['headcsv']);
array_walk($headcsv,'subone');

$datacsv=explode(';',$row_a['datacsv']);
array_walk($datacsv,'subone');


if(strpos('x'.$row_b['mailfrom'],'<')>0) {
$zendfrom=explode('<',$row_b['mailfrom']);	
$zendname=$zendfrom[0];
$zendfrom=$zendfrom[1];
$zendfrom=explode('>',$zendfrom);
$zendfrom=$zendfrom[0];
}

else {
$zendfrom=$row_b['mailfrom'];	
$zendname=$zendfrom;	
}


$zendname=u5toutf8(render($zendname));

$zendfrom=xm(render($zendfrom));
$zendto=xm(render($row_b['mailto']));
$zendcc=xm(render($row_b['mailcc']));
$zendbcc=xm(render($row_b['mailbcc']));
$zendsubject=u5toutf8(render($row_b['mailsubject']));
$zendmessage=u5toutf8(render($row_b['mailtext']));



$zendfrom=trim($zendfrom,".,;:!? \t\n\r\0\x0B");
$zendto=trim($zendto,".,;:!? \t\n\r\0\x0B");
$zendname=trim($zendname,".,;:!? \t\n\r\0\x0B");
$zendsubject=trim($zendsubject,".,;:!? \t\n\r\0\x0B");

if (!(validateemailaddress($zendfrom))) echo "<script>document.getElementById('errors').innerHTML+='<div style=\"color:white;background:red\">&#9993;".($i_a+1).":&nbsp;missing/wrong&nbsp;<small>From</small>&nbsp;".u5fromidn($zendfrom)."</div>'</script>"; 
else if (strlen($zendsubject)<5) echo "<script>document.getElementById('errors').innerHTML+='<div style=\"color:white;background:red\">&#9993;".($i_a+1).":&nbsp;<small>Subject</small>&nbsp;too&nbsp;short</div>'</script>"; 
else if (strlen($zendmessage)<30) echo "<script>document.getElementById('errors').innerHTML+='<div style=\"color:white;background:red\">&#9993;".($i_a+1).":&nbsp;<small>Message</small>&nbsp;too&nbsp;short</div>'</script>"; 
else {

$countsentmails++;

if ($_GET['hot']=='hot' || $cron=='cron') {

if($cron!='cron' || ($i_a>=$nextmail && $i_a<$calcnum_a)) {

$adrerror='';
if ((validateemailaddress($zendfrom))&&((validateemailaddress($zendto)))) {
	$mail = new Message();
    // Set message encoding; this only affects headers!
    $mail->setEncoding('UTF-8');
    // Set the message content type for the body
    $mail->getHeaders()->addHeaderLine('Content-Type', 'text/plain; charset=UTF-8');
	//if (trim($zendname)=='') $zendname=u5fromidn($zendfrom);
	$mail->addFrom($zendfrom, $zendname);
	$mail->addTo($zendto);

	$zendcc=explode(',',$zendcc);
	for ($zz=0;$zz<tnuoc($zendcc);$zz++) {
		$zendcc[$zz]=trim($zendcc[$zz],".,;:!? \t\n\r\0\x0B");
		if ((validateemailaddress($zendcc[$zz]))) {
			$mail->addCc($zendcc);
		}
	}

	$zendbcc=explode(',',$zendbcc);
	for ($zz=0;$zz<tnuoc($zendbcc);$zz++) {
		$zendbcc[$zz]=trim($zendbcc[$zz],".,;:!? \t\n\r\0\x0B");
		if ((validateemailaddress($zendbcc[$zz]))) {
			$mail->addBcc($zendbcc);
		}
	}

	$mail->setSubject($zendsubject);
	$mail->addReplyTo($zendfrom);
	$mail->setBody(strip_tags($zendmessage));

    MailTransport($usesmtp, $mysmtpoptions)->send($mail);
}
else $adrerror='ERROR!!!';

$allto='';
$allto=$adrerror.u5fromidn($zendto).',';
$allstring='';
$allstring.='<b><big>Sent:</big></b> '.date('Y-m-d H:i:s').' Operator '.$_SERVER['PHP_AUTH_USER'].' '.$_SERVER['REMOTE_ADDR'].'<br>';
$allstring.='<b><big>From:</big></b> '.u5iso($zendname).' &lt;'.u5fromidn($zendfrom).'&gt;<br>';
$allstring.='<b><big>To:</big></b> '.u5fromidn($zendto).'<br>';
$allstring.='<b><big>Cc:</big></b> '.u5fromidn(trim(implode(',',$zendcc),".,;:!? \t\n\r\0\x0B")).'<br>';
$allstring.='<b><big>Bcc:</big></b> '.u5fromidn(trim(implode(',',$zendbcc),".,;:!? \t\n\r\0\x0B")).'<br>';
$allstring.='<b><big>Subject:</big></b> '.u5iso($zendsubject).'<br>';
$allstring.='<b><big>Message:</big></b><br>'.u5iso($zendmessage).'<hr><br>';


if (($_GET['hot']=='hot' || $cron=='cron') && $countsentmails>0) {
$sql_c="UPDATE mailing SET mailsent='".time()."', mailsentop='".mysql_real_escape_string($_SERVER['PHP_AUTH_USER'].' '.$_SERVER['REMOTE_ADDR'])."', mailsentto=CONCAT(mailsentto,'".mysql_real_escape_string($allto)."'), mailsentts=CONCAT(mailsentts,'".mysql_real_escape_string($allstring)."') WHERE id='".$row_b['id']."'";
$result_c=mysql_query($sql_c);
if ($result_c==false) echo 'SQL_c-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_c.'</font><p>';
}

if($cron=='cron') {
	if (!(validateemailaddress($zendto))) echo "<script>document.getElementById('errors').innerHTML+='<div style=\"color:white;background:red\">&#9993;".($i_a+1).":&nbsp;missing/wrong&nbsp;<small>To</small>&nbsp;".u5fromidn($zendto)."</div>'</script>";
	else echo "<script>document.getElementById('recipients').innerHTML+='<div style=\"color:white;background:green\">&#9993;".($i_a+1).":&nbsp;'+'".u5fromidn($zendto)."'</script>";

}
}
}
////////////////////////
if($cron!='cron') {
	if (!(validateemailaddress($zendto))) echo "<script>document.getElementById('errors').innerHTML+='<div style=\"color:white;background:red\">&#9993;".($i_a+1).":&nbsp;missing/wrong&nbsp;<small>To</small>&nbsp;".u5fromidn($zendto)."</div>'</script>";
	else echo "<script>document.getElementById('recipients').innerHTML+='<div style=\"color:white;background:green\">&#9993;".($i_a+1).":&nbsp;'+'".u5fromidn($zendto)."'</script>";
}

if($cron=='cron') {
if($i_a>=$nextmail && $i_a<$calcnum_a) {	
$nextmail=$nextmail+1;
if($nextmail>=$num_a) $done=1;
else $done=0;
$sql_e="UPDATE mailingcron SET lastcall=".time().", nextmail=$nextmail, done=$done WHERE mailingid='".$_GET['id']."'";  
$result_e=mysql_query($sql_e);
if ($result_e==false) echo 'SQL_e-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_e.'</font><p>';
}
}
}
} 

if($countsentmails>0) {
trxlog("testOK mj ".$_GET['id']);
$sql_a="UPDATE mailing SET mailtested=1 WHERE id='".$row_b['id']."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}
else trxlog("testERR mj ".$_GET['id']);
?>
<?php if($cron!='cron') { 
if($_GET['hot']!='hot') trxlog("test mj ".$_GET['id']);
else trxlog("directsend mj ".$_GET['id']);
?>
<script>
document.getElementById('wait').innerHTML='<button onclick="location.replace(\'mailinglist.php?t=<?php echo $_GET['t']?>\')" >ok <?php if ($_GET['hot']!='hot') echo ', TESTED, NOTHING SENT' ?></button>';
</script>
<?php } 


if($cron=='cron') {
$nextmail=$nextmail+1;

$sql_e="UPDATE mailingcron SET done=1 WHERE done=0 AND nextmail>=numa";  
$result_e=mysql_query($sql_e);
if ($result_e==false) echo 'SQL_e-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_e.'</font><p>';
}
?>

</body>
</html>
