<?php
require_once('connect.inc.php');
require_once('t2.php');
require_once('resourceupdate.inc.php');
try {
$name=isset($_POST['name']) && is_string($_POST['name']) ? $_POST['name'] : '';
$row=u5ResourceForUpdate($name);
u5CodeAssetFilename($name);
if ($archiveRqHIADRI!='no' && $row['deleted']==2) require('accadmin.inc.php');
if ($cssRqHIADRI!='no') require('accadmin.inc.php');
$changes=array();
for ($language=1;$language<=5;$language++) foreach (array('content','title','desc','key') as $field) {
$column=$field.'_'.$language;
if (!isset($_POST[$column])) {
continue;
}
if (!is_string($_POST[$column])) throw new RuntimeException('Invalid resource field.');
$changes[$column]=$_POST[$column];
}
if (!isset($changes['content_1'])) throw new RuntimeException('Incomplete code form.');
if (isset($_POST['logins'])) {
if (!is_string($_POST['logins'])) throw new RuntimeException('Invalid resource logins.');
$changes['logins']=trim($_POST['logins']);
}
$changes['typ']=isset($_GET['typ']) && is_string($_GET['typ']) && $_GET['typ']!=='' ? $_GET['typ'] : $row['typ'];
if (($_POST['name']=='htmltemplate' || $_POST['name']==u5CodeTemplateName())) {
$teststr=$_POST['content_1'];
$teststr=str_replace("\t",' ',$teststr);
$teststr=str_replace("\r",' ',$teststr);
$teststr=str_replace("\n",' ',$teststr);
$teststr=str_replace('  ',' ',$teststr);
$teststr=str_replace('  ',' ',$teststr);
$teststr=str_replace('  ',' ',$teststr);
$teststr=str_replace('  ',' ',$teststr);
if (strpos(strtolower('xxx'.$teststr),'<body id=\"body\"')>1) die('<script>alert("ERROR: Cannot save because your server automatically adds slashes. Solve this problem by setting $quotehandling=\'on\' in config.php in the CMS directory on your server.")</script>');
if (strpos(strtolower('xxx'.$teststr),'<body id="body"')<1) die('<script>alert("ERROR: Cannot save htmltemplate because it is not containing a correct body tag. The body tag MUST have the id body as first attribute like this: <body id=\"body\">")</script>');
}


$result=u5UpdateResource($name,$changes,$row,$_POST['coco'] ?? null);
u5LogResourceAttempt($result);
trxlog('code'.($result['saved'] ? '' : '(!)').' '.$name);
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
if ($result['saved']) require('putcss.inc.php');
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
try { if(parent.opener && parent.opener.parent && parent.opener.parent.save) parent.opener.parent.save.location.href='done.php?n=updated <?php echo rawurlencode($name) ?>'; } catch(error) {}
}
// Older backend documents receive status but are never forced to reload.
if(!parent.u5ResourceSave && success && parent.form1 && parent.form1.coco) parent.form1.coco.value=<?php echo (int)$result['stamp'] ?>;
</script>
</body>
</html>
