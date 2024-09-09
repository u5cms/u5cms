<?php 
ignore_user_abort(true); 

if(file_get_contents('../fileversions/CBrunning.txt')<time()-60*60*24*10) {
file_put_contents('../fileversions/CBrunning.txt',time());

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
<audio id="doneaudio" src="'.rand(1,6).'.mp3" autoplay /><script>var audio = document.getElementById("doneaudio");audio.volume = 0.05;</script>
<?php 
}
?>