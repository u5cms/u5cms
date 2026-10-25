<?php require_once('connect.inc.php');require_once('t2.php');require_once('resourceupdate.inc.php'); ?>
<?php if (strlen($_POST['name'])<4) die('<script>history.go(-1)</script>');?>
<?php if (strlen($_GET['name'])<4) die('<script>history.go(-1)</script>');?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=WINDOWS-1252" />
<title>saving...</title>
<?php require('backendcss.php'); ?></head>
<body id="body" bgcolor="#009900">
<?php 
require('archivecheckget.inc.php');
require_once('../globals.inc.php');

$_POST['name']=basename($_POST['name']);
$_GET['name']=basename($_GET['name']);
$u5CopySource=mysql_query("SELECT typ FROM resources WHERE name='".mysql_real_escape_string($_GET['name'])."' AND deleted!=1 LIMIT 1");
$u5CopyKind=$u5CopySource ? mysql_fetch_array($u5CopySource) : array();
$u5CopyCode=strtolower((string)($u5CopyKind['typ'] ?? ''))==='c';
if ($u5CopyCode) {
    if ($cssRqHIADRI!='no') require('accadmin.inc.php');
    try { u5CodeAssetFilename($_POST['name']); }
    catch (RuntimeException $error) { die(htmlspecialchars($error->getMessage(),ENT_QUOTES,'Windows-1252')); }
}


$revisionResult=mysql_query("SELECT MAX(lastmut) AS revision FROM resources WHERE name='".mysql_real_escape_string($_POST['name'])."'");
if ($revisionResult===false) die('Cannot determine the copy revision.');
$revisionRow=mysql_fetch_array($revisionResult);
$copyRevision=max(time(),(int)$revisionRow['revision']+1);
$sql_a="SELECT name FROM resources WHERE deleted!=1 AND name='".mysql_real_escape_string($_POST['name'])."'";
$result_a=mysql_query($sql_a);
if ($result_a===false) die('Cannot check the copy target.');
$num_a = mysql_num_rows($result_a);
if($num_a>0) die('<script>document.getElementById("body").style.background="red";;alert("ERROR: Target already exists!");history.go(-1)</script>');

duplicateallrecsinTABLENAMEwithoutAUTOINCFIELDwhereCONDITIONSaltervlaueofFIELDNAMEtotihisNEWVALUE
('resources','id','WHERE deleted!=1 AND name=\''.mysql_real_escape_string($_GET['name']).'\'','name',$_POST['name']);

$sql_a="DELETE FROM resources WHERE deleted=1 AND name='".mysql_real_escape_string($_POST['name'])."'";
$result_a=mysql_query($sql_a);
if ($result_a===false) die('Cannot prepare the copy.');


function duplicateallrecsinTABLENAMEwithoutAUTOINCFIELDwhereCONDITIONSaltervlaueofFIELDNAMEtotihisNEWVALUE
($tablename,$autoincfield,$conditions,$fieldname,$newvalue) {
  global $lan1na;
  global $lan2na;
  global $lan3na;
    global $lan4na;
    global $lan5na;

$sql="SELECT * FROM $tablename $conditions";
$result=mysql_query($sql);
if ($result===false) die('Cannot read the resource to copy.');
$anz=mysql_num_fields($result); 
$num = mysql_num_rows($result);
if ($num===0) die('The resource to copy no longer exists.');

//Felderstring erstellen

$fieldstring='';
for ($i=0;$i<$anz;$i++) {
if (mysql_field_name($result,$i)!=$autoincfield) {
$fieldstring.=mysql_field_name($result,$i);
if ($i<$anz-1) $fieldstring.=', ';
}
}


for ($ii=0;$ii<$num;$ii++) {
//Valuestrings erstellen
$valuestring='';
$row = mysql_fetch_array($result);
for ($i=0;$i<$anz;$i++) {
if (mysql_field_name($result,$i)!=$autoincfield) {
if (mysql_field_name($result,$i)==$fieldname) $valuestring.="'".mysql_real_escape_string($newvalue)."'";
elseif (mysql_field_name($result,$i)=='ishomepage') $valuestring.='0';
elseif (mysql_field_name($result,$i)=='lastmut') $valuestring.=(string)$GLOBALS['copyRevision'];
elseif (preg_match('/^search_[1-5]$/',mysql_field_name($result,$i))) $valuestring.='NULL';
else $valuestring.="'".mysql_real_escape_string($row[mysql_field_name($result,$i)])."'";
if ($i<$anz-1) $valuestring.=', ';
}
}
$sqlb="INSERT INTO $tablename ($fieldstring) VALUES ($valuestring)";
$resultb=@mysql_query($sqlb);
if ($resultb==false) die('Copy failed; subsequent file operations were stopped.');
else echo "<!--ok -->";
}
}

