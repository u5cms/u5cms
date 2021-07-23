<?php require_once('connect.inc.php') ?><!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>
<style>
.out {
background:#eee;
padding:7px;
}
</style>
<body style="font-size:14px" id="srcbody">
<script>
document.write('<!--'+unescape(location.href)+'-->');
</script>
<?php
require('mailrenderfunctions.inc.php');

if($_GET['t']!='') {

$h=sha1($username.$password.$_SERVER['PHP_AUTH_USER'].$_SERVER['PHP_AUTH_PW'].$_GET['t']);
if($h!=$_GET['h'])die('<script>alert("forbidden")</script>');

$sql_a=base64_decode($_GET['t']);
$sql_a='SELECT * FROM formdata WHERE'.str_replace('SELECT * FROM formdata WHERE','',$sql_a);
$result_a=mysql_query($sql_a);

if ($result_a==false) echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$num_a = mysql_num_rows($result_a);

mysql_data_seek($result_a, ($_COOKIE['fd2p']-1));

}



else echo "<script>if(parent) if(parent.mapr_menu) if(parent.mapr_menu.document.getElementById('body')) parent.mapr_menu.document.getElementById('body').style.display='none'</script>";

$row_a = mysql_fetch_array($result_a);

$notespart=explode('|||',$row_a['notes']);
$row_a['notes']=$notespart[0];

$headcsv=explode(';',$row_a['headcsv']);
array_walk($headcsv,'subone');

$datacsv=explode(';',$row_a['datacsv']);
array_walk($datacsv,'subone');

if(strpos($_POST['mfrom'],'<')>-1) {
$_POST['mfrom']=explode('<',$_POST['mfrom']);	
$_POST['mfrom']=$_POST['mfrom'][1];
$_POST['mfrom']=explode('>',$_POST['mfrom']);	
$_POST['mfrom']=$_POST['mfrom'][0];
}


?>
<table style="width:100%">
<tr><td width="1">From:</td><td><?php echo '<div class="out" id="mfrom">'.u5fromidn(xm(render($_POST['mfrom']))).'</div>';?></td></tr>
<tr><td>To:</td><td><?php echo '<div class="out" id="mto">'.u5fromidn(xm(render($_POST['mto']))).'</div>';?></td></tr>
<tr><td>Cc:</td><td><?php echo '<div class="out" id="mcc">'.u5fromidn(xm(render($_POST['mcc']))).'</div>';?></td></tr>
<tr><td>Bcc:</td><td><?php echo '<div class="out" id="mbcc">'.u5fromidn(xm(render($_POST['mbcc']))).'</div>';?></td></tr>
<tr><td> </td><td><hr /> </td></tr>
<tr><td>Subject:</td><td><?php echo '<div class="out" id="mailsubject">'.ehtml(render($_POST['msubject'])).'</div>';?></td></tr>
</table>
Message:<br />
<?php echo '<div class="out" id="mmessage">'.render($_POST['mmessage']).'</div>';?>

<script>
if(parent) if(parent.mapr_view) if(parent.mapr_view.document.getElementById('body')) if(document.getElementById('srcbody')) parent.mapr_view.document.getElementById('body').innerHTML=document.getElementById('srcbody').innerHTML;
function numa() {
if (parent && parent.mapr_menu && parent.mapr_menu.document.getElementById('num')) {
parent.mapr_menu.document.getElementById('num').innerHTML='<?php echo $num_a?>';
<?php if($num_a!='' && $_COOKIE['fd2p']!='') {?>
if (<?php echo $_COOKIE['fd2p']?>!='' && <?php echo $_COOKIE['fd2p']?><1) parent.mapr_menu.location.href='mapr_menu.php?p=<?php echo $num_a ?>';
if (<?php echo $_COOKIE['fd2p']?>!='' && <?php echo $_COOKIE['fd2p']?>><?php echo $num_a ?> && <?php echo $num_a?>>0) parent.mapr_menu.location.href='mapr_menu.php?p=1';
if (<?php echo $_COOKIE['fd2p']?>==0) parent.mapr_menu.location.href='mapr_menu.php?p=<?php echo $num_a ?>';
<?php } ?>
}
setTimeout("numa()",222);
}
numa();
<?php
if($num_a<1) echo "if (parent && parent.mapr_menu && parent.mapr_menu.document.getElementById('body')) parent.mapr_menu.document.getElementById('body').style.display='none';";
if($num_a>0) echo "if (parent && parent.mapr_menu && parent.mapr_menu.document.getElementById('body')) parent.mapr_menu.document.getElementById('body').style.display='block';";
if($num_a<1) echo "if (parent && parent.mapr_view && parent.mapr_view.document.getElementById('body')) parent.mapr_view.document.getElementById('body').style.display='none';";
if($num_a>0) echo "if (parent && parent.mapr_view && parent.mapr_view.document.getElementById('body')) parent.mapr_view.document.getElementById('body').style.display='block';";
?>
</script>
</body>
</html>
