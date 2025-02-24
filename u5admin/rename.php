<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>rename <?php echo $_GET['name']?></title>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {

})
</script>
<?php require('backendcss.php'); ?></head>

<body>
<div id="hider" style="display:none">
<span id="spinner"></span><form id="form1" name="form1" method="post" action="rename2.php?name=<?php echo $_GET['name']?>" onsubmit="return check()">
  <h1>Rename <i><?php echo $_GET['name']?></i></h1>
  <p>Enter a new name for that item</p>
  <p>
    <input value="<?php echo $_GET['name']?>" name="name" type="text" onchange="if (this.value!=validated(this.value)) this.value=validated(this.value);nc.location.href='nc.php?name='+this.value" onkeyup="if (this.value!=validated(this.value)) this.value=validated(this.value);nc.location.href='nc.php?name='+this.value" size="20" maxlength="20" />
     | Update links? 
     <input name="ulinks" type="radio" value="yes" checked="checked" />
    yes / <input type="radio" name="ulinks" value="no" />
    no<br>
    </p>
  <br><br><br><br><br><br><br><br><iframe name="nc" frameborder="0" scrolling="no" width="500" height="50" src="nc.php"></iframe><br>
<input type="submit" value="rename" onclick="if ('<?php echo $_GET['name']?>'==document.form1.name.value) alert('ERROR: New name and old name must not be identical!')">
<?php require('t1.php') ?></form>
<script>
<?php include('az90.php'); ?>
function check() {
if (nc.document.form1.ok.value=='ok') {
setTimeout("location.href='renaming.php?n=<?php echo $_GET['name'] ?>'",7777);	
document.getElementById('spinner').innerHTML='<img src="../upload/spinner.gif" />';
document.getElementById('form1').style.display='none';
return true;
}
else return false;
}
</script>
</div>
<script>
changes=0;
if(opener)if(opener.parent)if(opener.parent.i1.changes)changes+=opener.parent.i1.changes;
if(opener)if(opener.parent)if(opener.parent.i2.changes)changes+=opener.parent.i2.changes;
if(changes>0) {
alert('You have to save the changes made in the editor(s) before performing a rename command.');
self.close();
}
else {
document.getElementById('hider').style.display='block';
document.form1.name.focus();
}
</script>

<?php include('focuslinktitle.inc.php') ?>
</body>
</html>
