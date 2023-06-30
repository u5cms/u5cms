<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>saving...</title>
<?php require('backendcss.php'); ?></head>
<body id="body" bgcolor="#009900">
<?php 
if($definetitelfixumRqHIADRI!='no')require_once('accadmin.inc.php'); 


$sql_a="UPDATE titlefixum SET `1`='".mysql_real_escape_string($_POST['1'])."', `2`='".mysql_real_escape_string($_POST['2'])."', `3`='".mysql_real_escape_string($_POST['3'])."', `4`='".mysql_real_escape_string($_POST['4'])."', `5`='".mysql_real_escape_string($_POST['5'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>');
}

trxlog('titlefixum');
?>
<script>
if(opener)if(opener.parent)if(opener.parent.save)opener.parent.save.location.href='done.php?n=saved title fixum';
  self.close();
</script>

</body>
</html>
