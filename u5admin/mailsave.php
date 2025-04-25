<textarea style="display:none" id="xgram">&#19904;</textarea><?php
require('connect.inc.php');

/////
$sql_a="SELECT * FROM mailing WHERE id='".mysql_real_escape_string($_GET['id'])."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}
$row_a = mysql_fetch_array($result_a);
if($row_a['mailsent']>0) die('<script>alert("You cannot save nor change mailjob "+document.getElementById("xgram").value+"'.$_GET['id'].' because it has been sent at '.date('Y-m-d H:i:s',$row_a['mailsent']).' by '.$row_a['mailsentop'].'");history.go(-1)</script>');
if ($_POST['coco']<$row_a['mailsaved'] && (($checksaveversionconflictinbackend!='none'&&$checksaveversionconflictinbackend!='foreign')||($checksaveversionconflictinbackend=='foreign'&&trim(u5flatidnlower($row_a['operator']))!=trim(u5flatidnlower($_SERVER['PHP_AUTH_USER'])))) ) {
?>
<div style="display:none" id="e"><?php echo str_replace('<','',str_replace('>','',$row_a['mailsavedop'])) ?></div>
<script>
msgconflict='CONFLICT(!)\n\nYour data can not be saved.\n\nReason: '+document.getElementById('e').innerHTML+' has saved this data in a new version during your editing session (at <?php echo date('Y-m-d H:i:s',$row_a['mailsaved'])?>).\n\nPlease copy and paste your unsavable mail text e. g. to a word document. Afterwards click the edit link of this mail in the right column at hand to load the new version created by '+document.getElementById('e').innerHTML+'.';
top.document.title=msgconflict;
location.href="mailinglist.php";
</script>
<?php
exit;
}
/////

$mailfrom=mysql_real_escape_string($_POST['mfrom']);
$mailto=mysql_real_escape_string($_POST['mto']);
$mailcc=mysql_real_escape_string($_POST['mcc']);
$mailbcc=mysql_real_escape_string($_POST['mbcc']);
$mailsubject=mysql_real_escape_string($_POST['msubject']);
$mailtext=mysql_real_escape_string($_POST['mmessage']);
$mailsaved=time();
$mailsavedop=mysql_real_escape_string($_SERVER['PHP_AUTH_USER'].' '.$_SERVER['REMOTE_ADDR']);
$maildeleted=0;

$sql_a="UPDATE mailing SET
mailfrom='$mailfrom',
mailto='$mailto',
mailcc='$mailcc',
mailbcc='$mailbcc',
mailsubject='$mailsubject',
mailtext='$mailtext',
mailsaved='$mailsaved',
mailsavedop='$mailsavedop',
maildeleted='$maildeleted',
mailtested='0'
WHERE mailsent=0 AND id=".mysql_real_escape_string($_GET['id']);
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}

trxlog("save mj ".$_GET['id']);
?>
<script>
parent.me.document.form.coco.value='<?php echo time()?>';
parent.me.document.form.changes.value='0';
parent.me.document.getElementById('save').style.background='lightgreen';
location.href="mailinglist.php?n=<?php echo $_GET['n']?>&t=<?php echo $_GET['t']?>";
</script>
