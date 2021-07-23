<?php 
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);

setcookie('pidvesa', $pidvesa , time()+3600*24*365*10,'/'); 

if ($_GET['pvs_p']=='') $_GET['pvs_p']=$_COOKIE['pvs_p'];
setcookie('pvs_p', $_GET['pvs_p'] , time()+3600*24*365*10,'/'); 

if ($_GET['pvs_s']=='') $_GET['pvs_s']=$_COOKIE['pvs_s'];
setcookie('pvs_s', $_GET['pvs_s'] , time()+3600*24*365*10,'/'); 

if ($_GET['pvs_f']=='') $_GET['pvs_f']=$_COOKIE['pvs_f'];
setcookie('pvs_f', $_GET['pvs_f'] , time()+3600*24*365*10,'/'); 

if (isset($_GET['f'])) setcookie('dgf', $_GET['f'] , time()+3600*24*365*10,'/');
else $_GET['f']=$_COOKIE['dgf'];

setcookie('f9focus', 'x' , time()+3600*24*365*10,'/');

if(trim($_GET['f']==''))$_GET['f']='**';
?>