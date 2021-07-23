<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Copy &amp; Paste Characters</title>
<?php require('backendcss.php'); ?></head>
<body onmousedown="md=1" onmouseup="md=0" style="font-size:25px">
<iframe width="1" height="1" frameborder="0" name="mychar" id="mychar"></iframe>
<h5>You need a special character? Copy and paste it from here (Set: <a href="chr.php">base</a>
<?php
for ($i=0;$i<14;$i++) {
$here='';
if ($i==$_GET['s']) $here="style=color:red;text-decoration:none";	
echo " <a id=set$i $here href=chrall.php?s=$i>".($i+1)."</a>";
}
?> 
)</h5>
<p>

<?php 

for ($i=0+$_GET['s']*5000;$i<5000+$_GET['s']*5000;$i++) {
?>
<a title="<?php echo $i.' ('.dechex($i).')' ?>" onmousedown="mychar.location.href='mychar.php?c='+escape(this.innerHTML)" onmouseover="if(md==1) mychar.location.href=('mychar.php?c='+escape(this.innerHTML))"><?php echo "&#$i;" ?></a>
<?php
}

?>
</p>
<h5>You need a special character? Copy and paste it from here (Set: <a href="chr.php">base</a>
<?php
for ($i=0;$i<14;$i++) {
$here='';
if ($i==$_GET['s']) $here="style=color:red;text-decoration:none";	
echo " <a id=set$i $here href=chrall.php?s=$i>".($i+1)."</a>";
}
?> 
)</h5>

</body>
</html>
