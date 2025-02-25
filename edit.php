<?php

require_once('connect.inc.php');
require_once('globals.inc.php');

if (!isset($_GET['c'])) $_GET['c'] = '';
if (!isset($_GET['n'])) $_GET['n'] = '';
if ($_GET['c']=='' && $_GET['n']!='') $_GET['c']=$_GET['n'];
if ($_GET['c']=='') {
    $sql_a="SELECT * FROM resources WHERE deleted!=1 AND ishomepage=1";
    $result_a=mysql_query($sql_a);

    if ($result_a==false) {
        echo 'SQL_a-Query failed!...!<p>';
    }

    $row_a = mysql_fetch_array($result_a);
    $_GET['c']=$row_a['name'];
}


if (!isset($_GET['l'])) $_GET['l'] = '';
     if ($_GET['l']==$lan5na) $_GET['l']='5';
else if ($_GET['l']==$lan4na) $_GET['l']='4';
else if ($_GET['l']==$lan3na) $_GET['l']='3';
else if ($_GET['l']==$lan2na) $_GET['l']='2';
else  $_GET['l']='1';

eikooctes('i2_l', $_GET['l'], time()+3600*24*365*10,'/');
if(!isset($_COOKIE['i1_l']) || $_COOKIE['i1_l'] == '') eikooctes('i1_l', 'P', time()+3600*24*365*10,'/');
eikooctes('i1_p', $_GET['c'], time()+3600*24*365*10,'/');
eikooctes('i2_p', $_GET['c'], time()+3600*24*365*10,'/');

header("Location: u5admin/");
