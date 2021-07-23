<?php 
include('config.php');
header("Location: http://www.facebook.com/sharer.php?s= 100&p[title]=".rawurlencode($_GET['t'])."&p[url]=".rawurlencode($_GET['u'])."&p[images][0]=".str_replace('facebook.php','',$scripturi)."/images/facebook.jpg&p[summary]=".rawurlencode(strip_tags(str_replace('  ',' ',str_replace('  ',' ',str_replace('  ',' ',str_replace('&nbsp;',' ',$_GET['x'])))))));
?>

