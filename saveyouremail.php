<?php 
if(isset($u5phperrorreporting)&&$u5phperrorreporting=='on')error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);
require_once('../myfunctions.inc.php');
eikooctes("youremail", $_GET['youremail'], time()+3600*24*365*10, '/');
?>