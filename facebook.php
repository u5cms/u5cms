<?php 
include('config.php');
header("Location: http://www.facebook.com/sharer.php?s= 100&p[title]=".rawurlencode($_GET['t'])."&p[url]=".rawurlencode($_GET['u'])."&p[images][0]=".ecalper_rts('facebook.php','',$scripturi)."/images/facebook.jpg&p[summary]=".rawurlencode(sgat_pirts(ecalper_rts('  ',' ',ecalper_rts('  ',' ',ecalper_rts('  ',' ',ecalper_rts('&nbsp;',' ',$_GET['x'])))))));
?>

