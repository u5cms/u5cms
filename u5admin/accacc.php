<?php require_once('connect.inc.php');  require_once('h1.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>saving...</title>
<?php require('backendcss.php'); ?></head>
<body><div style="display:none" id="e"><?php echo str_replace('<','',str_replace('>','',u5flatidn($_GET['y'])))?></div>
<?php 
if ($_GET['a']=='0') {
?>
<script>
if (confirm('Do you really want '+document.getElementById('e').innerHTML+' to have NO Higher Admin Rights?')) {
parent.main.document.getElementById('iL<?php echo $_GET['id']?>').checked=true;
location.href='accacc2.php?id=<?php echo $_GET['id']?>&a=<?php echo $_GET['a']?>&y=<?php echo $_GET['y']?>&h=<?php echo $u5cmsscrttkngt ?>';
}
else parent.main.document.getElementById('iR<?php echo $_GET['id']?>').checked=true;
</script>
<?php 
}


if ($_GET['a']=='1') {
?>
<script>
cf1=confirm('Do you really want '+document.getElementById('e').innerHTML+' to HAVE Higher Admin Rights?')
if (cf1) {
parent.main.document.getElementById('iR<?php echo $_GET['id']?>').checked=true;
location.href='accacc2.php?id=<?php echo $_GET['id']?>&a=<?php echo $_GET['a']?>&h=<?php echo $u5cmsscrttkngt ?>';
}
else parent.main.document.getElementById('iL<?php echo $_GET['id']?>').checked=true;

</script>
<?php 
}


if ($_GET['a']=='2') {
?>
<script>
cf2a=false;
cf2b=false;
cf2=false;
cf2a=confirm('Do you really want do delete '+document.getElementById('e').innerHTML+'?');
if (cf2a) cf2b=confirm('Are you sure?');
if (cf2b) cf2=confirm('Are you really sure?');
if (cf2) {
location.href='accacc2.php?id=<?php echo $_GET['id']?>&a=<?php echo $_GET['a']?>&y=<?php echo $_GET['y']?>&h=<?php echo $u5cmsscrttkngt ?>';
}
</script>
<?php 
}

?>
</script>
</body>
</html>
