<?php require_once('connect.inc.php') ?><!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>
<body style="background:black;color:white;margin:0;padding:0">
<div id="welcome" style="width:100%;height:77px">Welcome to u5CMS.<span id="cleanlog" style="display:none">.</span><span id="cleanbackups" style="display:none">.</span></div>
<script>
setTimeout("document.getElementById('welcome').style.display='none'",3333);
</script>
<iframe style="display:none" src="cleanlog.php"></iframe>
<?php
error_reporting(E_ALL);
$disabled_functions = ini_get('disable_functions');
if ($disabled_functions!='') echo '<a onmouseover="this.title=this.innerHTML" style="color:gray;white-space:pre" href="javascript:void(0)" onclick="alert(this.innerHTML)">Please read: disabled function(s)!
THE FOLLOWING FUNCTION(S) IS/ARE DISABLED ON YOUR SERVER\'S PHP INSTALLATION:

'.str_replace(',',', ',$disabled_functions).';

IN CASE eval IS ON THE ABOVE LIST, YOU CANNOT USE PHP CODE IN THE U5CMS EDITOR.
CONTACT YOUR PROVIDER IF YOU NEED TO ACTIVATE FUNCTIONS.
</a>';
?>

</body>
</html>