<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>saving...</title>
</head>
<body>
<?php
require_once('connect.inc.php');
require_once('render.inc.php');
require_once('loginformsave.inc.php');

/////////////////////////////////////////////////////////////////////////////////////////////////
if (tnuoc($_POST)<1) die('ERROR: POST missing, please consult https://yuba.ch/post<script>alert("ERROR: POST missing, please consult https://yuba.ch/post")</script>');

$sql_a="SELECT * FROM resources WHERE deleted!=1 AND name='".mysql_real_escape_string($_GET['n'])."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p><script>alert("'.htmlXspecialchars(mysql_error()).'")</script>';
}
$num_a = mysql_num_rows($result_a);
if ($disableformsourcenamecheck!='yes'&&$num_a==0) die('ERROR: Source form does not exist<script>alert("ERROR: Source form does not exist")</script>');

$row_a = mysql_fetch_array($result_a);

include('ob.php');
$pexcheck = $ob;

if($pexcheck==false) $pexcheck=render(def($row_a['content_1'],$row_a['content_2'],$row_a['content_3'],$row_a['content_4'],$row_a['content_5']));

if(strpos($pexcheck,'ifrmonofillshared')>0)$isauthuser='';
else $isauthuser="AND authuser='".mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."'";
if(strpos($pexcheck,'ifrmonofill')>0&&strpos($pexcheck,'ifrmonofillshared')<1) {
$ismformfs='1';
if($_GET['o']!=pwdhsh($_SERVER['PHP_AUTH_USER']))die('<script>alert("Login Error.\nDo not use multiple logins in multiple tabs or windows in the same browser.");parent.document.getElementById("body").style.opacity=0.1;</script>');
}
else $ismformfs='';

$pexerror=0;
foreach ($_POST as $key=>$value) {

if(strpos($key,'_MAX_')>1) require('maxcalc.php');

if (str_replace(trim($key),'',$pexcheck)==$pexcheck && $key!='ed2cu' && $key!='frstsvrwnspgldtmstp' && strpos('x'.trim($key),'userupload')<1) $pexerror++;
}
if ($pexerror>0 && $disableformfieldchecker!='yes') die('ERROR: Data cannot be saved. Possible reasons: There are forbidden characters in the form field name attributes (e.g. space) or the form is not hosted on the site (in the CMS) receiving the POST data. Try setting $disableformfieldchecker=\'yes\'; in your u5CMS\'s config.php.<script>alert("ERROR: Data cannot be saved. Possible reasons: There are forbidden characters in the form field name attributes (e.g. space) or the form is not hosted on the site (in the CMS) receiving the POST data. Try setting $disableformfieldchecker=\'yes\'; in your u5CMS\'s config.php.")</script>');

/////////////////////////////////////////////////////////////////////////////////////////////////

$email1='';
$email2='';
$head='';
$data='';
$thanks='';

$nt2me=explode(',',$_POST['nt2me']);
$nt2me=array_map('trim',$nt2me);

$nt2cu=explode(',',$_POST['nt2cu']);
$nt2cu=array_map('trim',$nt2cu);

$zendsubject=$_POST['thankssubject'];
if (trim($zendsubject)=='') $zendsubject='Quittung/Receipt/Acquit: '.$_SERVER['HTTP_HOST'];
$zendmessage=$_POST['thankstext']."\r\n\r\n";
$errors=0;
$foundliving=0;

