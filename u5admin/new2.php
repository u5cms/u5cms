<?php require_once('connect.inc.php');require_once('t2.php');
$u5MutationRelease=u5CodeMigrationLock(dirname(__DIR__));register_shutdown_function($u5MutationRelease);require_once('resourceupdate.inc.php'); ?>
<?php
if (strlen(($_POST['name'] ?? ''))<4) die('<script>history.go(-1)</script>');
if (strtolower((string)($_GET['typ'] ?? ''))==='c') {
    if ($cssRqHIADRI!='no') require('accadmin.inc.php');
    try {
        $u5NewFile='r/'.u5CodeAssetFilename($_POST['name']);
        if (file_exists(dirname(__DIR__).'/'.$u5NewFile)) {
            $u5Retained=mysql_query("SELECT name FROM resources WHERE typ='c' AND deleted=1 AND name='".mysql_real_escape_string($_POST['name'])."' LIMIT 1");
            if (!$u5Retained || !mysql_num_rows($u5Retained)) throw new RuntimeException('An independent file already occupies the target code filename.');
            require_once('codeassets-migrate.inc.php');u5CodeMigrationBackupFile(dirname(__DIR__),$u5NewFile);
        }
    }
    catch (RuntimeException $error) { die(htmlspecialchars($error->getMessage(),ENT_QUOTES,'Windows-1252')); }
}

if ($_POST['name']=='meta' || $_POST['name']=='search' || $_POST['name']=='languages' || $_POST['name']=='content' || $_POST['name']=='right') die('<script>alert("The following five words are reserved to be used in the htmltemplate between {{{ and }}}:\n\nmeta search languages content right\n\nPlease do not use these words for new pages or other content items.");history.go(-1)</script>');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=WINDOWS-1252" />
<title>saving...</title>
<?php require('backendcss.php'); ?></head>
<body id="body" bgcolor="#009900">
<?php 
$existingResult=mysql_query("SELECT name FROM resources WHERE deleted!=1 AND name='".mysql_real_escape_string($_POST['name'])."'");
if ($existingResult===false) die('Cannot check the resource name.');
if (mysql_num_rows($existingResult)>0) die('<script>document.getElementById("body").style.background="red";alert("ERROR: Target already exists!");history.go(-1)</script>');
$sql_a="SELECT name FROM resources WHERE deleted=1 AND name='".mysql_real_escape_string($_POST['name'])."'";
$result_a=mysql_query($sql_a);
if ($result_a===false) die('Cannot read the resource state.');
$num_a = mysql_num_rows($result_a);

if ($num_a>0) {

$sql_a="UPDATE resources SET lastmut=GREATEST(COALESCE(lastmut,0)+1,".time()."), deleted=0, typ='".mysql_real_escape_string($_GET['typ'])."' WHERE deleted=1 AND name='".mysql_real_escape_string($_POST['name'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!...!<p>');
}


}

else {
$sql_a="SELECT name FROM resources WHERE deleted!=1 AND name='".mysql_real_escape_string($_POST['name'])."'";
$result_a=mysql_query($sql_a);
if ($result_a===false) die('Cannot read the resource state.');
$num_a = mysql_num_rows($result_a);
if($num_a>0) die('<script>document.getElementById("body").style.background="red";;alert("ERROR: Target already exists!");history.go(-1)</script>');

$revisionResult=mysql_query("SELECT MAX(lastmut) AS revision FROM resources WHERE name='".mysql_real_escape_string($_POST['name'])."'");
if ($revisionResult===false) die('Cannot determine the resource revision.');
$revisionRow=mysql_fetch_array($revisionResult);
$newRevision=max(time(),(int)$revisionRow['revision']+1);
$sql_a="INSERT INTO resources (name,operator,ip,lastmut,deleted,typ) VALUES (
'".mysql_real_escape_string($_POST['name'])."',
'".mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."',
'".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."',
'".$newRevision."',0,'".mysql_real_escape_string($_GET['typ'])."')";

$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!...!<p>');
}
$sql_a="INSERT INTO resources_log (name,operator,ip,lastmut,deleted,typ) VALUES (
'".mysql_real_escape_string($_POST['name'])."',
'".mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."',
'".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."',
'".$newRevision."',0,'".mysql_real_escape_string($_GET['typ'])."')";

$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!...!<p>');
}
}
if (strtolower((string)($_GET['typ'] ?? ''))==='c') u5CodeAssetExport(dirname(__DIR__));
trxlog('new '.$_POST['name']);
?>
<script>
if(opener)if(opener.parent)if(opener.parent.save)opener.parent.save.location.href=<?php echo u5ResourceJsString('done.php?n='.rawurlencode('inserted '.$_POST['name'])) ?>;
if (<?php echo u5ResourceJsString($_GET['typ']) ?>=='p' && <?php echo u5ResourceJsString($_POST['go'] ?? '') ?>=='yes') if(opener)if(opener.parent)if(opener.parent.i2)opener.parent.i2.gotopage(<?php echo u5ResourceJsString($_POST['name']) ?>);
self.close();
</script>
</body>
</html>
