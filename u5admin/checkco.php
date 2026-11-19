<?php require('connect.inc.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=WINDOWS-1252" />
</head>
<body>
<?php
$sql_a="SELECT operator,ip,lastmut FROM resources WHERE deleted!=1 AND name='".mysql_real_escape_string($_GET['name'])."' LIMIT 1";
$result_a=mysql_query($sql_a);
if ($result_a==false) die('SQL_a-Query failed!...!<p>');
$row_a=mysql_fetch_array($result_a);
if ($row_a && (int)$_GET['coco']<(int)$row_a['lastmut']) {
function checkCoJsString($value) {
return "'".str_replace(array("\\","'","\r","\n","<"),array("\\\\","\\'","\\r","\\n","\\x3c"),$value)."'";
}
$message='Conflict with '.$row_a['operator'].' who has saved a new version of '.$_GET['name'].' during your editing session (at '.date('Y-m-d H:i:s',$row_a['lastmut']).").\n\nPlease save your page immediately. You automatically will receive instructions how to resolve the conflict afterwards.";
?>
<script>
(function() {
var form=parent.document.form1;
if (!form || !form.page || !form.coco || form.page.value!==<?php echo checkCoJsString($_GET['name']) ?>) return;
// A response can arrive after a save or after the next keystroke.
if (Number(form.coco.value)>=<?php echo (int)$row_a['lastmut'] ?> || parent.parent.u5SavePending) return;
var button=parent.document.getElementById('savebutton');
var dirty=Number(parent.changes)>0 || (button && button.textContent.indexOf('*')!==-1);
if (!dirty) parent.location.reload();
else {
top.document.title=<?php echo checkCoJsString($message) ?>;
parent.document.body.style.background='red';
}
})();
</script>
<?php } ?>
</body>
</html>
