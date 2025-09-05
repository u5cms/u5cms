<?php 
require_once('connect.inc.php');

if ($_SERVER['PHP_AUTH_PW']=='FirstPassword7') echo '<script>setTimeout("alert(\'In case you are still using the password\\\n\\\nFirstPassword7\\\n\\\nthis is not secure because it is published in the manual. Please make (if not yet done) a new user or change the password in PIDEVSA\\\\\'s A.\')",60000)</script>';

if (strtolower($_SERVER['PHP_AUTH_USER'])!='temp') {

$sql_a="SELECT * FROM accounts WHERE email!='Temp' AND lastused>0 AND email LIKE '%@%'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
	echo 'SQL_a-Query failed!...!<p>';
}

$num_a = mysql_num_rows($result_a);

if($num_a>0) {

$sql_a="SELECT * FROM accounts WHERE email='Temp'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
	echo 'SQL_a-Query failed!...!<p>';
}

$num_a = mysql_num_rows($result_a);

if($num_a>0) {

$sql_a="UPDATE accounts SET accadmin=1";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
	echo 'SQL_a-Query failed!...!<p>';
}

$sql_a="DELETE FROM accounts WHERE email='Temp'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
	echo 'SQL_a-Query failed!...!<p>';
}
}
}
}

if($allowu5pwprotectedpageashomepage!='yes') {
$sql_a="SELECT name, logins FROM resources WHERE deleted!=1 AND ishomepage=1";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!...!<p>';
$row_a = mysql_fetch_array($result_a);
$name=$row_a['name'];
if($row_a['name'][0]=='!')echo '<script>setTimeout("alert(\'You have defined the page '.$name.' as your homepage (mainpage). This page is protected with logins because it is an intranet page (indication: first character of its name is an exclamation mark). However, because it is defined as homepage it is also the fallback page and therefore accessible without login. It is better not do define an intranet page as homepage.\')",11111);</script>';
else if(trim($row_a['logins'])!='')echo '<script>setTimeout("alert(\'You have defined the page '.$name.' as your homepage (mainpage). This page is protected with logins (you have entered the usernames and passwords manually (i-button, Publicity status)). However, because it is defined as homepage it is also the fallback page and therefore accessible without login. It is better do define a page without logins as homepage.\')",11111);</script>';
}

if (file_exists('../r/runonce.php')) {
include('../r/runonce.php');
include('endrunonce.php');
}
?>
<iframe frameborder="0" width="0" height="0" style="display:none" src="writable.php"></iframe>