<?php
require_once('connect.inc.php');
require_once('t2.php');
require_once('resourceupdate.inc.php');
try {
$name=isset($_POST['name']) && is_string($_POST['name']) ? $_POST['name'] : '';
$row=u5ResourceForUpdate($name);
if ($archiveRqHIADRI!='no' && $row['deleted']==2) require('accadmin.inc.php');
if ($cssRqHIADRI!='no' && $row['typ']=='c') require('accadmin.inc.php');
$changes=array();
for ($language=1;$language<=5;$language++) foreach (array('content','title','desc','key') as $field) {
$column=$field.'_'.$language;
if (!isset($_POST[$column])) {
if ($field!='key') throw new RuntimeException('Incomplete metadata form. The current resource has been kept.');
continue;
}
if (!is_string($_POST[$column])) throw new RuntimeException('Invalid resource field.');
$changes[$column]=trim($_POST[$column]);
}
if (isset($_POST['logins'])) {
if (!is_string($_POST['logins'])) throw new RuntimeException('Invalid resource logins.');
$changes['logins']=trim($_POST['logins']);
}
$changes['typ']=isset($_GET['typ']) && is_string($_GET['typ']) && $_GET['typ']!=='' ? $_GET['typ'] : $row['typ'];

$result=u5UpdateResource($name,$changes,$row,$_POST['coco'] ?? null);
u5LogResourceAttempt($result);
trxlog('meta'.($result['saved'] ? '' : '(!)').' '.$name);
} catch (RuntimeException $error) {
http_response_code(500);
echo '<p>'.htmlXspecialchars($error->getMessage()).'</p>';
exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta http-equiv="Content-Type" content="text/html; charset=WINDOWS-1252" />
<?php require('backendcss.php'); ?></head>
<body id="body" bgcolor="#339900" text="#FFFFFF">
<?php
$nonce=isset($_POST['u5resourceid']) && is_string($_POST['u5resourceid']) && preg_match('/^[a-zA-Z0-9_]{1,80}$/',$_POST['u5resourceid']) ? $_POST['u5resourceid'] : '';
$current=$result['current'];
$message='CONFLICT: The current resource was kept. Your submitted content is in text versions. Latest editor: '.($current ? $current['operator'] : 'unknown').'. Merge your changes manually; your editor contents remain available.';
?>
<div>saved <?php echo $result['saved'] ? '' : '(!)' ?><?php echo htmlXspecialchars($name) ?></div>
<script>
window.u5ResourceAcknowledged=true;
var success=<?php echo $result['saved'] ? 'true' : 'false' ?>;
var clean=false;
if(parent.u5ResourceSave) clean=parent.u5ResourceSave.complete('<?php echo $nonce ?>',<?php echo u5ResourceJsString($name) ?>,<?php echo (int)$result['stamp'] ?>,success);
if(!success) {
alert(<?php echo u5ResourceJsString($message) ?>);
window.open('textversions.php?c=<?php echo rawurlencode($name) ?>','_blank','toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=640,height=500');
}
if(clean) {
if(parent.name==='ametai') {
var owner=parent.parent;
if(owner.document.getElementById('sacabu')) owner.document.getElementById('sacabu').style.display='none';
owner.location.reload();
} else {
try { if(parent.opener && parent.opener.parent && parent.opener.parent.save) parent.opener.parent.save.location.href='done.php?n=updated <?php echo rawurlencode($name) ?>'; } catch(error) {}
parent.close();
}
}
<?php if ($nonce==='') { ?>
// Preserve navigation for old forms, while retaining data on a conflict.
if(success) {
if(window.name==='ametai') parent.location.reload();
else self.close();
} else {
var back=<?php $uri=isset($_GET['uri']) && in_array($_GET['uri'],array('meta.php','metaf.php','metad.php','metai.php','metae.php','metay.php'),true) ? $_GET['uri'] : 'meta.php';echo u5ResourceJsString($uri.'?name='.rawurlencode($name).'&typ='.rawurlencode($changes['typ'])); ?>;
var form=document.createElement('form');form.method='post';form.action=back;
<?php foreach ($result['record'] as $field=>$value) { if (preg_match('/^(content_[1-5]|title_[1-5]|desc_[1-5]|key_[1-5]|logins)$/',$field)) { ?>
var input=document.createElement('textarea');input.name=<?php echo u5ResourceJsString($field) ?>;input.value=<?php echo u5ResourceJsString($value ?? '') ?>;form.appendChild(input);
<?php } } ?>
document.body.appendChild(form);
}
<?php } ?>
</script>
</body>
</html>
