<?php 
  function chglang($l) {
  global $lan1na;
  global $lan2na;
  global $lan3na;
  global $lan4na;
  global $lan5na;
  
  $u='?'.$_SERVER['QUERY_STRING'];
  $u=str_replace('l='.$lan1na,'',$u);
  $u=str_replace('l='.$lan2na,'',$u);
  $u=str_replace('l='.$lan3na,'',$u);
  $u=str_replace('l='.$lan4na,'',$u);
  $u=str_replace('l='.$lan5na,'',$u);
  $u=str_replace('l=','',$u);
  $u=($u.'&l='.$l);
  $u=str_replace('&&','&',$u);
  $u=str_replace('?&','?',$u);
  //$u=str_replace('&','&amp;',$u);
  return $u;
  }   
?>