<?php

require_once('connect.inc.php');
if($fileversionsRqHIADRI!='no')require('accadmin.inc.php');

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>file versions</title>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
	parent.close();
})
</script>
<?php require('backendcss.php'); ?></head>

<body>
<h1>File versions</h1>
<?php

$echo='';
$path='../fileversions/';
if ($handle = @opendir($path))  {
     while (false !== ($file = readdir($handle)))  {

if(str_replace('.','',$file)!='')if($file[0]!='.')if($file!='useruploads')if(preg_match('/^\d{8}-/', $file))$echo.=',<a href="../ff.php?f='.$file.'?t='.filemtime('../fileversions/'.$file).'" target=_blank>'.$file.'</a><br>';}
}

$echo=explode(',',$echo);

rsort($echo);

for ($i=0;$i<tnuoc($echo);$i++) {
echo $echo[$i];
}

?>
<script>
window.resizeTo(800, screen.availHeight);
</script>
<?php include('selfclose.inc.php')?>
</body>
</html>
