<?php require_once('connect.inc.php'); $donotshowtogglearchive=1; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<script><?php echo "location.href='" . preg_replace('/[^A-Za-z0-9_!-]/', '', $_COOKIE['pidvesa'] ?? '') . ".php';"; ?></script>
<?php require('backendcss.php'); ?></head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<?php include('pidvesanavi.inc.php');?>
<h1>Welcome!</h1>
<p>
Choose from the above radio-menu (e. g. to see a list of pages 'P' or media like images 'I'). 
</p>
<p>
'A' is your account where you can change the password.
</p>
<p>
In the two left columns you can edit pages and switch languages.</p>
<p>Please consult the <a href="u5shortreference.pdf" target="_blank">short reference</a> which is always available on the question mark in the upper right corner.</p>
</body>
</html>