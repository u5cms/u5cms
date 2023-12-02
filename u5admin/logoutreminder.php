<?php require('connect.inc.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>
<body bgcolor="purple" text="orange" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<div style="margin:-2px 0 0 2px;white-space: nowrap;" onmouseover="this.title=this.innerHTML" onclick="alert(this.innerHTML)"><marquee direction="left" behavior="scroll" scrollamount="10" scrolldelay="1">Last time you simply closed the backend instead of logging out correctly. You should log out correctly by clicking the Japanese exit sign at the top right. By logging out correctly, the search index and logins will be updated!</marquee></div>
<script>
var audio = new Audio('logoutreminder.mp3');
audio.play();
</script>
</body>
</html>