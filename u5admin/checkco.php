<?php require('connect.inc.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
</head>
<body>
<?php
$sql_a="SELECT operator,ip,lastmut FROM resources WHERE name='".mysql_real_escape_string($_GET['name'])."'";

$result_a=mysql_query($sql_a);
if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}
$row_a = mysql_fetch_array($result_a);

if ($_GET['coco']<$row_a['lastmut']) {
$conflict='(!)';
?>
<script>
msgconflict='Conflict with <?php echo htmlXspecialchars($row_a['operator']) ?> who has saved a new version of <?php echo $_GET['name']?> during your editing session (at <?php echo date('Y-m-d H:i:s',$row_a['lastmut'])?>).\n\nPlease save your page immediately. You automatically will receive instructions how to resolve the conflict afterwards.';
</script>

<?php 
if ($_GET['changes']<1) echo '<script>parent.location.href=parent.location.href;</script>';
else echo '<script>alert(msgconflict)</script>';

}
?>

</body>
</html>