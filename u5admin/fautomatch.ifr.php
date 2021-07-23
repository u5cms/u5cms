<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
<?php require('backendcss.php'); ?>
</head>
<body>
<?php 
$sql_a="SELECT name FROM resources WHERE deleted!=1 AND typ='p'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query schlug fehl!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$num_a = mysql_num_rows($result_a);
$names=',';
for ($i_a=0; $i_a<$num_a; $i_a++) {
$row_a = mysql_fetch_array($result_a);
$names.=$row_a['name'].',';
}
echo "<script>pnames='$names'</script>";

$sql_a="SELECT name FROM resources WHERE deleted!=1 AND typ!='p'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query schlug fehl!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$num_a = mysql_num_rows($result_a);
$names=',';
for ($i_a=0; $i_a<$num_a; $i_a++) {
$row_a = mysql_fetch_array($result_a);
$names.=$row_a['name'].',';
}
echo "<script>inames='$names'</script>";
?>
<script>
function autof() {
if(parent.document.getElementById('idgdf'))igdfvalue=parent.document.getElementById('idgdf').value.replace(/\*/g,'');
else igdfvalue='';

if(parent.document.getElementById('idgdf') && pnames.indexOf(','+igdfvalue.toLowerCase()+',')>-1 && igdfvalue!='' && parent.document.getElementById('fautomatch'))parent.document.getElementById('fautomatch').innerHTML='<a title="Open right (+Alt=left)" href="javascript:void(0)" onclick="if(event.altKey)parent.i1.lgotopage(\''+igdfvalue.toLowerCase()+'\');else parent.i2.gotopage(\''+igdfvalue.toLowerCase()+'\')">&#9998;&nbsp;</a>&nbsp;<a title="focus in repository" href="javascript:void(0)" onclick="parent.parent.i3.location.href=\'focus.php?c='+igdfvalue.toLowerCase()+'\'">F&nbsp;</a>';

else if(parent.document.getElementById('idgdf') && inames.indexOf(','+igdfvalue.toLowerCase()+',')>-1 && igdfvalue!='' && parent.document.getElementById('fautomatch'))parent.document.getElementById('fautomatch').innerHTML='<a title="show edit options" href="javascript:void(0)" onclick="parent.i3.location.href=(\'openpre.php?name='+igdfvalue.toLowerCase()+'\')">&hellip;&nbsp;</a>&nbsp;<a title="focus in repository" href="javascript:void(0)" onclick="parent.parent.i3.location.href=\'focus.php?c='+igdfvalue.toLowerCase()+'\'">F&nbsp;</a>';

else parent.document.getElementById('fautomatch').innerHTML='';	
}
setTimeout("autof()",111);
</script>
</body>
</html>