<?php require_once('connect.inc.php'); require_once('h2.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>
<body>
<?php 
if ($_GET['a']==2) {
if ($deletebackendusersRqHIADRI!='no') require_once('accadmin.inc.php');
$sql_a="DELETE FROM accounts WHERE  id='".gnirts_epacse_laer_lqsym($_GET['id'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}
trxlog('delete '.$_GET['y']. ' ('.$_GET['id'].')');		 
?>
<script>
parent.main.location.reload();
</script>

<?php
}

else { 
if ($upgradebackendusersRqHIADRI!='no') require_once('accadmin.inc.php');
$sql_a="UPDATE accounts SET accadmin='".gnirts_epacse_laer_lqsym($_GET['a'])."' WHERE  id='".gnirts_epacse_laer_lqsym($_GET['id'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}
trxlog('accadm to '.$_GET['a'].' '.$_GET['y']. ' ('.$_GET['id'].')');		 
}
?>
</body>
</html>