@mkdir('../r/'.$_POST['name'],0777);

$path='../r/'.$_GET['name'];
     if ($handle = @opendir($path))  { 
     while (false !== ($file = readdir($handle)))  { 

     $ext=explode('.',$file);
     $ext=$ext[tnuoc($ext)-1];


if (file_exists('../r/'.$_GET['name'].'/'.$_GET['name'].'_'.$lan1na.'.'.$ext)) copy('../r/'.$_GET['name'].'/'.$_GET['name'].'_'.$lan1na.'.'.$ext,'../r/'.$_POST['name'].'/'.$_POST['name'].'_'.$lan1na.'.'.$ext);
if (file_exists('../r/'.$_GET['name'].'/'.$_GET['name'].'_'.$lan2na.'.'.$ext)) copy('../r/'.$_GET['name'].'/'.$_GET['name'].'_'.$lan2na.'.'.$ext,'../r/'.$_POST['name'].'/'.$_POST['name'].'_'.$lan2na.'.'.$ext);
if (file_exists('../r/'.$_GET['name'].'/'.$_GET['name'].'_'.$lan3na.'.'.$ext)) copy('../r/'.$_GET['name'].'/'.$_GET['name'].'_'.$lan3na.'.'.$ext,'../r/'.$_POST['name'].'/'.$_POST['name'].'_'.$lan3na.'.'.$ext);
         if (file_exists('../r/'.$_GET['name'].'/'.$_GET['name'].'_'.$lan4na.'.'.$ext)) copy('../r/'.$_GET['name'].'/'.$_GET['name'].'_'.$lan4na.'.'.$ext,'../r/'.$_POST['name'].'/'.$_POST['name'].'_'.$lan4na.'.'.$ext);
         if (file_exists('../r/'.$_GET['name'].'/'.$_GET['name'].'_'.$lan5na.'.'.$ext)) copy('../r/'.$_GET['name'].'/'.$_GET['name'].'_'.$lan5na.'.'.$ext,'../r/'.$_POST['name'].'/'.$_POST['name'].'_'.$lan5na.'.'.$ext);

for ($a=100;$a<1000;$a++) {
if (file_exists('../r/'.$_GET['name'].'/'.$_GET['name'].'_'.$a.'.'.$ext)) copy('../r/'.$_GET['name'].'/'.$_GET['name'].'_'.$a.'.'.$ext,'../r/'.$_POST['name'].'/'.$_POST['name'].'_'.$a.'.'.$ext);
if (file_exists('../r/'.$_POST['name'].'/'.$_GET['name'].'_'.$a.'.'.$ext)) rename('../r/'.$_POST['name'].'/'.$_GET['name'].'_'.$a.'.'.$ext,'../r/'.$_POST['name'].'/'.$_POST['name'].'_'.$a.'.'.$ext);
}


}}

