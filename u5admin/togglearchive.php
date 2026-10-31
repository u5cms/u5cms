<?php
require_once __DIR__.'/../request.inc.php';
setcookie('shrchv', ($_GET['s'] ?? ''), time()+3600*24*365*10,'/');
header("Location:pidvesa.php");
?>