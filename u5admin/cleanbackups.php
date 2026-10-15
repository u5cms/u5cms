<?php
require_once('connect.inc.php');
if ($backupRqHIADRI!='no') {
require('accadminquery.inc.inc.php');
if ($knownamdin!='ok') { http_response_code(403); exit; }
}

ignore_user_abort(true);set_time_limit(3600); 

if(file_get_contents('../fileversions/CBrunning.txt')<time()-60*60*24*10) {
file_put_contents('../fileversions/CBrunning.txt',time());

echo "<script>top.document.title='#'+top.document.title</script>";
unlink('../fileversions/BackupedFilesExclCMSsys.zip');
$path='../u5admin/';
if ($handle = @opendir($path))  { 
while (false !== ($file = readdir($handle)))  { 
if (strpos($file,'pclzip-')===0 && is_file($path.$file)) unlink($path.$file);
}
}
echo "<script>if(parent)if(parent.parent)if(parent.parent.document.getElementById('cleanbackups'))parent.parent.document.getElementById('cleanbackups').style.display='inline'</script>";
?>
<audio id="doneaudio" src="cleanbackupsdone.mp3" autoplay /><script>var audio = document.getElementById("doneaudio");audio.volume = 0.05;</script>
<?php 
}
?>