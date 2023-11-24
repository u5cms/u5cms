<?php
setcookie('dora', 'a', time() + 3600 * 24 * 365 * 10, '/');
require_once('connect.inc.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>define archive status</title>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
	document.form1.submit();
})
</script>
<?php require('backendcss.php'); ?></head>

<body>
<form id="form1" name="form1" method="post" action="archiveunarchive2.php?name=<?php echo $_GET['name']?>">
  <h1><span id="h1title">Define (toggle) archive status of an item</span> <span style="font-size:40%"><a href="deleteanitem.php?name=<?php echo $_GET['name'] ?>">go to delete</a></span></h1>
  <p>To confirm, enter the name of the item  you are comig from (having clicked 'D'):</p>
  <p>
    <input name="name" type="text" onchange="if (this.value!=validated(this.value)) this.value=validated(this.value);" onkeyup="if (this.value!=validated(this.value)) this.value=validated(this.value);" size="20" maxlength="20" />
    <br><br>
    <small>An archived item may be still surfable (depending on its publicity status) but it will be listed in PIDVESA's archive section <span style="font-size:80%;color:white;background:black">a</span> instead of PIDVESA's current section. Use archiving according to your needs. Suggestions: Move delicate items (which should not be edited accidentally) to the archive or keep PIDVESAs current lists  short by moving rarely touched items to the archive. </small><br><br><br><br><br><br>
    </p>
<input id="submitbutton" type="submit" value="define (toggle) archive status">
<?php require('t1.php') ?></form>
<script>
<?php include('az90.php'); ?>
document.form1.name.focus();
</script>
<?php include('selfclose.inc.php')?>
<?php
    $sql_a = "SELECT name,deleted FROM resources WHERE deleted!=1";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
    $num_a = mysql_num_rows($result_a);
    for ($i_a = 0; $i_a < $num_a; $i_a++) {
    $row_a = mysql_fetch_array($result_a);
	$f='<span style="font-size:40%"><a style="text-decoration:none" title="focus in repository" href="javascript:void(0)" onclick="if(!opener){alert(\'Context window missing!\')} else {opener.parent.i3.location.href=\'focus.php?c='.$row_a['name'].'\'}">F</a>&nbsp;</span>';
	if(md5($row_a['name'])==$_GET['name'])echo'<script>
	document.form1.name.value="'.$row_a['name'].'";
	if("'.$row_a['deleted'].'"==0) document.getElementById("h1title").innerHTML="Define <i>'.$row_a['name'].'</i> '.addslashes($f).' as archived";
	if("'.$row_a['deleted'].'"==0) document.getElementById("submitbutton").value="archive";
	if("'.$row_a['deleted'].'"==2) document.getElementById("h1title").innerHTML="Define <i>'.$row_a['name'].'</i> '.addslashes($f).' as unarchived";
	if("'.$row_a['deleted'].'"==2) document.getElementById("submitbutton").value="unarchive";
	</script>';
	}
?>
</body>
</html>
