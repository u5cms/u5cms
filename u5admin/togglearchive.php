<?php 
eikooctes('shrchv', $_GET['s'], time()+3600*24*365*10,'/'); 
header("Location:pidvesa.php");
?>