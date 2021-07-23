<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>manage backend users</title>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
	
})
</script>
<?php require('backendcss.php'); ?></head>
<body id="body">
<h1>Manage backend users</h1><?php include('checkidn.inc.php');?>
<?php  
if ($viewbackenduserlistRqHIADRI!='no') require_once('accadmin.inc.php'); 
?>
<iframe name="main" src="acc0.php" width="100%" height="1000" frameborder="0"></iframe>
<iframe name="saver" src="blank.php" frameborder="0" width="1" height="1"></iframe>

<script>
window.resizeTo(800, screen.availHeight);
</script>
<?php include('selfclose.inc.php')?>
</body>
</html>
