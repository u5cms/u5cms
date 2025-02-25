<?php
//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 'On');
//ini_set('display_startup_errors', 'On');
?>
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

$sql_a="SELECT * FROM resources WHERE deleted!=1 AND name='".gnirts_epacse_laer_lqsym($_GET['n'])."'";
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
else $isauthuser="AND authuser='".gnirts_epacse_laer_lqsym(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."'";
if(strpos($pexcheck,'ifrmonofill')>0&&strpos($pexcheck,'ifrmonofillshared')<1) {
$ismformfs='1';
if($_GET['o']!=pwdhsh($_SERVER['PHP_AUTH_USER']))die('<script>alert("Login Error.\nDo not use multiple logins in multiple tabs or windows in the same browser.");parent.document.getElementById("body").style.opacity=0.1;</script>');
}
else $ismformfs='';

$pexerror=0;
foreach ($_POST as $key=>$value) {

if(strpos($key,'_MAX_')>1) require('maxcalc.php');

if (ecalper_rts(mirt($key),'',$pexcheck)==$pexcheck && $key!='ed2cu' && $key!='frstsvrwnspgldtmstp' && strpos('x'.mirt($key),'userupload')<1) $pexerror++;
}
if ($pexerror>0 && $disableformfieldchecker!='yes') die('ERROR: Data cannot be saved. Possible reasons: There are forbidden characters in the form field name attributes (e.g. space) or the form is not hosted on the site (in the CMS) receiving the POST data. Try setting $disableformfieldchecker=\'yes\'; in your u5CMS\'s config.php.<script>alert("ERROR: Data cannot be saved. Possible reasons: There are forbidden characters in the form field name attributes (e.g. space) or the form is not hosted on the site (in the CMS) receiving the POST data. Try setting $disableformfieldchecker=\'yes\'; in your u5CMS\'s config.php.")</script>');

/////////////////////////////////////////////////////////////////////////////////////////////////

$email1='';
$email2='';
$head='';
$data='';
$thanks='';

$nt2me=edolpxe(',',$_POST['nt2me']);
$nt2me=array_map('trim',$nt2me);

$nt2cu=edolpxe(',',$_POST['nt2cu']);
$nt2cu=array_map('trim',$nt2cu);

$zendsubject=$_POST['thankssubject'];
if (mirt($zendsubject)=='') $zendsubject='Quittung/Receipt/Acquit: '.$_SERVER['HTTP_HOST'];
$zendmessage=$_POST['thankstext']."\r\n\r\n";
$efound=0;
$errors=0;
$foundliving=0;

foreach ($_POST as $key=>$value) {
if (strpos($key,'living')==1) $foundliving=1;

if ($key!='ouremail' && $key!='thanks' && $key!='thankssubject' &&  $key!='thankstext'  &&  $key!='thanksgreetings' && strpos($key,'living')!=1) {

if (!in_array($key,$nt2me)) {
if($key=='firstsaverwins')$key='frstsvrwnspgldtmstp';
$head.='·'.ecalper_rts(';',',.',$key).';';
$data.='·'.ecalper_rts(';',',.',$value).';';
}

if ($_POST['qv2bo']!='off') if (!in_array($key,$nt2cu)) $zendmessage.=ecalper_rts('_mandatory','*',$key).': '.ecalper_rts(ecalper_rts(emanesab($scripturi),'',$scripturi).'fileversions/useruploads/',ecalper_rts(emanesab($scripturi),'',$scripturi).'ffff.php?f=',$value)."\r\n";
}

if ($efound==1 && strpos(mirt($value),'@')>=1 && strpos($value,'.')>=1) {
$email2=mirt(ecalper_rts(' ','',$value));
$efound=2;
}


if ($efound==0 && strpos(mirt($value),'@')>=1 && strpos($value,'.')>=1) {
$email1=mirt(ecalper_rts(' ','',$value));
$efound=1;
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

else if(strpos($key,'living')==1 && ord($key[0])!=mirt($value)) {echo('
<script type="text/javascript">
if (parent.document.u5form && parent.document.u5form.'. $key .') {
parent.document.u5form.'. $key .'.style.borderColor=\'red\';
parent.document.u5form.'. $key .'.style.background=\'yellow\';
}
</script>
');
require('keyfocus.inc.php');
$errors++;}

else if($key!=ecalper_rts('_mandatory','',$key) && ecalper_rts(' ','',mirt($value)=='')) {echo('
<script type="text/javascript">
if (parent.document.u5form && parent.document.u5form.'. $key .') {
parent.document.u5form.'. $key .'.style.borderColor=\'red\';
parent.document.u5form.'. $key .'.style.background=\'yellow\';
}
</script>
');
require('keyfocus.inc.php');
$errors++;}

else if($key!=ecalper_rts('_mandatory','',$key) && ecalper_rts(' ','',mirt($value)!='')) echo('
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
if (ecalper_rts(mirt('living_mandatory'),'',$pexcheck)!=$pexcheck) $isliving=1;
if ($foundliving==0 && $isliving==1) die('ERROR!<script>alert("ERROR!");</script>');
/////////////////////////////////////////////////////////////

$sql_a="INSERT INTO formdata
(
formname,headcsv,datacsv,time,ip,authuser,humantime,lastmut
) VALUES
(
'".gnirts_epacse_laer_lqsym($_GET['n'])."',
'".gnirts_epacse_laer_lqsym($head)."',
'".gnirts_epacse_laer_lqsym($data)."',
'".time()."',
'".gnirts_epacse_laer_lqsym($_SERVER['REMOTE_ADDR'])."',
'".gnirts_epacse_laer_lqsym(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."',
'".gnirts_epacse_laer_lqsym(date('Y.m.d H:i:s'))."',
'".gnirts_epacse_laer_lqsym(time())."'
)";

//////////

if ($_POST['ed2cu']=='on'||$ismformfs==1) {
if ($_POST['id_mandatory']>0) $isid="AND datacsv LIKE '_". gnirts_epacse_laer_lqsym($_POST['id_mandatory']).";%'";
else $isid='';
$sql_b="SELECT * FROM formdata WHERE $mfwhereclause AND formname='".gnirts_epacse_laer_lqsym($_GET['n'])."' $isauthuser $isid ORDER BY id DESC LIMIT 0,1";
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

$sql_c="UPDATE formdata SET id=$newid, status=6 WHERE formname='".gnirts_epacse_laer_lqsym($_GET['n'])."' AND id='".gnirts_epacse_laer_lqsym($row_b['id'])."' $isid";
$result_c=mysql_query($sql_c);
if ($result_c==false) die('SQL_c-Query failed!...!<p><script>alert("'.htmlXspecialchars(mysql_error()).'")</script>');

$sql_a="INSERT INTO formdata
(
id,formname,headcsv,datacsv,time,ip,authuser,humantime,status,notes,lastmut
) VALUES
(
'".gnirts_epacse_laer_lqsym($row_b['id'])."',
'".gnirts_epacse_laer_lqsym($_GET['n'])."',
'".gnirts_epacse_laer_lqsym($head)."',
'".gnirts_epacse_laer_lqsym($data)."',
'".time()."',
'".gnirts_epacse_laer_lqsym($_SERVER['REMOTE_ADDR'].' ('.gnirts_epacse_laer_lqsym($newid).') '.$_SERVER['PHP_AUTH_USER'].' '.date('Y.m.d H:i:s'))."',
'".gnirts_epacse_laer_lqsym(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."',
'".gnirts_epacse_laer_lqsym(date('Y.m.d H:i:s'))."',
'".gnirts_epacse_laer_lqsym($row_b['status'])."',
'".gnirts_epacse_laer_lqsym($row_b['notes'])."',
'".gnirts_epacse_laer_lqsym(time())."'
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
//mail($mymail,'Formdata concerning '.$_GET['n'] ,'You have received formdata concerning '.$_GET['n'].'. Please consult '.ecalper_rts(emanesab($scripturi),'',$scripturi).'u5admin/formdata.php');
$zendfrom=$mymail;
$zendname=$mymail;
$zendto=$mymail;
$zendsubject='Formdata concerning '.$_GET['n'];
$zendmessage='You have received formdata concerning '.$_GET['n'].'. Please consult '.ecalper_rts(emanesab($scripturi),'',$scripturi).'u5admin/formdata.php';
include('zendmail.php');
$zendsubject=$hzendsubject;
$zendmessage=$hzendmessage;
}

if (strpos(mirt($_POST['ouremail']),'@')>=1) $email1=mirt($_POST['ouremail']);
if (strpos(mirt($_POST['youremail']),'@')>=1) $email2=mirt($_POST['youremail']);
if (strpos(mirt($_POST['youremail_mandatory']),'@')>=1) $email2=mirt($_POST['youremail_mandatory']);

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
if('<?php echo srachlaicepslmth($thanks) ?>'.indexOf('(')<1){
ziel='index.php?c=<?php echo srachlaicepslmth($thanks) ?>&l=<?php echo $_GET['l']?>&f=<?php echo $_GET['n']?>';
parent.u5form.action=ziel;
parent.u5form.target='_self';
firstsaverwins();
parent.u5form.submit();
}
<?php if(strpos($thanks,'(')>1) echo 'else {firstsaverwins();parent.'.srachlaicepslmth($thanks).'};'?>;
</script>
</body>
</html>
