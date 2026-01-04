<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>manage intranet members</title>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
	document.form1.submit();
})
</script>
<style type="text/css">
<!--
.Stil1 {color: red}
#members { height: calc(100vh - 222px); width:99%; min-height: 123px }
-->
</style><?php require('backendcss.php'); ?></head>
<body>
<h1>Manage intranet members</h1><?php include('checkidn.inc.php');?>
<?php 
require('autointranetcheck.php');
$sql_a="SELECT * FROM intranetmembers";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}
$row_a = mysql_fetch_array($result_a);
?>

<form name="form1" action="intranetmembers2.php" method="post">

  Add/remove e-mail addresses of members allowed to read the intranet:<a style="float:right;" href="javascript:void(0)" onclick="alert('You can paste any amount of text into the field (initially or appended), email addresses are extracted automatically, and stored addresses can access intranet content via password; intranet pages are u5CMS page objects whose names start with an exclamation mark.')">read me!</a><br><br>
  <div id="membersdiv" style="display:none"></div><textarea onchange="this.value=this.value.replace(/;/g,',')" name="members" id="members" cols="77" rows="28" id="members"><?php echo htmlXspecialchars(str_replace(',',"\r\n",$row_a['members'])) ?></textarea>

<input style="width:99%" type="submit" name="button" value="save" />
<?php require('t1.php') ?></form>
<iframe src="intranetmembershtaccess.php" width="1" height="1" frameborder="0"></iframe>
<script>
document.getElementById('membersdiv').innerHTML=document.getElementById('members').value;
document.getElementById('members').value=document.getElementById('membersdiv').innerHTML;
window.resizeTo(800, screen.availHeight);
</script>
<?php include('selfclose.inc.php')?>
</body>
</html>
