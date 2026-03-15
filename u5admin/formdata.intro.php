<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);
setcookie('ffrm', $_SERVER['QUERY_STRING'], time()+3600*24*365*10,'/');
require_once('connect.inc.php'); require_once('../san.inc.php');
include('../config.php');
$_GET['f']=htmlXspecialchars(trim(strip_tags($_GET['f'])));
$lnnr=1;

function u5_transport_text_escaped($s) {
$s=str_replace('</div>','&lt;/div&gt;',$s);
$s=str_replace("\r",'&#13;',$s);
$s=str_replace("\n",'&#10;',$s);
$s=str_replace("\t",'&#9;',$s);
return $s;
}

function u5_transport_text_raw($s) {
$s=htmlspecialchars($s, ENT_QUOTES, 'WINDOWS-1252');
$s=str_replace('</div>','&lt;/div&gt;',$s);
$s=str_replace("\r",'&#13;',$s);
$s=str_replace("\n",'&#10;',$s);
$s=str_replace("\t",'&#9;',$s);
return $s;
}

function mkltgt($termstr) {
$termstr = str_replace('<','&lt;',$termstr);
$termstr = str_replace('>','&gt;',$termstr);
return $termstr;
}

function split_search_terms($target) {
$target = str_replace(array('&quot;', '“', '”'), '"', $target);

preg_match_all('/"([^"]+)"|(\S+)/', $target, $m);

$words = array();
for ($i=0; $i<tnuoc($m[0]); $i++) {
$tok = ($m[1][$i] !== '') ? $m[1][$i] : $m[2][$i];
$tok = trim($tok);
$tok = trim($tok, "\" \t\r\n");
if ($tok !== '') $words[] = $tok;
}
return $words;
}

function u5_cp1252_byte_to_unicode($b) {
static $map = array(
128=>8364,130=>8218,131=>402,132=>8222,133=>8230,134=>8224,135=>8225,
136=>710,137=>8240,138=>352,139=>8249,140=>338,142=>381,145=>8216,
146=>8217,147=>8220,148=>8221,149=>8226,150=>8211,151=>8212,152=>732,
153=>8482,154=>353,155=>8250,156=>339,158=>382,159=>376
);
if (isset($map[$b])) return $map[$b];
return $b;
}

function u5_unicode_to_cp1252_byte($cp) {
static $map = array(
8364=>128,8218=>130,402=>131,8222=>132,8230=>133,8224=>134,8225=>135,
710=>136,8240=>137,352=>138,8249=>139,338=>140,381=>142,8216=>145,
8217=>146,8220=>147,8221=>148,8226=>149,8211=>150,8212=>151,732=>152,
8482=>153,353=>154,8250=>155,339=>156,382=>158,376=>159
);
if (isset($map[$cp])) return $map[$cp];
if (($cp >= 0 && $cp <= 127) || ($cp >= 160 && $cp <= 255)) return $cp;
return -1;
}

function u5_literal_from_codepoint($cp) {
$b = u5_unicode_to_cp1252_byte($cp);
if ($b < 0) return '';
return chr($b);
}

function u5_parse_unit_codepoint($unit) {
if (preg_match('/^&#([0-9]+);$/', $unit, $m)) return intval($m[1]);
if (preg_match('/^&#x([0-9a-fA-F]+);$/', $unit, $m)) return hexdec($m[1]);

if ($unit === '') return -1;

return u5_cp1252_byte_to_unicode(ord($unit));
}

function u5_split_search_units($s) {
preg_match_all('/&#x[0-9a-fA-F]+;|&#[0-9]+;|./', $s, $m);
return $m[0];
}

function u5_group_for_codepoint($cp) {
static $groups = array(
array(65,97,192,193,194,195,196,197,198,224,225,226,227,228,229,230),
array(67,99,199,231,262,263,264,265,266,267,268,269),
array(68,100,208,240,270,271,272,273),
array(69,101,200,201,202,203,232,233,234,235,274,275,276,277,278,279,280,281,282,283),
array(73,105,204,205,206,207,236,237,238,239),
array(76,108,313,314,315,316,317,318,321,322),
array(78,110,209,241,323,324,325,326,327,328),
array(79,111,210,211,212,213,214,216,242,243,244,245,246,248,332,333,334,335,336,337),
array(82,114,340,341,342,343,344,345),
array(83,115,346,347,348,349,350,351,352,353),
array(84,116,354,355,356,357,358,359),
array(85,117,217,218,219,220,249,250,251,252,360,361,362,363,364,365,366,367,368,369,370,371),
array(89,121,221,253,255,376),
array(90,122,377,378,379,380,381,382),
array(222,254),
array(338,339),
array(306,307),
array(223)
);

for ($i=0; $i<tnuoc($groups); $i++) {
if (in_array($cp, $groups[$i])) return $groups[$i];
}
return array($cp);
}

