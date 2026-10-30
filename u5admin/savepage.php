<?php require_once('connect.inc.php');require_once('t2.php');require_once('resource-mutation.inc.php');
if (!isset($_POST['page']) || !is_string($_POST['page']) || str_replace(array('_',' '),'',$_POST['page'])==='') {
http_response_code(400);die('ERROR: no page selected. Your editor contents have been kept.');
}
$_POST['name']=$_POST['page'];
$result_a=mysql_query("SELECT operator,ip,lastmut,deleted,typ FROM resources WHERE deleted!=1 AND name='".mysql_real_escape_string($_POST['name'])."' LIMIT 1");
if ($result_a===false) { http_response_code(500);die('SQL_a-Query failed. Your editor contents have been kept.'); }
$row_a=mysql_fetch_array($result_a);
if (!$row_a) { http_response_code(409);die('ERROR: the page no longer exists. Your editor contents have been kept.'); }
if (strtolower((string)$row_a['typ'])!=='p') { http_response_code(409);die('ERROR: this resource is not a page. Its existing type and contents have been kept.'); }
$f9focusBis=basename(trim($_POST['page']));
setcookie('f9focusBis', $f9focusBis, time() + 3600 * 24 * 365 * 10, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=WINDOWS-1252" />
<?php require('backendcss.php'); ?></head>
<body bgcolor="#339900" text="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<?php
require('../config.php');
if ($archiveRqHIADRI!='no' && $row_a['deleted']==2) require('accadminnoclose.inc.php');
$deleted=$row_a['deleted']==2 ? 2 : 0;

$u5OriginalDeleted=$row_a['deleted'];
$u5Saved=false;
$conflict='';
$u5SaveTime=time();
$u5Assignments=array();
$u5Fields=array('ishomepage','logins','hidden');
for ($u5Language=1;$u5Language<=5;$u5Language++) {
foreach (array('content','title','desc','key') as $u5Field) $u5Fields[]=$u5Field.'_'.$u5Language;
}
foreach ($u5Fields as $u5Field) {
if (!isset($_POST[$u5Field]) || !is_scalar($_POST[$u5Field])) die('ERROR: incomplete editor form. The existing page has been kept.');
$u5Value=$_POST[$u5Field];
if ($u5Field=='logins') $u5Value=trim($u5Value);
$u5Assignments[]="`".$u5Field."`='".mysql_real_escape_string($u5Value)."'";
}
$u5Assignments[]="operator='".mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."'";
$u5Assignments[]="ip='".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."'";
$u5Assignments[]="deleted=".(int)$deleted;
$u5Assignments[]="typ='p'";
for ($u5Language=1;$u5Language<=5;$u5Language++) $u5Assignments[]='search_'.$u5Language.'=NULL';

// A single conditional UPDATE preserves the current page if a query fails.
// Retrying is allowed only by the configured overwrite policy.
if (!isset($_POST['coco']) || !is_numeric($_POST['coco'])) die('ERROR: missing editor revision. The existing page has been kept.');
for ($u5Attempt=0;$u5Attempt<3;$u5Attempt++) {
$u5CheckConflict=$checksaveversionconflictinbackend!='none' &&
($checksaveversionconflictinbackend!='foreign' || trim(u5flatidnlower($row_a['operator']))!=trim(u5flatidnlower($_SERVER['PHP_AUTH_USER'])));
if ($row_a['deleted']!=$u5OriginalDeleted || ((int)$_POST['coco']<(int)$row_a['lastmut'] && $u5CheckConflict)) break;
$u5SaveTime=max(time(),(int)$row_a['lastmut']+1);
$u5PreviousRevision=$row_a['lastmut']===NULL ? 'lastmut IS NULL' : 'lastmut='.(int)$row_a['lastmut'];
$sql_a="UPDATE resources SET ".implode(',',$u5Assignments).",lastmut=".$u5SaveTime.
" WHERE name='".mysql_real_escape_string($_POST['name'])."' AND typ='p' AND deleted=".(int)$u5OriginalDeleted." AND ".$u5PreviousRevision." LIMIT 1";
$result_a=mysql_query($sql_a);
if ($result_a==false) die('SQL_a-Query failed! The existing page has been kept.<p>');
if (mysql_affected_rows()==1) { $u5Saved=true; break; }
$result_a=mysql_query("SELECT operator,ip,lastmut,deleted,typ FROM resources WHERE deleted!=1 AND name='".mysql_real_escape_string($_POST['name'])."' LIMIT 1");
if ($result_a==false) die('SQL_a-Query failed!...!<p>');
$row_a=mysql_fetch_array($result_a);
if (!$row_a) break;
if (strtolower((string)$row_a['typ'])!=='p') { http_response_code(409);die('ERROR: the resource type changed. Your editor contents have been kept.'); }
}
if (!$u5Saved) {
$conflict='(!)';
$u5SaveTime=time();
?>
<div style="display:none" id="e"><?php echo htmlspecialchars($row_a ? $row_a['operator'] : 'another editor',ENT_QUOTES,'Windows-1252') ?></div>
<script>
var msgconflict='CONFLICT(!)\n\nYour data has been saved only in the section "text versions". The current page was kept.\n\nLatest editor: '+document.getElementById('e').textContent+'\nLatest known edit: <?php echo $row_a && $row_a['lastmut'] ? date('Y-m-d H:i:s',$row_a['lastmut']) : 'unknown' ?>\n\nA newer version or a changed page state was detected. Please merge your changes manually. Your editor contents remain available.';
</script>
<?php
}

$sql_a="INSERT INTO resources_log (name,ishomepage,content_1,content_2,content_3,content_4,content_5,title_1,title_2,title_3,title_4,title_5,desc_1,desc_2,desc_3,desc_4,desc_5,key_1,key_2,key_3,key_4,key_5,logins,hidden,operator,ip,lastmut,deleted,typ) VALUES 
(
'".mysql_real_escape_string($_POST['name'])."',
'".mysql_real_escape_string($_POST['ishomepage'])."',
'".mysql_real_escape_string($_POST['content_1'])."',
'".mysql_real_escape_string($_POST['content_2'])."',
'".mysql_real_escape_string($_POST['content_3'])."',
'".mysql_real_escape_string($_POST['content_4'])."',
'".mysql_real_escape_string($_POST['content_5'])."',
'".mysql_real_escape_string($_POST['title_1'])."',
'".mysql_real_escape_string($_POST['title_2'])."',
'".mysql_real_escape_string($_POST['title_3'])."',
'".mysql_real_escape_string($_POST['title_4'])."',
'".mysql_real_escape_string($_POST['title_5'])."',
'".mysql_real_escape_string($_POST['desc_1'])."',
'".mysql_real_escape_string($_POST['desc_2'])."',
'".mysql_real_escape_string($_POST['desc_3'])."',
'".mysql_real_escape_string($_POST['desc_4'])."',
'".mysql_real_escape_string($_POST['desc_5'])."',
'".mysql_real_escape_string($_POST['key_1'])."',
'".mysql_real_escape_string($_POST['key_2'])."',
'".mysql_real_escape_string($_POST['key_3'])."',
'".mysql_real_escape_string($_POST['key_4'])."',
'".mysql_real_escape_string($_POST['key_5'])."',
'".mysql_real_escape_string($_POST['logins'])."',
'".mysql_real_escape_string($_POST['hidden'])."',
'".mysql_real_escape_string($_SERVER['PHP_AUTH_USER'].$conflict)."',
'".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."',
'".$u5SaveTime."','$deleted','p')";

$result_a=mysql_query($sql_a);

if ($result_a==false) {
die('SQL_a-Query failed!...!<p>');
}

trxlog('saved'.$conflict.' '.$_POST['name']);

$echo='<a title="focus in repository" href="javascript:void(0)" style="text-decoration:none;background:white" onmouseover="this.click();this.style.background=\'gold\'" onmouseout="this.style.background=\'white\'" onclick="parent.parent.i3.location.href=\'focus.php?c='.rawurlencode(basename(trim($_POST['name']))).'\'">F</a>&nbsp;';
?> 
<div style="width:2px;height:19px;background:white;position:absolute;display:none;top:0px" id="htaccess"></div>
<div id="u5savestatus" style="margin:-2px 0 0 2px;white-space: nowrap;"><?php echo $echo ?><span onmouseover="this.title=this.innerHTML" onclick="alert(this.innerHTML)"><?php echo $conflict ?>saved <?php echo htmlspecialchars($_POST['name'],ENT_QUOTES,'Windows-1252')?></span></div>
<iframe style="display:none" src="../indexer.php?n=<?php echo rawurlencode($_POST['name']) ?>"></iframe>
<script>
if (parent.i3) parent.i3.location.reload();
</script>
<?php
$u5Frame=isset($_GET['i']) && in_array($_GET['i'],array('i1','i2'),true) ? $_GET['i'] : '';
$u5Serial=isset($_POST['u5saveid']) && preg_match('/^[a-zA-Z0-9_]{1,80}$/',$_POST['u5saveid']) ? $_POST['u5saveid'] : '';
$u5JsPage="'".str_replace(array("\\","'","\r","\n","<"),array("\\\\","\\'","\\r","\\n","\\x3c"),$_POST['name'])."'";
?>
<script>
window.u5SaveAcknowledged=true;
var savedEditor=parent.frames['<?php echo $u5Frame ?>'];
var clean=false;
if(savedEditor && typeof savedEditor.postsubmitfunction==='function')
clean=savedEditor.postsubmitfunction('<?php echo $u5Serial ?>',<?php echo $u5JsPage ?>,<?php echo (int)$u5SaveTime ?>,<?php echo $u5Saved ? 'true' : 'false' ?>);
if(parent.u5ShowSaveResult && (clean || !<?php echo $u5Saved ? 'true' : 'false' ?>)) parent.u5ShowSaveResult(document.getElementById('u5savestatus'));
<?php if (!$u5Saved) { ?>
alert(msgconflict);
window.open('textversions.php?c=<?php echo rawurlencode($_POST['name']) ?>','_blank','toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=640,height=500');
<?php } ?>
</script>
<iframe width="1" height="1" scrolling="no" frameborder="0" src="lastsave.php"></iframe>
<?php require('countblockrefresh.inc.php'); ?>
</body>
</html>
