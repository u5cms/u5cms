<?php require_once('connect.inc.php');require_once('t2.php'); ?>
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

logintitle_1='".mysql_real_escape_string($_POST['logintitle_1'])."',
logintitle_2='".mysql_real_escape_string($_POST['logintitle_2'])."',
logintitle_3='".mysql_real_escape_string($_POST['logintitle_3'])."',
logintitle_4='".mysql_real_escape_string($_POST['logintitle_4'])."',
logintitle_5='".mysql_real_escape_string($_POST['logintitle_5'])."',
loginintro_1='".mysql_real_escape_string($_POST['loginintro_1'])."',
loginintro_2='".mysql_real_escape_string($_POST['loginintro_2'])."',
loginintro_3='".mysql_real_escape_string($_POST['loginintro_3'])."',
loginintro_4='".mysql_real_escape_string($_POST['loginintro_4'])."',
loginintro_5='".mysql_real_escape_string($_POST['loginintro_5'])."',
username_1='".mysql_real_escape_string($_POST['username_1'])."',
username_2='".mysql_real_escape_string($_POST['username_2'])."',
username_3='".mysql_real_escape_string($_POST['username_3'])."',
username_4='".mysql_real_escape_string($_POST['username_4'])."',
username_5='".mysql_real_escape_string($_POST['username_5'])."',
password_1='".mysql_real_escape_string($_POST['password_1'])."',
password_2='".mysql_real_escape_string($_POST['password_2'])."',
password_3='".mysql_real_escape_string($_POST['password_3'])."',
password_4='".mysql_real_escape_string($_POST['password_4'])."',
password_5='".mysql_real_escape_string($_POST['password_5'])."',
loginbutton_1='".mysql_real_escape_string($_POST['loginbutton_1'])."',
loginbutton_2='".mysql_real_escape_string($_POST['loginbutton_2'])."',
loginbutton_3='".mysql_real_escape_string($_POST['loginbutton_3'])."',
loginbutton_4='".mysql_real_escape_string($_POST['loginbutton_4'])."',
loginbutton_5='".mysql_real_escape_string($_POST['loginbutton_5'])."',
loginoutro_1='".mysql_real_escape_string($_POST['loginoutro_1'])."',
loginoutro_2='".mysql_real_escape_string($_POST['loginoutro_2'])."',
loginoutro_3='".mysql_real_escape_string($_POST['loginoutro_3'])."',
loginoutro_4='".mysql_real_escape_string($_POST['loginoutro_4'])."',
loginoutro_5='".mysql_real_escape_string($_POST['loginoutro_5'])."',
logout_1='".mysql_real_escape_string($_POST['logout_1'])."',
logout_2='".mysql_real_escape_string($_POST['logout_2'])."',
logout_3='".mysql_real_escape_string($_POST['logout_3'])."',
logout_4='".mysql_real_escape_string($_POST['logout_4'])."',
logout_5='".mysql_real_escape_string($_POST['logout_5'])."',
wait_1='".mysql_real_escape_string($_POST['wait_1'])."',
wait_2='".mysql_real_escape_string($_POST['wait_2'])."',
wait_3='".mysql_real_escape_string($_POST['wait_3'])."',
wait_4='".mysql_real_escape_string($_POST['wait_4'])."',
wait_5='".mysql_real_escape_string($_POST['wait_5'])."'
";

$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>');
}

trxlog('def login instr');

$sql_a="DELETE FROM resources WHERE name='::LOGINPAGE::'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

