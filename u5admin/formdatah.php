<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>form data</title>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
	parent.close();
})
</script>
<?php require('backendcss.php'); ?></head>

<body id="body">
<h1>Form data <span style="font-size:40%"><a href="formdata.php">order by name</a></span></h1>
<table>
<?php 
require('../config.php');
if ($formdataRqHIADRI!='no') require('accadmin.inc.php');

$sql_a="SELECT formname, max(time) as maxtime FROM formdata WHERE status!=5 OR (status=5 AND lastmut>".(time()-30*24*60*60).") GROUP BY formname ORDER BY maxtime DESC";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$num_a = mysql_num_rows($result_a);


for ($i_a=0; $i_a<$num_a; $i_a++) {
   $row_a = mysql_fetch_array($result_a);
echo '<tr style="background:#eeeeee" onmouseover="this.style.background=\'lightyellow\'"  onmouseout="this.style.background=\'#eeeeee\'"><td>

<a target="_blank" href="formdata2.php?n='.$row_a['formname'].'&s=1">html</a>&nbsp;
<a target="_blank" href="../chart.php?n='.$row_a['formname'].'&s=1">chart</a>&nbsp;
<a target="_blank" href="formdatamail.php?n='.$row_a['formname'].'&s=1">mail</a>&nbsp;
<a target="_blank" href="formdata2csv.php?n='.$row_a['formname'].'&s=0">xls</a>&nbsp;

'.$row_a['formname'].'</td><td><iframe frameborder="0" height="22" width="777" scrolling="no" src="formdatanew.php?n='.$row_a['formname'].'"></iframe></td>';      
}
?>
</table>
<iframe frameborder="0" height="1" width="1" src="uniquenames.php"></iframe>
<p>
<small><a target="_blank" href="import.php?n=mydata">import from Excel</a>
<br /><br />
<small>To generate serial mails with many recipients use view "mail"</small>
</small>
</body>
</html>
