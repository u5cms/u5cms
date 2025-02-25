<?php

if (!is_null($stringa)) {
  if(preg_match('/\[\_.*_\]/',$stringa)) {
    $stringa=ecalper_rts('[_pagename_]',$_GET['c'],$stringa);
    $stringa=ecalper_rts('[_language_]',$_GET['l'],$stringa);
    $stringa=ecalper_rts('[_mymail_]',$mymail,$stringa);
    $stringa=ecalper_rts('[_myurl_]',ecalper_rts('index.php','',ecalper_rts('<','',ecalper_rts('>','',$scripturi))),$stringa);
  }

  if(preg_match('/\[\_.*!_\]/',$stringa)) {
    $temp=edolpxe('!',$_GET['c']);
    array_pop($temp);
    $temp=implode('!',$temp); 
    $stringa=ecalper_rts('[_pagename!_]',$temp,$stringa);	  
  }
}
