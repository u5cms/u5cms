<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);

if($_COOKIE['skipayor']!='yes' && file_exists('../fileversions/htarunning.txt') && file_get_contents('../fileversions/htarunning.txt')!=0 && file_get_contents('../fileversions/htarunning.txt')>time()-60*5)die('<center><img src="../upload/spinner.gif"><br><br>Provisioning is running...<br><br>This may take a few minutes.<br><br><a href="javascript:void(0)" onclick="document.cookie=\'skipayor=yes;max-age=10;path=/\';location.href=location.href">I’m in a hurry and take the risk of skipping the provisioning.</a><br><small>As a result, individual items, e.g. images, may not be available for you or may appear defective.</small><script>setTimeout("location.href=location.href",2222)</script>');


ignore_user_abort(true);set_time_limit(36000);
require_once __DIR__ . '/connect.inc.php';

if($_GET['k']!=sha1(date('Ymd').$password.$sessioncookiehashsalt.$_GET['e'])&&$_GET['k']!=sha1(date('Ymd',time()-24*60*60).$password.$sessioncookiehashsalt.$_GET['e']))die('ERROR: Authorization failed.');

if (!isset($_GET['e'])) {
die('ERROR: not enough data.');
}

$safe_mail = strtolower(mysql_real_escape_string(str_replace(',&#0;@&#0;','',base64_decode($_GET['e']))));

$sql_a="UPDATE intranetmembers SET members = CONCAT(members, '$safe_mail', ',') WHERE CONCAT(',', members) NOT LIKE CONCAT('%,', '$safe_mail', ',%')";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
    die('Login failed. Unable to add user to intranet members list');
}

// recalculate login field of intranet resources
$scriptFolder = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) ? 'https://' : 'http://';
$scriptFolder .= $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['REQUEST_URI']));
if(mysql_affected_rows()>0)die('<center><img src="../upload/spinner.gif"><center><iframe src="../htaccess.php" style="display:none"></iframe><script>setTimeout("location.href=location.href",3333)</script>');

if($_COOKIE['skipayor']!='yes' && file_exists('../fileversions/htarunning.txt') && file_get_contents('../fileversions/htarunning.txt')!=0 && file_get_contents('../fileversions/htarunning.txt')>time()-60*5)die('<center><img src="../upload/spinner.gif"><br><br>Provisioning is running...<br><br>This may take a few minutes.<br><br><a href="javascript:void(0)" onclick="document.cookie=\'skipayor=yes;max-age=10;path=/\';location.href=location.href">I’m in a hurry and take the risk of skipping the provisioning.</a><br><small>As a result, individual items, e.g. images, may not be available for you or may appear defective.</small><script>setTimeout("location.href=location.href",2222)</script>');
else echo '<script>parent.loginsave();</script>';