<?php require('connect.inc.php'); 

if(strpos($_GET['n'],'inserted ')===0) {
$f=explode('inserted ',$_GET['n']);
$echo='<a title="focus in repository" href="javascript:void(0)" style="text-decoration:none;background:white" onmouseover="this.click();this.style.background=\'gold\'" onmouseout="this.style.background=\'white\'" onclick="parent.parent.i3.location.href=\'focus.php?c='.basename(trim($f[1])).'\'">F</a>&nbsp;';
$f9focusBis=basename(trim($f[1]));
}

if(strpos($_GET['n'],'saved ')===0) {
$f=explode('saved ',$_GET['n']);
$echo='<a title="focus in repository" href="javascript:void(0)" style="text-decoration:none;background:white" onmouseover="this.click();this.style.background=\'gold\'" onmouseout="this.style.background=\'white\'" onclick="parent.parent.i3.location.href=\'focus.php?c='.basename(trim($f[1])).'\'">F</a>&nbsp;';
$f9focusBis=basename(trim($f[1]));
}

if(strpos($_GET['n'],'updated ')===0) {
$f=explode('updated ',$_GET['n']);
$echo='<a title="focus in repository" href="javascript:void(0)" style="text-decoration:none;background:white" onmouseover="this.click();this.style.background=\'gold\'" onmouseout="this.style.background=\'white\'" onclick="parent.parent.i3.location.href=\'focus.php?c='.basename(trim($f[1])).'\'">F</a>&nbsp;';
$f9focusBis=basename(trim($f[1]));
}

if(strpos($_GET['n'],'renamed ')===0) {
$f=explode(' ',$_GET['n']);
$echo='<a title="focus in repository" href="javascript:void(0)" style="text-decoration:none;background:white" onmouseover="this.click();this.style.background=\'gold\'" onmouseout="this.style.background=\'white\'" onclick="parent.parent.i3.location.href=\'focus.php?c='.basename(trim($f[3])).'\'">F</a>&nbsp;';
$f9focusBis=basename(trim($f[3]));
}

if(strpos($_GET['n'],'uploaded ')===0) {
$f=explode('uploaded ',$_GET['n']);
$f=explode('_',$f[1]);
$echo='<a title="focus in repository" href="javascript:void(0)" style="text-decoration:none;background:white" onmouseover="this.click();this.style.background=\'gold\'" onmouseout="this.style.background=\'white\'" onclick="parent.parent.i3.location.href=\'focus.php?c='.basename(trim($f[0])).'\'">F</a>&nbsp;';
$f9focusBis=basename(trim($f[0]));
}

if(strpos($_GET['n'],'archived ')===0) {
$f=explode('archived ',$_GET['n']);
$echo='<a title="focus in repository" href="javascript:void(0)" style="text-decoration:none;background:white" onmouseover="this.click();this.style.background=\'gold\'" onmouseout="this.style.background=\'white\'" onclick="parent.parent.i3.location.href=\'focus.php?c='.basename(trim($f[1])).'\'">F</a>&nbsp;';
$f9focusBis=basename(trim($f[1]));
}

if(strpos($_GET['n'],'unarchived ')===0) {
$f=explode('unarchived ',$_GET['n']);
$echo='<a title="focus in repository" href="javascript:void(0)" style="text-decoration:none;background:white" onmouseover="this.click();this.style.background=\'gold\'" onmouseout="this.style.background=\'white\'" onclick="parent.parent.i3.location.href=\'focus.php?c='.basename(trim($f[1])).'\'">F</a>&nbsp;';
$f9focusBis=basename(trim($f[1]));
}

if(strpos($_GET['n'],'deleted ')===0) {
$f=explode('deleted ',$_GET['n']);
$echo='<a title="focus in repository" href="javascript:void(0)" style="text-decoration:none;background:white" onmouseover="this.click();this.style.background=\'gold\'" onmouseout="this.style.background=\'white\'" onclick="parent.parent.i3.location.href=\'focus.php?c='.basename(trim($f[1])).'\'">F</a>&nbsp;';
$f9focusBis=basename(trim($f[1]));
}

if(strpos($_GET['n'],'copied ')===0) {
$f=explode(' ',$_GET['n']);
$echo='
<a title="focus in repository" href="javascript:void(0)" style="text-decoration:none;background:white" onmouseover="this.click();this.style.background=\'gold\'" onmouseout="this.style.background=\'white\'" onclick="parent.parent.i3.location.href=\'focus.php?c='.basename(trim($f[1])).'\'">F</a>&nbsp;
<a title="focus in repository" href="javascript:void(0)" style="text-decoration:none;background:white" onmouseover="this.click();this.style.background=\'gold\'" onmouseout="this.style.background=\'white\'" onclick="parent.parent.i3.location.href=\'focus.php?c='.basename(trim($f[3])).'\'">F</a>&nbsp;';
$f9focusBis=basename(trim($f[3]));
}

setcookie('f9focusBis', $f9focusBis, time() + 3600 * 24 * 365 * 10, '/');
?><!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>
<body bgcolor="#339900" text="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<div style="margin:-2px 0 0 2px;white-space: nowrap;">
<?php echo $echo ?>
<span  onmouseover="this.title=this.innerHTML" onclick="alert(this.innerHTML)">
<?php echo $_GET['n']?>
</span>
</div>
<script>
parent.i3.location.reload();
parent.i1.pframe.pviewit();
parent.i2.pframe.pviewit();
</script>
<div style="width:2px;height:19px;background:white;position:absolute;display:none;top:0px" id="htaccess"></div>
<iframe width="1" height="1" frameborder="0" src="htaccess0.php"></iframe>
<?php require('countblockrefresh.inc.php'); ?>
</body>
</html>