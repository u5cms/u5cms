<?php require_once('connect.inc.php'); ?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
</head>
<body>

<?php
require_once('login.inc.php');

if($_GET['id']>0) $isid="AND datacsv LIKE '·". mysql_real_escape_string($_GET['id']).";%'";
else $isid='';

require('monofill.privileges.php');

$sql_a="SELECT * FROM formdata WHERE $mfwhereclause AND formname='".mysql_real_escape_string($_GET['n'])."' AND authuser!='' AND authuser='".mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."' $isid ORDER BY id DESC";
$result_a=mysql_query($sql_a);

if ($result_a==false) echo 'SQL_a-Query failed!...!<p>';

require('remotefill.inc.php');
?>
<script>
parent.document.u5form.action=parent.document.u5form.action+'&o=<?php echo rawurlencode(pwdhsh($_SERVER['PHP_AUTH_USER'])) ?>';
if(parent)if(parent.starterscript)parent.starterscript();
</script>
</body>
</html>