<?php 
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);
$_GET['i']=htmlspecialchars(strip_tags($_GET['i']));
require('../config.php');
if(function_exists('PUPLOADexec'))PUPLOADexec();
if($_GET['k']!=sha1(date('Ymd').$password.$sessioncookiehashsalt)&&$_GET['k']!=sha1(date('Ymd',time()-24*60*60).$password.$sessioncookiehashsalt))die('ERROR: Authorization failed.');

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

<body>
<?php

echo '<span style="color:white;background:green" class="cssupload">OK:&nbsp;' . $_GET['e'] . '&nbsp;&#10004;</span>&nbsp;<span title="DELETE FILE" style="cursor:pointer;color:white;background:black" onclick="cf=confirm(\'DELETE FILE?\');if(cf) {deletefile()}">&#215;</span>&nbsp;<script>parent.document.getElementById("userupload' . $_GET['i'] . '").style.display="none";</script>';

?>
<script>
    function deletefile() {
        location.href = 'upload.php?i=<?php echo $_GET['i']?>&k=<?php echo $_GET['k']?>';
        parent.document.getElementById("userupload<?php echo $_GET['i']?>").value = '';
        parent.document.getElementById("userupload<?php echo $_GET['i']?>").style.display = 'block';
        parent.document.getElementById("userupload<?php echo $_GET['i']?>").style.background = 'gray';
		if(typeof parent.u5uploaddetected === "function")parent.u5uploaddetected(2);
    }
</script>
</body>

</html>
