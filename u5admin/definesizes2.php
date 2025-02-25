<?php require_once('connect.inc.php');require_once('t2.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>saving...</title>
<?php require('backendcss.php'); ?></head>
<body id="body" bgcolor="#009900">
<?php 
if($definesizesRqHIADRI!='no')require_once('accadmin.inc.php'); 

if($_POST['audioplayer_h']<1 || $_POST['audioplayer_w']<1 || $_POST['videoplayer_h']<1 || $_POST['videoplayer_w']<1 || $_POST['youtube_h']<1 || $_POST['youtube_w']<1 || $_POST['smallimgL_w']<1 || $_POST['smallimgL_w']<1 || $_POST['smallimgC_w']<1 || $_POST['smallimgC_w']<1 || $_POST['smallimgR_w']<1 || $_POST['smallimgR_w']<1 || $_POST['largeimg_w']<1 || $_POST['largeimg_w']<1 || $_POST['zoomedimg_h']<1 || $_POST['zoomedimg_w']<1 || $_POST['scrollingalbum_h']<1 || $_POST['scrollingalbum_w']<1) die('<script>alert("ERROR: At least one value is lower than 1 or missing! Nothing saved.");</script>');

$sql_a="UPDATE sizes SET 

audioplayer_h='".prpstring($_POST['audioplayer_h'])."',
audioplayer_w='".prpstring($_POST['audioplayer_w'])."',

videoplayer_h='".prpstring($_POST['videoplayer_h'])."',
videoplayer_w='".prpstring($_POST['videoplayer_w'])."',

youtube_h='".prpstring($_POST['youtube_h'])."',
youtube_w='".prpstring($_POST['youtube_w'])."',

smallimgL_h='".prpstring($_POST['smallimgL_h'])."',
smallimgL_w='".prpstring($_POST['smallimgL_w'])."',

smallimgC_h='".prpstring($_POST['smallimgC_h'])."',
smallimgC_w='".prpstring($_POST['smallimgC_w'])."',

smallimgR_h='".prpstring($_POST['smallimgR_h'])."',
smallimgR_w='".prpstring($_POST['smallimgR_w'])."',

largeimg_h='".prpstring($_POST['largeimg_h'])."',
largeimg_w='".prpstring($_POST['largeimg_w'])."',

zoomedimg_h='".prpstring($_POST['zoomedimg_h'])."',
zoomedimg_w='".prpstring($_POST['zoomedimg_w'])."',

scrollingalbum_h='".prpstring($_POST['scrollingalbum_h'])."',
scrollingalbum_w='".prpstring($_POST['scrollingalbum_w'])."',

tosquare='".prpstring($_POST['tosquare'])."',
cropedge='".prpstring($_POST['cropedge'])."'
";

$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!...!<p>');
}

trxlog('def sizes');

function prpstring($s) {
if($s=='')$s=0;
return gnirts_epacse_laer_lqsym($s);	
}
?>
<script>
if (parent && parent.opener && parent.opener.parent && parent.opener.parent.save) parent.opener.parent.save.location.href='done.php?n=defined sizes';
parent.document.getElementById('body').style.background='green';
setTimeout("parent.document.getElementById('body').style.background='white'",777);
parent.cchangesreset();
</script>

</body>
</html>
