<?php
require('connect.inc.php');
$scriptFolder = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) ? 'https://' : 'http://';
$scriptFolder .= $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['REQUEST_URI']));
file_get_contents($scriptFolder . '/htaccess.php');
?>