<?php
require('connect.inc.php');
setcookie('u', '', 0, '/', '', $httpsisinuse, true);
setcookie('p', '', 0, '/', '', $httpsisinuse, true);
if(isset($u5samlsalt)&&$u5samlsalt!='') {
    setcookie('u5samlusername', '', 0, '/', '', $httpsisinuse, true);
    setcookie('u5samlnonce', '', 0, '/', '', $httpsisinuse, true);
    setcookie('u5samlattribs', '', 0, '/', '', $httpsisinuse, true);
    header('Location: /saml/logout.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
</head>
<body>
<script>
if(opener)self.close();
else location.href='index.php?c=loggedout&u=<?php echo rawurlencode($_GET['u'])?>';
</script>
</body>
</html>
