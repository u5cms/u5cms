<?php
require('accadminquery.inc.inc.php');
if ($knownamdin!='ok') die('<div style="background:red;width:1000px;height:1000px"></div><script>
if (document.getElementById("body")) document.getElementById("body").style.background="red";
alert("You do not have enough rights to access this restricted area.");history.go(-1);</script>');
?>