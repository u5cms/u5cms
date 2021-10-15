<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);
header('X-XSS-Protection: 0');
ini_set('default_charset','latin1');
require_once('../myfunctions.inc.php');
require_once('../mysql.php');
$_GET['name']=basename($_GET['name']);
$_POST['name']=basename($_POST['name']);
$_GET['name']=str_replace(chr(0),'',$_GET['name']);
$_POST['name']=str_replace(chr(0),'',$_POST['name']);
$_GET['l']=htmlspecialchars($_GET['l']);
include('../config.php');
require_once('../san.inc.php');
require_once('u5idn.inc.php');
if($forcehttpsonbackend=='yes') {
eval($evaluateifhttpsisinuse);
if (!$httpsisinuse) {
$url=str_replace($searchforthisinhttpsurl,$replacewiththisinhttpsurl,'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
header('Location: ' . $url);
echo "<script>location.href='$url'</script>";
exit;
}
}

if ($quotehandling=='on') include('../quotehandling.inc.php');

// Add U5ROOT_PATH/lib to include_path
set_include_path(get_include_path() . PATH_SEPARATOR . U5ROOT_PATH . DIRECTORY_SEPARATOR . 'lib');

require_once('Zend/Loader.php');

spl_autoload_register(function ($class) {
    Zend_Loader::loadClass($class);
});

function connect_to_db() {
include('../config.php');

   $connected = @mysql_connect($host,$username,$password);

   if (!$connected) {
       die('Not connected to DB. Help: '.$mymail);
       exit;
       }

   $dbready = @mysql_select_db($db);

   if (!$dbready) {
      die('DB not ready. Help: '.$mymail);
      }

$sql_a="SET NAMES latin1";
$result_a=mysql_query($sql_a);
if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

}
connect_to_db();
if (basename($_SERVER['PHP_SELF'])!='htaccess.php') {
require_once('trxlog.inc.php');
require_once('usercheck.inc.php');
}

function ehtml($superstring) {

$search= array("&amp;#", "!4--!", "!--4!", "!3--!", "!--3!", "!2--!","!--2!");
$replace=array("&#",     "[[[[",  "]]]]",  "[[[",   "]]]",   "[[", "]]");

return str_replace($search,$replace,htmlXentities($superstring));
}

require_once('../globals.inc.php');

function def($d, $e, $f) {
global $lan1na;
global $lan2na;
global $lan3na;
       if ($_GET['l'] == $lan1na && trim($d) != '') return $d;
  else if ($_GET['l'] == $lan2na && trim($e) != '') return $e;
  else if ($_GET['l'] == $lan3na && trim($f) != '') return $f;
  else {
  if (trim($d) != '') return $d;
  else if (trim($e) != '') return $e;
  else if (trim($f) != '') return $f;
  else return $d;
  }
}

function htmlXspecialchars($that) {
return htmlspecialchars($that, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
}

function htmlXentities($that) {
return htmlentities($that, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
}

function htmlX_entity_decode($that) {
return html_entity_decode($that, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
}

function pwdhsh($p) {
$p = base64_encode(sha1(u5toutf8($p), true));
return('{SHA}' . $p);
}

function pwdcookie($p) {
    global $mymail, $host, $username, $password, $db, $sticksessiontoip, $sessioncookiehashsalt;
    $installationfingerprint = $mymail . $host . $username . $password . $db;
    if ($sticksessiontoip == 'yes') $installationfingerprint .= $_SERVER['REMOTE_ADDR'];
    return sha1($sessioncookiehashsalt . $installationfingerprint . pwdhsh($p));
}

function pwdcookieget($p) {
    global $mymail, $host, $username, $password, $db, $sticksessiontoip, $sessioncookiehashsalt;
    $installationfingerprint = $mymail . $host . $username . $password . $db;
    if ($sticksessiontoip == 'yes') $installationfingerprint .= $_SERVER['REMOTE_ADDR'];
    return sha1($sessioncookiehashsalt . $installationfingerprint . $p);
}

