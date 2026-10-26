<?php require_once('connect.inc.php');require_once('t2.php');
$u5MutationRelease=u5CodeMigrationLock(dirname(__DIR__));register_shutdown_function($u5MutationRelease); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=WINDOWS-1252" />
<title>saving...</title>
<?php require('backendcss.php'); ?></head>
<body bgcolor="#009900" id="body">
<?php 
if(($_POST['name']=='htmltemplate' || $_POST['name']==u5CodeTemplateName())) {
echo('<script>document.getElementById("body").style.background="red";
if(opener)if(opener.parent)if(opener.parent.save)opener.parent.save.location.href="notdone.php?n=forbidden to delete '.$_POST['name'].'";
self.close();
</script>');
trxlog('not deleted '.$_POST['name']);
exit;
}

require('archivecheck.inc.php');

$_POST['name']=basename($_POST['name']);
$_GET['name']=basename($_GET['name']);

if ($_GET['name']!=md5($_POST['name'])) {
echo('<script>document.getElementById("body").style.background="red";
if(opener)if(opener.parent)if(opener.parent.save)opener.parent.save.location.href="notdone.php?n=wrong name, not deleted '.$_POST['name'].'";
self.close();
</script>');

trxlog('not deleted '.$_POST['name']);
exit;
}

require_once('codeassets-migrate.inc.php');
u5CodeMigrationHistorySchema();u5CodeMigrationInitializeHistory();
$u5DeleteName=mysql_real_escape_string($_POST['name']);
$u5DeleteRows=u5CodeMigrationRows("SELECT typ,deleted FROM resources WHERE name='".$u5DeleteName."' ORDER BY deleted=0 DESC,deleted=2 DESC");
if (!$u5DeleteRows) die('The resource no longer exists.');
$u5DeleteKind=strtolower((string)$u5DeleteRows[0]['typ']);
$u5DeleteState=(int)$u5DeleteRows[0]['deleted'];
// The primary key includes deleted. Preserve other states in history instead of colliding with them.
u5CodeMigrationQuery("DELETE FROM resources WHERE name='".$u5DeleteName."' AND deleted!=".$u5DeleteState);
u5CodeMigrationQuery("UPDATE resources SET deleted=1,lastmut=GREATEST(COALESCE(lastmut,0)+1,".time().") WHERE name='".$u5DeleteName."' AND deleted=".$u5DeleteState);
if ($u5DeleteKind==='c' && ($_POST['dfiles'] ?? '')==='yes') {
    foreach (array('r/'.u5CodeAssetFilename($_POST['name']),'r/'.$_POST['name'].'.tpl') as $relative) if (is_file(dirname(__DIR__).'/'.$relative)) {
        u5CodeMigrationBackupFile(dirname(__DIR__),$relative);
        if (!unlink(dirname(__DIR__).'/'.$relative)) throw new RuntimeException('Cannot remove the deleted code file.');
    }
}

////////////////////////////////////////////////////////
if ($_POST['dfiles']=='yes') {
 $path='../r/'.$_POST['name'];
     if ($handle = @opendir($path))  { 
     while (false !== ($file = readdir($handle)))  { 
if (trim(str_replace(' ','',$_POST['name']))!='') @unlink('../r/'.$_POST['name'].'/'.$file);
 }
}

@rmdir('./../r/'.$_POST['name']);
}
////////////////////////////////////////////////////////

trxlog('deleted '.$_POST['name']);


$sql_a="SELECT typ FROM resources WHERE name='".mysql_real_escape_string($_POST['name'])."'";
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