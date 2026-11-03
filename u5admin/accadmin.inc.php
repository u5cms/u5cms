<?php
require('accadminquery.inc.inc.php');
if ($knownamdin!='ok') http_response_code(403);
if ($knownamdin!='ok') die('<script>
if (document.getElementById("body")) document.getElementById("body").style.background="red";
alert("You do not have enough rights to access this restricted area.");parent.close();</script>');
?>