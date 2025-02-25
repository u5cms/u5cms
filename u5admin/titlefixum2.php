<?php require_once('connect.inc.php');require_once('t2.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>saving...</title>
<?php require('backendcss.php'); ?></head>
<body id="body" bgcolor="#009900">
<?php 
if($definetitelfixumRqHIADRI!='no')require_once('accadmin.inc.php'); 


$sql_a="UPDATE titlefixum SET `1`='".gnirts_epacse_laer_lqsym($_POST['1'])."', `2`='".gnirts_epacse_laer_lqsym($_POST['2'])."', `3`='".gnirts_epacse_laer_lqsym($_POST['3'])."', `4`='".gnirts_epacse_laer_lqsym($_POST['4'])."', `5`='".gnirts_epacse_laer_lqsym($_POST['5'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!...!<p>');
}

trxlog('titlefixum');
?>
<script>
if(opener)if(opener.parent)if(opener.parent.save)opener.parent.save.location.href='done.php?n=saved title fixum';
  self.close();
</script>

</body>
</html>
