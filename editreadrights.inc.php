<?php 
u5ValidateTextRequest($_GET,array('c','n'));
require_once __DIR__ . '/onlymine.inc.php';
$cmdstring = u5onlymineFormRules();

if (u5onlymineHasMarker($cmdstring,'TOEDITITEMS_LOGINS_SAME_AS_[')) {
$loginsfrompage=explode('TOEDITITEMS_LOGINS_SAME_AS_[',$cmdstring);
$loginsfrompage=explode(']',$loginsfrompage[1]);
$thatpage=trim($loginsfrompage[0]);
require('login.inc.php');
require('checkeditself.inc.php');
}

else {
require_once('u5admin/usercheck.inc.php');
if ($formdataRqHIADRI!='no') require('u5admin/accadmin.inc.php');
}
?>