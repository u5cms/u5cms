<?php
setcookie('dora', 'd', time() + 3600 * 24 * 365 * 10, '/');
require_once('connect.inc.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>delete</title>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
	document.form1.submit();
})
</script>
<?php require('backendcss.php'); ?></head>

<body>
<script>
function areyousure() {
if (!document.form1.dfiles[0].checked && !document.form1.dfiles[1].checked) {
alert('Please indicate, if possible corresponding files shall be deleted.\n\nIf you do not delete the possible corresponding files, the item will be flagged as deleted in the database, but (in case of PIDVESAs I, D and V), the files are still surfable and the item remains fully recoverable by re-creating it with the new-button.\n\nYour selection (if to delete possible corresponding files or not) has of course no importance for items of PIDVESAs P and E.');
return false;
}
return confirm('Do you really want to delete\n\n'+document.form1.name.value+'\n\n?');	
}
</script>
<form id="form1" name="form1" method="post" action="delete2.php?name=<?php echo $_GET['name']?>" onsubmit="return areyousure()">
  <h1>Delete an item <span style="font-size:40%"><a href="archiveunarchive.php?name=<?php echo $_GET['name'] ?>">go to archive/unarchive</a></span></h1>
  <p>To confirm deletion, enter the name of the item  you are comig from (having clicked 'D'):</p>
  <p>
    <input name="name" type="text" onchange="if (this.value!=validated(this.value)) this.value=validated(this.value);" onkeyup="if (this.value!=validated(this.value)) this.value=validated(this.value);" size="20" maxlength="20" />
    | Delete files? 
     <input name="dfiles" type="radio" value="yes" />
yes /
<input type="radio" name="dfiles" value="no" />
no<br><br><br><br><br><br><br><br>
    </p>
<input type="submit" value="delete" id="submitbutton">
</form>
<script>
<?php include('az90.php'); ?>
document.form1.name.focus();
</script>
<?php include('selfclose.inc.php')?>
<?php
if($_COOKIE['u5cmsfastdelete']==1 && $fastdeleteforbidden!='yes') {
    $sql_a = "SELECT name,deleted FROM resources WHERE deleted!=1";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
    $num_a = mysql_num_rows($result_a);
    for ($i_a = 0; $i_a < $num_a; $i_a++) {
    $row_a = mysql_fetch_array($result_a);
	$f='<span style="font-size:40%"><a style="text-decoration:none" title="focus in repository" href="javascript:void(0)" onclick="if(!opener){alert(\'Context window missing!\')} else {opener.parent.i3.location.href=\'focus.php?c='.$row_a['name'].'\'}">F</a>&nbsp;</span>';
	if(md5($row_a['name'])==$_GET['name'])echo'<script>
	document.form1.name.value="'.$row_a['name'].'";
	document.getElementsByName("dfiles")[0].checked=true;
	document.getElementById("submitbutton").click();
	</script>';
	}
}
?>
</body>
</html>
