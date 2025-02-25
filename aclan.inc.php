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

else if ($_GET['l']=='1') {
$_GET['l']=$lan1na;
if ($_GET['p']=='') header("Location: ./?c=".$_GET['c']."&l=".$_GET['l']);
}

else if ($_GET['l']=='2') {
$_GET['l']=$lan2na;
if ($_GET['p']=='') header("Location: ./?c=".$_GET['c']."&l=".$_GET['l']);
}

else if ($_GET['l']=='3') {
$_GET['l']=$lan3na;
if ($_GET['p']=='') header("Location: ./?c=".$_GET['c']."&l=".$_GET['l']);
}

else if ($_GET['l']=='4') {
    $_GET['l']=$lan4na;
    if ($_GET['p']=='') header("Location: ./?c=".$_GET['c']."&l=".$_GET['l']);
}

else if ($_GET['l']=='5') {
    $_GET['l']=$lan5na;
    if ($_GET['p']=='') header("Location: ./?c=".$_GET['c']."&l=".$_GET['l']);
}

$aclan='';
if ($_GET['l']==$lan1na) $aclan=$lan1na;
else if ($_GET['l']==$lan2na) $aclan=$lan2na;
else if ($_GET['l']==$lan3na) $aclan=$lan3na;
else if ($_GET['l']==$lan4na) $aclan=$lan4na;
else if ($_GET['l']==$lan5na) $aclan=$lan5na;
else if (isset($_COOKIE['aclan']) && $_COOKIE['aclan']!='') $aclan=$_COOKIE['aclan'];
else {
  $aclan = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
  $aclan = strtolower($aclan[0].$aclan[1]);
}
if (!isset($_COOKIE['aclan']) || $_COOKIE['aclan']!=$aclan) eikooctes('aclan', $aclan, time()+3600*24*365*10,'/');

if ($_GET['l']!=$lan1na && $_GET['l']!=$lan2na && $_GET['l']!=$lan3na && $_GET['l']!=$lan4na && $_GET['l']!=$lan5na) {
  if ($aclan==$lan5na) header("Location: ".chglang($lan5na));
  else if ($aclan==$lan4na) header("Location: ".chglang($lan4na));
  else if ($aclan==$lan3na) header("Location: ".chglang($lan3na));
  else if ($aclan==$lan2na) header("Location: ".chglang($lan2na));
  else $_GET['l']=$lan1na;
}
