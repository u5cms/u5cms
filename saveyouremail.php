<?php
require_once __DIR__.'/request.inc.php';
setcookie("youremail", ($_GET['youremail'] ?? ''), time()+3600*24*365*10, '/');
?>