<?php require('connect.inc.php');?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>
<body>
<script>
document.write('<!--'+unescape(location.href)+'-->');
</script>
<button onclick="cf=prompt('Beginning a new mailjob. Please enter the subject (may be changed later):','');if(cf)location.replace('mailnew.php?y='+encodeURIComponent(cf)+'&n=<?php echo $_GET['n']?>&t=<?php echo $_GET['t'] ?>')">new mailjob &#19904;</button>
<?php 
$sql_a="SELECT * FROM mailing WHERE maildeleted=0 ORDER BY id DESC";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$num_a = mysql_num_rows($result_a);

echo '
<hr>
';

for ($i_a=0; $i_a<$num_a; $i_a++) {
$row_a = mysql_fetch_array($result_a);

echo '<span id="mail'.$row_a['id'].'">';
echo '&#19904;'.$row_a['id'].'&nbsp;';

echo '<small>';
if ($row_a['mailsent']==0) echo '<a target="me" href="mailingeditor.php?n='.$_GET['n'].'&id='.$row_a['id'].'&t='.$_GET['t'].'">edit</a>&nbsp;';
else echo '<a target="me" href="mailingeditor.php?n='.$_GET['n'].'&id='.$row_a['id'].'&t='.$_GET['t'].'">view</a>&nbsp;';

$h=hash('sha512',$username.$password.$_SERVER['PHP_AUTH_USER'].$_SERVER['PHP_AUTH_PW'].$_GET['t']);


if ($row_a['mailsent']==0) echo '<span style="display:inline;cursor:pointer" onclick="alert(\'The mailjob you want to test must be open (click edit) and all changes must be saved (click the save button).\')" id="notest'.$row_a['id'].'"><i>test</i></span><span style="display:none" id="test'.$row_a['id'].'"><a href="javascript:void(0)" onclick="location.href=\'mailsend.php?id='.$row_a['id'].'&t='.$_GET['t'].'&h='.$h.'\'">test</a></span>&nbsp;';

if ($row_a['mailtested']==1) {
if ($row_a['mailsent']==0) echo '<span style="display:inline;cursor:pointer" onclick="alert(\'The mailjob you want to execute must be open (click edit) and all changes must be saved (click the save button).\')" id="nosend'.$row_a['id'].'"><i>send</i></span><span style="display:none" id="send'.$row_a['id'].'"><a href="javascript:void(0)" onclick="if(confirm(\'Do you want to start mailjob &#19904;'.$row_a['id'].'?\')) location.href=\'mailsend.php?id='.$row_a['id'].'&t='.$_GET['t'].'&h='.$h.'&hot=hot\'">send</a></span>&nbsp;';

else echo '<span style="display:inline;cursor:pointer;color:white;background:gray" onclick="alert(\'A serial mail can be sent only once. Need to recycle? Copy and paste!\')" id="Xnosend'.$row_a['id'].'">sent</span> ';
}

else echo '<span style="display:inline;cursor:pointer" onclick="alert(\'The mailjob you want to execute must be open (click edit) and all changes must be saved (click the save button).\')" id="nosend'.$row_a['id'].'"><i>send</i></span><span style="display:none" id="send'.$row_a['id'].'"><span onclick="alert(\'You have to test this mail before sending it.\')"><i>send</i></span></span>&nbsp;';




echo '<a target="_blank" href="mailinfo.php?id='.$row_a['id'].'">info</a>&nbsp;';
echo '<a href="javascript:void(0)" onclick="if(confirm(\'Do you want to DELETE mailjob &#19904;'.$row_a['id'].'?\\n\\nIMPORTANT: If you delete this serial mail and you have already started it by clicking on the corresponding send link and its mail queue is still being processed, the mail dispatch is not stopped but processed to the end.\')) location.href=\'maildelete.php?id='.$row_a['id'].'&t='.$_GET['t'].'&hot=hot\'">del</a>&nbsp;';
echo '</small>';

$saved=$row_a['mailsaved'];
if($saved<1)$saved='-';
else $saved=date('Y-m-d H:i:s',$saved);
$saop=$row_a['mailsavedop'];
if($saop=='')$saop='-';

$sent=$row_a['mailsent'];
if($sent<1)$sent='-';
else $sent=date('Y-m-d H:i:s',$sent);
$seop=$row_a['mailsentop'];
if($seop=='')$seop='-';

$title="saved $saved by $saop; sent $sent by $seop";

echo '<span title="'.$title.'">'.$row_a['mailsubject'].'</span>';

echo '</span>';

echo '
<hr>
';

}

?>

<script>
oldapos=0;
apos=0;
function yellow() {
if(parent && parent.me && parent.me.location.href.indexOf('id=')>0)  apos=parent.me.location.href.split('id=')[1].split('&')[0];
if(oldapos!=apos) {
if(parent.me.location.href.indexOf('id=')>0) if (document.getElementById('mail'+apos)) document.getElementById('mail'+apos).style.background='yellow';
if(parent.me.location.href.indexOf('id=')>0) if (document.getElementById('mail'+oldapos)) document.getElementById('mail'+oldapos).style.background='white';
oldapos=apos;
}
setTimeout("yellow()",333);
}
yellow();
</script>
</body>
</html>
