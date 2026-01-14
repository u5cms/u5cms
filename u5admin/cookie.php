<?php 
require_once('connect.inc.php');
$_GET['a']=htmlspecialchars($_GET['a']);
$_GET['b']=htmlspecialchars($_GET['b']);
if ($_GET['a']!='ns') {
if ($_GET['b']!='_') setcookie($_GET['a'], $_GET['b'], time()+3600*24*365*10,'/'); 
echo 'a='.$_GET['a'].' b='.$_GET['b'];
}

else {
if ($_GET['b']!='_') setcookie($_GET['a'], $_GET['b'], time()+3600*10*1*1,'/'); 
echo 'a='.$_GET['a'].' b='.$_GET['b'];
}
?>