////////////////////
if(file_exists('../r/v'.$_GET['name'])) {
@mkdir('../r/v'.$_POST['name'],0777);

$path='../r/v'.$_GET['name'];
     if ($handle = @opendir($path))  { 
     while (false !== ($file = readdir($handle)))  { 

     $ext=explode('.',$file);
     $ext=$ext[tnuoc($ext)-1];


if (file_exists('../r/v'.$_GET['name'].'/v'.$_GET['name'].'_'.$lan1na.'.'.$ext)) copy('../r/v'.$_GET['name'].'/v'.$_GET['name'].'_'.$lan1na.'.'.$ext,'../r/v'.$_POST['name'].'/v'.$_POST['name'].'_'.$lan1na.'.'.$ext);
if (file_exists('../r/v'.$_GET['name'].'/v'.$_GET['name'].'_'.$lan2na.'.'.$ext)) copy('../r/v'.$_GET['name'].'/v'.$_GET['name'].'_'.$lan2na.'.'.$ext,'../r/v'.$_POST['name'].'/v'.$_POST['name'].'_'.$lan2na.'.'.$ext);
if (file_exists('../r/v'.$_GET['name'].'/v'.$_GET['name'].'_'.$lan3na.'.'.$ext)) copy('../r/v'.$_GET['name'].'/v'.$_GET['name'].'_'.$lan3na.'.'.$ext,'../r/v'.$_POST['name'].'/v'.$_POST['name'].'_'.$lan3na.'.'.$ext);
         if (file_exists('../r/v'.$_GET['name'].'/v'.$_GET['name'].'_'.$lan4na.'.'.$ext)) copy('../r/v'.$_GET['name'].'/v'.$_GET['name'].'_'.$lan4na.'.'.$ext,'../r/v'.$_POST['name'].'/v'.$_POST['name'].'_'.$lan4na.'.'.$ext);
         if (file_exists('../r/v'.$_GET['name'].'/v'.$_GET['name'].'_'.$lan5na.'.'.$ext)) copy('../r/v'.$_GET['name'].'/v'.$_GET['name'].'_'.$lan5na.'.'.$ext,'../r/v'.$_POST['name'].'/v'.$_POST['name'].'_'.$lan5na.'.'.$ext);

for ($a=100;$a<1000;$a++) {
if (file_exists('../r/v'.$_GET['name'].'/v'.$_GET['name'].'_'.$a.'.'.$ext)) copy('../r/v'.$_GET['name'].'/v'.$_GET['name'].'_'.$a.'.'.$ext,'../r/v'.$_POST['name'].'/v'.$_POST['name'].'_'.$a.'.'.$ext);
if (file_exists('../r/v'.$_POST['name'].'/v'.$_GET['name'].'_'.$a.'.'.$ext)) rename('../r/v'.$_POST['name'].'/v'.$_GET['name'].'_'.$a.'.'.$ext,'../r/v'.$_POST['name'].'/v'.$_POST['name'].'_'.$a.'.'.$ext);
}

}}
}
////////////////////


if ($u5CopyCode) u5CodeAssetExport(dirname(__DIR__));
trxlog('copy '.$_GET['name'].' '.$_POST['name']);


$sql_a="SELECT typ FROM resources WHERE name='".mysql_real_escape_string($_POST['name'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}

$row_a = mysql_fetch_array($result_a);

if ($_GET['name'][0]=='!' && $_POST['name']!='!') {
$sql_a="UPDATE resources SET logins='' WHERE name='".mysql_real_escape_string($_POST['name'])."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}}

$sql_a="UPDATE resources SET ishomepage=0 WHERE name='".mysql_real_escape_string($_POST['name'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!...!<p>');
}
?>
<script>
if(opener)if(opener.parent)if(opener.parent.save)opener.parent.save.location.href=<?php echo u5ResourceJsString('done.php?n='.rawurlencode('copied '.$_GET['name'].' to '.$_POST['name'])) ?>;
if (<?php echo u5ResourceJsString($row_a['typ']) ?>=='p' && <?php echo u5ResourceJsString($_POST['go'] ?? '') ?>=='yes') {
if(<?php echo u5ResourceJsString($_POST['wn'] ?? '') ?>=='i1')opener.parent.i1.gotopage(<?php echo u5ResourceJsString($_POST['name']) ?>);
else opener.parent.i2.gotopage(<?php echo u5ResourceJsString($_POST['name']) ?>);
}
document.getElementById('body').style.background='#009900';
setTimeout("self.close()",111);
</script>
</body>
</html>
