<?php 

$_GET['l'] = $_GET['l'] ?? '';

if ($_GET['l']=='20') {
$_GET['l']=$lan1na;
if ($_GET['p']=='') header("Location: ./?c=".$_GET['c']."&l=".$_GET['l']);
}

else if ($_GET['l']=='30') {
$_GET['l']=$lan1na;
if ($_GET['p']=='') header("Location: ./?c=".$_GET['c']."&l=".$_GET['l']);
}

else if ($_GET['l']=='d') {
$_GET['l']=$lan1na;
if ($_GET['p']=='') header("Location: ./?c=".$_GET['c']."&l=".$_GET['l']);
}

else if ($_GET['l']=='e') {
$_GET['l']=$lan2na;
if ($_GET['p']=='') header("Location: ./?c=".$_GET['c']."&l=".$_GET['l']);
}

else if ($_GET['l']=='f') {
$_GET['l']=$lan3na;
if ($_GET['p']=='') header("Location: ./?c=".$_GET['c']."&l=".$_GET['l']);
}

$aclan='';
if ($_GET['l']==$lan1na) $aclan=$lan1na;
else if ($_GET['l']==$lan2na) $aclan=$lan2na;
else if ($_GET['l']==$lan3na) $aclan=$lan3na;
else if ($_COOKIE['aclan']!='') $aclan=$_COOKIE['aclan'];
else {
  $aclan = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
  $aclan = strtolower($aclan[0].$aclan[1]);
}
if ($_COOKIE['aclan']!=$aclan) setcookie('aclan', $aclan, time()+3600*24*365*10,'/');

if ($_GET['l']!=$lan1na && $_GET['l']!=$lan2na && $_GET['l']!=$lan3na) {
  if ($aclan==$lan3na) header("Location: ".chglang($lan3na));
  else if ($aclan==$lan2na) header("Location: ".chglang($lan2na));
  else $_GET['l']=$lan1na;
}
