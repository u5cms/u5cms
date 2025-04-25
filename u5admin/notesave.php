<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>
<body id="cbody" bgcolor="red" text="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<?php 
$sql_a="UPDATE formdata SET notes='".mysql_real_escape_string(str_replace(';',',.',$_POST['note']))."' WHERE id='".mysql_real_escape_string($_GET['id'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
	   die('SQL_a-Query failed!...!<p>');
}

?>
<script>
document.getElementById('cbody').style.background="lightgreen";
setTimeout("document.getElementById('cbody').style.background='#ffffff'",1111);
</script>
</body>
</html>
