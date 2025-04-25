<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);
header('X-XSS-Protection: 0');
ini_set('default_charset','iso-8859-1');
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

eval($evaluateifhttpsisinuse);
$httpsisinuse = $httpsisinuse ? true : false;
if ($forcehttpsonfrontend=='yes' && !$httpsisinuse) {
    $url=str_replace($searchforthisinhttpsurl,$replacewiththisinhttpsurl,'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    header('Location: ' . $url);
    echo "<script>location.href='$url'</script>";
    exit;
} else {
    $httpsisinuse = false;
}

if ($quotehandling=='on') include('../quotehandling.inc.php');

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
echo 'SQL_a-Query failed!...!<p>';
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

function def($l1='',$l2='',$l3='',$l4='',$l5='') {
    $l1 = $l1 ?? '';
    $l3 = $l3 ?? '';
    $l2 = $l2 ?? '';
    $l4 = $l4 ?? '';
    $l5 = $l5 ?? '';

    global $lan1na;
    global $lan2na;
    global $lan3na;
    global $lan4na;
    global $lan5na;

       if ($_GET['l'] == $lan1na && trim($l1) != '') return $l1;
  else if ($_GET['l'] == $lan2na && trim($l2) != '') return $l2;
  else if ($_GET['l'] == $lan3na && trim($l3) != '') return $l3;
  else if ($_GET['l'] == $lan4na && trim($l4) != '') return $l4;
  else if ($_GET['l'] == $lan5na && trim($l5) != '') return $l5;
  else {
  if (trim($l1) != '') return $l1;
  else if (trim($l2) != '') return $l2;
  else if (trim($l3) != '') return $l3;
  else if (trim($l4) != '') return $l4;
  else if (trim($l5) != '') return $l5;
  else return $l1;
  }
}

function htmlXspecialchars($that) {
    $that = $that ?? '';
    return htmlspecialchars($that, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
}

function htmlXentities($that) {
    $that = $that ?? '';
    return htmlentities($that, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
}

function htmlX_entity_decode($that) {
    $that = $that ?? '';
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