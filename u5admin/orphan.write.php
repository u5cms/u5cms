<?php
ignore_user_abort(true);set_time_limit(36000); 
require('connect.inc.php');
require('../config.php');
require_once('codeassets-usage.inc.php');

$orphanLock=@fopen('../fileversions/orphan.lock','c');
if (!$orphanLock || !flock($orphanLock,LOCK_EX|LOCK_NB)) die('<script>setTimeout("location.reload()",10000)</script>');
$orphanTemp=NULL;
$orphanOwned=false;
register_shutdown_function(function() use ($orphanLock,&$orphanTemp,&$orphanOwned) {
if ($orphanTemp && is_file($orphanTemp)) @unlink($orphanTemp);
if ($orphanOwned) @file_put_contents('../fileversions/orphanrunning.txt','0');
flock($orphanLock,LOCK_UN);
fclose($orphanLock);
});
// Also respect an in-flight writer from before this update.
$previousRun=@file_get_contents('../fileversions/orphanrunning.txt');
if ($previousRun!==false && (int)$previousRun>time()-60*15) die('<script>setTimeout("location.reload()",10000)</script>');
if (file_put_contents('../fileversions/orphanrunning.txt',(string)time())===false) die('Cannot start orphan scan.');
$orphanOwned=true;
function orphanJsString($value) {
return "'".str_replace(array("\\","'","\r","\n","<"),array("\\\\","\\'","\\r","\\n","\\x3c"),$value)."'";
}

$squot1='"';
$squot2="\\'";

$echo="(function(){\nvar doc=parent.parent.document;\nfunction mark(id,orphan){\n".
"var html=orphan?'L<!--_orphan_-->':'L',color=orphan?'red':'blue',title=orphan?'orphan':'';\n".
"['o_','oo_'].forEach(function(prefix){var el=doc.getElementById(prefix+id);if(!el)return;\n".
"if(el.style.color!==color)el.style.color=color;if(el.title!==title)el.title=title;if(el.innerHTML!==html)el.innerHTML=html;});\n}\n";

$sql_a='SELECT name,typ FROM resources WHERE deleted!=1';

$result_a=mysql_query($sql_a);

if ($result_a==false) {
	die('SQL_a-Query failed! Previous orphan data has been kept.');
}

$num_a = mysql_num_rows($result_a);

