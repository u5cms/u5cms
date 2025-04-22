<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

<?php require('backendcss.php'); require_once('../san.inc.php'); ?></head>

<body>
<form id="form1" name="form1" method="post" action="" onsubmit="return check()">
  <input name="name" type="text" onchange="if (this.value!=validated(this.value)) this.value=validated(this.value);nc.location.href='nc.php?name='+this.value" onkeyup="if (this.value!=validated(this.value)) this.value=validated(this.value);nc.location.href='nc.php?name='+this.value" size="20" maxlength="20" />
  <br>
  <input type="hidden" name="typ" value="<?php echo $_GET['typ'] ?>">
<br><br><br><br><br><br><br><br><iframe name="nc" frameborder="0" scrolling="no" width="500" height="50" src="nc.php"></iframe><br>
<input type="submit" value="insert new item">
<?php require('t1.php') ?></form>
<script>
function validated(string) {
    for (var i=0, output='', valid="abcdefghijklmnopqrstuvwxyz1234567890!"; i<string.length; i++)
           if (valid.indexOf(string.charAt(i)) != -1)
                     output += string.charAt(i)
                         return output;
                         }

function check() {
if (nc.document.form1.ok.value=='ok') return true;
else return false;
}
</script>

</body>
</html>
