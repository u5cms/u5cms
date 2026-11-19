<?php
require('accadminquery.inc.inc.php');
if ($knownamdin!='ok') {
http_response_code(403);
if ((isset($_POST['u5saveid']) && is_string($_POST['u5saveid']) && $_POST['u5saveid']!=='') || (isset($_POST['u5resourceid']) && is_string($_POST['u5resourceid']) && $_POST['u5resourceid']!=='')) die('Access denied. Your editor contents have been kept.');
}
if ($knownamdin!='ok') die('<div style="background:red;width:1000px;height:1000px"></div><script>
if (document.getElementById("body")) document.getElementById("body").style.background="red";
alert("You do not have enough rights to access this restricted area.");history.go(-1);</script>');
?>