<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php
if($viewtrxlistRqHIADRI!='no')require_once('accadmin.inc.php');
if($_GET['more']==1)$limit=100000;
else $limit='2000';
?>
<title>last <?php echo $limit ?> transactions</title>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
	self.close();
})
</script>
<?php require('backendcss.php'); ?></head>
<body id="body">
<h1>Last <?php echo $limit ?> transactions</h1>
<?php
$sql_a="SELECT * FROM trxlog ORDER BY ts DESC LIMIT 0,$limit";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}

$num_a = mysql_num_rows($result_a);

echo "<table cellspacing=3><tr><th></th><th>User</th><th>IP</th><th>Time</th><th>Transaction</th></tr>";

for ($i_a=0; $i_a<$num_a; $i_a++) {
$row_a = mysql_fetch_array($result_a);
if($i_a==2000)$more='name="more"';
else $more='';
echo '
<tr bgcolor="#eeeeee">
<td>
<a '.$more.'></a>'.$row_a['id'].'
</td>
<td>
'.$row_a['operator'].'
</td>
<td>
'.$row_a['ip'].'
</td>
<td>
'.date('Y.m.d H:i:s',$row_a['ts']).'
</td>
<td>
'.$row_a['trx'].'
</td>
</tr>
';
}
echo "<table>";
?>
</script>

<script>
window.resizeTo(800, screen.availHeight);
</script>
<?php include('selfclose.inc.php')?>
<?php if($_GET['more']!=1&&$num_a>1999)echo'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="trxlist.php?more=1#more">more</a><br><br><br>'?>
</body>
</html>
