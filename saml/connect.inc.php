<?php 
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED); 
header('X-XSS-Protection: 0');
ini_set('default_charset','latin1');
ini_set('magic_quotes_gpc', 'Off');
require_once('../mysql.php');
include('../config.php');

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
