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

-->
</style><?php require('backendcss.php'); ?></head>
<body>
<h1>Manage intranet members</h1><?php include('checkidn.inc.php');?>
<?php 
require('autointranetcheck.php');
$sql_a="SELECT * FROM intranetmembers";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}
$row_a = mysql_fetch_array($result_a);
?>

<form name="form1" action="intranetmembers2.php" method="post">
  <p>
  Add/remove  e-mail addresses* of members<span class="Stil1"> </span>allowed to read the intranet**<br />
  <div id="membersdiv" style="display:none"></div><textarea onchange="this.value=this.value.replace(/;/g,',')" name="members" id="members" cols="77" rows="28" id="members"><?php echo htmlXspecialchars(str_replace(',',"\r\n",$row_a['members'])) ?></textarea>
  </p>
<p><small>*Any delimiter style is allowed, even unpurified address lists containing names, street addresses a.s.o can be copied and pasted to here and will be purified automatically. Duplicates are removed automatically.<br />
    **All pages named <em><strong>!...</strong></em> and all files carried on these pages (but not on public pages) will be password protected (i. e. accessible only to intranet members); u5CMS backend users have access to the intranet with their respective logins (i. e. it is not necessary to list the e-mail addresses of u5CMS backend users in the above intranet members form).</small></p>
  <p>
    <input type="submit" name="button" value="save" />
  </p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
</form>
<iframe src="htaccess.php" widht="1" height="1" frameborder="0"></iframe>
<script>
document.getElementById('membersdiv').innerHTML=document.getElementById('members').value;
document.getElementById('members').value=document.getElementById('membersdiv').innerHTML;
window.resizeTo(800, screen.availHeight);
</script>
<?php include('selfclose.inc.php')?>
</body>
</html>
