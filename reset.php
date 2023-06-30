<?php require('connect.inc.php');
if(isset($u5samlsalt)&&$u5samlsalt!='')die('You caonnot reset your password here because this u5CMS is SAML-integrated to an IAM-system. In other words, your credantials are managed in that IAM-system.');
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>u5CMS password reset for backend users</title>
<?php require('u5admin/backendcss.php') ?>
</head>
<body>
<?php
if(!isset($waitsecondsbetweenpasswordresets))$waitsecondsbetweenpasswordresets=(60*10);
if(time()-file_get_contents('fileversions/lastreset.txt')<$waitsecondsbetweenpasswordresets){
require_once('globalslogin.inc.php');
echo('<h1>'.def($wait_1,$wait_2,$wait_3).' '.(file_get_contents('fileversions/lastreset.txt')-time()+$waitsecondsbetweenpasswordresets).'&#8243;</h1><script>setTimeout("location.href=location.href",1000)</script>');
exit;	
}
?>
<h1>Reset my u5CMS backend user password</h1>
<h2>Step 1</h2>
<form name="form1" method="post" action="reset2.php">
u5CMS backend username (must be an e-mail address):
<br /><br />
<input name="e" type="text" required size="40" onkeyup="if(this.value!=this.value.replace(/\s/g,''))this.value=this.value.replace(/\s/g,'')" onchange="this.value=this.value.replace(/\s/g,'')" />
<input type="submit" name="button" id="button" value="send" />
</form>
<script>document.form1.e.focus();</script>
</body>
</html>
