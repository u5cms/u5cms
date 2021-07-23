<?php require_once('connect.inc.php'); ?>
<?php $pidvesa = 'a'; $donotshowtogglearchive=1;
setcookie('pidvesa', $pidvesa, time() + 3600 * 24 * 365 * 10, '/'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <script src="shortcut.js"></script>
    <?php include('repoctrls.inc.php'); ?>
    <?php require('backendcss.php'); ?></head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<?php include('pidvesanavi.inc.php'); ?>
<h1>Account</h1>
<button
    onClick="f1=window.open('pwd.php','_blank','toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999');">
    change your password
</button>
<p>
    <button
        onClick="f1=window.open('acc.php','_blank','toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999');">
        manage backend users
    </button>
<p>
    <button
        onClick="f1=window.open('trxlist.php','_blank','toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999');">
        view transaction log
    </button>
<p>
    <button
        onClick="f1=window.open('intranetmembers.php','_blank','toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999');">
        manage intranet members
    </button>
</body>

</html>
