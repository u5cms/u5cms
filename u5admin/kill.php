<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>delete</title>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
	cchanges=0;document.form1.submit();
})
</script>
<?php require('backendcss.php'); ?></head>

<body>
<form onsubmit="cchanges=0;document.querySelectorAll('.asterisk').forEach(e=>e.classList.add('blink_me'));" id="form1" name="form1" method="post" action="kill2.php">
  <h1>Kill item </h1>
  <p>If you have deleted an item it is recoverable in the resources table (incl. uploaded files if you said explicitly no to their deletion) by making a new item of the same or similar type with the name of the formerly deleted one. Use the kill function to physically remove it from the resources table (it must have been deleted before, files explicitly not deleted remain surfable in the r-directory). Recoverability from history remains.</p>
  <p>Enter name:</p>
  <p>
    <input name="name" type="text" onchange="if (this.value!=validated(this.value)) this.value=validated(this.value);" onkeyup="if (this.value!=validated(this.value)) this.value=validated(this.value);" size="20" maxlength="20" />
    <br><br><br><br><br><br><br><br>
    </p>
<input type="submit" value="kill"><span class="asterisk" style="display:none;color:red">*</span>
  <?php include('metachg.inc.php') ?><script>initchanges()</script>
<?php require('t1.php') ?></form>
<script>
<?php include('az90.php'); ?>
document.form1.name.focus();
</script>
<?php include('selfclose.inc.php')?>
</body>
</html>