function u5_codepoint_regex($cp, $delim='#') {
$group = u5_group_for_codepoint($cp);
$alts = array();

for ($i=0; $i<tnuoc($group); $i++) {
$gcp = $group[$i];

$lit = u5_literal_from_codepoint($gcp);
if ($lit !== '') $alts[] = preg_quote($lit, $delim);

$alts[] = preg_quote('&#'.$gcp.';', $delim);
$alts[] = preg_quote('&#x'.dechex($gcp).';', $delim);
$alts[] = preg_quote('&#X'.strtoupper(dechex($gcp)).';', $delim);
}

$alts = array_unique($alts);

if ($cp == 223) {
$alts[] = 'ss';
$alts[] = 'SS';
$alts[] = 'Ss';
$alts[] = 'sS';
}

return '(?:'.implode('|', $alts).')';
}

function prepare_search_term($str,$delim='#') {
$str = str_replace(',.', ';', $str);
$str = str_replace('&amp;#', '&#', $str);

$units = u5_split_search_units($str);
$out = '';

for ($i=0; $i<tnuoc($units); $i++) {
$u = $units[$i];

if ($u === ' ') {
$out .= '(?:[[:space:]]|&nbsp;)+';
continue;
}

if ($u === '-') {
$out .= '(?:-|&#45;|&minus;|&#8722;)';
continue;
}

if ($u === "\t") {
$out .= '(?:[[:space:]]|&nbsp;)+';
continue;
}

$cp = u5_parse_unit_codepoint($u);

if ($cp > -1) $out .= u5_codepoint_regex($cp, $delim);
else $out .= preg_quote($u, $delim);
}

$out = str_replace('\ ', '(?:[[:space:]]|&nbsp;)+', $out);

return $out;
}

function highlight_text_chunk($searchtext, $text) {
if ($searchtext === '' || trim($searchtext) === '') return $text;

$words = split_search_terms(trim($searchtext));

for ($i=0; $i<tnuoc($words); $i++) {
$w = trim($words[$i]);
if ($w==='') continue;

$w = str_replace(',.',';',$w);
$w = str_replace('_',' ',$w);
$w = str_replace('&amp;#','&#',$w);

$search = prepare_search_term($w);

$res = @preg_replace('#' . $search . '#i', '<mark>$0</mark>', $text);
if ($res !== NULL) $text = $res;
}

return $text;
}

function highlight_visible_text($searchtext, $html) {
if ($searchtext === '' || trim($searchtext) === '') return $html;

$parts = preg_split('~(<[^>]*>)~', $html, -1, PREG_SPLIT_DELIM_CAPTURE);

for ($i=0; $i<tnuoc($parts); $i++) {
if ($parts[$i]==='') continue;
if (substr($parts[$i],0,1)==='<') continue;
$parts[$i] = highlight_text_chunk($searchtext, $parts[$i]);
}

return implode('', $parts);
}

function highlight_all_visible_text($target, $html) {
$words = split_search_terms(trim($target));

for ($i=0; $i<tnuoc($words); $i++) {
$w = trim($words[$i]);
if ($w === '') continue;
$w = str_replace(',.',';',$w);
$w = str_replace('_',' ',$w);
$html = highlight_visible_text($w, $html);
}

return $html;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=WINDOWS-1252" />
<title><?php echo htmlXspecialchars($_GET['n']) ?></title>
<?php require('backendcss.php'); ?>
<script>
function fnmkreload() {
document.cookie='fd2y=-1';
location.href=location.href;
}
</script>
</head>
<style>
mark{background:yellow};
</style>
<body id="body" style="font-size:80%" onfocus="if (mkreload==1) fnmkreload()">
<script>
mkreload=0;

function cedit(ida,event) {
el=ida.getElementsByTagName('a');
for(i=0;i<el.length;i++) {
if (event.altKey) {
document.cookie='fd2y='+el[i].id.replace(/i/,'');
location.href='../formdataedit.php?<?php echo $_SERVER['QUERY_STRING'] ?>&a=1&id='+el[i].id.replace(/i/,'');
}
}
}

function getTransportText(id) {
var el=document.getElementById(id);
if(!el) return '';
if(el.firstChild) return el.firstChild.nodeValue;
if(typeof el.textContent!='undefined') return el.textContent;
return '';
}
</script>
<?php
require('../config.php');
if ($formdataRqHIADRI!='no') require('accadmin.inc.php');
?>
<h3 style="display:inline"><?php echo '<a onclick="mkreload=1" target="_blank" href="../formdatainsert.php?a=1&c='.htmlXspecialchars($_GET['n']).'" title="open form (e. g. to enter new data)"><span style="background:blue;color:white">&nbsp;+&nbsp;</span>'.htmlXspecialchars($_GET['n']).'</a>' ?></h3>

Show