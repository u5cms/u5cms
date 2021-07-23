<?php
$keepoldgetc = $_GET['c'];
$_GET['c'] = $_GET['n'];
ob_start();
include('index.php');
$ob = ob_get_contents();
ob_end_clean();
$_GET['c'] = $keepoldgetc;
?>