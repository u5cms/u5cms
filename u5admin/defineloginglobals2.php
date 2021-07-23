<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>
changes=0;
if(opener)if(opener.parent)if(opener.parent.i1.changes)changes+=opener.parent.i1.changes;
if(opener)if(opener.parent)if(opener.parent.i2.changes)changes+=opener.parent.i2.changes;
if(changes>0) {
alert('You have to save the changes made in the editor(s) before saving these configurative parameters!');
history.go(-1);
window.stop();
}
</script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>saving...</title>
<?php require('backendcss.php'); ?></head>
<body id="body" bgcolor="#009900">
<?php 
if($definelanguagesRqHIADRI!='no')require_once('accadmin.inc.php');

$sql_a="UPDATE loginglobals SET 

logintitle_d='".mysql_real_escape_string($_POST['logintitle_d'])."',
logintitle_e='".mysql_real_escape_string($_POST['logintitle_e'])."',
logintitle_f='".mysql_real_escape_string($_POST['logintitle_f'])."',
loginintro_d='".mysql_real_escape_string($_POST['loginintro_d'])."',
loginintro_e='".mysql_real_escape_string($_POST['loginintro_e'])."',
loginintro_f='".mysql_real_escape_string($_POST['loginintro_f'])."',
username_d='".mysql_real_escape_string($_POST['username_d'])."',
username_e='".mysql_real_escape_string($_POST['username_e'])."',
username_f='".mysql_real_escape_string($_POST['username_f'])."',
password_d='".mysql_real_escape_string($_POST['password_d'])."',
password_e='".mysql_real_escape_string($_POST['password_e'])."',
password_f='".mysql_real_escape_string($_POST['password_f'])."',
loginbutton_d='".mysql_real_escape_string($_POST['loginbutton_d'])."',
loginbutton_e='".mysql_real_escape_string($_POST['loginbutton_e'])."',
loginbutton_f='".mysql_real_escape_string($_POST['loginbutton_f'])."',
loginoutro_d='".mysql_real_escape_string($_POST['loginoutro_d'])."',
loginoutro_e='".mysql_real_escape_string($_POST['loginoutro_e'])."',
loginoutro_f='".mysql_real_escape_string($_POST['loginoutro_f'])."',
logout_d='".mysql_real_escape_string($_POST['logout_d'])."',
logout_e='".mysql_real_escape_string($_POST['logout_e'])."',
logout_f='".mysql_real_escape_string($_POST['logout_f'])."',
wait_d='".mysql_real_escape_string($_POST['wait_d'])."',
wait_e='".mysql_real_escape_string($_POST['wait_e'])."',
wait_f='".mysql_real_escape_string($_POST['wait_f'])."'
";

$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>');
}

trxlog('def login instr');

$sql_a="DELETE FROM resources WHERE name='::LOGINPAGE::'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

if(trim($_POST['loginintro_d']).trim($_POST['loginintro_e']).trim($_POST['loginintro_f']).trim($_POST['loginoutro_d']).trim($_POST['loginoutro_e']).trim($_POST['loginoutro_f'])!='') {

$sql_a="INSERT INTO resources (hidden,name,operator,ip,lastmut,deleted,typ,content_d,content_e,content_f) VALUES (
1,
'::LOGINPAGE::',
'".mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."',
'".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."',
'".time()."',0,'x',

'[v]CONTENT FROM PIDVESA\'s S -> special functions -> define languages -> goto login instructions
    The page ::LOGINPAGE:: itself is non-functional, it is only a content copy of the aforementionad login instructions![/]

[v]LOGININTRO[/]
".mysql_real_escape_string($_POST['loginintro_d'])."

[v]LOGINOUTRO[/]
".mysql_real_escape_string($_POST['loginoutro_d'])."
',


'[v]CONTENT FROM PIDVESA\'s S -> special functions -> define languages -> goto login instructions
    The page ::LOGINPAGE:: itself is non-functional, it is only a content copy of the aforementionad login instructions![/]
	
[v]LOGININTRO[/]
".mysql_real_escape_string($_POST['loginintro_e'])."

[v]LOGINOUTRO[/]
".mysql_real_escape_string($_POST['loginoutro_e'])."
',


'[v]CONTENT FROM PIDVESA\'s S -> special functions -> define languages -> goto login instructions
    The page ::LOGINPAGE:: itself is non-functional, it is only a content copy of the aforementionad login instructions![/]
	
[v]LOGININTRO[/]
".mysql_real_escape_string($_POST['loginintro_f'])."

[v]LOGINOUTRO[/]
".mysql_real_escape_string($_POST['loginoutro_f'])."
'


)";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

////////////////
$sql_a="INSERT INTO resources_log (hidden,name,operator,ip,lastmut,deleted,typ,content_d,content_e,content_f) VALUES (
1,
'::LOGINPAGE::',
'".mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."',
'".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."',
'".time()."',0,'x',

'[v]CONTENT FROM PIDVESA\'s S -> special functions -> define languages -> goto login instructions
    The page ::LOGINPAGE:: itself is non-functional, it is only a content copy of the aforementionad login instructions![/]

[v]LOGININTRO[/]
".mysql_real_escape_string($_POST['loginintro_d'])."

[v]LOGINOUTRO[/]
".mysql_real_escape_string($_POST['loginoutro_d'])."
',


'[v]CONTENT FROM PIDVESA\'s S -> special functions -> define languages -> goto login instructions
    The page ::LOGINPAGE:: itself is non-functional, it is only a content copy of the aforementionad login instructions![/]
	
[v]LOGININTRO[/]
".mysql_real_escape_string($_POST['loginintro_e'])."

[v]LOGINOUTRO[/]
".mysql_real_escape_string($_POST['loginoutro_e'])."
',


'[v]CONTENT FROM PIDVESA\'s S -> special functions -> define languages -> goto login instructions
    The page ::LOGINPAGE:: itself is non-functional, it is only a content copy of the aforementionad login instructions![/]
	
[v]LOGININTRO[/]
".mysql_real_escape_string($_POST['loginintro_f'])."

[v]LOGINOUTRO[/]
".mysql_real_escape_string($_POST['loginoutro_f'])."
'


)";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';



}


?>
<script>
if(opener)if(opener.parent)if(opener.parent.save)opener.parent.save.location.href='done.php?n=defined languages <?php echo $_POST['lan1na']?> <?php echo $_POST['lan2na']?> <?php echo $_POST['lan3na']?>';
setTimeout("opener.top.location.href=opener.top.location.href",777);
setTimeout("self.close()",1111);
</script>

</body>
</html>