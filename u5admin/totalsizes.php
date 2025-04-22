<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); require_once('../san.inc.php'); ?></head>
<body>
<?php
$totalsizes=0;
if (file_exists('../r/'.$_GET['name'])) {
     $path='../r/'.$_GET['name'];
     if ($handle = @opendir($path))  {
     while (false !== ($file = readdir($handle)))  {

    if (str_replace('.','',$file)!='') {
	$i++;
$thefilesize=filesize($path.'/'.$file);
$totalsizes+=$thefilesize;
}
}}}
echo '<script>totalsizes1='.$_GET['t'].';totalsizes=0;totalsizes='.$totalsizes.'</script>';
?>

<script>
if (totalsizes!=totalsizes1) {
setTimeout("parent.parent.location.href=parent.parent.location.href",1111);
}
</script>

</body>
</html>
