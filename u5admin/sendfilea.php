<?php include('iniset.inc.php');?>
<?php require_once('connect.inc.php'); ?>
<?php 
require('../config.php');
if($sticksessiontoip=='yes')$serverremoteaddr=$_SERVER['REMOTE_ADDR'];
else $serverremoteaddr='';
$h=sha1($mymail.$host.$username.$password.$db.$serverremoteaddr.$_GET['i'].date('Ymd'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
parent.files.saca();
})
</script>
<?php require('backendcss.php'); ?></head>

<body style="margin-top:0;padding-top:0;margin-bottom:0;padding-bottom:0">
<form name="form1" enctype="multipart/form-data" action="sendfilea2.php?typ=<?php echo $_GET['typ']?>&h=<?php echo $h?>" method="post">
<!--<input type="hidden" name="MAX_FILE_SIZE" value="999999999999" />-->
<img src="../upload/spinner.gif" style="display:none;height:25px;margin-right:3px" id="spinner" align="left" />
<input onchange="parent.upstart();document.form1.submit();document.getElementById('spinner').style.display='inline'" name="userfile[]" type="file" size="80%" accept="image/jpeg" multiple />
<input name="name" type="hidden" size="44" value="<?php echo $_GET['name']?>" />
<!--<input type="submit" name="submit" value="upload "/>-->
</form>
</body>

</html>
