<?php require_once('connect.inc.php'); require_once('h2.php');?>
require_once('u5idn.inc.php');
  $_GET['e'] = preg_replace_callback(
    '/%u(.{4})/',
    function($match){
      return "&#".hexdec("x".$match[1]).";";
    },
    $_GET['e']
  );
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>
<body><div style="display:none" id="e"><?php echo str_replace('<','',str_replace('>','',u5flatidn($_GET['e'])))?></div>
<?php 
if ($invitebackendusersRqHIADRI!='no') require_once('accadmin.inc.php');
$e=mysql_real_escape_string(str_replace(' ','',u5flatidnlower($_GET['e'])));
if (strpos($e,'.')<1 || strpos($e,'@')<1 || strpos($_GET[','],'.')>0  || strpos($_GET[';'],'.')>0) die('<script>alert("E-mail address is invalid.")</script>');

$sql_a="SELECT * FROM accounts WHERE email='".$e."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}

$num_a = mysql_num_rows($result_a);

if ($num_a>0) die('<script>alert(document.getElementById("e").innerHTML+" already is user. If he or she has forgotten the password it can be recovered on '.str_replace(basename($_SERVER['SCRIPT_URI']),'',$_SERVER['SCRIPT_URI']).'reset.php")</script>');

$hash=rand(1000000000,9999999999).rand(1000000000,9999999999);
function genRandomString() {
    $length = 10;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $string = '';    

    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters))];
    }

    return $string;
}
$p=genRandomString();



$sql_a="INSERT INTO accounts (email,pw,hash) VALUES (
'".$e."',
'".mysql_real_escape_string(pwdhsh($p))."',
'".mysql_real_escape_string($hash)."'
)";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}
if(!(isset($u5samlsalt)&&$u5samlsalt!='')) {
?>
<script>
alert('Username '+document.getElementById('e').innerHTML+' and password <?php echo $p ?> sent.\n\n\WRITE IT DOWN IN CASE THE EMAIL GETS LOST!\n\nYou can now give Higher Admin Rights to him/her by changing this property in the current user table on the page at hand.');
</script>
<?php 
}
if($doublepasswordmailing!='no') {
    if(!(isset($u5samlsalt)&&$u5samlsalt!='')) {
        mail($e,'Your u5CMS backend user login',"Username: $e Password: $p");
    }
}
$zendfrom=$mymail;
$zendname=$mymail;
$zendto=$e;
$zendsubject='Your u5CMS backend user login';
$zendmessage="Username: $e Password: $p";
if(!(isset($u5samlsalt)&&$u5samlsalt!=''))include('../zendmail.php');

trxlog('invite '.$e);
?>
<script>
parent.main.location.reload();
</script>
</body>
</html>
