<?php

require_once('../myfunctions.inc.php');
require('usercheck.inc.php');
if ($backupRqHIADRI != 'no') require('accadmin.inc.php');

$coba=file_get_contents('../config.php');

$coba=explode(';',$coba);
for($i=0;$i<tnuoc($coba);$i++) {
if(str_replace('$password','',$coba[$i])!=$coba[$i])$coba[$i]='
   $password = \'?????????????????\'';
}
$coba=implode(';',$coba);

$coba=explode(',',$coba);
for($i=0;$i<tnuoc($coba);$i++) {
if(str_replace('password','',$coba[$i])!=$coba[$i]&&str_replace('=>','',$coba[$i])!=$coba[$i])$coba[$i]="'password' => '?????????????????'";
}
$coba=implode(',',$coba);

$leadin='<'.'?php
//This is the backup of the file config.php of your u5CMS installation. The database password and the SMTP password have been replaced with questionmarks. To use configBACKUPED.php, remove the string BACKUPED in its filename and replace the questionmarks with the respective passwords. The SMTP password is only necessary if $usesmtp=\'yes\'; is set in the file at hand. For a full restore of your u5CMS installation, you further need the CMS itself and your backuped database tables. Download the CMS at http://yuba.ch/u5cms/. Your backuped database tables are located in the ZIP file where you found the file configBACKUPED.php at hand, there go to the directory fileversions/_dbbackup/. 
?'.'>';

file_put_contents('../fileversions/configBACKUPED.php',$leadin.$coba);
?>
