<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>
<body id="body" bgcolor="#339900" text="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<?php
require('archivecheck.inc.php');
require('delcheck.inc.php');

$sql_a="SELECT operator,ip,lastmut FROM resources WHERE name='".mysql_real_escape_string($_POST['name'])."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}
$row_a = mysql_fetch_array($result_a);
if ($_POST['coco']<$row_a['lastmut'] && (($checksaveversionconflictinbackend!='none'&&$checksaveversionconflictinbackend!='foreign')||($checksaveversionconflictinbackend=='foreign'&&trim(u5flatidnlower($row_a['operator']))!=trim(u5flatidnlower($_SERVER['PHP_AUTH_USER'])))) ) {
$conflict='(!)';
?>
<div style="display:none" id="e"><?php echo str_replace('<','',str_replace('>','',$row_a['operator'])) ?></div>
<script>
msgconflict='CONFLICT(!)\n\nYour data has been saved only in the section «text versions» but it is not functional.\n\nReason: '+document.getElementById('e').innerHTML+' has saved this data in a new version during your editing session (at <?php echo date('Y-m-d H:i:s',$row_a['lastmut'])?>).\n\nPlease find your changes in the section «text versions» and merge it manually into '+document.getElementById('e').innerHTML+'\'s work.';
if(window.name=='ametai'){
}
</script>
<?php 
}

else {
$conflict='';
require('delold.php');
$sql_a="INSERT INTO resources (name,content_d,content_e,content_f,title_d,title_e,title_f,desc_d,desc_e,desc_f,key_d,key_e,key_f,logins,hidden,operator,ip,lastmut,deleted,typ) VALUES 
(
'".mysql_real_escape_string(trim($_POST['name']))."',
'".mysql_real_escape_string(trim($_POST['content_d']))."',
'".mysql_real_escape_string(trim($_POST['content_e']))."',
'".mysql_real_escape_string(trim($_POST['content_f']))."',
'".mysql_real_escape_string(trim($_POST['title_d']))."',
'".mysql_real_escape_string(trim($_POST['title_e']))."',
'".mysql_real_escape_string(trim($_POST['title_f']))."',
'".mysql_real_escape_string(trim($_POST['desc_d']))."',
'".mysql_real_escape_string(trim($_POST['desc_e']))."',
'".mysql_real_escape_string(trim($_POST['desc_f']))."',
'".mysql_real_escape_string(trim($_POST['key_d']))."',
'".mysql_real_escape_string(trim($_POST['key_e']))."',
'".mysql_real_escape_string(trim($_POST['key_f']))."',
'".mysql_real_escape_string(trim($_POST['logins']))."',
0,
'".mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."',
'".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."',
'".time()."',$deleted,'".mysql_real_escape_string($_GET['typ'])."')";


$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>');
}

} //else if coco

$sql_a="INSERT INTO resources_log (name,content_d,content_e,content_f,title_d,title_e,title_f,desc_d,desc_e,desc_f,key_d,key_e,key_f,logins,hidden,operator,ip,lastmut,deleted,typ) VALUES 
(
'".mysql_real_escape_string(trim($_POST['name']))."',
'".mysql_real_escape_string(trim($_POST['content_d']))."',
'".mysql_real_escape_string(trim($_POST['content_e']))."',
'".mysql_real_escape_string(trim($_POST['content_f']))."',
'".mysql_real_escape_string(trim($_POST['title_d']))."',
'".mysql_real_escape_string(trim($_POST['title_e']))."',
'".mysql_real_escape_string(trim($_POST['title_f']))."',
'".mysql_real_escape_string(trim($_POST['desc_d']))."',
'".mysql_real_escape_string(trim($_POST['desc_e']))."',
'".mysql_real_escape_string(trim($_POST['desc_f']))."',
'".mysql_real_escape_string(trim($_POST['key_d']))."',
'".mysql_real_escape_string(trim($_POST['key_e']))."',
'".mysql_real_escape_string(trim($_POST['key_f']))."',
'".mysql_real_escape_string(trim($_POST['logins']))."',
0,
'".mysql_real_escape_string($_SERVER['PHP_AUTH_USER'].$conflict)."',
'".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."',
'".time()."','$deleted','".mysql_real_escape_string($_GET['typ'])."')";


$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>');
}

trxlog('metadata'.$conflict.' '.$_POST['name']);
?> 

<?php 
if ($conflict!='') echo "
<script>
setTimeout(\"foundconflict()\",1);
function foundconflict() {
alert(msgconflict);
window.open('textversions.php?c=".$_POST['name']."','_blank','toolbar=0,location=0,status=1,screenX=1000,screenY=0,top=0,left=1000,menubar=0,scrollbars=1,resizable=1,width=640,height=500');
location.href='".$_GET['uri']."?name=".mysql_real_escape_string($_POST['name'])."&typ=".$_GET['typ']."';
if(window.name=='ametai'){parent.document.getElementById('sacabu').style.display='none';parent.location.href=parent.location.href};
}
</script>";

else {
?>


<script>
if(opener)if(opener.parent)if(opener.parent.save)opener.parent.save.location.href='done.php?n=updated <?php echo $_POST['name']?>';
if(parent)if(parent.parent)if(parent.parent.opener)if(parent.parent.opener.parent)if(parent.parent.opener.parent.save)parent.parent.opener.parent.save.location.href='done.php?n=updated <?php echo $_POST['name']?>';
if(window.name=='ametai'){
parent.document.getElementById('sacabu').style.display='none';
location.href='metai.php?typ=a&name=<?php echo $_POST['name']?>';
}
else self.close();
</script>
<?php } ?>
<div style="margin:-2px 0 0 2px;white-space: nowrap;" onmouseover="this.title=this.innerHTML" onclick="alert(this.innerHTML)"><?php echo $conflict ?>saved <?php echo $_POST['name']?></div>
</body>
</html>
