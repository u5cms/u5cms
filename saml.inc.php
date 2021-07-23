<?php 
if($_COOKIE['u5samlnonce']!=$u5samlnonce||!isset($_COOKIE['u5samlusername']))die('<script>location.href="saml/saml.php?u='.rawurlencode($_GET['u']).'"</script>');
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

else {
//Intranet- and Singlepage-Users
$sql_a="SELECT logins,name FROM resources WHERE deleted!=1 AND logins LIKE '%:%'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$num_a = mysql_num_rows($result_a);


for ($i_a=0; $i_a<$num_a; $i_a++) {
$row_a = mysql_fetch_array($result_a);

$logins=explode(';',$row_a['logins']);

$newlogins='';
$foundone=0;
$totalfound=0;
for($ii=0;$ii<count($logins);$ii++) {
if(str_replace('?'.u5flatidnlower($founduserincookie).':','',$logins[$ii])==$logins[$ii])$newlogins.=$logins[$ii].';';	
else {
	$newlogins.='
?'.u5flatidnlower($founduserincookie).':'.$newautosamlpw.';';	
	$foundone=1;
}
if($foundone==1) {
$sql_b="UPDATE resources SET logins='".mysql_real_escape_string(str_replace(';;',';',str_replace(';;',';',$newlogins)))."' WHERE name='".$row_a['name']."'";
$result_b=mysql_query($sql_b);
if ($result_b==false) echo 'SQL_b-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_b.'</font><p>';
$totalfound++;
}
}
}
$_POST['u']=$founduserincookie;
$_POST['p']=$newautosamlpw;
if($totalfound>0)file_get_contents($scriptFolder.'/u5admin/htaccess.php');
}
?>