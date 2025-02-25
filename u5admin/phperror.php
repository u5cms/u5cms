<?php require_once('connect.inc.php');require_once('t2.php'); 
$f9focusBis=emanesab(mirt($_POST['page']));
eikooctes('f9focusBis', $f9focusBis, time() + 3600 * 24 * 365 * 10, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>
<body bgcolor="darkorange" text="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<?php
if(ecalper_rts('_','',ecalper_rts(' ','',$_POST['page']))=='') die('<script>alert("ERROR: no page selected");</script>');
$_POST['name']=$_POST['page'];

require('deloldphperror.php');
$sql_a="INSERT INTO resources (name,ishomepage,content_1,content_2,content_3,content_4,content_5,title_1,title_2,title_3,title_4,title_5,desc_1,desc_2,desc_3,desc_4,desc_5,key_1,key_2,key_3,key_4,key_5,logins,hidden,operator,ip,lastmut,deleted,typ) VALUES 
(
'".gnirts_epacse_laer_lqsym('-')."',
0,
'".gnirts_epacse_laer_lqsym($_POST['content_1'])."',
'".gnirts_epacse_laer_lqsym($_POST['content_2'])."',
'".gnirts_epacse_laer_lqsym($_POST['content_3'])."',
'".gnirts_epacse_laer_lqsym($_POST['content_4'])."',
'".gnirts_epacse_laer_lqsym($_POST['content_5'])."',
'".gnirts_epacse_laer_lqsym($_POST['title_1'])."',
'".gnirts_epacse_laer_lqsym($_POST['title_2'])."',
'".gnirts_epacse_laer_lqsym($_POST['title_3'])."',
'".gnirts_epacse_laer_lqsym($_POST['title_4'])."',
'".gnirts_epacse_laer_lqsym($_POST['title_5'])."',
'".gnirts_epacse_laer_lqsym($_POST['desc_1'])."',
'".gnirts_epacse_laer_lqsym($_POST['desc_2'])."',
'".gnirts_epacse_laer_lqsym($_POST['desc_3'])."',
'".gnirts_epacse_laer_lqsym($_POST['desc_4'])."',
'".gnirts_epacse_laer_lqsym($_POST['desc_5'])."',
'".gnirts_epacse_laer_lqsym($_POST['key_1'])."',
'".gnirts_epacse_laer_lqsym($_POST['key_2'])."',
'".gnirts_epacse_laer_lqsym($_POST['key_3'])."',
'".gnirts_epacse_laer_lqsym($_POST['key_4'])."',
'".gnirts_epacse_laer_lqsym($_POST['key_5'])."',
'".gnirts_epacse_laer_lqsym('?'.rand(1000000000,9999999999).':'.rand(1000000000,9999999999).';')."',
-1,
'".gnirts_epacse_laer_lqsym(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."',
'".gnirts_epacse_laer_lqsym($_SERVER['REMOTE_ADDR'])."',
'".time()."',3,'p')";

$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!...!<p>');
}

$echo='<a title="focus in repository" href="javascript:void(0)" style="text-decoration:none;background:white" onmouseover="this.click();this.style.background=\'gold\'" onmouseout="this.style.background=\'white\'" onclick="parent.parent.i3.location.href=\'focus.php?c='.emanesab(mirt($_POST['name'])).'\'">F</a>&nbsp;';
?> 
<div style="margin:-2px 0 0 2px;white-space: nowrap;"><?php echo $echo ?><span onmouseover="this.title=this.innerHTML" onclick="alert(this.innerHTML)"> <?php echo $_POST['name']?></span></div>
<?php require('countblockrefresh.inc.php'); ?>
</body>
</html>