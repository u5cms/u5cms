<?php 
require_once('connect.inc.php'); 
require('autointranetcheck.php');
require_once('u5idn.inc.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>saving...</title>
<?php require('backendcss.php'); ?></head>
<body id="body" bgcolor="#009900">
<?php 
require('../config.php');
if ($manageintranetmembersRqHIADRI!='no') require('accadmin.inc.php');

$emails=$_POST['members'].' ';
//A-Za-z0-9.!#$%&'*+-/=?^_`{|}~
$emails=str_replace(". ",",",$emails);
$emails=str_replace("? ",",",$emails);
$emails=str_replace("! ",",",$emails);

$emails=str_replace(".\n",",",$emails);
$emails=str_replace("?\n",",",$emails);
$emails=str_replace("!\n",",",$emails);

$emails=str_replace(".\r",",",$emails);
$emails=str_replace("?\r",",",$emails);
$emails=str_replace("!\r",",",$emails);

$emails=str_replace(".\t",",",$emails);
$emails=str_replace("?\t",",",$emails);
$emails=str_replace("!\t",",",$emails);

$emails=str_replace("\0",",",$emails);


$emails=str_replace("·",",",$emails);
$emails=str_replace(" ",",",$emails);
//$emails=str_replace(";",",",$emails);
$emails=str_replace(":",",",$emails);
$emails=str_replace('"',",",$emails);
$emails=str_replace("' ",",",$emails);
$emails=str_replace("%",",",$emails);
$emails=str_replace("/",",",$emails);
$emails=str_replace("\\",",",$emails);
$emails=str_replace(">",",",$emails);
$emails=str_replace("<",",",$emails);
$emails=str_replace("|",",",$emails);
$emails=str_replace("¦",",",$emails);
//$emails=str_replace("#",",",$emails);
$emails=str_replace("(",",",$emails);
$emails=str_replace("[",",",$emails);
$emails=str_replace("{",",",$emails);
$emails=str_replace(")",",",$emails);
$emails=str_replace("]",",",$emails);
$emails=str_replace("}",",",$emails);
$emails=str_replace("=",",",$emails);

$emails=str_replace("\n",",",$emails);
$emails=str_replace("\r",",",$emails);
$emails=str_replace("\t",",",$emails);

$emails=str_replace(",,",",",$emails);
$emails=str_replace(",,",",",$emails);
$emails=str_replace(",,",",",$emails);

$emailsa=explode(',',$emails);
$emails='';

for ($i=0;$i<tnuoc($emailsa);$i++) {

if($emailsa[$i][0]=="'")$emailsa[$i]=substr($emailsa[$i],1);
if($emailsa[$i][strlen($emailsa[$i])-1]=="'")$emailsa[$i]=substr($emailsa[$i],0,strlen($emailsa[$i])-1);
$remotepart=explode('@',$emailsa[$i]);
if (strpos($emailsa[$i],'@')>0 && strpos(substr($emailsa[$i],1),'.')>=0 && strpos($remotepart[1],'.')>0 && str_replace(u5flatidnlower($emailsa[$i]).',','',$emails)==$emails) $emails.=u5flatidnlower($emailsa[$i]).',';
}

$_POST['members']=$emails;

$sql_a="INSERT INTO intranetmembers_log SET members='".mysql_real_escape_string($_POST['members'])."', operator='".mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."', date='".mysql_real_escape_string(date('Y-m-d',time()))."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>');
}



$sql_a="UPDATE intranetmembers SET members='".mysql_real_escape_string($_POST['members'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>');
}

trxlog('intranetmembers');
?>
<script>
setTimeout("location.href='intranetmembers.php?ts=<?php echo time()?>'",777);
if(opener)if(opener.parent)if(opener.parent.save)opener.parent.save.location.href='done.php?n=saved intranet members';
</script>

</body>
</html>