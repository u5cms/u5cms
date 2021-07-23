<?php 
 $xfile='';
 $path='../r/'.$row_a['name'];
     if ($handle = @opendir($path))  { 
     while (false !== ($file = readdir($handle)))  { 
     if (str_replace($row_a['name'].'_'.$la,'',$file)!=$file) $xfile=$file;
     }}
?>