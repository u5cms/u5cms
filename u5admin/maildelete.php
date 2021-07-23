<?php ignore_user_abort(true); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>
<body>
<span id="wait">deleting, please wait...</span>
<div id="recipients"></div>
<?php 
require('connect.inc.php');


$sql_b="UPDATE mailing SET maildeleted=1 WHERE id=".mysql_real_escape_string($_GET['id']);
$result_b=mysql_query($sql_b);

if ($result_b==false) {
echo 'SQL_b-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_b.'</font><p>';
}

echo '<script>
parent.me.location.href="mailingeditorpre.php?n='.$_GET['n'].'&id='.$id.'&t='.$_GET['t'].'";
location.href="mailinglist.php?n='.$_GET['n'].'&t='.$_GET['t'].'";
</script>';
?>
</body>
</html>
