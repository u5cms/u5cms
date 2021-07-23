<?php
$path='../r';
$files='';
$subdirs='';
$delimiter='?';
if ($handle = @opendir($path))  { 
   while (false !== ($item = readdir($handle)))  { 
      if (substr($item,0,1)!='.') {
	  if(strpos($item,'.')>0) $files.=$item.$delimiter;
	  else $subdirs.=$item.$delimiter;
	     }
      }
   }

$files=explode($delimiter,$files);
for($i=0;$i<count($files);$i++) {
if(!is_writable($path.'/'.$files[$i]))echo('<script>alert("ERROR: The file r/'.$files[$i].' is not writable by the server. \n\nSOLUTION: CHMOD the folder \'r\' RECURSIVELY (incl. all its files, subfolders a.s.o.) e. g. to 777 e. g. with FileZilla.");</script>');	
}
$files='';

$subdirs=explode($delimiter,$subdirs);
for($i=0;$i<count($subdirs);$i++) {
if(!is_writable($path.'/'.$subdirs[$i]))echo('<script>alert("ERROR: The directory r/'.$subdirs[$i].' is not writable by the server. \n\nSOLUTION: CHMOD the folder \'r\' RECURSIVELY (incl. all its files, subfolders a.s.o.) e. g. to 777 e. g. with FileZilla.");</script>');	
////////////////////////
if ($handle = @opendir($path.'/'.$subdirs[$i]))  { 
   while (false !== ($item = readdir($handle)))  { 
	  if($item!='.' && $item!='..')$files.=$item.$delimiter;
     }
   }

$files=explode($delimiter,$files);
for($ii=0;$ii<count($files);$ii++) {
if($files[$ii]!='' && $subdirs[$i]!='')if(!is_writable($path.'/'.$subdirs[$i].'/'.$files[$ii]))echo('<script>alert("ERROR: The file r/'.$subdirs[$i].'/'.$files[$ii].' is not writable by the server. \n\nSOLUTION: CHMOD the folder \'r\' RECURSIVELY (incl. all its files, subfolders a.s.o.) e. g. to 777 e. g. with FileZilla.");</script>');	
}
$files='';
////////////////////////
}
unset($subdirs);
?>