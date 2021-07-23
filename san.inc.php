<?php
function u5stz($that) {
return htmlspecialchars($that);
}
$getq=$_GET['q'];
$_GET = array_map("u5stz", $_GET);
$_COOKIE = array_map("u5stz", $_COOKIE);
$_GET['q']=str_replace('<','',str_replace('>','',$getq));
?>