if(trim($_POST['loginintro_1']).trim($_POST['loginintro_2']).trim($_POST['loginintro_3']).trim($_POST['loginintro_4']).trim($_POST['loginintro_5']).trim($_POST['loginoutro_1']).trim($_POST['loginoutro_2']).trim($_POST['loginoutro_3']).trim($_POST['loginoutro_4']).trim($_POST['loginoutro_5'])!='') {

$sql_a="INSERT INTO resources (hidden,name,operator,ip,lastmut,deleted,typ,content_1,content_2,content_3,content_4,content_5) VALUES (
1,
'::LOGINPAGE::',
'".mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."',
'".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."',
'".time()."',0,'x',

'[v]CONTENT FROM PIDVESA\'s S -> special functions -> define languages -> goto login instructions
    The page ::LOGINPAGE:: itself is non-functional, it is only a content copy of the aforementionad login instructions![/]

[v]LOGININTRO[/]
".mysql_real_escape_string($_POST['loginintro_1'])."

[v]LOGINOUTRO[/]
".mysql_real_escape_string($_POST['loginoutro_1'])."
',


'[v]CONTENT FROM PIDVESA\'s S -> special functions -> define languages -> goto login instructions
    The page ::LOGINPAGE:: itself is non-functional, it is only a content copy of the aforementionad login instructions![/]
	
[v]LOGININTRO[/]
".mysql_real_escape_string($_POST['loginintro_2'])."

[v]LOGINOUTRO[/]
".mysql_real_escape_string($_POST['loginoutro_2'])."
',


'[v]CONTENT FROM PIDVESA\'s S -> special functions -> define languages -> goto login instructions
    The page ::LOGINPAGE:: itself is non-functional, it is only a content copy of the aforementionad login instructions![/]
	
[v]LOGININTRO[/]
".mysql_real_escape_string($_POST['loginintro_3'])."

[v]LOGINOUTRO[/]
".mysql_real_escape_string($_POST['loginoutro_3'])."
',


'[v]CONTENT FROM PIDVESA\'s S -> special functions -> define languages -> goto login instructions
    The page ::LOGINPAGE:: itself is non-functional, it is only a content copy of the aforementionad login instructions![/]
	
[v]LOGININTRO[/]
".mysql_real_escape_string($_POST['loginintro_4'])."

[v]LOGINOUTRO[/]
".mysql_real_escape_string($_POST['loginoutro_4'])."
',


'[v]CONTENT FROM PIDVESA\'s S -> special functions -> define languages -> goto login instructions
    The page ::LOGINPAGE:: itself is non-functional, it is only a content copy of the aforementionad login instructions![/]
	
[v]LOGININTRO[/]
".mysql_real_escape_string($_POST['loginintro_5'])."

[v]LOGINOUTRO[/]
".mysql_real_escape_string($_POST['loginoutro_5'])."
'


)";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

////////////////
$sql_a="INSERT INTO resources_log (hidden,name,operator,ip,lastmut,deleted,typ,content_1,content_2,content_3) VALUES (
1,
'::LOGINPAGE::',
'".mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."',
'".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."',
'".time()."',0,'x',

'[v]CONTENT FROM PIDVESA\'s S -> special functions -> define languages -> goto login instructions
    The page ::LOGINPAGE:: itself is non-functional, it is only a content copy of the aforementionad login instructions![/]

[v]LOGININTRO[/]
".mysql_real_escape_string($_POST['loginintro_1'])."

[v]LOGINOUTRO[/]
".mysql_real_escape_string($_POST['loginoutro_1'])."
',


'[v]CONTENT FROM PIDVESA\'s S -> special functions -> define languages -> goto login instructions
    The page ::LOGINPAGE:: itself is non-functional, it is only a content copy of the aforementionad login instructions![/]
	
[v]LOGININTRO[/]
".mysql_real_escape_string($_POST['loginintro_2'])."

[v]LOGINOUTRO[/]
".mysql_real_escape_string($_POST['loginoutro_2'])."
',


'[v]CONTENT FROM PIDVESA\'s S -> special functions -> define languages -> goto login instructions
    The page ::LOGINPAGE:: itself is non-functional, it is only a content copy of the aforementionad login instructions![/]
	
[v]LOGININTRO[/]
".mysql_real_escape_string($_POST['loginintro_3'])."

[v]LOGINOUTRO[/]
".mysql_real_escape_string($_POST['loginoutro_3'])."
',


'[v]CONTENT FROM PIDVESA\'s S -> special functions -> define languages -> goto login instructions
    The page ::LOGINPAGE:: itself is non-functional, it is only a content copy of the aforementionad login instructions![/]
	
[v]LOGININTRO[/]
".mysql_real_escape_string($_POST['loginintro_4'])."

[v]LOGINOUTRO[/]
".mysql_real_escape_string($_POST['loginoutro_4'])."
',


'[v]CONTENT FROM PIDVESA\'s S -> special functions -> define languages -> goto login instructions
    The page ::LOGINPAGE:: itself is non-functional, it is only a content copy of the aforementionad login instructions![/]
	
[v]LOGININTRO[/]
".mysql_real_escape_string($_POST['loginintro_5'])."

[v]LOGINOUTRO[/]
".mysql_real_escape_string($_POST['loginoutro_5'])."
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