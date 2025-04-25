<?php 
require_once('render.inc.php');
$sql_a="SELECT * FROM loginglobals";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}

$row_a = mysql_fetch_array($result_a);

$logintitle_1=ehtml($row_a['logintitle_1']);
$loginintro_1=render($row_a['loginintro_1']);
$username_1=ehtml($row_a['username_1']);
$password_1=ehtml($row_a['password_1']);
$loginbutton_1=ehtml($row_a['loginbutton_1']);
$loginoutro_1=render($row_a['loginoutro_1']);
$logout_1=ehtml($row_a['logout_1']);
$wait_1=ehtml($row_a['wait_1']);

$logintitle_2=ehtml($row_a['logintitle_2']);
$loginintro_2=render($row_a['loginintro_2']);
$username_2=ehtml($row_a['username_2']);
$password_2=ehtml($row_a['password_2']);
$loginbutton_2=ehtml($row_a['loginbutton_2']);
$loginoutro_2=render($row_a['loginoutro_2']);
$logout_2=ehtml($row_a['logout_2']);
$wait_2=ehtml($row_a['wait_2']);

$logintitle_3=ehtml($row_a['logintitle_3']);
$loginintro_3=render($row_a['loginintro_3']);
$username_3=ehtml($row_a['username_3']);
$password_3=ehtml($row_a['password_3']);
$loginbutton_3=ehtml($row_a['loginbutton_3']);
$loginoutro_3=render($row_a['loginoutro_3']);
$logout_3=ehtml($row_a['logout_3']);
$wait_3=ehtml($row_a['wait_3']);

$logintitle_4=ehtml($row_a['logintitle_4']);
$loginintro_4=render($row_a['loginintro_4']);
$username_4=ehtml($row_a['username_4']);
$password_4=ehtml($row_a['password_4']);
$loginbutton_4=ehtml($row_a['loginbutton_4']);
$loginoutro_4=render($row_a['loginoutro_4']);
$logout_4=ehtml($row_a['logout_4']);
$wait_4=ehtml($row_a['wait_4']);

$logintitle_5=ehtml($row_a['logintitle_5']);
$loginintro_5=render($row_a['loginintro_5']);
$username_5=ehtml($row_a['username_5']);
$password_5=ehtml($row_a['password_5']);
$loginbutton_5=ehtml($row_a['loginbutton_5']);
$loginoutro_5=render($row_a['loginoutro_5']);
$logout_5=ehtml($row_a['logout_5']);
$wait_5=ehtml($row_a['wait_5']);
?>