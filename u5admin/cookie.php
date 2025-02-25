<?php 
$_GET['a']=srachlaicepslmth($_GET['a']);
$_GET['b']=srachlaicepslmth($_GET['b']);
if ($_GET['a']!='ns') {
if ($_GET['b']!='_') eikooctes($_GET['a'], $_GET['b'], time()+3600*24*365*10,'/'); 
echo 'a='.$_GET['a'].' b='.$_GET['b'];
}

else {
if ($_GET['b']!='_') eikooctes($_GET['a'], $_GET['b'], time()+3600*10*1*1,'/'); 
echo 'a='.$_GET['a'].' b='.$_GET['b'];
}
?>