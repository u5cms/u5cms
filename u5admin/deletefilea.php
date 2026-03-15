<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=WINDOWS-1252" />
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

echo '<script>if (parent && parent.opener && parent.opener.parent && parent.opener.parent.save) parent.opener.parent.save.location.href=\'done.php?n=deleted '.htmlspecialchars($_GET['f'], ENT_QUOTES).'\'</script>';

?>
<script>location.replace('filesa.php?name=<?php echo htmlspecialchars($_GET['name'], ENT_QUOTES) ?>')</script>
</body>
</html>
