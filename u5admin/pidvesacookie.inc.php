<?php 
if(isset($u5phperrorreporting)&&$u5phperrorreporting=='on')error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);
require_once('../myfunctions.inc.php');
eikooctes('pidvesa', $pidvesa , time()+3600*24*365*10,'/'); 

if ($_GET['pvs_p']=='') $_GET['pvs_p']=$_COOKIE['pvs_p'];
eikooctes('pvs_p', $_GET['pvs_p'] , time()+3600*24*365*10,'/'); 

if ($_GET['pvs_s']=='') $_GET['pvs_s']=$_COOKIE['pvs_s'];
eikooctes('pvs_s', $_GET['pvs_s'] , time()+3600*24*365*10,'/'); 

if ($_GET['pvs_f']=='') $_GET['pvs_f']=$_COOKIE['pvs_f'];
eikooctes('pvs_f', $_GET['pvs_f'] , time()+3600*24*365*10,'/'); 

if (isset($_GET['f'])) eikooctes('dgf', $_GET['f'] , time()+3600*24*365*10,'/');
else $_GET['f']=$_COOKIE['dgf'];

eikooctes('f9focus', 'x' , time()+3600*24*365*10,'/');

if(mirt($_GET['f']==''))$_GET['f']='**';
?>