<?php require_once('connect.inc.php');require_once('t2.php'); 
$f9focusBis=basename(trim($_POST['page']));
setcookie('f9focusBis', $f9focusBis, time() + 3600 * 24 * 365 * 10, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>
<body bgcolor="#339900" text="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<?php
if(str_replace('_','',str_replace(' ','',$_POST['page']))=='') die('<script>alert("ERROR: no page selected");</script>');
$_POST['name']=$_POST['page'];

require('archivecheck.inc.php');
require('delcheck.inc.php');

$sql_a="SELECT operator,ip,lastmut FROM resources WHERE name='".mysql_real_escape_string($_POST['name'])."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!...!<p>';
$row_a = mysql_fetch_array($result_a);

if ($_POST['coco']<$row_a['lastmut'] && (($checksaveversionconflictinbackend!='none'&&$checksaveversionconflictinbackend!='foreign')||($checksaveversionconflictinbackend=='foreign'&&trim(u5flatidnlower($row_a['operator']))!=trim(u5flatidnlower($_SERVER['PHP_AUTH_USER'])))) ) {
$conflict='(!)';
?>
<div style="display:none" id="e"><?php echo str_replace('<','',str_replace('>','',$row_a['operator'])) ?></div>
<script>
msgconflict='CONFLICT(!)\n\nYour data has been saved only in the section «text versions» but it is not functional.\n\nReason: '+document.getElementById('e').innerHTML+' has saved this data in a new version during your editing session (at <?php echo date('Y-m-d H:i:s',$row_a['lastmut'])?>).\n\nPlease find your changes in the section «text versions» and merge it manually into '+document.getElementById('e').innerHTML+'\'s work.';
</script>
<?php 
}

else {
$conflict='';
require('delold.php');
$sql_a="INSERT INTO resources (name,ishomepage,content_1,content_2,content_3,content_4,content_5,title_1,title_2,title_3,title_4,title_5,desc_1,desc_2,desc_3,desc_4,desc_5,key_1,key_2,key_3,key_4,key_5,logins,hidden,operator,ip,lastmut,deleted,typ) VALUES 
(
'".mysql_real_escape_string($_POST['name'])."',
'".mysql_real_escape_string($_POST['ishomepage'])."',
'".mysql_real_escape_string($_POST['content_1'])."',
'".mysql_real_escape_string($_POST['content_2'])."',
'".mysql_real_escape_string($_POST['content_3'])."',
'".mysql_real_escape_string($_POST['content_4'])."',
'".mysql_real_escape_string($_POST['content_5'])."',
'".mysql_real_escape_string($_POST['title_1'])."',
'".mysql_real_escape_string($_POST['title_2'])."',
'".mysql_real_escape_string($_POST['title_3'])."',
'".mysql_real_escape_string($_POST['title_4'])."',
'".mysql_real_escape_string($_POST['title_5'])."',
'".mysql_real_escape_string($_POST['desc_1'])."',
'".mysql_real_escape_string($_POST['desc_2'])."',
'".mysql_real_escape_string($_POST['desc_3'])."',
'".mysql_real_escape_string($_POST['desc_4'])."',
'".mysql_real_escape_string($_POST['desc_5'])."',
'".mysql_real_escape_string($_POST['key_1'])."',
'".mysql_real_escape_string($_POST['key_2'])."',
'".mysql_real_escape_string($_POST['key_3'])."',
'".mysql_real_escape_string($_POST['key_4'])."',
'".mysql_real_escape_string($_POST['key_5'])."',
'".mysql_real_escape_string(trim($_POST['logins']))."',
'".mysql_real_escape_string($_POST['hidden'])."',
'".mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."',
'".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."',
'".time()."',$deleted,'p')";

$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!...!<p>');
}

} // else if coco


