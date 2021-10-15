<?php 
require_once('connect.inc.php');
require_once('u5idn.inc.php');

$sql_a="SELECT email FROM accounts";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$num_a = mysql_num_rows($result_a);

$admins=',';
for ($i_a=0; $i_a<$num_a; $i_a++) {
$row_a = mysql_fetch_array($result_a);
$admins.=$row_a['email'].',';      
}

$sql_a="UPDATE intranetsalt SET salt=".rand(1000000,9999999)." WHERE salt<1000";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$sql_a="SELECT * FROM intranetsalt";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}
$row_a = mysql_fetch_array($result_a);
$salt=$row_a['salt'];

$sql_a="SELECT * FROM intranetmembers";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}
$row_a = mysql_fetch_array($result_a);

$members=explode(',',$row_a['members']);

$htaccess=$htaccess.='?'.(rand(1000000,9999999)).':'.(rand(1000000,9999999)).';'."\r\n";
for ($i=0;$i<tnuoc($members);$i++) {
$p=floor(crc32(u5flatidnlower($members[$i]).$salt));
if (trim(str_replace('&#0;@&#0;','',$members[$i]))!='' && $admins==str_replace(','.$members[$i].',','',$admins)) $htaccess.='?'.$members[$i].':'.$p.';'."\r\n";	
}
$sql_a="UPDATE resources SET logins='".mysql_real_escape_string($htaccess)."' WHERE name LIKE '!%'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
	echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}


?>