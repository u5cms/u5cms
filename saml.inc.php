<?php 
// do not include myfunction.inc.php here
if($_COOKIE['skipayor']!='yes' && file_exists('fileversions/htarunning.txt') && file_get_contents('fileversions/htarunning.txt')!=0 && file_get_contents('fileversions/htarunning.txt')>time()-60*5)die('<center style="margin:auto;padding:77px;font-size:22px;font-family:sans-serif;"><img src="upload/spinner.gif"><br><br>Provisioning in progress...<br><br>This may take a few minutes.<br><br><a href="javascript:void(0)" onclick="document.cookie=\'skipayor=yes;max-age=10;path=/\';location.href=location.href">I am in a hurry and take the risk of skipping the provisioning.</a><br><small>As a result, individual items, e.g. images, may not be available for you or may appear defective.</small><script>setTimeout("location.href=location.href",2222)</script>');
if($_COOKIE['u5samlnonce']!=sha1($samlsalt.$u5samlnonce.$_COOKIE['u5samlusername'])||!isset($_COOKIE['u5samlusername']))die('<script>location.href="saml/login.php?u='.rawurlencode($_GET['u']).'"</script>');
$founduserincookie=$_COOKIE['u5samlusername'];
$newautosamlpw=sha1($u5samlsalt.$founduserincookie.$password);

$scriptFolder = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) ? 'https://' : 'http://';
$scriptFolder .= $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);


////Backendusers
//Set new Password

//Set Logincookie
$sql_a="SELECT pw FROM accounts WHERE email='".mysql_real_escape_string(u5flatidnlower($founduserincookie))."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!...!<p>';
$num_a = mysql_num_rows($result_a);

if($num_a>0) {
    $row_a=mysql_fetch_array($result_a);
    if($row_a['pw']!=pwdhsh($newautosamlpw)) {
        $sql_a="UPDATE accounts SET pw='".mysql_real_escape_string(pwdhsh($newautosamlpw))."' WHERE email='".mysql_real_escape_string(u5flatidnlower($founduserincookie))."'";
        $result_a=mysql_query($sql_a);
        if ($result_a==false) die('SQL_a-Query failed!...!<p>');

        if(mysql_affected_rows()>0)die('<center style="margin:auto;padding:77px;font-size:22px;font-family:sans-serif;"><img src="upload/spinner.gif"><center><iframe src="htaccess.php" style="display:none"></iframe><script>setTimeout("location.href=location.href",3333)</script>');
		
    }

    $_POST['u']=$founduserincookie;
    $_POST['p']=$newautosamlpw;
}