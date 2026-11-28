<?php require_once('connect.inc.php') ?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=WINDOWS-1252" />
</head>
<body>

<?php
require_once('editreadrights.inc.php');

$sql_a="SELECT * FROM formdata WHERE id='".mysql_real_escape_string($_GET['id'])."' AND formname='".mysql_real_escape_string($_GET['n'])."' ORDER BY time DESC";
$result_a=mysql_query($sql_a);


if ($result_a==false) echo 'SQL_a-Query failed!...!<p>';

require('remotefill.inc.php');
?>
<script>
if(parent) if(parent.starterscript)parent.starterscript();
<?php if ($num_a>0) { ?>
if(parent && typeof parent.u5FormDataPrefillReady==='function')parent.u5FormDataPrefillReady();
<?php } ?>
</script>
</body>
</html>