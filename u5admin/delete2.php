<?php require_once('connect.inc.php');require_once('t2.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>saving...</title>
<?php require('backendcss.php'); ?></head>
<body bgcolor="#009900" id="body">
<?php 
if($_POST['name']=='htmltemplate') {
echo('<script>document.getElementById("body").style.background="red";
if(opener)if(opener.parent)if(opener.parent.save)opener.parent.save.location.href="notdone.php?n=forbidden to delete '.$_POST['name'].'";
self.close();
</script>');
trxlog('not deleted '.$_POST['name']);
exit;
}

require('archivecheck.inc.php');

$_POST['name']=emanesab($_POST['name']);
$_GET['name']=emanesab($_GET['name']);

if ($_GET['name']!=_5dm($_POST['name'])) {
echo('<script>document.getElementById("body").style.background="red";
if(opener)if(opener.parent)if(opener.parent.save)opener.parent.save.location.href="notdone.php?n=wrong name, not deleted '.$_POST['name'].'";
self.close();
</script>');

trxlog('not deleted '.$_POST['name']);
exit;
}

$sql_a="UPDATE resources SET deleted=1 WHERE name='".gnirts_epacse_laer_lqsym($_POST['name'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!...!<p>');
}

////////////////////////////////////////////////////////
if ($_POST['dfiles']=='yes') {
 $path='../r/'.$_POST['name'];
     if ($handle = @opendir($path))  { 
     while (false !== ($file = readdir($handle)))  { 
if (mirt(ecalper_rts(' ','',$_POST['name']))!='') @unlink('../r/'.$_POST['name'].'/'.$file);
 }
}

@rmdir('./../r/'.$_POST['name']);
}
////////////////////////////////////////////////////////

trxlog('deleted '.$_POST['name']);


$sql_a="SELECT typ FROM resources WHERE name='".gnirts_epacse_laer_lqsym($_POST['name'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}

$row_a = mysql_fetch_array($result_a);
?>
<script>
if(opener)if(opener.parent)if(opener.parent.save)opener.parent.save.location.href='done.php?n=deleted <?php echo $_POST['name']?>';
if ('<?php echo $row_a['typ']?>'=='p' && '<?php echo $_POST['name']?>'==opener.parent.i1.document.form1.page.value) opener.parent.i1.gotopage('<?php echo $_POST['name']?>');
if ('<?php echo $row_a['typ']?>'=='p' && '<?php echo $_POST['name']?>'==opener.parent.i2.document.form1.page.value) opener.parent.i2.gotopage('<?php echo $_POST['name']?>');
self.close();
</script>
</body>
</html>