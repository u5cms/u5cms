<?php require_once('connect.inc.php'); ?>
<?php if (strlen($_POST['name'])<4) die('<script>history.go(-1)</script>');?>
<?php if (strlen($_GET['name'])<4) die('<script>history.go(-1)</script>');?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>saving...</title>
<?php require('backendcss.php'); ?></head>
<body id="body" bgcolor="#009900">
<?php 
require('archivecheckget.inc.php');
require_once('../globals.inc.php');

$_POST['name']=basename($_POST['name']);
$_GET['name']=basename($_GET['name']);

$sql_a="DELETE FROM resources WHERE deleted=1 AND name='".mysql_real_escape_string($_POST['name'])."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) 
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="SELECT name FROM resources WHERE deleted!=1 AND name='".mysql_real_escape_string($_POST['name'])."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$num_a = mysql_num_rows($result_a);
if($num_a>0) die('<script>document.getElementById("body").style.background="red";;alert("ERROR: Target already exists!");history.go(-1)</script>');

duplicateallrecsinTABLENAMEwithoutAUTOINCFIELDwhereCONDITIONSaltervlaueofFIELDNAMEtotihisNEWVALUE
('resources','id','WHERE name=\''.$_GET['name'].'\'','name',$_POST['name']);

function duplicateallrecsinTABLENAMEwithoutAUTOINCFIELDwhereCONDITIONSaltervlaueofFIELDNAMEtotihisNEWVALUE
($tablename,$autoincfield,$conditions,$fieldname,$newvalue) {
  global $lan1na;
  global $lan2na;
  global $lan3na;

$sql="SELECT * FROM $tablename $conditions";
$result=@mysql_query($sql);
$anz=mysql_num_fields($result); 
$num = mysql_num_rows($result);

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
if (mysql_field_name($result,$i)==$fieldname) $valuestring.="'".$newvalue."'";
else $valuestring.="'".mysql_real_escape_string($row[mysql_field_name($result,$i)])."'";
if ($i<$anz-1) $valuestring.=', ';
}
}
$sqlb="INSERT INTO $tablename ($fieldstring) VALUES ($valuestring)";
$resultb=@mysql_query($sqlb);
if ($resultb==false) echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sqlb.'</font><p>';
else echo "<!--ok -->";
}
}

@mkdir('../r/'.$_POST['name'],0777);

$path='../r/'.$_GET['name'];
     if ($handle = @opendir($path))  { 
     while (false !== ($file = readdir($handle)))  { 

     $ext=explode('.',$file);
     $ext=$ext[count($ext)-1];


if (file_exists('../r/'.$_GET['name'].'/'.$_GET['name'].'_'.$lan1na.'.'.$ext)) copy('../r/'.$_GET['name'].'/'.$_GET['name'].'_'.$lan1na.'.'.$ext,'../r/'.$_POST['name'].'/'.$_POST['name'].'_'.$lan1na.'.'.$ext);
if (file_exists('../r/'.$_GET['name'].'/'.$_GET['name'].'_'.$lan2na.'.'.$ext)) copy('../r/'.$_GET['name'].'/'.$_GET['name'].'_'.$lan2na.'.'.$ext,'../r/'.$_POST['name'].'/'.$_POST['name'].'_'.$lan2na.'.'.$ext);
if (file_exists('../r/'.$_GET['name'].'/'.$_GET['name'].'_'.$lan3na.'.'.$ext)) copy('../r/'.$_GET['name'].'/'.$_GET['name'].'_'.$lan3na.'.'.$ext,'../r/'.$_POST['name'].'/'.$_POST['name'].'_'.$lan3na.'.'.$ext);

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
     $ext=$ext[count($ext)-1];


if (file_exists('../r/v'.$_GET['name'].'/v'.$_GET['name'].'_'.$lan1na.'.'.$ext)) copy('../r/v'.$_GET['name'].'/v'.$_GET['name'].'_'.$lan1na.'.'.$ext,'../r/v'.$_POST['name'].'/v'.$_POST['name'].'_'.$lan1na.'.'.$ext);
if (file_exists('../r/v'.$_GET['name'].'/v'.$_GET['name'].'_'.$lan2na.'.'.$ext)) copy('../r/v'.$_GET['name'].'/v'.$_GET['name'].'_'.$lan2na.'.'.$ext,'../r/v'.$_POST['name'].'/v'.$_POST['name'].'_'.$lan2na.'.'.$ext);
if (file_exists('../r/v'.$_GET['name'].'/v'.$_GET['name'].'_'.$lan3na.'.'.$ext)) copy('../r/v'.$_GET['name'].'/v'.$_GET['name'].'_'.$lan3na.'.'.$ext,'../r/v'.$_POST['name'].'/v'.$_POST['name'].'_'.$lan3na.'.'.$ext);

for ($a=100;$a<1000;$a++) {
if (file_exists('../r/v'.$_GET['name'].'/v'.$_GET['name'].'_'.$a.'.'.$ext)) copy('../r/v'.$_GET['name'].'/v'.$_GET['name'].'_'.$a.'.'.$ext,'../r/v'.$_POST['name'].'/v'.$_POST['name'].'_'.$a.'.'.$ext);
if (file_exists('../r/v'.$_POST['name'].'/v'.$_GET['name'].'_'.$a.'.'.$ext)) rename('../r/v'.$_POST['name'].'/v'.$_GET['name'].'_'.$a.'.'.$ext,'../r/v'.$_POST['name'].'/v'.$_POST['name'].'_'.$a.'.'.$ext);
}

}}
}
////////////////////


trxlog('copy '.$_GET['name'].' '.$_POST['name']);


$sql_a="SELECT typ FROM resources WHERE name='".mysql_real_escape_string($_POST['name'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$row_a = mysql_fetch_array($result_a);

if ($_GET['name'][0]=='!' && $_POST['name']!='!') {
$sql_a="UPDATE resources SET logins='' WHERE name='".mysql_real_escape_string($_POST['name'])."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}}

$sql_a="UPDATE resources SET ishomepage=0 WHERE name='".mysql_real_escape_string($_POST['name'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>');
}
?>
<script>
if(opener)if(opener.parent)if(opener.parent.save)opener.parent.save.location.href='done.php?n=copied <?php echo $_GET['name']?> to <?php echo $_POST['name']?>';
  
if ('<?php echo $row_a['typ']?>'=='p' && '<?php echo $_POST['go']?>'=='yes') {
if('<?php echo $_POST['wn']?>'=='i1')opener.parent.i1.gotopage('<?php echo $_POST['name']?>');
else opener.parent.i2.gotopage('<?php echo $_POST['name']?>');
}
document.getElementById('body').style.background='#009900';
setTimeout("self.close()",111);
</script>
</body>
</html>
