<?php 
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED); 
require('login.inc.php'); 
require_once ('render.inc.php');
require_once('globalslogin.inc.php');
if($_SERVER['PHP_AUTH_USER']=='')exit;
$_GET['t']=htmlXspecialchars(strip_tags($_GET['t']));

$_SERVER['PHP_AUTH_USER'] = htmlspecialchars($_SERVER['PHP_AUTH_USER'], ENT_NOQUOTES, 'UTF-8');
$_SERVER['PHP_AUTH_USER'] = str_replace('&amp;', '&', $_SERVER['PHP_AUTH_USER']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <?php if (file_exists('r/cssauthuser.css')) echo '<link rel="stylesheet" href="r/cssauthuser.css?' . filemtime('r/cssauthuser.css') . '" type="text/css" title="base"/>' ?>
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0"
      style="font-family:Arial, Helvetica, sans-serif;font-size:14px;font-weight:bold">
<?php
echo '<span class="authuser_welcomemessage">' . $_GET['t'] . '</span> <span class="authuser_username">' . $_SERVER['PHP_AUTH_USER'] . '</span>';
if ($usesessioninsteadofbasicauth != 'no') {
    $_GET['l'] = $lan1na;
    echo ' <button id="1" style="display:none" class="authuser_logoutbutton" onclick="parent.location.href=\'logout.php?u=\'+parent.location.href">' . def($logout_1, $logout_2, $logout_3, $logout_4, $logout_5) . '</button>';
    $_GET['l'] = $lan2na;
    echo ' <button id="2" style="display:none" class="authuser_logoutbutton" onclick="parent.location.href=\'logout.php?u=\'+parent.location.href">' . def($logout_1, $logout_2, $logout_3, $logout_4, $logout_5) . '</button>';
    $_GET['l'] = $lan3na;
    echo ' <button id="3" style="display:none" class="authuser_logoutbutton" onclick="parent.location.href=\'logout.php?u=\'+parent.location.href">' . def($logout_1, $logout_2, $logout_3, $logout_4, $logout_5) . '</button>';
    $_GET['l'] = $lan4na;
    echo ' <button id="4" style="display:none" class="authuser_logoutbutton" onclick="parent.location.href=\'logout.php?u=\'+parent.location.href">' . def($logout_1, $logout_2, $logout_3, $logout_4, $logout_5) . '</button>';
    $_GET['l'] = $lan5na;
    echo ' <button id="5" style="display:none" class="authuser_logoutbutton" onclick="parent.location.href=\'logout.php?u=\'+parent.location.href">' . def($logout_1, $logout_2, $logout_3, $logout_4, $logout_5) . '</button>';
}
?>
<script>
    function automailfill() {
        if (parent.document.u5form) if (parent.document.u5form.youremail) parent.document.u5form.youremail.value = "<?php echo $_SERVER['PHP_AUTH_USER']?>";
    }
    setTimeout("automailfill()", 1111);
<?php if ($usesessioninsteadofbasicauth != 'no') { ?>
    if (parent.location.href.indexOf('&l=<?php echo $lan5na ?>') > 0)document.getElementById('5').style.display = 'inline';
    else if (parent.location.href.indexOf('&l=<?php echo $lan4na ?>') > 0)document.getElementById('4').style.display = 'inline';
    else if (parent.location.href.indexOf('&l=<?php echo $lan3na ?>') > 0)document.getElementById('3').style.display = 'inline';
    else if (parent.location.href.indexOf('&l=<?php echo $lan2na ?>') > 0)document.getElementById('2').style.display = 'inline';
    else document.getElementById('1').style.display = 'inline';
<?php } ?>
</script>
</body>
</html>
