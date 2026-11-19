<?php
ignore_user_abort(true);
set_time_limit(3600);
if (!isset($_GET['name'],$_POST['name']) || !is_string($_GET['name']) || !is_string($_POST['name'])) {
    http_response_code(400);die('Invalid rename request.');
}
$u5RawRenameOld=$_GET['name'];$u5RawRenameNew=$_POST['name'];
// Bound database-side lock waits before authentication or resource reads.
define('U5CMS_RESOURCE_RENAME_REQUEST', true);
require_once('connect.inc.php');
http_response_code(403);require_once('t2.php');http_response_code(200);
require_once('resource-rename.inc.php');

$old=$u5RawRenameOld;$new=$u5RawRenameNew;$updateLinks=($_POST['ulinks'] ?? '')==='yes';
$u5RenameError=null;$u5RenamePlan=null;
try {
    u5RenameName($old,true);
    if (!is_string($new) || strlen($new)<4 || $old===$new) throw new RuntimeException('Choose a different valid resource name.');
    $source=u5RenameStorageRows('SELECT typ,deleted FROM resources WHERE BINARY name='.u5RenameStorageValue($old).' AND deleted!=1 LIMIT 1');
    $pending=u5RenameBase(dirname(__DIR__),$old,$new).'.pending.php';
    if (!$source && is_file($pending)) {
        $saved=u5RenameStorageReadGuarded($pending);$source=array(array('typ'=>$saved['typ'],'deleted'=>$saved['deleted']));
    }
    $codeCheckpoint='../fileversions/codeasset-rename-'.sha1($old."\0".$new).'.pending.php';
    $codePending=is_file($codeCheckpoint);
    if (!$source && $codePending) {
        $saved=u5RenameStorageReadJournal($codeCheckpoint);
        u5RenameStorageEachChange(dirname(__DIR__),$saved['updates'],function($change) use(&$source,$old) {
            $before=$change['before'];
            if (!$source && $change['table']==='resources' && ($before['name'] ?? null)===$old && ($before['deleted'] ?? '1')!=1) $source=array(array('typ'=>$before['typ'],'deleted'=>$before['deleted']));
        });
    }
    $u5RenameAdmin=($archiveRqHIADRI!='no' && ($source[0]['deleted'] ?? '')==2) || ($cssRqHIADRI!='no' && strtolower((string)($source[0]['typ'] ?? ''))==='c');
    if ($u5RenameAdmin) {http_response_code(403);require('accadmin.inc.php');http_response_code(200);}
    $u5RenameCode=function_exists('u5CodeAssetsRename') && (strtolower((string)($source[0]['typ'] ?? ''))==='c' || $codePending);
    if ($u5RenameCode) {
        if (!$u5RenameAdmin && $cssRqHIADRI!='no') {http_response_code(403);require('accadmin.inc.php');http_response_code(200);}
        u5CodeAssetsRename(dirname(__DIR__),$old,$new,$updateLinks);
    } else {
        $u5RenamePlan=u5ResourceRename(dirname(__DIR__),$old,$new,u5RenameOptions($updateLinks));
    }
    trxlog('rename '.$old.' '.$new);
} catch (Throwable $error) {
    http_response_code($error instanceof U5ResourceBusyException ? 409 : 500);
    if ($error instanceof U5ResourceBusyException) header('Retry-After: 2');$u5RenameError=$error;
}
?><!DOCTYPE html>
<html lang="en"><head>
<meta http-equiv="Content-Type" content="text/html; charset=WINDOWS-1252" />
<title>Rename resource</title>
<?php require('backendcss.php'); ?></head><body id="body">
<?php if ($u5RenameError) { ?>
<h1>Rename <?php echo htmlXspecialchars($old).' to '.htmlXspecialchars($new); ?> has not completed</h1>
<p><?php echo htmlXspecialchars($u5RenameError->getMessage()); ?></p>
<p>If a checkpoint exists, retry this same operation to resume it. Completed steps are recognised. A concurrent edit is never silently overwritten. An interrupted operation does not block ordinary saves.</p>
<p><a href="renaming.php" target="_blank" rel="noopener">Show active Rename and recovery plans</a></p>
<form method="post" action="rename2.php?name=<?php echo rawurlencode($old); ?>">
<input type="hidden" name="name" value="<?php echo htmlXspecialchars($new); ?>" />
<input type="hidden" name="ulinks" value="<?php echo $updateLinks ? 'yes' : 'no'; ?>" />
<?php require('t1.php'); ?><button type="submit">Retry rename</button></form>
<?php } else { ?>
<p>Renamed <?php echo htmlXspecialchars($old); ?> to <?php echo htmlXspecialchars($new); ?>. Files, references and history have completed.</p>
<script>
(function(){
 var oldName=<?php echo u5RenameJsString($old); ?>,newName=<?php echo u5RenameJsString($new); ?>;
 if(opener && !opener.closed) {
  var host=opener.parent;
  if(host && host.save)host.save.location.href='done.php?n='+encodeURIComponent('renamed '+oldName+' to '+newName);
  for(var i=1;i<=2;i++) {
   var editor=host && host['i'+i];
   if(editor && editor.document.form1 && editor.document.form1.page && typeof editor.gotopage==='function') {
    var current=editor.document.form1.page.value;
    editor.gotopage(current===oldName ? newName : current);
   }
  }
  if(host && host.i3)host.i3.location.reload();
  else opener.location.reload();
  opener.focus();
 }
 self.close();
})();
</script>
<?php } ?></body></html>
