<?php header('Content-Type: text/html; charset=WINDOWS-1252'); ?><?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=WINDOWS-1252" />
<title>...</title>
<?php require('backendcss.php'); ?>
<script>
window.moveTo(0,0);
window.resizeTo(screen.availWidth, screen.availHeight);
function isopener() {
if(!opener)self.close();
setTimeout("isopener()",777);
}
setTimeout("isopener()",777);
</script>
</head>

<frameset rows="85px,*" frameborder="0" border="0">
  <frame src="diffmenu.php" name="menu">
  <frame name="result">
</frameset>

</html>