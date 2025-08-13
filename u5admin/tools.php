<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>tools</title>
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" style="font-size:120%;background:#eee">
<script>
<?php 
if(!isset($_GET['s'])) {
	if (intval($_GET['w'])<100) $_GET['w']=640;
	if (intval($_GET['h'])<100) $_GET['h']=640;
	echo 'window.resizeTo('.floor(intval($_GET['w'])*0.42).','.intval($_GET['h']).');';
    echo 'window.moveTo(0,0);';
}
?>
location.href='https://yuba.ch/tools';
</script>
</body>
</html>
