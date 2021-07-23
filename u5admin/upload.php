<?php 
include('iniset.inc.php');
require_once('connect.inc.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>upload <?php echo $_GET['name']?></title>
<script>if('<?php echo $_GET['name']?>'=='::LOGINPAGE::'||'<?php echo $_GET['name']?>'=='LOGINPAGE::')location.href='defineloginglobals.php';</script>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
	self.close();
})
</script>
<?php require('backendcss.php'); ?></head>
<body><?php require('uploading.inc.php'); ?>
<h1 style="margin-bottom:5px">Upload type <span style="color:red"><?php echo $_GET['typ']?></span> as <i><?php echo $_GET['name']?></i><br><span style="font-size:40%;"><?php include('iniget.inc.php')?></span></h1>
<?php
if ($_GET['typ']=='i') echo 'For type <span style="color:red">i</span> only jpg (!) images are allowed.<!--<br><small>Video player pre-start images: same name as video with a preceding letter v</small>-->';
if ($_GET['typ']=='d') echo 'Type <span style="color:red">d</span> is for documents (standard is pdf). All other file types (e. g. zip) are also allowed.';
if ($_GET['typ']=='v') {
echo '<a title="video and audio format infos" style="float:right;text-decoration:none;font-size:70%;color:white;background:orange;" href="javascript:alert(\'';
include('html5videoinstructions.php');
echo '\')">&nbsp;audio &amp; video infos&nbsp;</a>';
}
if ($_GET['typ']=='i') {
echo '<a title="image size and compression infos" style="float:right;text-decoration:none;font-size:70%;color:white;background:orange;" href="javascript:alert(\'';
include('jpginstructions.php');
echo '\')">&nbsp;JPG size infos&nbsp;</a>';
}
if ($_GET['typ']=='f') {
echo '<a title="image size and file types infos" style="float:right;text-decoration:none;font-size:70%;color:white;background:orange;" href="javascript:alert(\'';
include('freeimginstructions.php');
echo '\')">&nbsp;image size infos&nbsp;</a>';
}
if ($_GET['typ']=='d') {
echo '<a title="general infos" style="float:right;text-decoration:none;font-size:70%;color:white;background:orange;" href="javascript:alert(\'';
include('docinstructions.php');
echo '\')">infos&nbsp;</a>';
}
?>
<p> 
 
Files already uploaded here: 
<iframe src="files.php?name=<?php echo $_GET['name']?>&typ=<?php echo $_GET['typ']?>" name="files" frameborder="0" width="100%" height="110"></iframe>
<p> 
 
Upload the <i><?php echo "$lan1na"?></i> version or the only and one main version of the file here:
<iframe src="sendfile.php?name=<?php echo $_GET['name']?>&l=_<?php echo $lan1na ?>&typ=<?php echo $_GET['typ']?>" frameborder="0" width="100%" height="88"></iframe>

<div id="lan2name"><hr>

If in the <i><?php echo $lan2na?></i> version of the website a <i><?php echo $lan2na?></i> version of this file should appear, upload it here: 
<iframe src="sendfile.php?name=<?php echo $_GET['name']?>&l=_<?php echo $lan2na ?>&typ=<?php echo $_GET['typ']?>" frameborder="0" width="100%" height="88"></iframe>
</div>
<div id="lan3name"><hr>

If in the <i><?php echo $lan3na?></i> version of the website a <i><?php echo $lan3na?></i> version of this file should appear, upload it here: 
<iframe src="sendfile.php?name=<?php echo $_GET['name']?>&l=_<?php echo $lan3na ?>&typ=<?php echo $_GET['typ']?>" frameborder="0" width="100%" height="88"></iframe>
</div>
<script src="emptylanhide.js.php"></script>
<?php include('focuslinktitle.inc.php') ?>
</body>
</html>