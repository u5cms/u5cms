<?php require('connect.inc.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>
<body bgcolor="purple" text="orange" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<div style="margin:-2px 0 0 2px;white-space: nowrap;" onmouseover="this.title=this.innerHTML" onclick="alert(this.innerHTML)"><marquee direction="left" behavior="scroll" scrollamount="10" scrolldelay="1">Someone has simply closed the backend instead of logging out correctly. The Japanese exit character at the top right should always be used to log out correctly so that the search index and logins are updated!</marquee></div>
<script>
var audio = new Audio('logoutreminder.mp3');
audio.play();
</script>
</body>
</html>