<?php require_once('connect.inc.php');require_once('t2.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>saving...</title>
<?php require('backendcss.php'); ?></head>
<body bgcolor="#009900" id="body">
<?php 
require_once('accadmin.inc.php'); 

$sql_a="SELECT * FROM resources WHERE deleted=1 AND name='".mysql_real_escape_string($_POST['name'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$num_a = mysql_num_rows($result_a);


if ($num_a==0) {
echo('<script>document.getElementById("body").style.background="red";
if(opener)if(opener.parent)if(opener.parent.save)opener.parent.save.location.href="notdone.php?n=not formerly deleted: '.$_POST['name'].'";
self.close();
</script>');

trxlog('not killed '.$_POST['name']);
exit;
}

$sql_a="DELETE FROM resources WHERE deleted=1 AND name='".mysql_real_escape_string($_POST['name'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>');
}

trxlog('killed '.$_POST['name']);

?>
<script>
if(opener)if(opener.parent)if(opener.parent.save)opener.parent.save.location.href='done.php?n=killed <?php echo $_POST['name']?>';
self.close();
</script>
</body>
</html>
