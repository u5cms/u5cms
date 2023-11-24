<?php require_once('connect.inc.php');require_once('t2.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>saving...</title>
<?php require('backendcss.php'); ?></head>
<script>err=0;</script><body onload="if(err==0)self.close()">
<?php
if(!isset($u5cmsbackendpasswordminimumlength))$u5cmsbackendpasswordminimumlength=6;
$_POST['old']=trim($_POST['old']);
$_POST['new1']=trim($_POST['new1']);
$_POST['new2']=trim($_POST['new2']);

if(strlen($_POST['new1'])<$u5cmsbackendpasswordminimumlength) die("<script>alert('The new password is too short. Minimum length is ".ehtml($u5cmsbackendpasswordminimumlength)." characters.');err=1;history.go(-1);</script>");
if ($_POST['new1']!=$_POST['new2']) die("<script>alert('You entered the new password twice but they do not match.');err=1;history.go(-1);</script>");

$sql_a="SELECT * FROM accounts where email='".mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."' AND pw='".mysql_real_escape_string(pwdhsh($_POST['old']))."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$num_a = mysql_num_rows($result_a);

if ($num_a<1) die("<script>alert('The current password you indicated is wrong.');err=1;history.go(-1);</script>");
else if ($_POST['new1']!=$_POST['new2']) die("<script>alert('You entered the new password twice but they do not match.');err=1;history.go(-1);</script>");
else if (trim($_POST['new1'])=='') die("<script>alert('Your new password must not be empty.');err=1;history.go(-1);</script>");
else if (strpos('x'.$_POST['new1'],':')>0) die("<script>alert('The colon (:) is a forbidden character in passwords.');err=1;history.go(-1);</script>");
else {

$sql_a="UPDATE accounts SET pw='".mysql_real_escape_string(pwdhsh($_POST['new1']))."' WHERE email='".mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>');
}
trxlog('chgpwd');
?>
<img src="../upload/spinner.gif" />
<iframe width="1" height="1" frameborder="0" src="htaccess.php"></iframe>
<script>
if(opener)if(opener.parent)if(opener.parent.save)opener.parent.save.location.href='done.php?n=password changed';
if(opener)opener.top.location.href=opener.top.location.href;
setTimeout("self.close()",77777);
</script>
<?php } ?>
</body>
</html>