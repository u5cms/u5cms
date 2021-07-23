<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>
<body bgcolor="red" text="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<div style="margin:-2px 0 0 2px;white-space: nowrap;" onmouseover="this.title=this.innerHTML" onclick="alert(this.innerHTML)"><?php echo $_GET['n']?></div>
<script>
parent.i3.location.reload();
if(parent.i1.document.getElementById('savebutton').innerHTML.indexOf('*')>0 || parent.i2.document.getElementById('savebutton').innerHTML.indexOf('*')>0) {
setTimeout("location.href='notsaved.php';",3333);
}
</script>
</body>
</html>
