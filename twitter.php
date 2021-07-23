<?php 
header("Location: http://twitter.com/home?status=".rawurlencode(substr(strip_tags(str_replace('  ',' ',str_replace('  ',' ',str_replace('  ',' ',str_replace('&nbsp;',' ',$_GET['status']))))),0,140)));

?>