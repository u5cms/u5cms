<?php
require('../config.php');
$squot1='"';
$squot2="\\'";

$h=sha1($username.$password.$_SERVER['PHP_AUTH_USER'].$_SERVER['PHP_AUTH_PW'].htmlspecialchars_decode($_GET['sql']));

if($h!=$_GET['h'])die('<script>alert("forbidden query (orphan sql not signed)")</script>');

$sql_a=htmlspecialchars_decode($_GET['sql']);
$sql_a='SELECT * FROM resources WHERE'.str_replace('SELECT * FROM resources WHERE','',$sql_a);  

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
if ($num_o==0) echo "
<script>
if (parent.document.getElementById('o_".$row_a['name']."')) parent.document.getElementById('o_".$row_a['name']."').style.color='red';
if (parent.document.getElementById('o_".$row_a['name']."')) parent.document.getElementById('o_".$row_a['name']."').title='orphan';
if (parent.document.getElementById('o_".$row_a['name']."')) parent.document.getElementById('o_".$row_a['name']."').innerHTML='L<!--_orphan_-->';
if (parent.document.getElementById('oo_".$row_a['name']."')) parent.document.getElementById('oo_".$row_a['name']."').style.color='red';
if (parent.document.getElementById('oo_".$row_a['name']."')) parent.document.getElementById('oo_".$row_a['name']."').title='orphan';
if (parent.document.getElementById('oo_".$row_a['name']."')) parent.document.getElementById('oo_".$row_a['name']."').innerHTML='L<!--_orphan_-->';
</script>";
}
?>
