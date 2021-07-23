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

$sql_a="UPDATE languages SET 

lan1na='".mysql_real_escape_string($_POST['lan1na'])."',
lan2na='".mysql_real_escape_string($_POST['lan2na'])."',
lan3na='".mysql_real_escape_string($_POST['lan3na'])."',

lan1name='".mysql_real_escape_string($_POST['lan1name'])."',
lan2name='".mysql_real_escape_string($_POST['lan2name'])."',
lan3name='".mysql_real_escape_string($_POST['lan3name'])."',

recherche_d='".mysql_real_escape_string($_POST['recherche_d'])."',
recherche_e='".mysql_real_escape_string($_POST['recherche_e'])."',
recherche_f='".mysql_real_escape_string($_POST['recherche_f'])."',
term_d='".mysql_real_escape_string($_POST['term_d'])."',
term_e='".mysql_real_escape_string($_POST['term_e'])."',
term_f='".mysql_real_escape_string($_POST['term_f'])."',
andhit_d='".mysql_real_escape_string($_POST['andhit_d'])."',
andhit_e='".mysql_real_escape_string($_POST['andhit_e'])."',
andhit_f='".mysql_real_escape_string($_POST['andhit_f'])."',
andhits_d='".mysql_real_escape_string($_POST['andhits_d'])."',
andhits_e='".mysql_real_escape_string($_POST['andhits_e'])."',
andhits_f='".mysql_real_escape_string($_POST['andhits_f'])."',
orhit_d='".mysql_real_escape_string($_POST['orhit_d'])."',
orhit_e='".mysql_real_escape_string($_POST['orhit_e'])."',
orhit_f='".mysql_real_escape_string($_POST['orhit_f'])."',
orhits_d='".mysql_real_escape_string($_POST['orhits_d'])."',
orhits_e='".mysql_real_escape_string($_POST['orhits_e'])."',
orhits_f='".mysql_real_escape_string($_POST['orhits_f'])."',
nohit_d='".mysql_real_escape_string($_POST['nohit_d'])."',
nohit_e='".mysql_real_escape_string($_POST['nohit_e'])."',
nohit_f='".mysql_real_escape_string($_POST['nohit_f'])."',
notpub_d='".mysql_real_escape_string($_POST['notpub_d'])."',
notpub_e='".mysql_real_escape_string($_POST['notpub_e'])."',
notpub_f='".mysql_real_escape_string($_POST['notpub_f'])."',
czoom_d='".mysql_real_escape_string($_POST['czoom_d'])."',	
czoom_e='".mysql_real_escape_string($_POST['czoom_e'])."',
czoom_f='".mysql_real_escape_string($_POST['czoom_f'])."',
picsfound_d='".mysql_real_escape_string($_POST['picsfound_d'])."',	
picsfound_e='".mysql_real_escape_string($_POST['picsfound_e'])."',
picsfound_f='".mysql_real_escape_string($_POST['picsfound_f'])."',
morepics_d='".mysql_real_escape_string($_POST['morepics_d'])."',	
morepics_e='".mysql_real_escape_string($_POST['morepics_e'])."',
morepics_f='".mysql_real_escape_string($_POST['morepics_f'])."'
";

$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>');
}

trxlog('def lang '.$_POST['lan1na'].' '.$_POST['lan2na'].' '.$_POST['lan3na']);
?>
<script>
if(opener)if(opener.parent)if(opener.parent.save)opener.parent.save.location.href='done.php?n=defined languages <?php echo $_POST['lan1na']?> <?php echo $_POST['lan2na']?> <?php echo $_POST['lan3na']?>';
setTimeout("opener.top.location.href=opener.top.location.href",777);
setTimeout("self.close()",1111);
</script>

</body>
</html>