<?php 
echo'<pre>';
var_dump($_SERVER);
echo'</pre>';
echo str_replace(basename($_SERVER['SCRIPT_URI']),'',$_SERVER['SCRIPT_URI']);
?>