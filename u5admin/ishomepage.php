<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>define homepage</title>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
	cchanges=0;document.form1.submit();
})
</script>
<?php require('backendcss.php'); ?></head>

<body>
<h1>Define homepage</h1>
<form onsubmit="cchanges=0" name="form1" method="post" action="ishomepage2.php">
<p>Define which page shall be displayed as your homepage (mainpage).</p>

<?php 
$sql_a="SELECT * FROM resources WHERE typ='p' AND deleted!=1 AND hidden=0 AND name!='-' ORDER BY name";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$num_a = mysql_num_rows($result_a);


echo '<select onchange="changes()" name="name">';
echo '<option value="'.$row_a['name'].'" '.$selected.'>'.$row_a['name'].'</option>';      
for ($i_a=0; $i_a<$num_a; $i_a++) {
$row_a = mysql_fetch_array($result_a);
$selected='';
if ($row_a['ishomepage']==1) $selected='selected="selected"';
echo '<option value="'.$row_a['name'].'" '.$selected.'>'.$row_a['name'].'</option>';      
}
echo '</select>';

?>
<input title="[Ctrl+s]" type="submit" name="button" value="save &amp; close" /><span class="asterisk" style="display:none;color:red">*</span>
  <?php include('metachg.inc.php') ?><script>initchanges()</script>
</form>
</script>
<?php include('selfclose.inc.php')?>
</body>
</html>
