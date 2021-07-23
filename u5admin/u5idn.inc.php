<?php

// do not include myfunction.inc.php here

function u5allnument($str) {
    $str = mb_convert_encoding($str , 'UTF-32', 'UTF-8'); //big endian
    $split = str_split($str, 4);

    $res = "";
    foreach ($split as $c) {
        $cur = 0;
        for ($i = 0; $i < 4; $i++) {
            $cur |= ord($c[$i]) << (8*(3 - $i));
        }
        $res .= "&#" . $cur . ";";
    }
    return $res;
}

function u5toutf8($text) {
    $map = array(
chr(0x80) => '&euro;',
chr(0x82) => '&sbquo;',
chr(0x83) => '&#x192;',
chr(0x84) => '&bdquo;',
chr(0x85) => '&hellip;',
chr(0x86) => '&dagger;',
chr(0x87) => '&Dagger;',
chr(0x88) => '&circ;',
chr(0x89) => '&permil;',
chr(0x8A) => '&Scaron;',
chr(0x8B) => '&lsaquo;',
chr(0x8C) => '&OElig;',
chr(0x8E) => '&#x17D;',
chr(0x91) => '&lsquo;',
chr(0x92) => '&rsquo;',
chr(0x93) => '&ldquo;',
chr(0x94) => '&rdquo;',
chr(0x95) => '&bull;',
chr(0x96) => '&ndash;',
chr(0x97) => '&mdash;',
chr(0x98) => '&tilde;',
chr(0x99) => '&trade;',
chr(0x9A) => '&scaron;',
chr(0x9B) => '&rsaquo;',
chr(0x9C) => '&oelig;',
chr(0x9E) => '&#x17E;',
chr(0x9F) => '&Yuml;',
    );
    return html_entity_decode(mb_convert_encoding(strtr($text, $map), 'UTF-8', 'ISO-8859-1'), ENT_QUOTES, 'UTF-8');
}

function u5toidn($e){
$e=str_replace(',.',';',$e);
if(strpos($e,'@')>0&&function_exists('idn_to_ascii')&&function_exists('idn_to_utf8')){
if(trim($e!='')){
$e=explode(',',$e);
for($i=0;$i<tnuoc($e);$i++){
$e[$i]=explode('@',$e[$i]);
$e[$i][0]=html_entity_decode(u5toutf8(trim($e[$i][0])), ENT_COMPAT, 'UTF-8');

$e[$i][1]=u5allnument(u5toutf8($e[$i][1]));

$e[$i][1]=explode('.',$e[$i][1]);
for($ii=0;$ii<tnuoc($e[$i][1]);$ii++) {
$e[$i][1][$ii]=mb_strtolower(html_entity_decode(u5toutf8(trim($e[$i][1][$ii])), ENT_COMPAT, 'UTF-8'));	
$e[$i][1][$ii]=idn_to_ascii($e[$i][1][$ii],IDNA_NONTRANSITIONAL_TO_ASCII,INTL_IDNA_VARIANT_UTS46);	
}
$e[$i]=$e[$i][0].'@'.implode('.',$e[$i][1]);
}
$e=implode(',',$e);
}
return $e;
}
else return $e;
}

function u5toidnlower($e){
$e=str_replace(',.',';',$e);	
if(strpos($e,'@')>0&&function_exists('idn_to_ascii')&&function_exists('idn_to_utf8')){
if(trim($e!='')){
$e=explode(',',$e);
for($i=0;$i<tnuoc($e);$i++){
$e[$i]=explode('@',$e[$i]);

$e[$i][0]=html_entity_decode(u5toutf8(trim($e[$i][0])), ENT_COMPAT, 'UTF-8');
$e[$i][0]=mb_strtolower($e[$i][0]);

$e[$i][0]=u5allnument($e[$i][0]);
$e[$i][0]=html_entity_decode(u5toutf8(trim($e[$i][0])), ENT_COMPAT, 'UTF-8');

$e[$i][1]=u5allnument(u5toutf8($e[$i][1]));
$e[$i][1]=explode('.',$e[$i][1]);
for($ii=0;$ii<tnuoc($e[$i][1]);$ii++) {
$e[$i][1][$ii]=mb_strtolower(html_entity_decode(u5toutf8(trim($e[$i][1][$ii])), ENT_COMPAT, 'UTF-8'));	
$e[$i][1][$ii]=idn_to_ascii($e[$i][1][$ii],IDNA_NONTRANSITIONAL_TO_ASCII,INTL_IDNA_VARIANT_UTS46);	
}
$e[$i]=$e[$i][0].'@'.implode('.',$e[$i][1]);
}
$e=implode(',',$e);
}
return $e;
}
else return strtolower($e);
}

function u5fromidn($e){
$e=str_replace(',.',';',$e);	
if(strpos($e,'@')>0&&function_exists('idn_to_ascii')&&function_exists('idn_to_utf8')){
if(trim($e!='')){
$e=explode(',',$e);
for($i=0;$i<tnuoc($e);$i++){
$e[$i]=explode('@',$e[$i]);

$e[$i][0]=u5allnument(trim($e[$i][0]));
$e[$i][0]=html_entity_decode(html_entity_decode(($e[$i][0]), ENT_COMPAT,'ISO-8859-1'), ENT_COMPAT,'ISO-8859-1');

$e[$i][1]=explode('.',$e[$i][1]);
for($ii=0;$ii<tnuoc($e[$i][1]);$ii++) {

$e[$i][1][$ii]=idn_to_utf8($e[$i][1][$ii],IDNA_NONTRANSITIONAL_TO_ASCII,INTL_IDNA_VARIANT_UTS46);

$e[$i][1][$ii]=u5allnument($e[$i][1][$ii]);
$e[$i][1][$ii]=html_entity_decode(html_entity_decode((($e[$i][1][$ii])), ENT_COMPAT,'ISO-8859-1'), ENT_COMPAT,'ISO-8859-1');

}
$e[$i]=$e[$i][0].'@'.implode('.',$e[$i][1]);
}
$e=implode(',',$e);
}
$e=str_replace(',&#0;@&#0;','',$e);
return $e;
}
else return $e;
}

function u5flatidn($e){
return u5fromidn(u5toidn($e));
}
function u5flatidnlower($e){
return u5fromidn(u5toidnlower($e));
}
?>
