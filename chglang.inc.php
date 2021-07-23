<?php 
  function chglang($l) {
  global $lan1na;
  global $lan2na;
  global $lan3na;
  
  $u='?'.$_SERVER['QUERY_STRING'];
  $u=str_replace('l='.$lan1na,'',$u);
  $u=str_replace('l='.$lan2na,'',$u);
  $u=str_replace('l='.$lan3na,'',$u);
  $u=str_replace('l=','',$u);
  $u=($u.'&l='.$l);
  $u=str_replace('&&','&',$u);
  $u=str_replace('?&','?',$u);
  //$u=str_replace('&','&amp;',$u);
  return $u;
  }   
?>