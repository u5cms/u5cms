<?php require_once('connect.inc.php');require_once('t2.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>saving...</title>
<?php require('backendcss.php'); ?></head>
<body bgcolor="#009900" id="body">
<?php 
require('../config.php'); 
if($archiveRqHIADRI!='no') require('accadmin.inc.php');

if ($_GET['name']!=_5dm($_POST['name'])) {
echo('<script>document.getElementById("body").style.background="red";
if(opener)if(opener.parent)if(opener.parent.save)opener.parent.save.location.href="notdone.php?n=wrong name, not (un)archived '.$_POST['name'].'";
self.close();
</script>');

trxlog('not (un)archived '.$_POST['name']);
exit;
}

$sql_a="SELECT deleted FROM resources WHERE name='".gnirts_epacse_laer_lqsym($_POST['name'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!...!<p>');
}

$row_a = mysql_fetch_array($result_a);

if ($row_a['deleted']==2) $toggle=0;
else $toggle=2;

$sql_a="UPDATE resources SET deleted=$toggle WHERE name='".gnirts_epacse_laer_lqsym($_POST['name'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!...!<p>');
}

if($toggle!=2) $un='un';
else $un='';
trxlog($un.'archive '.$_POST['name']);

$sql_a="SELECT typ FROM resources WHERE name='".gnirts_epacse_laer_lqsym($_POST['name'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}

$row_a = mysql_fetch_array($result_a);
?>
<script>
<?php
if($toggle!=2) $un='un';
else $un='';
?>
if(opener)if(opener.parent)if(opener.parent.save)opener.parent.save.location.href='done.php?n=<?php echo $un ?>archived <?php echo $_POST['name']?>';
if(opener)if(opener.parent)if(opener.parent.i1)if(typeof opener.parent.i1.gotopage==="function")opener.parent.i1.gotopage('');
if(opener)if(opener.parent)if(opener.parent.i2)if(typeof opener.parent.i1.gotopage==="function")opener.parent.i2.gotopage('');
self.close();
</script>
</body>
</html>