foreach ($_POST as $key=>$value) {
if (strpos($key,'living')==1) $foundliving=1;

if ($key!='ouremail' && $key!='thanks' && $key!='thankssubject' &&  $key!='thankstext'  &&  $key!='thanksgreetings' && strpos($key,'living')!=1) {

if (!in_array($key,$nt2me)) {
if($key=='firstsaverwins')$key='frstsvrwnspgldtmstp';
$head.='·'.str_replace(';',',.',$key).';';
$data.='·'.str_replace(';',',.',$value).';';
}

if ($_POST['qv2bo']!='off') if (!in_array($key,$nt2cu)) $zendmessage.=str_replace('_mandatory','*',$key).': '.str_replace(str_replace(basename($scripturi),'',$scripturi).'fileversions/useruploads/',str_replace(basename($scripturi),'',$scripturi).'ffff.php?f=',$value)."\r\n";
}


if($key=='thanks') $thanks=$value;


if($key=='youremail_mandatory' && strpos($value,'@')<1) {echo('
<script type="text/javascript">
if (parent.document.u5form && parent.document.u5form.'. $key .') {
parent.document.u5form.'. $key .'.style.borderColor=\'red\';
parent.document.u5form.'. $key .'.style.background=\'yellow\';
}
</script>
');
require('keyfocus.inc.php');
$errors++;}

else if(strpos($key,'living')==1 && ord($key[0])!=trim($value)) {echo('
<script type="text/javascript">
if (parent.document.u5form && parent.document.u5form.'. $key .') {
parent.document.u5form.'. $key .'.style.borderColor=\'red\';
parent.document.u5form.'. $key .'.style.background=\'yellow\';
}
</script>
');
require('keyfocus.inc.php');
$errors++;}

else if($key!=str_replace('_mandatory','',$key) && str_replace(' ','',trim($value))=='') {echo('
<script type="text/javascript">
if (parent.document.u5form && parent.document.u5form.'. $key .') {
parent.document.u5form.'. $key .'.style.borderColor=\'red\';
parent.document.u5form.'. $key .'.style.background=\'yellow\';
}
</script>
');
require('keyfocus.inc.php');
$errors++;}

else if($key!=str_replace('_mandatory','',$key) && str_replace(' ','',trim($value))!='') echo('
<script type="text/javascript">
if (parent.document.u5form && parent.document.u5form.'. $key .') {
parent.document.u5form.'. $key .'.style.borderColor=\'black\';
parent.document.u5form.'. $key .'.style.background=\'white\';
}
</script>
');
}
if ($errors>0) die(0);

/////////////////////////////////////////////////////////////
$isliving=0;
if (str_replace(trim('living_mandatory'),'',$pexcheck)!=$pexcheck) $isliving=1;
if ($foundliving==0 && $isliving==1) die('ERROR!<script>alert("ERROR!");</script>');
/////////////////////////////////////////////////////////////

$sql_a="INSERT INTO formdata
(
formname,headcsv,datacsv,time,ip,authuser,humantime,lastmut
) VALUES
(
'".mysql_real_escape_string($_GET['n'])."',
'".mysql_real_escape_string($head)."',
'".mysql_real_escape_string($data)."',
'".time()."',
'".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."',
'".mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."',
'".mysql_real_escape_string(date('Y.m.d H:i:s'))."',
'".mysql_real_escape_string(time())."'
)";

//////////

if ($_POST['ed2cu']=='on'||$ismformfs==1) {
if ($_POST['id_mandatory']>0) $isid="AND datacsv LIKE '_". mysql_real_escape_string($_POST['id_mandatory']).";%'";
else $isid='';
$sql_b="SELECT * FROM formdata WHERE $mfwhereclause AND formname='".mysql_real_escape_string($_GET['n'])."' $isauthuser $isid ORDER BY id DESC LIMIT 0,1";
$result_b=mysql_query($sql_b);
if ($result_b==false) echo 'SQL_b-Query failed!...!<p>';
$num_b = mysql_num_rows($result_b);
$row_b = mysql_fetch_array($result_b);

$foundthisformerauthuser=$row_b['authuser'];
$foundthisformerlastmut=$row_b['lastmut'];
require('saveversionconflict.inc.php');

if ($num_b>0&&$_POST['ed2cu']=='on') {

$sql_c="SELECT id FROM formdata ORDER BY id DESC LIMIT 0,1";
$result_c=mysql_query($sql_c);
if ($result_c==false) die('SQL_c-Query failed!...!<p><script>alert("'.htmlXspecialchars(mysql_error()).'")</script>');
$row_c = mysql_fetch_array($result_c);
$newid=$row_c['id']+1;

$sql_c="UPDATE formdata SET id=$newid, status=6 WHERE formname='".mysql_real_escape_string($_GET['n'])."' AND id='".mysql_real_escape_string($row_b['id'])."' $isid";
$result_c=mysql_query($sql_c);
if ($result_c==false) die('SQL_c-Query failed!...!<p><script>alert("'.htmlXspecialchars(mysql_error()).'")</script>');

$sql_a="INSERT INTO formdata
(
id,formname,headcsv,datacsv,time,ip,authuser,humantime,status,notes,lastmut
) VALUES
(
'".mysql_real_escape_string($row_b['id'])."',
'".mysql_real_escape_string($_GET['n'])."',
'".mysql_real_escape_string($head)."',
'".mysql_real_escape_string($data)."',
'".time()."',
'".mysql_real_escape_string($_SERVER['REMOTE_ADDR'].' ('.mysql_real_escape_string($newid).') '.$_SERVER['PHP_AUTH_USER'].' '.date('Y.m.d H:i:s'))."',
'".mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."',
'".mysql_real_escape_string(date('Y.m.d H:i:s'))."',
'".mysql_real_escape_string($row_b['status'])."',
'".mysql_real_escape_string($row_b['notes'])."',
'".mysql_real_escape_string(time())."'
)";
} //if num_b
} // if ed2cu

//////////

$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!...!<p><script>parent.u5form.submit();</script>');
}

$zendmessage.="\r\n\r\n".$_POST['thanksgreetings'];

include('config.php');
if ($_POST['in2my']!='off'){
$hzendsubject=$zendsubject;
$hzendmessage=$zendmessage;
//mail($mymail,'Formdata concerning '.$_GET['n'] ,'You have received formdata concerning '.$_GET['n'].'. Please consult '.str_replace(basename($scripturi),'',$scripturi).'u5admin/formdata.php');
$zendfrom=$mymail;
$zendname=$mymail;
$zendto=$mymail;
$zendsubject='Formdata concerning '.$_GET['n'];
$zendmessage='You have received formdata concerning '.$_GET['n'].'. Please consult '.str_replace(basename($scripturi),'',$scripturi).'u5admin/formdata.php';
include('zendmail.php');
$zendsubject=$hzendsubject;
$zendmessage=$hzendmessage;
}

if (strpos(trim($_POST['ouremail']),'@')>=1) $email1=trim($_POST['ouremail']);
if (strpos(trim($_POST['youremail']),'@')>=1) $email2=trim($_POST['youremail']);
if (strpos(trim($_POST['youremail_mandatory']),'@')>=1) $email2=trim($_POST['youremail_mandatory']);

$zendfrom=$email1;
$zendname=$email1;
$zendto=$email2;
if ($_POST['em2cu']!='off') if ($email1!='' && $email2!='') include('zendmail.php');

$zendfrom=$email1;
$zendname=$email1;
$zendto=$email1;
if ($_POST['em2me']!='off') if ($email1!='' && $email2!='') include('zendmail.php');

?>
<script type="text/javascript">
function firstsaverwins() {
if(parent)if(parent.u5form)if(parent.u5form.firstsaverwins)parent.u5form.firstsaverwins.value='<?php echo time()?>';
}
if('<?php echo htmlspecialchars($thanks) ?>'.indexOf('(')<1){
//ziel='index.php?c=<?php echo $thanks ?>&l=<?php echo $_GET['l']?>&f=<?php echo $_GET['n']?>';
//parent.u5form.action=ziel;
//parent.u5form.target='_self';
//parent.u5form.submit();
}
<?php if(strpos($thanks,'(')>1) echo 'else {firstsaverwins();parent.'.htmlspecialchars($thanks).'};'?>;
</script>

<form name="form1" target="_top" action="https://www.paypal.com/ch/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="business" value="<?php echo htmlXspecialchars($_POST['business']) ?>">
<input type="hidden" name="item_name" value="<?php echo htmlXspecialchars($_POST['item_name']) ?>">
<input type="hidden" name="currency_code" value="<?php echo htmlXspecialchars($_POST['currency_code']) ?>">
<input type="hidden" name="amount" value="<?php echo htmlXspecialchars($_POST['amount']) ?>">
<input type="hidden" name="return" value="<?php echo htmlXspecialchars($_POST['return']) ?>">
<input type="hidden" name="cancel" value="<?php echo htmlXspecialchars($_POST['cancel']) ?>">
<input type="hidden" name="no_shipping" value="<?php echo htmlXspecialchars($_POST['']) ?>">
<input type="hidden" name="shipping" value="<?php echo htmlXspecialchars($_POST['shiping']) ?>">
<input type="hidden" name="shipping2" value="<?php echo htmlXspecialchars($_POST['shipping2']) ?>">
<input type="hidden" name="handling" value="<?php echo htmlXspecialchars($_POST['handling']) ?>">
<input type="hidden" name="image_url" value="<?php echo htmlXspecialchars($_POST['image_url']) ?>">
<input type="image" src="<?php echo htmlXspecialchars($_POST['submit']) ?>" name="submit" alt="<?php echo def('Zahlen Sie mit PayPal - schnell, kostenlos und sicher!','Pay with PayPal','Payer avec PayPal') ?>">
</form>

<script>
document.form1.submit();
</script>

</body>
</html>