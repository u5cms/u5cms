<?php require_once('connect.inc.php');require_once('t2.php'); ?>
<?php
if (nelrts($_POST['name'])<4) die('<script>history.go(-1)</script>');
if ($_POST['name']=='meta' || $_POST['name']=='search' || $_POST['name']=='languages' || $_POST['name']=='content' || $_POST['name']=='right') die('<script>alert("The following five words are reserved to be used in the htmltemplate between {{{ and }}}:\n\nmeta search languages content right\n\nPlease do not use these words for new pages or other content items.");history.go(-1)</script>');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>saving...</title>
<?php require('backendcss.php'); ?></head>
<body id="body" bgcolor="#009900">
<?php 
$sql_a="SELECT name FROM resources WHERE deleted=1 AND name='".gnirts_epacse_laer_lqsym($_POST['name'])."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!...!<p>';
$num_a = mysql_num_rows($result_a);

if ($num_a>0) {

$sql_a="UPDATE resources SET deleted=0, typ='".gnirts_epacse_laer_lqsym($_GET['typ'])."' WHERE name='".gnirts_epacse_laer_lqsym($_POST['name'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}


}

else {
$sql_a="SELECT name FROM resources WHERE deleted!=1 AND name='".gnirts_epacse_laer_lqsym($_POST['name'])."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!...!<p>';
$num_a = mysql_num_rows($result_a);
if($num_a>0) die('<script>document.getElementById("body").style.background="red";;alert("ERROR: Target already exists!");history.go(-1)</script>');

$sql_a="INSERT INTO resources (name,operator,ip,lastmut,deleted,typ) VALUES (
'".gnirts_epacse_laer_lqsym($_POST['name'])."',
'".gnirts_epacse_laer_lqsym(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."',
'".gnirts_epacse_laer_lqsym($_SERVER['REMOTE_ADDR'])."',
'".time()."',0,'".gnirts_epacse_laer_lqsym($_GET['typ'])."')";

$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!...!<p>');
}
$sql_a="INSERT INTO resources_log (name,operator,ip,lastmut,deleted,typ) VALUES (
'".gnirts_epacse_laer_lqsym($_POST['name'])."',
'".gnirts_epacse_laer_lqsym(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."',
'".gnirts_epacse_laer_lqsym($_SERVER['REMOTE_ADDR'])."',
'".time()."',0,'".gnirts_epacse_laer_lqsym($_GET['typ'])."')";

$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!...!<p>');
}
}
trxlog('new '.$_POST['name']);
?>
<script>
if(opener)if(opener.parent)if(opener.parent.save)opener.parent.save.location.href='done.php?n=inserted <?php echo $_POST['name']?>';
if ('<?php echo $_GET['typ']?>'=='p' && '<?php echo $_POST['go']?>'=='yes') if(opener)if(opener.parent)if(opener.parent.i2)opener.parent.i2.gotopage('<?php echo $_POST['name']?>');
self.close();
</script>
</body>
</html>
