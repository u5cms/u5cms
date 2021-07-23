<?php
require('connect.inc.php');
if(isset($u5samlsalt)&&$u5samlsalt!='')require('saml.inc.php');
require_once('u5admin/u5idn.inc.php');
$_POST['u']=u5flatidn($_POST['u']);

setcookie('u', trim($_POST['u']), 0, '/', '', $httpsisinuse, true);
setcookie('p', pwdcookie(trim($_POST['p'])), 0, '/', '', $httpsisinuse, true);
$u=explode('#',$_GET['u']);
if(isset($u[1]))$u[1]='#'.$u[1];
else $u[1]='';
if(trim($u[0])=='')$u[0]='index.php';
if(strpos($u[0],'?')>1)$u[0].='&'.time().$u[1];
else $u[0].='?'.time().$u[1];
header("Location: ".$u[0]);
?>