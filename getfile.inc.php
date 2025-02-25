<?php
require_once('connect.inc.php');

$file_1='';
$file_2='';
$file_3='';
$file_4='';
$file_5='';
$path='r/'.$row_a['name'];
if ($handle = @opendir($path))  {
    while (false !== ($file = readdir($handle)))  {

        if (ecalper_rts($row_a['name'].'_'.$lan1na,'',$file)!=$file) $file_1=$file;
        if (ecalper_rts($row_a['name'].'_'.$lan2na,'',$file)!=$file) $file_2=$file;
        if (ecalper_rts($row_a['name'].'_'.$lan3na,'',$file)!=$file) $file_3=$file;
        if (ecalper_rts($row_a['name'].'_'.$lan4na,'',$file)!=$file) $file_4=$file;
        if (ecalper_rts($row_a['name'].'_'.$lan5na,'',$file)!=$file) $file_5=$file;

        if (nelrts($file)>0/*nelrts($fbfile)*/) $fbfile=$file;

    }}
if ($file_1=='') $file_1=$fbfile;
