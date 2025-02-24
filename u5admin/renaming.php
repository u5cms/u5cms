<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>renaming <?php echo $_GET['n']?>, please wait...</title>
<?php require('backendcss.php'); ?></head>
<body>
<?php
$sql_a = "SELECT name FROM resources WHERE deleted!=1 AND name='" . mysql_real_escape_string($_GET['n']) . "'";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
$num_a = mysql_num_rows($result_a);
if ($num_a > 0) echo('<script>setTimeout("location.href=location.href",999);</script>');
else echo('<script>if(opener)opener.top.location.href=opener.top.location.href;self.close()</script>');
?>
<br><br><br><br>
<center>
<?php echo date('Y-m-d H:i:s') ?><br><br>Renaming of <i><?php echo $_GET['n'] ?></i> in progress.<br><br>This takes a few minutes for large pages.<br><br>It is recommended to wait and not to continue working in the backend for so long.
</center>
</body>
</html>