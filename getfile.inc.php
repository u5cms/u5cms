<?php 
require_once('connect.inc.php');

 $file_d='';
 $file_e='';
 $file_f='';
 $path='r/'.$row_a['name'];
     if ($handle = @opendir($path))  { 
     while (false !== ($file = readdir($handle)))  { 

     if (str_replace($row_a['name'].'_'.$lan1na,'',$file)!=$file) $file_d=$file;
     if (str_replace($row_a['name'].'_'.$lan2na,'',$file)!=$file) $file_e=$file;
     if (str_replace($row_a['name'].'_'.$lan3na,'',$file)!=$file) $file_f=$file;

     if (strlen($file)>strlen($fbfile)) $fbfile=$file;

     }}
if ($file_d=='') $file_d=$fbfile;
?>