<?php
require_once('connect.inc.php'); 
if(function_exists('PWRESETexec'))PWRESETexec();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>u5CMS password reset for backend users</title>
<?php require('u5admin/backendcss.php') ?>
</head>
<body>
<h1>Reset my u5CMS backend user password</h1>
<?php 
if ($_GET['c']!='') {
$sql_a="SELECT * FROM accounts WHERE hash='".mysql_real_escape_string($_GET['c'])."' AND id='".mysql_real_escape_string($_GET['i'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$num_a = mysql_num_rows($result_a);
$row_a = mysql_fetch_array($result_a);
if ($num_a==0) {
trxlog('expired '.$e.' ('.$_GET['i'].')');
echo('Reset URL invalid or expired.');      
exit;
}
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
$hash=rand(1000000000,9999999999).rand(1000000000,9999999999);
if($doublepasswordmailing!='no')mail($row_a['email'],"Your new u5CMS backend user password","Your new u5CMS backend user password is $p");
$zendfrom=$mymail;
$zendname=$mymail;
$zendto=$row_a['email'];
$zendsubject='Your new u5CMS backend user password';
$zendmessage="Your new u5CMS backend user password is $p";
include('zendmail.php');
?>

<h2>Step 3</h2>
<ul>
<li>An e-mail containing the new password was sent to <b><?php echo htmlXspecialchars($row_a['email']) ?></b></li>
<li>With the new password, <a href="u5admin">log in</a> now</li>
</ul>
<iframe src="u5admin/htaccess.php?o=intranet" frameborder="0" width="0" height="0"></iframe>

<?php 
trxlog('reseted '.$row_a['email'].' ('.$row_a['id'].')');



$sql_a="UPDATE accounts SET pw='".pwdhsh($p)."', hash='$hash' WHERE hash='".mysql_real_escape_string($_GET['c'])."' AND id='".mysql_real_escape_string($_GET['i'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

}
else {

if(!isset($waitsecondsbetweenpasswordresets))$waitsecondsbetweenpasswordresets=(60*10);
if(time()-file_get_contents('fileversions/lastreset.txt')<$waitsecondsbetweenpasswordresets){
echo('<script>location.href="reset.php"</script>');
exit;	
}

file_put_contents('fileversions/lastreset.txt',time());

$e=str_replace(' ','',trim($_POST['e']));
require_once('u5admin/u5idn.inc.php');
$e=u5flatidn($e);

if (strpos($e,'@')<1 || strpos($e,'.')<1) die('<script type="text/javascript">alert("You have to enter a valid e-mail address!");location.href="reset.php"</script>');
$sql_a="SELECT * FROM accounts WHERE email='".mysql_real_escape_string($e)."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$num_a = mysql_num_rows($result_a);

$row_a = mysql_fetch_array($result_a);

if ($num_a>0) {

if($doublepasswordmailing!='no')mail($e,"Link to reset your u5CMS backend user password","Please click this link to reset your u5CMS backend user password:\n\n".str_replace(basename($scripturi),'',$scripturi)."reset2.php?c=".$row_a['hash']."&i=".$row_a['id']);
$zendfrom=$mymail;
$zendname=$mymail;
$zendto=$e;
$zendsubject='Link to reset your u5CMS backend user password';
$zendmessage="Please click this link to reset your u5CMS backend user password:\n\n".str_replace(basename($scripturi),'',$scripturi)."reset2.php?c=".$row_a['hash']."&i=".$row_a['id'];
include('zendmail.php');

trxlog('resetrq '.$e.' ('.$row_a['id'].')');

}

else {

if($dontmailyouarenotmemberalerttopwreseters!='yes')if($doublepasswordmailing!='no')mail($e,"Sorry, $e is not an existing u5CMS backend username.","Sorry, $e is not an existing u5CMS backend username.");
$zendfrom=$mymail;
$zendname=$mymail;
$zendto=$e;
$zendsubject="Sorry, $e is not an existing u5CMS backend username.";
$zendmessage="Sorry, $e is not an existing u5CMS backend username.";
if($dontmailyouarenotmemberalerttopwreseters!='yes')include('zendmail.php');

if($dontmailyouarenotmemberalerttoadmin!='yes')if($doublepasswordmailing!='no')mail($mymail,"Sorry, $e is not an existing u5CMS backend username.","Sorry, $e is not an existing u5CMS backend username.");
$zendfrom=$mymail;
$zendname=$mymail;
$zendto=$mymail;
$zendsubject="Sorry, $e is not an existing u5CMS backend username.";
$zendmessage="Sorry, $e is not an existing u5CMS backend username.";
if($dontmailyouarenotmemberalerttoadmin!='yes')include('zendmail.php');

trxlog('notreseted '.$e);

}      


?>
<h2>Step 2</h2>
<ul>
<li>An e-mail was sent to <b><?php echo htmlXspecialchars($e) ?></b></li>
<li>Please find this e-mail in your inbox</li>
<li>If it is overdue, do not forget to have a look to your spam folder too</li>
<li>Follow the instructions in the e-mail</li>
</ul>
<?php } ?>
</body>
</html>