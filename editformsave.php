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
require_once('editreadrights.inc.php');
/////////////////////////////////////////////////////////////////////////////////////////////////
if (tnuoc($_POST)<1) die('ERROR: Post is not set<script>alert("ERROR: Post is not set")</script>');

$sql_a="SELECT * FROM formdata WHERE id='".mysql_real_escape_string($_GET['id'])."' AND formname='".mysql_real_escape_string($_GET['n'])."' ORDER BY time DESC";
$result_a=mysql_query($sql_a);

if ($result_a==false) die('SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p><script>alert("'.htmlXspecialchars(mysql_error()).'")</script>');
$row_a = mysql_fetch_array($result_a);
$authuser=$row_a['authuser'];
$notes=$row_a['notes'];

$foundthisformerauthuser=$row_a['authuser'];
$foundthisformerlastmut=$row_a['lastmut'];
require('saveversionconflict.inc.php');

$sql_a="SELECT * FROM resources WHERE deleted!=1 AND name='".mysql_real_escape_string($_GET['n'])."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p><script>alert("'.htmlXspecialchars(mysql_error()).'")</script>';
}
$num_a = mysql_num_rows($result_a);
/*if ($num_a==0) die('ERROR: Source form does not exist<script>alert("ERROR: Source form does not exist")</script>');*/

$row_a = mysql_fetch_array($result_a);

include('ob.php');
$pexcheck = $ob;

if($pexcheck==false) $pexcheck=render(def($row_a['content_d'],$row_a['content_e'],$row_a['content_f']));

$pexerror=0;
foreach ($_POST as $key=>$value) {

if(strpos($key,'_MAX_')>1) require('maxcalc.php');

if (str_replace(trim($key),'',$pexcheck)==$pexcheck && $key!='ed2cu' && $key!='frstsvrwnspgldtmstp' && strpos('x'.trim($key),'userupload')<1) $pexerror++;
}
if ($pexerror>0 && $disableformfieldchecker!='yes' && $enableformfieldcheckerforedits=='yes') die('ERROR: Data cannot be saved. Possible reasons: There are forbidden characters in the form field name attributes (e.g. space) or the form is not hosted on the site (in the CMS) receiving the POST data. Try setting $disableformfieldchecker=\'yes\'; in your u5CMS\'s config.php.<script>alert("ERROR: Data cannot be saved. Possible reasons: There are forbidden characters in the form field name attributes (e.g. space) or the form is not hosted on the site (in the CMS) receiving the POST data. Try setting $disableformfieldchecker=\'yes\'; in your u5CMS\'s config.php.")</script>');
/////////////////////////////////////////////////////////////////////////////////////////////////


$head='';
$data='';
$thanks='';
$zendsubject=$_POST['thankssubject'];
if (trim($zendsubject)=='') $zendsubject='Quittung/Receipt/Acquit: '.$_SERVER['HTTP_HOST'];
$zendmessage=$_POST['thankstext']."\r\n\r\n";
$efound=0;
$errors=0;
$foundliving=0;

foreach ($_POST as $key=>$value) {
if (strpos($key,'living')==1) $foundliving=1;

if ($key!='ouremail' && $key!='thanks' && $key!='thankssubject' &&  $key!='thankstext'  &&  $key!='thanksgreetings' && strpos($key,'living')!=1) {
if($key=='firstsaverwins')$key='frstsvrwnspgldtmstp';
$head.='·'.str_replace(';',',.',$key).';';
$data.='·'.str_replace(';',',.',$value).';';
$zendmessage.=str_replace('_mandatory','*',$key).': '.$value."\r\n";
}

if ($efound==1 && strpos($value,'@')>=1 && strpos($value,'.')>=1) {
$email2=trim(str_replace(' ','',$value));
$efound=2;
}


if ($efound==0 && strpos($value,'@')>=1 && strpos($value,'.')>=1) {
$email1=trim(str_replace(' ','',$value));
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

else if($key!=str_replace('_mandatory','',$key) && str_replace(' ','',trim($value)=='')) {echo('
<script type="text/javascript">
if (parent.document.u5form && parent.document.u5form.'. $key .') {
parent.document.u5form.'. $key .'.style.borderColor=\'red\';
parent.document.u5form.'. $key .'.style.background=\'yellow\';
}
</script>
');
require('keyfocus.inc.php');
$errors++;}

else if($key!=str_replace('_mandatory','',$key) && str_replace(' ','',trim($value)!='')) echo('
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
/////////////////////////////////////////////////////////////

$sql_a="SELECT id FROM formdata ORDER BY id DESC LIMIT 0,1";
$result_a=mysql_query($sql_a);
if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}
$row_a = mysql_fetch_array($result_a);
$newid=$row_a['id']+1;
      
$sql_a="UPDATE formdata SET id=$newid, status=6 WHERE formname='".mysql_real_escape_string($_GET['n'])."' AND id='".mysql_real_escape_string($_GET['id'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p><script>alert("'.htmlXspecialchars(mysql_error()).'")</script>');
}

if($_GET['id']<1)$_GET['id']=$newid;

$sql_a="INSERT INTO formdata 
(
id,formname,headcsv,datacsv,time,ip,authuser,humantime,status,notes,lastmut
) VALUES 
(
'".mysql_real_escape_string($_GET['id'])."',
'".mysql_real_escape_string($_GET['n'])."',
'".mysql_real_escape_string($head)."',
'".mysql_real_escape_string($data)."',
'".mysql_real_escape_string($_GET['ti'])."',
'".mysql_real_escape_string($_SERVER['REMOTE_ADDR'].' ('.mysql_real_escape_string($newid).') '.$_SERVER['PHP_AUTH_USER'].' '.date('Y.m.d H:i:s'))."',
'".mysql_real_escape_string($authuser)."',
'".mysql_real_escape_string($_GET['hu'])."',
'".mysql_real_escape_string($_GET['st'])."',
'".mysql_real_escape_string($notes)."',
'".mysql_real_escape_string(time())."'
)";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
  die('SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p><script>parent.u5form.submit();</script>');
}

$zendmessage.="\r\n\r\n".$_POST['thanksgreetings'];

include('config.php');
//mail($mymail,'Formdata concerning '.$_GET['n'] ,'You have received formdata concerning '.$_GET['n'].'. Please consult '.str_replace(basename($scripturi),'',$scripturi).'u5admin/formdata.php');

$zendfrom=$email1;
$zendname=$email1;
$zendto=$email2;
//if ($email1!='' && $email2!='') include('zendmail.php');

$zendfrom=$email2;
$zendname=$email2;
$zendto=$email1;
//if ($email1!='' && $email2!='') include('zendmail.php');

?>
<script type="text/javascript">
function firstsaverwins() {
if(parent)if(parent.u5form)if(parent.u5form.firstsaverwins)parent.u5form.firstsaverwins.value='<?php echo time()?>';	
}
if('<?php echo $_GET['a']?>'!='1'){
if('<?php echo $thanks?>'.indexOf('(')<1){
ziel='index.php?c=<?php echo $thanks ?>&l=<?php echo $_GET['l']?>&f=<?php echo $_GET['n']?>';
parent.u5form.action=ziel;
parent.u5form.target='_self';
firstsaverwins();
parent.u5form.submit();
}
<?php if(strpos($thanks,'(')>1) echo 'else {firstsaverwins();parent.'.$thanks.'};'?>;
}
else {
ziel='u5admin/formdata2.php?<?php echo $_COOKIE['ffrm'] ?>';
parent.location.replace(ziel);
setTimeout("parent.window.location.href=ziel",333);
}
</script>
</body>
</html>