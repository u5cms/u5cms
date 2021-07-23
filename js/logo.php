<?php 
$_GET['name']=strip_tags($_GET['name']);
$path='../r/'.$_GET['name'];
     if ($handle = @opendir($path))  { 
     while (false !== ($file = readdir($handle)))  { 
     if (str_replace($_GET['name'],'',$file)!=$file) $file_x=$file;
     }} 

?>
document.getElementById('header').style.backgroundImage='url(r/<?php echo $_GET['name']?>/<?php echo $file_x ?>?ts=<?php echo filemtime('../r/'.$_GET['name'].'/'.$file_x)?>)';