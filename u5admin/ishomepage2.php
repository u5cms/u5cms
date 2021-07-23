<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>saving...</title>
<?php require('backendcss.php'); ?></head>
<body id="body" bgcolor="#009900">
<?php 
if($definehomepageRqHIADRI!='no')require_once('accadmin.inc.php'); 

$sql_a="UPDATE resources SET ishomepage=0";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>');
}


$sql_a="UPDATE resources SET ishomepage=0";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>');
}

$sql_a="UPDATE resources SET ishomepage=1 WHERE name='".mysql_real_escape_string($_POST['name'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>');
}

trxlog('sethpto '.$_POST['name']);
?>
<script>
if(opener){
if(opener.parent) {

if(opener.parent.i1.document.form1.ishomepage){
if(opener.parent.i1.document.form1.page){
if(opener.parent.i1.document.form1.page.value=='<?php echo $_POST['name']?>')opener.parent.i1.document.form1.ishomepage.value='1';
else opener.parent.i1.document.form1.ishomepage.value='0';
}
}

if(opener.parent.i2.document.form1.ishomepage){
if(opener.parent.i2.document.form1.page){
if(opener.parent.i2.document.form1.page.value=='<?php echo $_POST['name']?>')opener.parent.i2.document.form1.ishomepage.value='1';
else opener.parent.i2.document.form1.ishomepage.value='0';
}
}


}
}

if(opener)if(opener.parent)if(opener.parent.save)opener.parent.save.location.href='done.php?n=set homepage to <?php echo $_POST['name']?>';
  self.close();
</script>

</body>
</html>
