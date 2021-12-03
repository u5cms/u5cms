<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);
$_GET['i']=htmlspecialchars(strip_tags($_GET['i']));
require_once '../myfunctions.inc.php';
require_once '../config.php';
if(function_exists('UPLOADexec'))UPLOADexec();
if($_GET['k']!=sha1(date('Ymd').$password.$sessioncookiehashsalt)&&$_GET['k']!=sha1(date('Ymd',time()-24*60*60).$password.$sessioncookiehashsalt))die('ERROR: Authorization failed.');

if ($allowuseruploads != 'yes') die('document.write("ERROR: Upload not available. REASON: $allowuseruploads is not set to yes in config.php");');
if($sticksessiontoip=='yes')$serverremoteaddr=$_SERVER['REMOTE_ADDR'];
else $serverremoteaddr='';
$h=sha1($mymail.$host.$username.$password.$db.$serverremoteaddr.$_GET['i'].date('Ymd'));
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <?php if (file_exists('../r/cssupload.css')) echo '<link rel="stylesheet" href="r/cssupload.css?' . filemtime('../r/cssupload.css') . '" type="text/css" title="base"/>' ?>
    <style type="text/css">
        body {
            margin-left: 0px;
            margin-top: 0px;
            margin-right: 0px;
            margin-bottom: 0px;
        }
    </style>
</head>

<body style="font-family:Arial, Helvetica, sans-serif">
<form name="form1" enctype="multipart/form-data" action="uploaddone.php?i=<?php echo $_GET['i'] ?>&h=<?php echo $h ?>&k=<?php echo htmlspecialchars($_GET['k']) ?>"
      method="post">
    <!--<input type="hidden" name="MAX_FILE_SIZE" value="999999999999" />-->
    <img height="16" valign="middle" src="spinner.gif" id="spinnergif" style="display:none"/><input
        onchange="document.getElementById('spinnergif').style.display='block';document.form1.submit()" name="userfile"
        type="file" size="50%"/>
    <script>if(parent)if(parent.window)parent.window.focus();</script>
</form>
</body>

</html>
