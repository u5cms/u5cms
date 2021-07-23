<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>change your password</title>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
	document.form1.submit();
})
</script>
<?php require('backendcss.php'); ?></head>
<body>
<h1>Change your password</h1>
<form name="form1" action="pwd2.php" method="post">
  <p>
<?php
if(!isset($u5cmsbackendpasswordminimumlength))$u5cmsbackendpasswordminimumlength=6;
?>  
  Your current password
  <br />
    <input name="old" type="password" />
  </p>
  <p>Your new password  (min. <?php echo $u5cmsbackendpasswordminimumlength; ?>)  <br />
    <input name="new1" type="password" minlength="<?php echo $u5cmsbackendpasswordminimumlength; ?>" onchange="if(this.value!=this.value.replace(/\s/g,''))this.value!=this.value.replace(/\s/g,'')" onkeyup="this.onchange()" />
  </p>
  <p>Repeat your new password<br />
    <input name="new2" type="password" minlength="<?php echo $u5cmsbackendpasswordminimumlength; ?>" onchange="if(this.value!=this.value.replace(/\s/g,''))this.value!=this.value.replace(/\s/g,'')" onkeyup="this.onchange()" />
      </p>
  <p>
    <input type="submit" name="button" value="change password" />
  </p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
</form>
<script>
document.form1.old.focus();
</script>
<?php include('selfclose.inc.php')?>
</body>
</html>
