<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>
<body>
<?php
require('archivecheckget.inc.php');
$_GET['f']=basename($_GET['f']);
$_GET['name']=basename($_GET['name']);
@unlink('./../r/'.$_GET['name'].'/'.$_GET['f']);
trxlog('delfile '.$_GET['name'].' '.$_GET['f']);

$sql_a="UPDATE resources SET lastmut='".time()."' WHERE name='".mysql_real_escape_string($_GET['name'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
	   echo 'SQL_a-Query failed!...!<p>';
}

echo '<script>if (parent && parent.opener && parent.opener.parent && parent.opener.parent.save) parent.opener.parent.save.location.href=\'done.php?n=deleted '.$_GET['f'].'\'</script>';

?>
<script>location.replace('filesa.php?name=<?php echo $_GET['name']?>')</script>';
</body>
</html>
