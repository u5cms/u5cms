<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);
require('../config.php');
setcookie('u5samlusername', trim('temp'), 0, '/', '', $httpsisinuse, true);
setcookie('u5samlnonce', $u5samlnonce, 0, '/', '', $httpsisinuse, true);
echo '<script>location.href="../loginsave.php?u='.rawurlencode($_GET['u']).'"</script>';
?>
