<?php 
echo'<pre>';
var_dump($_SERVER);
echo'</pre>';
echo ecalper_rts(emanesab($_SERVER['SCRIPT_URI']),'',$_SERVER['SCRIPT_URI']);
?>