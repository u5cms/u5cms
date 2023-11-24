<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>new</title>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
	
})
</script>
<?php require('backendcss.php'); ?></head>
<body>
<span id="spinner"></span><form id="form1" name="form1" method="post" action="new2.php?typ=<?php echo $_GET['typ']?>" onsubmit="return check()">
  <h1>New item of type <span style="color:red"><?php echo $_GET['typ']?></span></h1>
  <p>Enter a name for the new item.
  
  <?php 
  if ($_GET['typ']=='i') {
  echo 'You are in single image mode. More than one image to show?<br>&rarr; <a href="new.php?typ=a">switch to album mode</a> now.';
  }
  if ($_GET['typ']=='a') {
  echo 'You are in album mode. Only one image to show?<br>&rarr; <a href="new.php?typ=i">switch to single image mode</a> now.';
  }
  ?>
  
  </p>
  <p>
    <input name="name" type="text" onchange="if (this.value!=validated(this.value)) this.value=validated(this.value);nc.location.href='nc.php?name='+this.value" onkeyup="if (this.value!=validated(this.value)) this.value=validated(this.value);nc.location.href='nc.php?name='+this.value" size="20" maxlength="20" />
    <br>
    <input type="hidden" name="typ" value="<?php echo $_GET['typ'] ?>">
    </p>
  <br><br><br><br><br><br><br><br><iframe name="nc" frameborder="0" scrolling="no" width="500" height="50" src="nc.php"></iframe><br>
<input type="submit" value="insert new item">
<span id="go" style="display:none;font-size:10px">&nbsp;automatically go to the new page<input id="newgo" name="go" type="checkbox" value="yes" checked="checked" 
onchange="
if(this.checked==true)document.cookie='newgo=yes; expires=Thu, 31 Dec 2037 12:00:00 GMT';
else document.cookie='newgo=no; expires=Thu, 31 Dec 2037 12:00:00 GMT';
" /></span>
<script>
if (document.cookie.indexOf('newgo=')>-1){ 
if(('; '+document.cookie).split('; newgo=')[1].split(';')[0]=='no')document.getElementById('newgo').checked=false;
}
if('<?php echo $_GET['typ']?>'=='p')document.getElementById('go').style.display='inline';
</script>
<?php require('t1.php') ?></form>
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
<?php include('selfclose.inc.php')?>
</body>
</html>