$sql_a="INSERT INTO resources_log (name,ishomepage,content_1,content_2,content_3,content_4,content_5,title_1,title_2,title_3,title_4,title_5,desc_1,desc_2,desc_3,desc_4,desc_5,key_1,key_2,key_3,key_4,key_5,logins,hidden,operator,ip,lastmut,deleted,typ) VALUES 
(
'".mysql_real_escape_string($_POST['name'])."',
'".mysql_real_escape_string($_POST['ishomepage'])."',
'".mysql_real_escape_string($_POST['content_1'])."',
'".mysql_real_escape_string($_POST['content_2'])."',
'".mysql_real_escape_string($_POST['content_3'])."',
'".mysql_real_escape_string($_POST['content_4'])."',
'".mysql_real_escape_string($_POST['content_5'])."',
'".mysql_real_escape_string($_POST['title_1'])."',
'".mysql_real_escape_string($_POST['title_2'])."',
'".mysql_real_escape_string($_POST['title_3'])."',
'".mysql_real_escape_string($_POST['title_4'])."',
'".mysql_real_escape_string($_POST['title_5'])."',
'".mysql_real_escape_string($_POST['desc_1'])."',
'".mysql_real_escape_string($_POST['desc_2'])."',
'".mysql_real_escape_string($_POST['desc_3'])."',
'".mysql_real_escape_string($_POST['desc_4'])."',
'".mysql_real_escape_string($_POST['desc_5'])."',
'".mysql_real_escape_string($_POST['key_1'])."',
'".mysql_real_escape_string($_POST['key_2'])."',
'".mysql_real_escape_string($_POST['key_3'])."',
'".mysql_real_escape_string($_POST['key_4'])."',
'".mysql_real_escape_string($_POST['key_5'])."',
'".mysql_real_escape_string($_POST['logins'])."',
'".mysql_real_escape_string($_POST['hidden'])."',
'".mysql_real_escape_string($_SERVER['PHP_AUTH_USER'].$conflict)."',
'".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."',
'".time()."','$deleted','p')";

$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!...!<p>');
}

trxlog('saved'.$conflict.' '.$_POST['name']);

$echo='<a title="focus in repository" href="javascript:void(0)" style="text-decoration:none;background:white" onmouseover="this.click();this.style.background=\'gold\'" onmouseout="this.style.background=\'white\'" onclick="parent.parent.i3.location.href=\'focus.php?c='.basename(trim($_POST['name'])).'\'">F</a>&nbsp;';
?> 
<div style="width:2px;height:19px;background:white;position:absolute;display:none;top:0px" id="htaccess"></div>
<div style="margin:-2px 0 0 2px;white-space: nowrap;"><?php echo $echo ?><span onmouseover="this.title=this.innerHTML" onclick="alert(this.innerHTML)"><?php echo $conflict ?>saved <?php echo $_POST['name']?></span></div>
<iframe style="display:none" src="../indexer.php?n=<?php echo htmlspecialchars($_POST['name']) ?>"></iframe>
<script>
if (parent && parent.i2 && parent.i2.form1.coco) parent.i2.form1.coco.value='<?php echo time() ?>';
if (parent && parent.i1 && parent.i1.form1.coco) parent.i1.form1.coco.value='<?php echo time() ?>';
parent.i3.location.reload();
</script>
<?php 
if ($conflict!='') echo "
<script>
if (parent && parent.i1 && parent.i1.form1.coco) parent.i1.resetchanges();
if (parent && parent.i2 && parent.i2.form1.coco) parent.i2.resetchanges();
setTimeout(\"foundconflict()\",1);
function foundconflict() {
alert(msgconflict);
top.location.href=top.location.href;
window.open('textversions.php?c=".$_POST['name']."','_blank','toolbar=0,location=0,status=1,screenX=0,screenY=0,top=0,left=0,menubar=0,scrollbars=1,resizable=1,width=640,height=500');
}
</script>";
?>
<iframe width="1" height="1" scrolling="no" frameborder="0" src="lastsave.php"></iframe>
<?php require('countblockrefresh.inc.php'); ?>
<script><?php echo 'if(parent)if(parent.'.$_GET['i'].')parent.'.$_GET['i'].'.postsubmitfunction();' ?></script>
</body>
</html>