<?php 
require_once('render.inc.php');
$sql_a="SELECT * FROM loginglobals";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$row_a = mysql_fetch_array($result_a);

$logintitle_d=ehtml($row_a['logintitle_d']);
$loginintro_d=render($row_a['loginintro_d']);
$username_d=ehtml($row_a['username_d']);
$password_d=ehtml($row_a['password_d']);
$loginbutton_d=ehtml($row_a['loginbutton_d']);
$loginoutro_d=render($row_a['loginoutro_d']);
$logout_d=ehtml($row_a['logout_d']);
$wait_d=ehtml($row_a['wait_d']);

$logintitle_e=ehtml($row_a['logintitle_e']);
$loginintro_e=render($row_a['loginintro_e']);
$username_e=ehtml($row_a['username_e']);
$password_e=ehtml($row_a['password_e']);
$loginbutton_e=ehtml($row_a['loginbutton_e']);
$loginoutro_e=render($row_a['loginoutro_e']);
$logout_e=ehtml($row_a['logout_e']);
$wait_e=ehtml($row_a['wait_e']);

$logintitle_f=ehtml($row_a['logintitle_f']);
$loginintro_f=render($row_a['loginintro_f']);
$username_f=ehtml($row_a['username_f']);
$password_f=ehtml($row_a['password_f']);
$loginbutton_f=ehtml($row_a['loginbutton_f']);
$loginoutro_f=render($row_a['loginoutro_f']);
$logout_f=ehtml($row_a['logout_f']);
$wait_f=ehtml($row_a['wait_f']);
?>