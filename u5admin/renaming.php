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
else {
?>
<script>
if(opener) {
    if ('<?php echo $_GET['newname'] ?>' == '') opener.parent.save.location.href = 'done.php?n=renamed <?php echo $_GET['name']?> to <?php echo $_GET['nn']?>';

    if ('<?php echo $row_a['typ']?>' == 'p' && '<?php echo $_GET['name'] ?>' == opener.parent.i1.document.form1.page.value) opener.parent.i1.gotopage('<?php echo $_GET['nn'] ?>');
    else opener.parent.i1.gotopage(opener.parent.i1.document.form1.page.value);
    if ('<?php echo $row_a['typ']?>' == 'p' && '<?php echo $_GET['name'] ?>' == opener.parent.i2.document.form1.page.value) opener.parent.i2.gotopage('<?php echo $_GET['nn'] ?>');
    else opener.parent.i2.gotopage(opener.parent.i2.document.form1.page.value);
    opener.focus();
}
self.close();
</script>
<?php	
}
?>
<br><br><br><br>
<center>
<?php echo date('Y-m-d H:i:s') ?><br><br>Renaming of <i><?php echo $_GET['n'] ?></i> in progress.<br><br>This takes a few minutes for large sites.<br><br>It is recommended to wait and not to continue working in the backend for so long.<br><br>PLEASE WAIT AND DO NOT CLOSE THIS WINDOW!
</center>
</body>
</html>