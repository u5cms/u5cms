<?php
require_once __DIR__.'/../request.inc.php';

function fd_cp1252_variants($s) {
$map=array(
'€'=>'&#8364,.',
'‚'=>'&#8218,.',
'ƒ'=>'&#402,.',
'„'=>'&#8222,.',
'…'=>'&#8230,.',
'†'=>'&#8224,.',
'‡'=>'&#8225,.',
'ˆ'=>'&#710,.',
'‰'=>'&#8240,.',
'Š'=>'&#352,.',
'‹'=>'&#8249,.',
'Œ'=>'&#338,.',
'Ž'=>'&#381,.',
'‘'=>'&#8216,.',
'’'=>'&#8217,.',
'“'=>'&#8220,.',
'”'=>'&#8221,.',
'•'=>'&#8226,.',
'–'=>'&#8211,.',
'—'=>'&#8212,.',
'˜'=>'&#732,.',
'™'=>'&#8482,.',
'š'=>'&#353,.',
'›'=>'&#8250,.',
'œ'=>'&#339,.',
'ž'=>'&#382,.',
'Ÿ'=>'&#376,.'
);

$variants=array();

$variants[]=$s;
$variants[]=str_replace(';',',.',$s);

$entityform=$s;
foreach($map as $lit=>$ent) $entityform=str_replace($lit,$ent,$entityform);
$variants[]=$entityform;

$literalfromentity=$s;
foreach($map as $lit=>$ent) {
$literalfromentity=str_replace($ent,$lit,$literalfromentity);
$literalfromentity=str_replace(str_replace(',.',';',$ent),$lit,$literalfromentity);
}
$variants[]=$literalfromentity;
$variants[]=str_replace(';',',.',$literalfromentity);

$variants=array_unique($variants);

$clean=array();
for($i=0;$i<count($variants);$i++) {
if($variants[$i]!=='') $clean[]=$variants[$i];
}

return $clean;
}

if ($_GET['s']>0) $andstatus='AND status = '.mysql_real_escape_string(intval($_GET['s']));
else $andstatus='AND status < '.mysql_real_escape_string(5);
$toolate=30;
if ($_GET['s']==5) $andstatus.=' AND lastmut>'.(time()-$toolate*24*60*60);

  $_GET['f'] = preg_replace_callback(
    '/%u(.{4})/',
    function($match){      return "&#".hexdec("x".$match[1]).",.";
    },
    ($_GET['f'] ?? '')
  );

if ($_GET['f']!='') {
$keywords=((str_replace('  ',' ',str_replace(' ',' ',trim(($_GET['f'] ?? ''))))));
$keywords=str_replace('"',' ',$keywords);
$keywords=str_replace('"',' ',$keywords);
$keywords=str_replace('"',' ',$keywords);
$keywords=str_replace('  ',' ',$keywords);
$keywords=str_replace('  ',' ',$keywords);
$keywords=str_replace('  ',' ',$keywords);

  $keywords = preg_replace_callback(
    '/%u(.{4})/',
    function($match){
      return "&#".hexdec("x".$match[1]).";";
    },
    $keywords
  );

$keywords=explode(' ',trim($keywords));
$andfilter="AnD ( (";
$orand='oR';
if ($_COOKIE['fdbool']=='and') $orand='anD';

for ($k=0;$k<count($keywords);$k++) {

if($keywords[$k]=='') {
if ($k==count($keywords)-1) $andfilter.=')';
else $andfilter.=") $orand (";
continue;
}

if($keywords[$k][0]=='-') {
$andfilter.="datacsv!='' ";
$keywords[$k]=ltrim($keywords[$k], '-');

$variants=fd_cp1252_variants($keywords[$k]);

for($v=0;$v<count($variants);$v++) {
$andfilter.="AND datacsv NOT LIKE '%".mysql_real_escape_string($variants[$v])."%' ";
$andfilter.="AND authuser NOT LIKE '%".mysql_real_escape_string($variants[$v])."%' ";
$andfilter.="AND ip NOT LIKE '%".mysql_real_escape_string($variants[$v])."%' ";
$andfilter.="AND notes NOT LIKE '%".mysql_real_escape_string($variants[$v])."%' ";
$andfilter.="AND humantime NOT LIKE '%".mysql_real_escape_string($variants[$v])."%' ";
}
}

else {
$variants=fd_cp1252_variants($keywords[$k]);

$andfilter.="datacsv='' ";
for($v=0;$v<count($variants);$v++) {
$andfilter.="OR datacsv LIKE '%".mysql_real_escape_string($variants[$v])."%' ";
$andfilter.="OR authuser LIKE '%".mysql_real_escape_string($variants[$v])."%' ";
$andfilter.="OR ip LIKE '%".mysql_real_escape_string($variants[$v])."%' ";
$andfilter.="OR notes LIKE '%".mysql_real_escape_string($variants[$v])."%' ";
$andfilter.="OR humantime LIKE '%".mysql_real_escape_string($variants[$v])."%' ";
}
}

if ($k==count($keywords)-1) $andfilter.=')';
else $andfilter.=") $orand (";
}
$andfilter.=')';
}

$timeorid='time DESC, id DESC';
     if ($_COOKIE['fdorder']=='no') $timeorid='notes ASC, time DESC, id DESC';
else if ($_COOKIE['fdorder']=='au') $timeorid='authuser ASC, time DESC, id DESC';
else if ($_COOKIE['fdorder']=='ff') $timeorid='datacsv ASC, time DESC, id DESC';

$sql_a="SELECT * FROM formdata WHERE formname='".mysql_real_escape_string($_GET['n'])."' $andstatus $andfilter ORDER BY $timeorid";
$result_a=mysql_query($sql_a);
$tsql=base64_encode($sql_a);
if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}
?>