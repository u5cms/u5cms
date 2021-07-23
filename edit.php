<?php 
require_once('connect.inc.php');
require_once('globals.inc.php');
if ($_GET['c']=='' && $_GET['n']!='') $_GET['c']=$_GET['n'];
if ($_GET['c']=='') {
$sql_a="SELECT * FROM resources WHERE deleted!=1 AND ishomepage=1";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$row_a = mysql_fetch_array($result_a);
$_GET['c']=$row_a['name'];
}      

     if ($_GET['l']==$lan3na) $_GET['l']='f';
else if ($_GET['l']==$lan2na) $_GET['l']='e';
else  $_GET['l']='d';

setcookie('i2_l', $_GET['l'], time()+3600*24*365*10,'/'); 
if($_COOKIE['i1_l']=='')setcookie('i1_l', 'P', time()+3600*24*365*10,'/'); 
setcookie('i1_p', $_GET['c'], time()+3600*24*365*10,'/'); 
setcookie('i2_p', $_GET['c'], time()+3600*24*365*10,'/'); 

header("Location: u5admin/");
?>