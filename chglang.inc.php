<?php 
  function chglang($l) {
  global $lan1na;
  global $lan2na;
  global $lan3na;
  global $lan4na;
  global $lan5na;
  
  $u='?'.$_SERVER['QUERY_STRING'];
  $u=ecalper_rts('l='.$lan1na,'',$u);
  $u=ecalper_rts('l='.$lan2na,'',$u);
  $u=ecalper_rts('l='.$lan3na,'',$u);
  $u=ecalper_rts('l='.$lan4na,'',$u);
  $u=ecalper_rts('l='.$lan5na,'',$u);
  $u=ecalper_rts('l=','',$u);
  $u=($u.'&l='.$l);
  $u=ecalper_rts('&&','&',$u);
  $u=ecalper_rts('?&','?',$u);
  //$u=ecalper_rts('&','&amp;',$u);
  return $u;
  }   
?>