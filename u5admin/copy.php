<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>copy <?php echo $_GET['name']?></title>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
	
})
</script>
<?php require('backendcss.php'); ?></head>

<body>
<span id="spinner"></span><form id="form1" name="form1" method="post" action="copy2.php?name=<?php echo $_GET['name']?>" onsubmit="return check()">
  <h1>Copy <i><?php echo $_GET['name']?></i></h1>
  <p>Enter a new name for the item</p>
  <p>
    <input value="<?php echo $_GET['name']?>" name="name" type="text" onchange="if (this.value!=validated(this.value)) this.value=validated(this.value);nc.location.href='nc.php?name='+this.value" onkeyup="if (this.value!=validated(this.value)) this.value=validated(this.value);nc.location.href='nc.php?name='+this.value" size="20" maxlength="20" />
   
 <br>
    </p>
  <br><br><br><br><br><br><br><br><iframe name="nc" frameborder="0" scrolling="no" width="500" height="50" src="nc.php"></iframe><br>
<input type="submit" value="copy" onclick="if ('<?php echo $_GET['name']?>'==document.form1.name.value) alert('ERROR: New name and old name must not be identical!')">
<span id="go" style="display:none;font-size:10px">&nbsp;automatically go to the copied page<input id="copygo" name="go" type="checkbox" value="yes" checked="checked" 
onchange="
if(this.checked==true)document.cookie='copygo=yes; expires=Thu, 31 Dec 2037 12:00:00 GMT';
else document.cookie='copygo=no; expires=Thu, 31 Dec 2037 12:00:00 GMT';
" />
<script>
if (document.cookie.indexOf('copygo=')>-1){ 
if(('; '+document.cookie).split('; copygo=')[1].split(';')[0]=='no')document.getElementById('copygo').checked=false;
}
<?php
$sql_a="SELECT typ FROM resources WHERE name='".mysql_real_escape_string($_GET['name'])."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$row_a = mysql_fetch_array($result_a);
?>
if('<?php echo $row_a['typ']?>'=='p')document.getElementById('go').style.display='inline';
</script>
<?php if ($_COOKIE['ns'] == 'true') {
if($_GET['wn']=='i1')$i1selected='selected="selected"';
else $i1selected='';
echo '
<select name="wn" style="font-size:90%">
<option value="i2">in editor on the right</option>
<option '.$i1selected.' value="i1">in editor on the left</option>
</select>
';
}
?>
<?php require('t1.php') ?></form></span>
<script>
<?php include('az90.php'); ?>
function check() {
if (nc.document.form1.ok.value=='ok') {
document.getElementById('spinner').innerHTML='<img src="../upload/spinner.gif" />';
document.getElementById('form1').style.display='none';
return true;
}
else return false;
}
document.form1.name.focus();
</script>

<?php include('focuslinktitle.inc.php') ?>
</body>
</html>
