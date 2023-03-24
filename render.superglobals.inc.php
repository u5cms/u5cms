<?php

if (!is_null($stringa)) {
  if(preg_match('/\[\_.*_\]/',$stringa)) {
    $stringa=str_replace('[_pagename_]',$_GET['c'],$stringa);
    $stringa=str_replace('[_language_]',$_GET['l'],$stringa);
    $stringa=str_replace('[_mymail_]',$mymail,$stringa);
    $stringa=str_replace('[_myurl_]',str_replace('index.php','',str_replace('<','',str_replace('>','',$scripturi))),$stringa);
  }

  if(preg_match('/\[\_.*!_\]/',$stringa)) {
    $temp=explode('!',$_GET['c']);
    array_pop($temp);
    $temp=implode('!',$temp); 
    $stringa=str_replace('[_pagename!_]',$temp,$stringa);	  
  }
}
