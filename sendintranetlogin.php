<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>saving...</title>
<?php if(file_exists('r/csspworder.css')) echo'<link rel="stylesheet" href="r/csspworder.css" type="text/css" />';?>
</head>
<body leftmargin="0" id="body">
<div id="spinner">
<img src="upload/spinner.gif" />
</div>
<div id="form" style="display:none">
<?php
require_once('connect.inc.php');
require_once('u5admin/u5idn.inc.php');
if($orderingintranetpasswordsisforbidden=='yes')die("MESSAGE TO THE ADMINISTRATOR: You have to set $orderingintranetpasswordsisforbidden=='no' in the file config.php of your u5CMS installation to allow that.");
if(function_exists('INTRANETORDERPWexec'))INTRANETORDERPWexec();

if(!isset($waitsecondsbetweenintranetpworders))$waitsecondsbetweenintranetpworders=(60*10);
if(time()-file_get_contents('fileversions/lastintrapworder.txt')<$waitsecondsbetweenintranetpworders){
require_once('globalslogin.inc.php');
echo('<b>'.def($wait_d,$wait_e,$wait_f).' '.(file_get_contents('fileversions/lastintrapworder.txt')-time()+$waitsecondsbetweenintranetpworders).'&#8243;</b><script>document.getElementById("spinner").style.display="none";document.getElementById("form").style.display="block";setTimeout("location.href=location.href",1000)</script>');
exit;	
}

$sql_a="UPDATE intranetsalt SET salt=".rand(1000000,9999999)." WHERE salt<1000";
$result_a=mysql_query($sql_a);

if ($result_a==false) echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$sql_a="SELECT * FROM intranetsalt";
$result_a=mysql_query($sql_a);

if ($result_a==false) echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$row_a = mysql_fetch_array($result_a);
$salt=$row_a['salt'];


$_POST['email']=trim($_POST['email']);

if (strpos($_POST['email'],'@')>0 && strpos($_POST['email'],'.')>0) {
	
if(!isset($waitsecondsbetweenintranetpworders))$waitsecondsbetweenintranetpworders=(60*10);
if(time()-file_get_contents('fileversions/lastintrapworder.txt')<$waitsecondsbetweenintranetpworders){
echo('<script>location.href=location.href</script>');
exit;	
}
file_put_contents('fileversions/lastintrapworder.txt',time());

echo '<span style="font-size:130%;font-family:Arial, Helvetica, sans-serif;color:green">OK &rarr; E-Mail Inbox<br><small>'.str_replace('&amp;','&',htmlXspecialchars($_POST['email'])).'</small></span><iframe src="autointranet.php" frameborder="0" width="0" height="0"></iframe><script>if(parent.donescript)parent.donescript();document.getElementById(\'form\').style.display=\'block\';document.getElementById(\'spinner\').style.display=\'none\';</script>';

$sql_a="SELECT email FROM accounts WHERE email='".mysql_real_escape_string($_POST['email'])."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$isadmin = mysql_num_rows($result_a);


$sql_a="SELECT * FROM intranetmembers";
$result_a=mysql_query($sql_a);

if ($result_a==false) echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$row_a = mysql_fetch_array($result_a);

$members=','.$row_a['members'].',';
if (str_replace(','.mb_strtolower($_POST['email']).',','',mb_strtolower($members))!=$members || $isadmin>0) {
$zendfrom=$mymail;
$zendname=$mymail;
$zendto=$_POST['email'];
$zendsubject='Intranet Login '.str_replace(basename($scripturi),'',$scripturi);
$p=floor(crc32(u5flatidnlower($_POST['email']).$salt));

$sql_a="SELECT email FROM accounts WHERE email='".mysql_real_escape_string(u5flatidn($_POST['email']))."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$num_a = mysql_num_rows($result_a);

if ($num_a>0) $p='The system does not send an intranet password to you because you already have a password for the u5CMS backend. Please use your u5CMS backend password also for the intranet. If you have forgotten it, recover it on '.str_replace(basename($scripturi),'',$scripturi).'reset.php';

$zendmessage=$zendsubject."\n\n".strip_tags(str_replace("<p>","\n\n",str_replace("<br />","\n",rawurldecode($_SERVER['QUERY_STRING'])))).'

Username:
'.$_POST['email'].'

Password:
'.$p.'

';
if($doublepasswordmailing!='no')mail($zendto,$zendsubject,$zendmessage);
include('zendmail.php');
}

else {
$zendfrom=$mymail;
$zendname=$mymail;
$zendto=$_POST['email'];
$zendsubject='ERROR: Intranet Login '.str_replace(basename($scripturi),'',$scripturi);
$zendmessage=$zendsubject."\n\n".'Unfortunately your e-mail address '.$_POST['email'].' is not registered as a member. Please try again with exactly that e-mail address you registered with in our institution.';
if($dontmailyouarenotintranetmemberalerttocustomers!='yes')if($doublepasswordmailing!='no')mail($zendto,$zendsubject,$zendmessage);
if($dontmailyouarenotintranetmemberalerttocustomers!='yes')include('zendmail.php');

$zendfrom=$mymail;
$zendname=$mymail;
$zendto=$mymail;
$zendsubject='ERROR: Intranet Login '.str_replace(basename($scripturi),'',$scripturi);
$zendmessage=$zendsubject."\n\n".'Unfortunately your e-mail address '.$_POST['email'].' is not registered as a member. Please try again with exactly that e-mail address you registered with in our institution.';
if($dontmailyouarenotintranetmemberalerttoadmin!='yes')if($doublepasswordmailing!='no')mail($zendto,$zendsubject,$zendmessage);
if($dontmailyouarenotintranetmemberalerttoadmin!='yes')include('zendmail.php');
}
}

else {
echo '<form name="form" method="post"><input type="text" name="email" style="width:99%" id="email" /><input type="submit" value="OK" /></form><script>if(parent==top)document.getElementById(\'email\').focus();</script>';
}
?>
<script>
if(document.getElementById('email')&&parent.location.href.indexOf('email=')>0) {
document.getElementById('email').value=unescape(parent.location.href.split('email=')[1].split('&')[0]);	
document.form.submit();
}
else {
document.getElementById('spinner').style.display='none';
document.getElementById('form').style.display='block';
}
</script>
</div>
</body>
</html>