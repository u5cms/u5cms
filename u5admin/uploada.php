<?php include('iniset.inc.php');?>
<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>upload <?php echo $_GET['name']?></title>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
files.sacapre();
})
</script>
<?php require('backendcss.php'); ?></head>
<body onresize="resizer();" style="overflow-y:hidden"><?php require('uploading.inc.php'); ?>
<h1 id="h1" style="margin-bottom:5px">Manage album <i><?php echo $_GET['name']?></i>
<?php
if ($_GET['typ']=='a') echo '<span style="font-size:40%">In albums only jpg images are allowed!</span>';
?>
<br>
<span style="font-size:40%"><?php include('iniget.inc.php')?>. Multifile upload: Max. <?php echo (ini_get('max_file_uploads')-1) ?> files with a total of <?php echo floor($post_max_size/1024/1024).'MB' ?> at once. </span></h1>
<?php
if ($_GET['typ']=='a') echo '<script>document.getElementById("h1").style.marginTop="-11px"</script>';
echo '<a title="image size and compression infos" style="float:right;text-decoration:none;font-size:70%;color:white;background:orange;" href="javascript:alert(\'';
include('jpginstructions.php');
echo '\')">&nbsp;JPG size infos&nbsp;</a>';
?>
<p>
<iframe src="sendfilea.php?name=<?php echo $_GET['name']?>&typ=<?php echo $_GET['typ']?>" frameborder="0" width="100%" height="33" scrolling="no"></iframe>
<iframe name="albumsort" frameborder="0" width="1" height="1" src="blank.php" style="visibility:hidden"></iframe>
<small><span id="i"></span> <small>(reverse sort=the image at the bottom on this list is the 1st one displayed in the album)</small>:</small>
<iframe src="filesa.php?name=<?php echo $_GET['name']?>" name="files" frameborder="0" width="100%" height="1000" id="filesaifr"></iframe>
<br>
<script>
function sizer() {
document.getElementById('filesaifr').style.height=document.documentElement.clientHeight-150+'px';
}
function resizer() {
res=30;
if (document.getElementById('filesaifr').style.height!=document.documentElement.clientHeight-150) sizer();
}
setTimeout("resizer()",1);
window.resizeTo(800, screen.availHeight);
</script>
<?php include('focuslinktitle.inc.php') ?>
</body>
</html>