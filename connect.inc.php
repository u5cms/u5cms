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
if (isset($_GET['l']) && !is_null($_GET['l'])) $_GET['l']=htmlspecialchars($_GET['l']);
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

function def($l1, $l2, $l3, $l4 = '', $l5 = '') {
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
$p = base64_encode(hash('sha512',u5toutf8($p), true));
return('{SHA}' . $p);
}

function pwdcookie($p) {
if($invalidatesessionafterminutesofinactivity<1)$invalidatesessionafterminutesofinactivity=600;
$sql_a="DELETE FROM nonces WHERE time<".(time()-60*$invalidatesessionafterminutesofinactivity);
$result_a=mysql_query($sql_a);
$sql_a="SELECT * FROM nonces WHERE user='".mysql_real_escape_string(u5flatidn($_POST['u']))."'";
$result_a=mysql_query($sql_a);
$row_a = mysql_fetch_array($result_a);
$sql_a="UPDATE nonces SET time=".time()." WHERE nonce='".mysql_real_escape_string($row_a['nonce'])."'";
$result_a=mysql_query($sql_a);

    global $mymail, $host, $username, $password, $db, $sticksessiontoip, $sessioncookiehashsalt;
    $installationfingerprint = $mymail . $host . $username . $password . $db;
    if ($sticksessiontoip == 'yes') $installationfingerprint .= $_SERVER['REMOTE_ADDR'];
    return hash('sha512',$row_a['nonce'].$sessioncookiehashsalt . $installationfingerprint . pwdhsh($p));
}

function pwdcookieget($p) {
if($invalidatesessionafterminutesofinactivity<1)$invalidatesessionafterminutesofinactivity=600;
$sql_a="DELETE FROM nonces WHERE time<".(time()-60*$invalidatesessionafterminutesofinactivity);
$result_a=mysql_query($sql_a);
$sql_a="SELECT * FROM nonces WHERE user='".mysql_real_escape_string($_SERVER['PHP_AUTH_USER'])."'";
$result_a=mysql_query($sql_a);
$row_a = mysql_fetch_array($result_a);
$sql_a="UPDATE nonces SET time=".time()." WHERE nonce='".mysql_real_escape_string($row_a['nonce'])."'";
$result_a=mysql_query($sql_a);

    global $mymail, $host, $username, $password, $db, $sticksessiontoip, $sessioncookiehashsalt;
    $installationfingerprint = $mymail . $host . $username . $password . $db;
    if ($sticksessiontoip == 'yes') $installationfingerprint .= $_SERVER['REMOTE_ADDR'];
    return hash('sha512',$row_a['nonce'].$sessioncookiehashsalt . $installationfingerprint . $p);
}