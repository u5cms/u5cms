<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>define title fixum</title>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
	cchanges=0;document.form1.submit();
})
</script>
<?php require('backendcss.php'); ?></head>

<body>
<form onsubmit="cchanges=0" action="titlefixum2.php" method="post" name="form1" id="form1">
  <p>
    <?php 

$sql_a="SELECT * FROM titlefixum";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}


$row_a = mysql_fetch_array($result_a);

?>
  </p>
  <h1>Define title fixum</h1>
Every page has a title which is displayed as browser frame name and as title in search enginge results. This title is stored in the meta data of every page (see m-button in one of the page editors). These variable titles are complemented (on their right) by a dash followed by a fixum (usually the company name and/or company slogan).<br /><br />
It's recommended that you start with a space, followed by a delimiter (<a href="javascript:void(0)" onclick="window.open('../characters','_blank','toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=1200,height=1000')">list of characters</a>), followed by a space and the text. E.g.:<b> — BeFoo, the food and beverage company</b>
<br /><br />
<table bgcolor="#FFFF99" width="100%">
    <tr id="lan1name">
      <td><?php echo $lan1name ?></td>
      <td width="99%"><input name="1" type="text" id="t11" style="width:100%" value="<?php echo ehtml($row_a['1'])?>" /></td>
    </tr>
    <tr id="lan2name">
      <td><?php echo $lan2name ?></td>
      <td><input name="2" type="text" id="t12" style="width:100%" value="<?php echo ehtml($row_a['2'])?>" /></td>
    </tr>
    <tr id="lan3name">
      <td><?php echo $lan3name ?></td>
      <td><input name="3" type="text" id="t13" style="width:100%" value="<?php echo ehtml($row_a['3'])?>" /></td>
    </tr>
    <tr id="lan4name">
        <td><?php echo $lan4name ?></td>
        <td><input name="4" type="text" id="t14" style="width:100%" value="<?php echo ehtml($row_a['4'])?>" /></td>
    </tr>
    <tr id="lan5name">
        <td><?php echo $lan5name ?></td>
        <td><input name="5" type="text" id="t13" style="width:100%" value="<?php echo ehtml($row_a['5'])?>" /></td>
    </tr>
  </table>
  <p><input type="submit" title="[Ctrl+s]" name="button" value="save &amp; close" /><span class="asterisk" style="display:none;color:red">*</span>
  <?php include('metachg.inc.php') ?><script>initchanges()</script>
  </p>
  <p></p>
<?php require('t1.php') ?></form>
<script>document.form1.d.focus()</script>
<script src="emptylanhide.js.php"></script>
<?php include('selfclose.inc.php')?>
<script>
function putdoins(that) {
if(document.form1.1.value[0]==' ')document.form1.1.value=' '+that+' '+document.form1.1.value.substr(3);
else document.form1.1.value=' '+that+' '+document.form1.1.value;
if(document.form1.2.value[0]==' ')document.form1.1.value=' '+that+' '+document.form1.2.value.substr(3);
else document.form1.2.value=' '+that+' '+document.form1.2.value;
if(document.form1.3.value[0]==' ')document.form1.3.value=' '+that+' '+document.form1.3.value.substr(3);
else document.form1.3.value=' '+that+' '+document.form1.3.value;
if(document.form1.4.value[0]==' ')document.form1.4.value=' '+that+' '+document.form1.4.value.substr(3);
else document.form1.4.value=' '+that+' '+document.form1.4.value;
if(document.form1.5.value[0]==' ')document.form1.5.value=' '+that+' '+document.form1.5.value.substr(3);
else document.form1.5.value=' '+that+' '+document.form1.5.value;
}
</script>
</body>
</html>
