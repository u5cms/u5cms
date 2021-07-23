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
$sql_a="INSERT INTO resources (name,ishomepage,content_d,content_e,content_f,title_d,title_e,title_f,desc_d,desc_e,desc_f,key_d,key_e,key_f,logins,hidden,operator,ip,lastmut,deleted,typ) VALUES 
(
'".mysql_real_escape_string('-')."',
0,
'".mysql_real_escape_string($_POST['content_d'])."',
'".mysql_real_escape_string($_POST['content_e'])."',
'".mysql_real_escape_string($_POST['content_f'])."',
'".mysql_real_escape_string($_POST['title_d'])."',
'".mysql_real_escape_string($_POST['title_e'])."',
'".mysql_real_escape_string($_POST['title_f'])."',
'".mysql_real_escape_string($_POST['desc_d'])."',
'".mysql_real_escape_string($_POST['desc_e'])."',
'".mysql_real_escape_string($_POST['desc_f'])."',
'".mysql_real_escape_string($_POST['key_d'])."',
'".mysql_real_escape_string($_POST['key_e'])."',
'".mysql_real_escape_string($_POST['key_f'])."',
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