<?php 
ignore_user_abort(true); 
echo "<script>top.document.title='#'+top.document.title</script>";
unlink('../fileversions/BackupedFilesExclCMSsys.zip');
$path='../u5admin/';
if ($handle = @opendir($path))  { 
while (false !== ($file = readdir($handle)))  { 
if (strpos('x'.$file,'pclzip-')==1) unlink($file);
}
}
echo "<script>if(parent)if(parent.parent)if(parent.parent.document.getElementById('cleanbackups'))parent.parent.document.getElementById('cleanbackups').style.display='inline'</script>";
?>