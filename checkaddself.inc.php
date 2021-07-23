<?php
if (strpos($cmdstring,'SELF_TOADDITEMS_LOGINS_SAME_AS_[')>1) {
$sql_a="SELECT id FROM formdata WHERE authuser='".mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."' AND id='".mysql_real_escape_string($_GET['id'])."' AND formname='".mysql_real_escape_string($_GET['n'])."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$num_a = mysql_num_rows($result_a);
if($num_a<1) {
if ($_GET['a']!=1) die('<script>alert("forbidden");parent.history.go(-1)</script>');
else {
require_once('u5admin/usercheck.inc.php');
if ($formdataRqHIADRI!='no') require('u5admin/accadmin.inc.php');	
}
}
}
?>