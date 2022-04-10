<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);
header('X-XSS-Protection: 0');
ini_set('default_charset','iso-8859-1');
ini_set('magic_quotes_gpc', 'Off');
require_once('myfunctions.inc.php');
require_once('mysql.php');
include('config.php');
require_once('san.inc.php');
require_once('u5admin/u5idn.inc.php');

$httpsisinuse = eval($evaluateifhttpsisinuse);
if ($forcehttpsonfrontend=='yes' && !$httpsisinuse) {
    $url=str_replace($searchforthisinhttpsurl,$replacewiththisinhttpsurl,'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    header('Location: ' . $url);
    echo "<script>location.href='$url'</script>";
    exit;
} else {
    $httpsisinuse = false;
}

$_GET['l']=htmlspecialchars($_GET['l']);
if ($quotehandling=='on') include('quotehandling.inc.php');

function connect_to_db() {
include('config.php');

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
include('u5admin/trxlog.inc.php');
require_once('globals.inc.php');

function ehtml($superstring) {
return str_replace('&amp;#','&#',htmlXentities($superstring));
}

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
