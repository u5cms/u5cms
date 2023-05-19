<?php require_once('connect.inc.php'); 
$f9focusBis=basename(trim($_POST['page']));
setcookie('f9focusBis', $f9focusBis, time() + 3600 * 24 * 365 * 10, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>
<body bgcolor="darkorange" text="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<?php
if(str_replace('_','',str_replace(' ','',$_POST['page']))=='') die('<script>alert("ERROR: no page selected");</script>');
$_POST['name']=$_POST['page'];

require('deloldphperror.php');
$sql_a="INSERT INTO resources (name,ishomepage,content_1,content_2,content_3,content_4,content_5,title_1,title_2,title_3,title_4,title_5,desc_1,desc_2,desc_3,desc_4,desc_5,key_1,key_2,key_3,key_4,key_5,logins,hidden,operator,ip,lastmut,deleted,typ) VALUES 
(
'".mysql_real_escape_string('-')."',
0,
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
'".mysql_real_escape_string('?'.rand(1000000000,9999999999).':'.rand(1000000000,9999999999).';')."',
-1,
'".mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."',
'".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."',
'".time()."',3,'p')";

$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>');
}

$echo='<a title="focus in repository" href="javascript:void(0)" style="text-decoration:none;background:white" onmouseover="this.click();this.style.background=\'gold\'" onmouseout="this.style.background=\'white\'" onclick="parent.parent.i3.location.href=\'focus.php?c='.basename(trim($_POST['name'])).'\'">F</a>&nbsp;';
?> 
<div style="margin:-2px 0 0 2px;white-space: nowrap;"><?php echo $echo ?><span onmouseover="this.title=this.innerHTML" onclick="alert(this.innerHTML)"> <?php echo $_POST['name']?></span></div>
<?php require('countblockrefresh.inc.php'); ?>
</body>
</html>