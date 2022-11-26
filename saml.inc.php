<?php 
if($_COOKIE['u5samlnonce']!=$u5samlnonce||!isset($_COOKIE['u5samlusername']))die('<script>location.href="saml/login.php?u='.rawurlencode($_GET['u']).'"</script>');
$founduserincookie=$_COOKIE['u5samlusername'];
$newautosamlpw=sha1($u5samlsalt.$founduserincookie.$db);

$scriptFolder = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) ? 'https://' : 'http://';
$scriptFolder .= $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);


////Backendusers
//Set new Password

//Set Logincookie
$sql_a="SELECT pw FROM accounts WHERE email='".mysql_real_escape_string(u5flatidnlower($founduserincookie))."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$num_a = mysql_num_rows($result_a);

if($num_a>0) {
if($row_a['pw']!=pwdhsh($newautosamlpw)) {
$sql_a="UPDATE accounts SET pw='".mysql_real_escape_string(pwdhsh($newautosamlpw))."' WHERE email='".mysql_real_escape_string(u5flatidnlower($founduserincookie))."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) die('SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>');
file_get_contents($scriptFolder.'/u5admin/htaccess.php');
}

$_POST['u']=$founduserincookie;
$_POST['p']=$newautosamlpw;
}

