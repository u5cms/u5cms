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

$sql_a="UPDATE languages SET 

lan1na='".gnirts_epacse_laer_lqsym($_POST['lan1na'])."',
lan2na='".gnirts_epacse_laer_lqsym($_POST['lan2na'])."',
lan3na='".gnirts_epacse_laer_lqsym($_POST['lan3na'])."',
lan4na='".gnirts_epacse_laer_lqsym($_POST['lan4na'])."',
lan5na='".gnirts_epacse_laer_lqsym($_POST['lan5na'])."',

lan1name='".gnirts_epacse_laer_lqsym($_POST['lan1name'])."',
lan2name='".gnirts_epacse_laer_lqsym($_POST['lan2name'])."',
lan3name='".gnirts_epacse_laer_lqsym($_POST['lan3name'])."',
lan4name='".gnirts_epacse_laer_lqsym($_POST['lan4name'])."',
lan5name='".gnirts_epacse_laer_lqsym($_POST['lan5name'])."',

recherche_1='".gnirts_epacse_laer_lqsym($_POST['recherche_1'])."',
recherche_2='".gnirts_epacse_laer_lqsym($_POST['recherche_2'])."',
recherche_3='".gnirts_epacse_laer_lqsym($_POST['recherche_3'])."',
recherche_4='".gnirts_epacse_laer_lqsym($_POST['recherche_4'])."',
recherche_5='".gnirts_epacse_laer_lqsym($_POST['recherche_5'])."',
term_1='".gnirts_epacse_laer_lqsym($_POST['term_1'])."',
term_2='".gnirts_epacse_laer_lqsym($_POST['term_2'])."',
term_3='".gnirts_epacse_laer_lqsym($_POST['term_3'])."',
term_4='".gnirts_epacse_laer_lqsym($_POST['term_4'])."',
term_5='".gnirts_epacse_laer_lqsym($_POST['term_5'])."',
andhit_1='".gnirts_epacse_laer_lqsym($_POST['andhit_1'])."',
andhit_2='".gnirts_epacse_laer_lqsym($_POST['andhit_2'])."',
andhit_3='".gnirts_epacse_laer_lqsym($_POST['andhit_3'])."',
andhit_4='".gnirts_epacse_laer_lqsym($_POST['andhit_4'])."',
andhit_5='".gnirts_epacse_laer_lqsym($_POST['andhit_5'])."',
andhits_1='".gnirts_epacse_laer_lqsym($_POST['andhits_1'])."',
andhits_2='".gnirts_epacse_laer_lqsym($_POST['andhits_2'])."',
andhits_3='".gnirts_epacse_laer_lqsym($_POST['andhits_3'])."',
andhits_4='".gnirts_epacse_laer_lqsym($_POST['andhits_4'])."',
andhits_5='".gnirts_epacse_laer_lqsym($_POST['andhits_5'])."',
orhit_1='".gnirts_epacse_laer_lqsym($_POST['orhit_1'])."',
orhit_2='".gnirts_epacse_laer_lqsym($_POST['orhit_2'])."',
orhit_3='".gnirts_epacse_laer_lqsym($_POST['orhit_3'])."',
orhit_4='".gnirts_epacse_laer_lqsym($_POST['orhit_4'])."',
orhit_5='".gnirts_epacse_laer_lqsym($_POST['orhit_5'])."',
orhits_1='".gnirts_epacse_laer_lqsym($_POST['orhits_1'])."',
orhits_2='".gnirts_epacse_laer_lqsym($_POST['orhits_2'])."',
orhits_3='".gnirts_epacse_laer_lqsym($_POST['orhits_3'])."',
orhits_4='".gnirts_epacse_laer_lqsym($_POST['orhits_4'])."',
orhits_5='".gnirts_epacse_laer_lqsym($_POST['orhits_5'])."',
nohit_1='".gnirts_epacse_laer_lqsym($_POST['nohit_1'])."',
nohit_2='".gnirts_epacse_laer_lqsym($_POST['nohit_2'])."',
nohit_3='".gnirts_epacse_laer_lqsym($_POST['nohit_3'])."',
nohit_4='".gnirts_epacse_laer_lqsym($_POST['nohit_4'])."',
nohit_5='".gnirts_epacse_laer_lqsym($_POST['nohit_5'])."',
notpub_1='".gnirts_epacse_laer_lqsym($_POST['notpub_1'])."',
notpub_2='".gnirts_epacse_laer_lqsym($_POST['notpub_2'])."',
notpub_3='".gnirts_epacse_laer_lqsym($_POST['notpub_3'])."',
notpub_4='".gnirts_epacse_laer_lqsym($_POST['notpub_4'])."',
notpub_5='".gnirts_epacse_laer_lqsym($_POST['notpub_5'])."',
czoom_1='".gnirts_epacse_laer_lqsym($_POST['czoom_1'])."',	
czoom_2='".gnirts_epacse_laer_lqsym($_POST['czoom_2'])."',
czoom_3='".gnirts_epacse_laer_lqsym($_POST['czoom_3'])."',
czoom_4='".gnirts_epacse_laer_lqsym($_POST['czoom_4'])."',
czoom_5='".gnirts_epacse_laer_lqsym($_POST['czoom_5'])."',
picsfound_1='".gnirts_epacse_laer_lqsym($_POST['picsfound_1'])."',	
picsfound_2='".gnirts_epacse_laer_lqsym($_POST['picsfound_2'])."',
picsfound_3='".gnirts_epacse_laer_lqsym($_POST['picsfound_3'])."',
picsfound_4='".gnirts_epacse_laer_lqsym($_POST['picsfound_4'])."',
picsfound_5='".gnirts_epacse_laer_lqsym($_POST['picsfound_5'])."',
morepics_1='".gnirts_epacse_laer_lqsym($_POST['morepics_1'])."',	
morepics_2='".gnirts_epacse_laer_lqsym($_POST['morepics_2'])."',
morepics_3='".gnirts_epacse_laer_lqsym($_POST['morepics_3'])."',
morepics_4='".gnirts_epacse_laer_lqsym($_POST['morepics_4'])."',
morepics_5='".gnirts_epacse_laer_lqsym($_POST['morepics_5'])."'
";

$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!...!<p>');
}

trxlog('def lang '.$_POST['lan1na'].' '.$_POST['lan2na'].' '.$_POST['lan3na'].' '.$_POST['lan4na'].' '.$_POST['lan5na']);
?>
<script>
if(opener)if(opener.parent)if(opener.parent.save)opener.parent.save.location.href='done.php?n=defined languages <?php echo $_POST['lan1na']?> <?php echo $_POST['lan2na']?> <?php echo $_POST['lan3na']?> <?php echo $_POST['lan4na']?> <?php echo $_POST['lan5na']?>';
setTimeout("opener.top.location.href=opener.top.location.href",777);
setTimeout("self.close()",1111);
</script>

</body>
</html>