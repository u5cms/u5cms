<?php
ignore_user_abort(true);set_time_limit(36000); 
require('connect.inc.php');
require('../config.php');

if(file_exists('../fileversions/orphanrunning.txt') && file_get_contents('../fileversions/orphanrunning.txt')!=0 && file_get_contents('../fileversions/orphanrunning.txt')>time()-60*15)die('<script>setTimeout("location.href=location.href",10000)</script>');
file_put_contents('../fileversions/orphanrunning.txt',time());
file_put_contents('../fileversions/lastorphan.txt',time());


$squot1='"';
$squot2="\\'";

$echo='';

$sql_a='SELECT * FROM resources WHERE deleted!=1';  

$result_a=mysql_query($sql_a);

if ($result_a==false) {
	echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$num_a = mysql_num_rows($result_a);

for ($i_a=0; $i_a<$num_a; $i_a++) {
$row_a = mysql_fetch_array($result_a);

$manfph='';

$replacestart="replace(replace(replace(replace(replace(replace(";
$replaceend=",'[[[[',' '),'[[[',' '),'[[',' '),']]]]',' '),']]]',' '),']]',' ')";

if ($ignoremanualfullpaths!='yes') $manfph= "

OR
(".$replacestart."content_1".$replaceend." LIKE '%c=".$row_a['name']."&%' OR ".$replacestart."content_2".$replaceend." LIKE '%c=".$row_a['name']."&%' OR ".$replacestart."content_3".$replaceend." LIKE '%c=".$row_a['name']."&%' OR ".$replacestart."content_4".$replaceend." LIKE '%c=".$row_a['name']."&%' OR ".$replacestart."content_5".$replaceend." LIKE '%c=".$row_a['name']."&%')
OR
(".$replacestart."content_1".$replaceend." LIKE '%c=".$row_a['name']."\"%' OR ".$replacestart."content_2".$replaceend." LIKE '%c=".$row_a['name']."\"%' OR ".$replacestart."content_3".$replaceend." LIKE '%c=".$row_a['name']."\"%' OR ".$replacestart."content_4".$replaceend." LIKE '%c=".$row_a['name']."\"%' OR ".$replacestart."content_5".$replaceend." LIKE '%c=".$row_a['name']."\"%')
OR
(".$replacestart."content_1".$replaceend." LIKE '%c=".$row_a['name'].$squot1."%' OR ".$replacestart."content_2".$replaceend." LIKE '%c=".$row_a['name'].$squot1."%' OR ".$replacestart."content_3".$replaceend." LIKE '%c=".$row_a['name'].$squot1."%' OR ".$replacestart."content_4".$replaceend." LIKE '%c=".$row_a['name'].$squot1."%' OR ".$replacestart."content_5".$replaceend." LIKE '%c=".$row_a['name'].$squot1."%')
OR
(".$replacestart."content_1".$replaceend." LIKE '%c=".$row_a['name'].$squot2."%' OR ".$replacestart."content_2".$replaceend." LIKE '%c=".$row_a['name'].$squot2."%' OR ".$replacestart."content_3".$replaceend." LIKE '%c=".$row_a['name'].$squot2."%' OR ".$replacestart."content_4".$replaceend." LIKE '%c=".$row_a['name'].$squot2."%' OR ".$replacestart."content_5".$replaceend." LIKE '%c=".$row_a['name'].$squot2."%')



OR
(".$replacestart."content_1".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."content_2".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."content_3".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."content_4".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."content_5".$replaceend." LIKE '%r/".$row_a['name']."/%')
OR
(".$replacestart."title_1".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."title_2".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."title_3".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."title_4".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."title_5".$replaceend." LIKE '%r/".$row_a['name']."/%')
OR
(".$replacestart."desc_1".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."desc_2".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."desc_3".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."desc_4".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."desc_5".$replaceend." LIKE '%r/".$row_a['name']."/%')
OR
(".$replacestart."key_1".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."key_2".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."key_3".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."key_4".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."key_5".$replaceend." LIKE '%r/".$row_a['name']."/%')
";

$sql_o="SELECT name FROM resources WHERE deleted!=1 AND name!='-' AND
( 

(".$replacestart."content_1".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."content_2".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."content_3".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."content_4".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."content_5".$replaceend." LIKE '%[".$row_a['name']."]%')
OR
(".$replacestart."content_1".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."content_2".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."content_3".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."content_4".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."content_5".$replaceend." LIKE '%:".$row_a['name']."]%')
OR
(".$replacestart."content_1".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."content_2".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."content_3".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."content_4".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."content_5".$replaceend." LIKE '%[".$row_a['name']."?%')
OR
(".$replacestart."content_1".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."content_2".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."content_3".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."content_4".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."content_5".$replaceend." LIKE '%:".$row_a['name']."?%')
OR
(".$replacestart."content_1".$replaceend." LIKE '%[go:]".$row_a['name']."[:go]%' OR ".$replacestart."content_2".$replaceend." LIKE '%[go:]".$row_a['name']."[:go]%' OR ".$replacestart."content_3".$replaceend." LIKE '%[go:]".$row_a['name']."[:go]%' OR ".$replacestart."content_4".$replaceend." LIKE '%[go:]".$row_a['name']."[:go]%' OR ".$replacestart."content_5".$replaceend." LIKE '%[go:]".$row_a['name']."[:go]%')
OR
(".$replacestart."content_1".$replaceend." LIKE '%[lo:]".$row_a['name']."[:lo]%' OR ".$replacestart."content_2".$replaceend." LIKE '%[lo:]".$row_a['name']."[:lo]%' OR ".$replacestart."content_3".$replaceend." LIKE '%[lo:]".$row_a['name']."[:lo]%' OR ".$replacestart."content_4".$replaceend." LIKE '%[lo:]".$row_a['name']."[:lo]%' OR ".$replacestart."content_5".$replaceend." LIKE '%[lo:]".$row_a['name']."[:lo]%')
OR
(".$replacestart."content_1".$replaceend." LIKE '%name=\"thanks\" value=\"".$row_a['name']."\"%' OR ".$replacestart."content_2".$replaceend." LIKE '%name=\"thanks\" value=\"".$row_a['name']."\"%' OR ".$replacestart."content_3".$replaceend." LIKE '%name=\"thanks\" value=\"".$row_a['name']."\"%' OR ".$replacestart."content_4".$replaceend." LIKE '%name=\"thanks\" value=\"".$row_a['name']."\"%' OR ".$replacestart."content_5".$replaceend." LIKE '%name=\"thanks\" value=\"".$row_a['name']."\"%')

 OR

(".$replacestart."title_1".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."title_2".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."title_3".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."title_4".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."title_5".$replaceend." LIKE '%[".$row_a['name']."]%')
OR
(".$replacestart."title_1".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."title_2".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."title_3".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."title_4".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."title_5".$replaceend." LIKE '%:".$row_a['name']."]%')
OR
(".$replacestart."title_1".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."title_2".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."title_3".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."title_4".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."title_5".$replaceend." LIKE '%[".$row_a['name']."?%')
OR
(".$replacestart."title_1".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."title_2".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."title_3".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."title_4".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."title_5".$replaceend." LIKE '%:".$row_a['name']."?%')
OR
(".$replacestart."title_1".$replaceend." LIKE '%[go:]".$row_a['name']."[:go]%' OR ".$replacestart."title_2".$replaceend." LIKE '%[go:]".$row_a['name']."[:go]%' OR ".$replacestart."title_3".$replaceend." LIKE '%[go:]".$row_a['name']."[:go]%' OR ".$replacestart."title_4".$replaceend." LIKE '%[go:]".$row_a['name']."[:go]%' OR ".$replacestart."title_5".$replaceend." LIKE '%[go:]".$row_a['name']."[:go]%')
OR
(".$replacestart."title_1".$replaceend." LIKE '%[lo:]".$row_a['name']."[:lo]%' OR ".$replacestart."title_2".$replaceend." LIKE '%[lo:]".$row_a['name']."[:lo]%' OR ".$replacestart."title_3".$replaceend." LIKE '%[lo:]".$row_a['name']."[:lo]%' OR ".$replacestart."title_4".$replaceend." LIKE '%[lo:]".$row_a['name']."[:lo]%' OR ".$replacestart."title_5".$replaceend." LIKE '%[lo:]".$row_a['name']."[:lo]%')
OR
(".$replacestart."title_1".$replaceend." LIKE '%name=\"thanks\" value=\"".$row_a['name']."\"%' OR ".$replacestart."title_2".$replaceend." LIKE '%name=\"thanks\" value=\"".$row_a['name']."\"%' OR ".$replacestart."title_3".$replaceend." LIKE '%name=\"thanks\" value=\"".$row_a['name']."\"%' OR ".$replacestart."title_4".$replaceend." LIKE '%name=\"thanks\" value=\"".$row_a['name']."\"%' OR ".$replacestart."title_5".$replaceend." LIKE '%name=\"thanks\" value=\"".$row_a['name']."\"%')

 OR
 
(".$replacestart."desc_1".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."desc_2".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."desc_3".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."desc_4".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."desc_5".$replaceend." LIKE '%[".$row_a['name']."]%')
OR
(".$replacestart."desc_1".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."desc_2".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."desc_3".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."desc_4".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."desc_5".$replaceend." LIKE '%:".$row_a['name']."]%')
OR
(".$replacestart."desc_1".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."desc_2".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."desc_3".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."desc_4".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."desc_5".$replaceend." LIKE '%[".$row_a['name']."?%')
OR
(".$replacestart."desc_1".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."desc_2".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."desc_3".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."desc_4".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."desc_5".$replaceend." LIKE '%:".$row_a['name']."?%')
OR
(".$replacestart."desc_1".$replaceend." LIKE '%[go:]".$row_a['name']."[:go]%' OR ".$replacestart."desc_2".$replaceend." LIKE '%[go:]".$row_a['name']."[:go]%' OR ".$replacestart."desc_3".$replaceend." LIKE '%[go:]".$row_a['name']."[:go]%' OR ".$replacestart."desc_4".$replaceend." LIKE '%[go:]".$row_a['name']."[:go]%' OR ".$replacestart."desc_5".$replaceend." LIKE '%[go:]".$row_a['name']."[:go]%')
OR
(".$replacestart."desc_1".$replaceend." LIKE '%[lo:]".$row_a['name']."[:lo]%' OR ".$replacestart."desc_2".$replaceend." LIKE '%[lo:]".$row_a['name']."[:lo]%' OR ".$replacestart."desc_3".$replaceend." LIKE '%[lo:]".$row_a['name']."[:lo]%' OR ".$replacestart."desc_4".$replaceend." LIKE '%[lo:]".$row_a['name']."[:lo]%' OR ".$replacestart."desc_5".$replaceend." LIKE '%[lo:]".$row_a['name']."[:lo]%')
OR
(".$replacestart."desc_1".$replaceend." LIKE '%name=\"thanks\" value=\"".$row_a['name']."\"%' OR ".$replacestart."desc_2".$replaceend." LIKE '%name=\"thanks\" value=\"".$row_a['name']."\"%' OR ".$replacestart."desc_3".$replaceend." LIKE '%name=\"thanks\" value=\"".$row_a['name']."\"%' OR ".$replacestart."desc_4".$replaceend." LIKE '%name=\"thanks\" value=\"".$row_a['name']."\"%' OR ".$replacestart."desc_5".$replaceend." LIKE '%name=\"thanks\" value=\"".$row_a['name']."\"%')

 OR

(".$replacestart."key_1".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."key_2".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."key_3".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."key_4".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."key_5".$replaceend." LIKE '%[".$row_a['name']."]%')
OR
(".$replacestart."key_1".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."key_2".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."key_3".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."key_4".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."key_5".$replaceend." LIKE '%:".$row_a['name']."]%')
OR
(".$replacestart."key_1".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."key_2".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."key_3".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."key_4".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."key_5".$replaceend." LIKE '%[".$row_a['name']."?%')
OR
(".$replacestart."key_1".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."key_2".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."key_3".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."key_4".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."key_5".$replaceend." LIKE '%:".$row_a['name']."?%')
OR
(".$replacestart."key_1".$replaceend." LIKE '%[go:]".$row_a['name']."[:go]%' OR ".$replacestart."key_2".$replaceend." LIKE '%[go:]".$row_a['name']."[:go]%' OR ".$replacestart."key_3".$replaceend." LIKE '%[go:]".$row_a['name']."[:go]%' OR ".$replacestart."key_4".$replaceend." LIKE '%[go:]".$row_a['name']."[:go]%' OR ".$replacestart."key_5".$replaceend." LIKE '%[go:]".$row_a['name']."[:go]%')
OR
(".$replacestart."key_1".$replaceend." LIKE '%[lo:]".$row_a['name']."[:lo]%' OR ".$replacestart."key_2".$replaceend." LIKE '%[lo:]".$row_a['name']."[:lo]%' OR ".$replacestart."key_3".$replaceend." LIKE '%[lo:]".$row_a['name']."[:lo]%' OR ".$replacestart."key_4".$replaceend." LIKE '%[lo:]".$row_a['name']."[:lo]%' OR ".$replacestart."key_5".$replaceend." LIKE '%[lo:]".$row_a['name']."[:lo]%')
OR
(".$replacestart."key_1".$replaceend." LIKE '%name=\"thanks\" value=\"".$row_a['name']."\"%' OR ".$replacestart."key_2".$replaceend." LIKE '%name=\"thanks\" value=\"".$row_a['name']."\"%' OR ".$replacestart."key_3".$replaceend." LIKE '%name=\"thanks\" value=\"".$row_a['name']."\"%' OR ".$replacestart."key_4".$replaceend." LIKE '%name=\"thanks\" value=\"".$row_a['name']."\"%' OR ".$replacestart."key_5".$replaceend." LIKE '%name=\"thanks\" value=\"".$row_a['name']."\"%')

OR

   (typ='f' AND (

(".$replacestart."content_1".$replaceend." LIKE '".$row_a['name']."' OR ".$replacestart."content_2".$replaceend." LIKE '".$row_a['name']."' OR ".$replacestart."content_3".$replaceend." LIKE '".$row_a['name']."' OR ".$replacestart."content_4".$replaceend." LIKE '".$row_a['name']."' OR ".$replacestart."content_5".$replaceend." LIKE '".$row_a['name']."')

    ))

OR
(".$replacestart."content_1".$replaceend." LIKE '%c=".$row_a['name']."' OR ".$replacestart."content_2".$replaceend." LIKE '%c=".$row_a['name']."' OR ".$replacestart."content_3".$replaceend." LIKE '%c=".$row_a['name']."' OR ".$replacestart."content_4".$replaceend." LIKE '%c=".$row_a['name']."' OR ".$replacestart."content_5".$replaceend." LIKE '%c=".$row_a['name']."')
OR
(".$replacestart."content_1".$replaceend." LIKE '%c=".$row_a['name']."&%' OR ".$replacestart."content_2".$replaceend." LIKE '%c=".$row_a['name']."&%' OR ".$replacestart."content_3".$replaceend." LIKE '%c=".$row_a['name']."&%' OR ".$replacestart."content_4".$replaceend." LIKE '%c=".$row_a['name']."&%' OR ".$replacestart."content_5".$replaceend." LIKE '%c=".$row_a['name']."&%')

OR
   (name='htmltemplate' AND (
   
(".$replacestart."content_1".$replaceend." LIKE '%{{{".$row_a['name']."}}}%' OR ".$replacestart."content_1".$replaceend." LIKE '%[_".$row_a['name']."_]%')

   ))

$manfph


) LIMIT 1
";
$result_o=mysql_query($sql_o);

if ($result_o==false) {
echo 'SQL_o-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_o.'</font><p>';
}

$num_o = mysql_num_rows($result_o);
$echo.= "
if (parent.parent.document.getElementById('o_".$row_a['name']."')) parent.parent.document.getElementById('o_".$row_a['name']."').style.color='blue';
if (parent.parent.document.getElementById('o_".$row_a['name']."')) parent.parent.document.getElementById('o_".$row_a['name']."').title='';
if (parent.parent.document.getElementById('o_".$row_a['name']."')) parent.parent.document.getElementById('o_".$row_a['name']."').innerHTML='L';
if (parent.parent.document.getElementById('oo_".$row_a['name']."')) parent.parent.document.getElementById('oo_".$row_a['name']."').style.color='blue';
if (parent.parent.document.getElementById('oo_".$row_a['name']."')) parent.parent.document.getElementById('oo_".$row_a['name']."').title='';
if (parent.parent.document.getElementById('oo_".$row_a['name']."')) parent.parent.document.getElementById('oo_".$row_a['name']."').innerHTML='L';
";
if ($num_o==0) $echo.= "
if (parent.parent.document.getElementById('o_".$row_a['name']."')) parent.parent.document.getElementById('o_".$row_a['name']."').style.color='red';
if (parent.parent.document.getElementById('o_".$row_a['name']."')) parent.parent.document.getElementById('o_".$row_a['name']."').title='orphan';
if (parent.parent.document.getElementById('o_".$row_a['name']."')) parent.parent.document.getElementById('o_".$row_a['name']."').innerHTML='L<!--_orphan_-->';
if (parent.parent.document.getElementById('oo_".$row_a['name']."')) parent.parent.document.getElementById('oo_".$row_a['name']."').style.color='red';
if (parent.parent.document.getElementById('oo_".$row_a['name']."')) parent.parent.document.getElementById('oo_".$row_a['name']."').title='orphan';
if (parent.parent.document.getElementById('oo_".$row_a['name']."')) parent.parent.document.getElementById('oo_".$row_a['name']."').innerHTML='L<!--_orphan_-->';
";
}
file_put_contents('../fileversions/orphan.js.php',''.$echo);
file_put_contents('../fileversions/orphanrunning.txt',0);
?>