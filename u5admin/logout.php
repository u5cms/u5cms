<?php 
ignore_user_abort(true); 
require_once('connect.inc.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
<link rel="stylesheet" href="../r/csshot.css" type="text/css" title="base"/>
<title>bye</title>
<?php require 'backendcss.php' ?>
<style>
.bye {
        color:#111;
		font-size:30vw;
		width: 50vw;
        height: 50vw;

        position:absolute; /*it can be fixed too*/
        left:0; right:0;
        top:0; bottom:0;
        margin:auto;

        /*this to solve "the content will not be cut when the window is smaller than the content": */
        max-width:100%;
        max-height:100%;
        overflow:visible;
    }
</style>
</head>
<body style="background:black" onload="setTimeout('location.href=\'../logout.php\'',2222);">
<iframe name="i4e" frameborder="0" src="htaccessandindexer.php" width="1" height="1" style="display:none"></iframe>
<iframe style="display:none" src="cleanlog.php"></iframe>
<script>
setTimeout("location.href='../logout.php'",11111);
</script>
<div class="bye">bye.<span id="cleanlog" style="display:none">.</span><span id="cleanbackups" style="display:none">.</span></div>
</body>
</html>