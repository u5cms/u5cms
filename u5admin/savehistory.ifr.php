<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
<script src="shortcut.js"></script>
<?php include('repoctrlssub.inc.php'); ?>
<?php require('backendcss.php'); ?>
</head>
<body>
<table cellpadding="1" cellspacing="1" width="100%">
<?php 
if($_COOKIE['hplus']=='on')$limit='';
else $limit='LIMIT 0,7';
$sql_a="SELECT * FROM resources WHERE deleted!=1 AND typ!='s' AND name!='-' AND name LIKE '" . mysql_real_escape_string(str_replace('*', '%', $_GET['f'])) . "' AND operator='".mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."' ORDER BY lastmut DESC $limit";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query schlug fehl!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$num_a = mysql_num_rows($result_a);

for ($i_a=0; $i_a<$num_a; $i_a++) {
$row_a = mysql_fetch_array($result_a);

$attr='';
$isalbum='';
if($row_a['typ']=='a')$isalbum='a';

if($row_a['typ']=='p')$attr='href="javascript:void(0)" onclick="if(event.altKey)parent.i1.lgotopage(\'' . $row_a['name'] . '\');else parent.i2.gotopage(\'' . $row_a['name'] . '\')"';
else if($row_a['typ']=='c')$attr='href="javascript:void(0)" onclick="f1=window.open(\'coding.php?name='.$row_a['name'].'\',\'_blank\',\'toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=1200,height=7777\')";';
else if($row_a['typ']=='y'||$row_a['typ']=='e')$attr='href="javascript:void(0)" onclick="f1=window.open(\'meta'.$row_a['typ'].$isalbum.'.php?typ='.$row_a['typ'].'&name='.$row_a['name'].'\',\'_blank\',\'toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999\')";';
else $attr='href="javascript:void(0)" onclick="f1=window.open(\'upload'.$isalbum.'.php?typ='.$row_a['typ'].'&name='.$row_a['name'].'\',\'_blank\',\'toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999\')";';

echo '<tr onmouseover="this.style.background=\'lightyellow\'" onmouseout="this.style.background=\'#f5f5f5\'" style="text-decoration:none;font-size:11px;background:#f5f5f5;margin-bottom:1px">

<td style="cursor:pointer;color:blue" title="focus in repository" onclick="parent.parent.i3.location.href=\'focus.php?c='.$row_a['name'].'\';">F</td>
<td style="color:gray;word-break:break-all">'.$row_a['typ'].'</span>&nbsp;<span style="word-break:break-all;cursor:pointer;color:blue" '.$attr.' class="last7" id="id'.$row_a['name'].'">'.$row_a['name'].'</span></td>
<td style="color:gray;">'.date('Y-m-d H:i:s',$row_a['lastmut']).'</td>

</tr>
';	  
}
?>
</table>
<script>
function hilite() {
last7=document.getElementsByClassName('last7');
for(i=0;i<last7.length;i++) {
last7[i].style.background='transparent';	

if(parent.parent.i1){
if(parent.parent.i1.document.form1.page){
that='id'+parent.parent.i1.document.form1.page.value;
if(document.getElementById(that))document.getElementById(that).style.background='yellow';
}
}

if(parent.parent.i2){
if(parent.parent.i2.document.form1.page){
that='id'+parent.parent.i2.document.form1.page.value;
if(document.getElementById(that))document.getElementById(that).style.background='yellow';
}
}


}
setTimeout("hilite();",777);
}
setTimeout("hilite();",777);
</script>
</body>
</html>