<?php 
require_once ('connect.inc.php');
require_once('u5idn.inc.php');
$sql_a="SELECT email,pw FROM accounts";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$num_a = mysql_num_rows($result_a);


$alladmins='';
for ($i_a=0; $i_a<$num_a; $i_a++) {
$row_a = mysql_fetch_array($result_a);
$alladmins.=($row_a['email']).':'.$row_a['pw']."\r\n";
}
//$alladmins=html_entity_decode(utf8_encode(trim($alladmins)), ENT_COMPAT, 'UTF-8');
$alladmins=u5toutf8($alladmins);
file_put_contents("../fileversions/.htpasswd",$alladmins);
include('../config.php');
if(!isset($u5cmsrealm))$u5cmsrealm='LOGIN';
$htpasswd="AuthName \"".$u5cmsrealm."\"
AuthType Basic
AuthUserFile \"".str_replace('/htaccess.php','',str_replace('\\','/',$_SERVER['SCRIPT_FILENAME']))."/fileversions/.htpasswd\"
Require valid-user
";

file_put_contents("../fileversions/.htaccess",$htpasswd);


?>