for ($i_a=0; $i_a<$num_a; $i_a++) {
$row_a = mysql_fetch_array($result_a);
$likeName=mysql_real_escape_string(str_replace(array('!','%','_'),array('!!','!%','!_'),$row_a['name']));

$manfph='';
$codeUsage=strtolower((string)$row_a['typ'])==='c' ? 'OR '.u5CodeAssetUsageSql($row_a['name']) : '';

$replacestart="replace(replace(replace(replace(replace(replace(";
$replaceend=",'[[[[',' '),'[[[',' '),'[[',' '),']]]]',' '),']]]',' '),']]',' ')";

if ($ignoremanualfullpaths!='yes') $manfph= "

OR
(".$replacestart."content_1".$replaceend." LIKE '%c=".$likeName."&%' OR ".$replacestart."content_2".$replaceend." LIKE '%c=".$likeName."&%' OR ".$replacestart."content_3".$replaceend." LIKE '%c=".$likeName."&%' OR ".$replacestart."content_4".$replaceend." LIKE '%c=".$likeName."&%' OR ".$replacestart."content_5".$replaceend." LIKE '%c=".$likeName."&%')
OR
(".$replacestart."content_1".$replaceend." LIKE '%c=".$likeName."\"%' OR ".$replacestart."content_2".$replaceend." LIKE '%c=".$likeName."\"%' OR ".$replacestart."content_3".$replaceend." LIKE '%c=".$likeName."\"%' OR ".$replacestart."content_4".$replaceend." LIKE '%c=".$likeName."\"%' OR ".$replacestart."content_5".$replaceend." LIKE '%c=".$likeName."\"%')
OR
(".$replacestart."content_1".$replaceend." LIKE '%c=".$likeName.$squot1."%' OR ".$replacestart."content_2".$replaceend." LIKE '%c=".$likeName.$squot1."%' OR ".$replacestart."content_3".$replaceend." LIKE '%c=".$likeName.$squot1."%' OR ".$replacestart."content_4".$replaceend." LIKE '%c=".$likeName.$squot1."%' OR ".$replacestart."content_5".$replaceend." LIKE '%c=".$likeName.$squot1."%')
OR
(".$replacestart."content_1".$replaceend." LIKE '%c=".$likeName.$squot2."%' OR ".$replacestart."content_2".$replaceend." LIKE '%c=".$likeName.$squot2."%' OR ".$replacestart."content_3".$replaceend." LIKE '%c=".$likeName.$squot2."%' OR ".$replacestart."content_4".$replaceend." LIKE '%c=".$likeName.$squot2."%' OR ".$replacestart."content_5".$replaceend." LIKE '%c=".$likeName.$squot2."%')



OR
(".$replacestart."content_1".$replaceend." LIKE '%r/".$likeName."/%' OR ".$replacestart."content_2".$replaceend." LIKE '%r/".$likeName."/%' OR ".$replacestart."content_3".$replaceend." LIKE '%r/".$likeName."/%' OR ".$replacestart."content_4".$replaceend." LIKE '%r/".$likeName."/%' OR ".$replacestart."content_5".$replaceend." LIKE '%r/".$likeName."/%')
OR
(".$replacestart."title_1".$replaceend." LIKE '%r/".$likeName."/%' OR ".$replacestart."title_2".$replaceend." LIKE '%r/".$likeName."/%' OR ".$replacestart."title_3".$replaceend." LIKE '%r/".$likeName."/%' OR ".$replacestart."title_4".$replaceend." LIKE '%r/".$likeName."/%' OR ".$replacestart."title_5".$replaceend." LIKE '%r/".$likeName."/%')
OR
(".$replacestart."desc_1".$replaceend." LIKE '%r/".$likeName."/%' OR ".$replacestart."desc_2".$replaceend." LIKE '%r/".$likeName."/%' OR ".$replacestart."desc_3".$replaceend." LIKE '%r/".$likeName."/%' OR ".$replacestart."desc_4".$replaceend." LIKE '%r/".$likeName."/%' OR ".$replacestart."desc_5".$replaceend." LIKE '%r/".$likeName."/%')
OR
(".$replacestart."key_1".$replaceend." LIKE '%r/".$likeName."/%' OR ".$replacestart."key_2".$replaceend." LIKE '%r/".$likeName."/%' OR ".$replacestart."key_3".$replaceend." LIKE '%r/".$likeName."/%' OR ".$replacestart."key_4".$replaceend." LIKE '%r/".$likeName."/%' OR ".$replacestart."key_5".$replaceend." LIKE '%r/".$likeName."/%')
";

$sql_o="SELECT name FROM resources WHERE deleted!=1 AND name!='-' AND
( 

(".$replacestart."content_1".$replaceend." LIKE '%[".$likeName."]%' OR ".$replacestart."content_2".$replaceend." LIKE '%[".$likeName."]%' OR ".$replacestart."content_3".$replaceend." LIKE '%[".$likeName."]%' OR ".$replacestart."content_4".$replaceend." LIKE '%[".$likeName."]%' OR ".$replacestart."content_5".$replaceend." LIKE '%[".$likeName."]%')
OR
(".$replacestart."content_1".$replaceend." LIKE '%:".$likeName."]%' OR ".$replacestart."content_2".$replaceend." LIKE '%:".$likeName."]%' OR ".$replacestart."content_3".$replaceend." LIKE '%:".$likeName."]%' OR ".$replacestart."content_4".$replaceend." LIKE '%:".$likeName."]%' OR ".$replacestart."content_5".$replaceend." LIKE '%:".$likeName."]%')
OR
(".$replacestart."content_1".$replaceend." LIKE '%[".$likeName."?%' OR ".$replacestart."content_2".$replaceend." LIKE '%[".$likeName."?%' OR ".$replacestart."content_3".$replaceend." LIKE '%[".$likeName."?%' OR ".$replacestart."content_4".$replaceend." LIKE '%[".$likeName."?%' OR ".$replacestart."content_5".$replaceend." LIKE '%[".$likeName."?%')
OR
(".$replacestart."content_1".$replaceend." LIKE '%:".$likeName."?%' OR ".$replacestart."content_2".$replaceend." LIKE '%:".$likeName."?%' OR ".$replacestart."content_3".$replaceend." LIKE '%:".$likeName."?%' OR ".$replacestart."content_4".$replaceend." LIKE '%:".$likeName."?%' OR ".$replacestart."content_5".$replaceend." LIKE '%:".$likeName."?%')
OR
(".$replacestart."content_1".$replaceend." LIKE '%[go:]".$likeName."[:go]%' OR ".$replacestart."content_2".$replaceend." LIKE '%[go:]".$likeName."[:go]%' OR ".$replacestart."content_3".$replaceend." LIKE '%[go:]".$likeName."[:go]%' OR ".$replacestart."content_4".$replaceend." LIKE '%[go:]".$likeName."[:go]%' OR ".$replacestart."content_5".$replaceend." LIKE '%[go:]".$likeName."[:go]%')
OR
(".$replacestart."content_1".$replaceend." LIKE '%[lo:]".$likeName."[:lo]%' OR ".$replacestart."content_2".$replaceend." LIKE '%[lo:]".$likeName."[:lo]%' OR ".$replacestart."content_3".$replaceend." LIKE '%[lo:]".$likeName."[:lo]%' OR ".$replacestart."content_4".$replaceend." LIKE '%[lo:]".$likeName."[:lo]%' OR ".$replacestart."content_5".$replaceend." LIKE '%[lo:]".$likeName."[:lo]%')
OR
(".$replacestart."content_1".$replaceend." LIKE '%name=\"thanks\" value=\"".$likeName."\"%' OR ".$replacestart."content_2".$replaceend." LIKE '%name=\"thanks\" value=\"".$likeName."\"%' OR ".$replacestart."content_3".$replaceend." LIKE '%name=\"thanks\" value=\"".$likeName."\"%' OR ".$replacestart."content_4".$replaceend." LIKE '%name=\"thanks\" value=\"".$likeName."\"%' OR ".$replacestart."content_5".$replaceend." LIKE '%name=\"thanks\" value=\"".$likeName."\"%')

 OR

(".$replacestart."title_1".$replaceend." LIKE '%[".$likeName."]%' OR ".$replacestart."title_2".$replaceend." LIKE '%[".$likeName."]%' OR ".$replacestart."title_3".$replaceend." LIKE '%[".$likeName."]%' OR ".$replacestart."title_4".$replaceend." LIKE '%[".$likeName."]%' OR ".$replacestart."title_5".$replaceend." LIKE '%[".$likeName."]%')
OR
(".$replacestart."title_1".$replaceend." LIKE '%:".$likeName."]%' OR ".$replacestart."title_2".$replaceend." LIKE '%:".$likeName."]%' OR ".$replacestart."title_3".$replaceend." LIKE '%:".$likeName."]%' OR ".$replacestart."title_4".$replaceend." LIKE '%:".$likeName."]%' OR ".$replacestart."title_5".$replaceend." LIKE '%:".$likeName."]%')
OR
(".$replacestart."title_1".$replaceend." LIKE '%[".$likeName."?%' OR ".$replacestart."title_2".$replaceend." LIKE '%[".$likeName."?%' OR ".$replacestart."title_3".$replaceend." LIKE '%[".$likeName."?%' OR ".$replacestart."title_4".$replaceend." LIKE '%[".$likeName."?%' OR ".$replacestart."title_5".$replaceend." LIKE '%[".$likeName."?%')
OR
(".$replacestart."title_1".$replaceend." LIKE '%:".$likeName."?%' OR ".$replacestart."title_2".$replaceend." LIKE '%:".$likeName."?%' OR ".$replacestart."title_3".$replaceend." LIKE '%:".$likeName."?%' OR ".$replacestart."title_4".$replaceend." LIKE '%:".$likeName."?%' OR ".$replacestart."title_5".$replaceend." LIKE '%:".$likeName."?%')
OR
(".$replacestart."title_1".$replaceend." LIKE '%[go:]".$likeName."[:go]%' OR ".$replacestart."title_2".$replaceend." LIKE '%[go:]".$likeName."[:go]%' OR ".$replacestart."title_3".$replaceend." LIKE '%[go:]".$likeName."[:go]%' OR ".$replacestart."title_4".$replaceend." LIKE '%[go:]".$likeName."[:go]%' OR ".$replacestart."title_5".$replaceend." LIKE '%[go:]".$likeName."[:go]%')
OR
(".$replacestart."title_1".$replaceend." LIKE '%[lo:]".$likeName."[:lo]%' OR ".$replacestart."title_2".$replaceend." LIKE '%[lo:]".$likeName."[:lo]%' OR ".$replacestart."title_3".$replaceend." LIKE '%[lo:]".$likeName."[:lo]%' OR ".$replacestart."title_4".$replaceend." LIKE '%[lo:]".$likeName."[:lo]%' OR ".$replacestart."title_5".$replaceend." LIKE '%[lo:]".$likeName."[:lo]%')
OR
(".$replacestart."title_1".$replaceend." LIKE '%name=\"thanks\" value=\"".$likeName."\"%' OR ".$replacestart."title_2".$replaceend." LIKE '%name=\"thanks\" value=\"".$likeName."\"%' OR ".$replacestart."title_3".$replaceend." LIKE '%name=\"thanks\" value=\"".$likeName."\"%' OR ".$replacestart."title_4".$replaceend." LIKE '%name=\"thanks\" value=\"".$likeName."\"%' OR ".$replacestart."title_5".$replaceend." LIKE '%name=\"thanks\" value=\"".$likeName."\"%')

 OR
 
(".$replacestart."desc_1".$replaceend." LIKE '%[".$likeName."]%' OR ".$replacestart."desc_2".$replaceend." LIKE '%[".$likeName."]%' OR ".$replacestart."desc_3".$replaceend." LIKE '%[".$likeName."]%' OR ".$replacestart."desc_4".$replaceend." LIKE '%[".$likeName."]%' OR ".$replacestart."desc_5".$replaceend." LIKE '%[".$likeName."]%')
OR
(".$replacestart."desc_1".$replaceend." LIKE '%:".$likeName."]%' OR ".$replacestart."desc_2".$replaceend." LIKE '%:".$likeName."]%' OR ".$replacestart."desc_3".$replaceend." LIKE '%:".$likeName."]%' OR ".$replacestart."desc_4".$replaceend." LIKE '%:".$likeName."]%' OR ".$replacestart."desc_5".$replaceend." LIKE '%:".$likeName."]%')
OR
(".$replacestart."desc_1".$replaceend." LIKE '%[".$likeName."?%' OR ".$replacestart."desc_2".$replaceend." LIKE '%[".$likeName."?%' OR ".$replacestart."desc_3".$replaceend." LIKE '%[".$likeName."?%' OR ".$replacestart."desc_4".$replaceend." LIKE '%[".$likeName."?%' OR ".$replacestart."desc_5".$replaceend." LIKE '%[".$likeName."?%')
OR
(".$replacestart."desc_1".$replaceend." LIKE '%:".$likeName."?%' OR ".$replacestart."desc_2".$replaceend." LIKE '%:".$likeName."?%' OR ".$replacestart."desc_3".$replaceend." LIKE '%:".$likeName."?%' OR ".$replacestart."desc_4".$replaceend." LIKE '%:".$likeName."?%' OR ".$replacestart."desc_5".$replaceend." LIKE '%:".$likeName."?%')
OR
(".$replacestart."desc_1".$replaceend." LIKE '%[go:]".$likeName."[:go]%' OR ".$replacestart."desc_2".$replaceend." LIKE '%[go:]".$likeName."[:go]%' OR ".$replacestart."desc_3".$replaceend." LIKE '%[go:]".$likeName."[:go]%' OR ".$replacestart."desc_4".$replaceend." LIKE '%[go:]".$likeName."[:go]%' OR ".$replacestart."desc_5".$replaceend." LIKE '%[go:]".$likeName."[:go]%')
OR
(".$replacestart."desc_1".$replaceend." LIKE '%[lo:]".$likeName."[:lo]%' OR ".$replacestart."desc_2".$replaceend." LIKE '%[lo:]".$likeName."[:lo]%' OR ".$replacestart."desc_3".$replaceend." LIKE '%[lo:]".$likeName."[:lo]%' OR ".$replacestart."desc_4".$replaceend." LIKE '%[lo:]".$likeName."[:lo]%' OR ".$replacestart."desc_5".$replaceend." LIKE '%[lo:]".$likeName."[:lo]%')
OR
(".$replacestart."desc_1".$replaceend." LIKE '%name=\"thanks\" value=\"".$likeName."\"%' OR ".$replacestart."desc_2".$replaceend." LIKE '%name=\"thanks\" value=\"".$likeName."\"%' OR ".$replacestart."desc_3".$replaceend." LIKE '%name=\"thanks\" value=\"".$likeName."\"%' OR ".$replacestart."desc_4".$replaceend." LIKE '%name=\"thanks\" value=\"".$likeName."\"%' OR ".$replacestart."desc_5".$replaceend." LIKE '%name=\"thanks\" value=\"".$likeName."\"%')

 OR

(".$replacestart."key_1".$replaceend." LIKE '%[".$likeName."]%' OR ".$replacestart."key_2".$replaceend." LIKE '%[".$likeName."]%' OR ".$replacestart."key_3".$replaceend." LIKE '%[".$likeName."]%' OR ".$replacestart."key_4".$replaceend." LIKE '%[".$likeName."]%' OR ".$replacestart."key_5".$replaceend." LIKE '%[".$likeName."]%')
OR
(".$replacestart."key_1".$replaceend." LIKE '%:".$likeName."]%' OR ".$replacestart."key_2".$replaceend." LIKE '%:".$likeName."]%' OR ".$replacestart."key_3".$replaceend." LIKE '%:".$likeName."]%' OR ".$replacestart."key_4".$replaceend." LIKE '%:".$likeName."]%' OR ".$replacestart."key_5".$replaceend." LIKE '%:".$likeName."]%')
OR
(".$replacestart."key_1".$replaceend." LIKE '%[".$likeName."?%' OR ".$replacestart."key_2".$replaceend." LIKE '%[".$likeName."?%' OR ".$replacestart."key_3".$replaceend." LIKE '%[".$likeName."?%' OR ".$replacestart."key_4".$replaceend." LIKE '%[".$likeName."?%' OR ".$replacestart."key_5".$replaceend." LIKE '%[".$likeName."?%')
OR
(".$replacestart."key_1".$replaceend." LIKE '%:".$likeName."?%' OR ".$replacestart."key_2".$replaceend." LIKE '%:".$likeName."?%' OR ".$replacestart."key_3".$replaceend." LIKE '%:".$likeName."?%' OR ".$replacestart."key_4".$replaceend." LIKE '%:".$likeName."?%' OR ".$replacestart."key_5".$replaceend." LIKE '%:".$likeName."?%')
OR
(".$replacestart."key_1".$replaceend." LIKE '%[go:]".$likeName."[:go]%' OR ".$replacestart."key_2".$replaceend." LIKE '%[go:]".$likeName."[:go]%' OR ".$replacestart."key_3".$replaceend." LIKE '%[go:]".$likeName."[:go]%' OR ".$replacestart."key_4".$replaceend." LIKE '%[go:]".$likeName."[:go]%' OR ".$replacestart."key_5".$replaceend." LIKE '%[go:]".$likeName."[:go]%')
OR
(".$replacestart."key_1".$replaceend." LIKE '%[lo:]".$likeName."[:lo]%' OR ".$replacestart."key_2".$replaceend." LIKE '%[lo:]".$likeName."[:lo]%' OR ".$replacestart."key_3".$replaceend." LIKE '%[lo:]".$likeName."[:lo]%' OR ".$replacestart."key_4".$replaceend." LIKE '%[lo:]".$likeName."[:lo]%' OR ".$replacestart."key_5".$replaceend." LIKE '%[lo:]".$likeName."[:lo]%')
OR
(".$replacestart."key_1".$replaceend." LIKE '%name=\"thanks\" value=\"".$likeName."\"%' OR ".$replacestart."key_2".$replaceend." LIKE '%name=\"thanks\" value=\"".$likeName."\"%' OR ".$replacestart."key_3".$replaceend." LIKE '%name=\"thanks\" value=\"".$likeName."\"%' OR ".$replacestart."key_4".$replaceend." LIKE '%name=\"thanks\" value=\"".$likeName."\"%' OR ".$replacestart."key_5".$replaceend." LIKE '%name=\"thanks\" value=\"".$likeName."\"%')

OR

   (typ='f' AND (

(".$replacestart."content_1".$replaceend." LIKE '".$likeName."' OR ".$replacestart."content_2".$replaceend." LIKE '".$likeName."' OR ".$replacestart."content_3".$replaceend." LIKE '".$likeName."' OR ".$replacestart."content_4".$replaceend." LIKE '".$likeName."' OR ".$replacestart."content_5".$replaceend." LIKE '".$likeName."')

    ))

OR
(".$replacestart."content_1".$replaceend." LIKE '%c=".$likeName."' OR ".$replacestart."content_2".$replaceend." LIKE '%c=".$likeName."' OR ".$replacestart."content_3".$replaceend." LIKE '%c=".$likeName."' OR ".$replacestart."content_4".$replaceend." LIKE '%c=".$likeName."' OR ".$replacestart."content_5".$replaceend." LIKE '%c=".$likeName."')
OR
(".$replacestart."content_1".$replaceend." LIKE '%c=".$likeName."&%' OR ".$replacestart."content_2".$replaceend." LIKE '%c=".$likeName."&%' OR ".$replacestart."content_3".$replaceend." LIKE '%c=".$likeName."&%' OR ".$replacestart."content_4".$replaceend." LIKE '%c=".$likeName."&%' OR ".$replacestart."content_5".$replaceend." LIKE '%c=".$likeName."&%')

OR
   (name='".mysql_real_escape_string(u5CodeTemplateName())."' AND (
   
(".$replacestart."content_1".$replaceend." LIKE '%{{{".$likeName."}}}%' OR ".$replacestart."content_1".$replaceend." LIKE '%[!_".$likeName."!_]%')

   ))

$manfph

$codeUsage

) LIMIT 1
";
$sql_o=preg_replace("~(\\bLIKE\\s+'(?:[^'\\\\]|\\\\.)*')~", "$1 ESCAPE '!'", $sql_o);
$result_o=mysql_query($sql_o);

if ($result_o==false) {
die('SQL_o-Query failed! Previous orphan data has been kept.');
}

$num_o = mysql_num_rows($result_o);
$echo.='mark('.orphanJsString($row_a['name']).','.($num_o==0 ? 'true' : 'false').");\n";
}
$echo.="})();\n";
$orphanDirectory=realpath('../fileversions');
$orphanTemp=tempnam($orphanDirectory,'orphan_');
if (!$orphanTemp || dirname($orphanTemp)!==$orphanDirectory) die('Cannot create orphan cache.');
$orphanMode=is_file('../fileversions/orphan.js.php') ? (fileperms('../fileversions/orphan.js.php') & 0777) : (0666 & ~umask());
if (file_put_contents($orphanTemp,$echo)!==strlen($echo) || !chmod($orphanTemp,$orphanMode) || !rename($orphanTemp,'../fileversions/orphan.js.php')) die('Cannot publish orphan cache. Previous data has been kept.');
$orphanTemp=NULL;
file_put_contents('../fileversions/lastorphan.txt',(string)time());
?>
