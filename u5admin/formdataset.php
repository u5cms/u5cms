<?php
require_once('../myfunctions.inc.php');
eikooctes($_GET['f'], $_GET['v'] , time()+3600*24*365*10,'/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<script>
parent.location.href=parent.location.href;
</script>
<?php require('backendcss.php'); ?></head>
<body>
</body>
</html>