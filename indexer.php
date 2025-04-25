<?php
ignore_user_abort(true);set_time_limit(36000); 
require_once ('connect.inc.php');
require_once ('render.inc.php');
require_once('getinserts.inc.php');

if(!isset($_GET['n'])) {
if(file_exists('fileversions/indexerrunning.txt') && file_get_contents('fileversions/indexerrunning.txt')!=0 && file_get_contents('fileversions/indexerrunning.txt')>time()-60*15)die('<script>top.document.title="_"+top.document.title</script>');
file_put_contents('fileversions/indexerrunning.txt',time());
file_put_contents('fileversions/lastindex.txt',time());
}

function sanitize_content($content) {
	$content=htmlX_entity_decode($content);
    $content = str_replace(['<nobr>', '</nobr>'], '', $content);
    $content = str_replace('&nbsp;', ' ', $content);
    $content = preg_replace('/>(\S)/', '> $1', $content);
    $content = preg_replace('/(\S)</', '$1 <', $content);
    $content = preg_replace([
        '/<script\b[^>]*>[\s\S]*?<\/script>/is',
        '/<\?(php)?[\s\S]*?\?>/is',
        '/<!--[\s\S]*?-->/',
        '/<style\b[^>]*>[\s\S]*?<\/style>/is'
    ], '', $content);
    $content = strip_tags($content);
    $content = preg_replace('/\s+/', ' ', $content);
    return trim($content);
}

function takeSomeWords(string $input): string {
	global $maxwordsindocumenttitle;
	if($maxwordsindocumenttitle<1)$maxwordsindocumenttitle=7;
    $words = explode(' ', $input);
    $firstSevenWords = array_slice($words, 0, $maxwordsindocumenttitle);
    return implode(' ', $firstSevenWords);
}

function dblltgt($lg) {
$lg=str_replace('&lt;','&amp;lt;',$lg);
$lg=str_replace('&gt;','&amp;gt;',$lg);
return $lg;
}

if(isset($_GET['n'])) {
	$oneitemonly=" name='".mysql_real_escape_string(htmlspecialchars($_GET['n']))."' AND";
}	
else $oneitemonly='';

$sql_ii = "SELECT * FROM resources WHERE $oneitemonly deleted!=1 ORDER BY lastmut DESC";
$result_ii = mysql_query($sql_ii);
if ($result_ii == false) echo 'SQL_ii-Query failed!...!<p>';
$num_ii = mysql_num_rows($result_ii);

for ($i_ii = 0;$i_ii < $num_ii;$i_ii++) {
usleep(100000);
$row_ii = mysql_fetch_array($result_ii);

$_GET['c']=$row_ii['name'];

$_GET['l']=$lan1na;$content_1=render(idef($row_ii['content_1'],$row_ii['content_2'],$row_ii['content_3'],$row_ii['content_4'],$row_ii['content_5'],'1'));
$_GET['l']=$lan2na;$content_2=render(idef($row_ii['content_1'],$row_ii['content_2'],$row_ii['content_3'],$row_ii['content_4'],$row_ii['content_5'],'2'));
$_GET['l']=$lan3na;$content_3=render(idef($row_ii['content_1'],$row_ii['content_2'],$row_ii['content_3'],$row_ii['content_4'],$row_ii['content_5'],'3'));
$_GET['l']=$lan4na;$content_4=render(idef($row_ii['content_1'],$row_ii['content_2'],$row_ii['content_3'],$row_ii['content_4'],$row_ii['content_5'],'4'));
$_GET['l']=$lan5na;$content_5=render(idef($row_ii['content_1'],$row_ii['content_2'],$row_ii['content_3'],$row_ii['content_4'],$row_ii['content_5'],'5'));

$content_1 = sanitize_content($content_1);
$content_2 = sanitize_content($content_2);
$content_3 = sanitize_content($content_3);
$content_4 = sanitize_content($content_4);
$content_5 = sanitize_content($content_5);

$autotitle_1=takeSomeWords($content_1);
$autotitle_2=takeSomeWords($content_2);
$autotitle_3=takeSomeWords($content_3);
$autotitle_4=takeSomeWords($content_4);
$autotitle_5=takeSomeWords($content_5);

if (trim($row_ii['title_1'])=='' || str_replace(' . . .','',$row_ii['title_1'])!=$row_ii['title_1']) {
$title_1=idef($autotitle_1,$autotitle_2,$autotitle_3,$autotitle_4,$autotitle_5,'1').' . . .';
}
else {
$title_1=$row_ii['title_1'];
}

if (trim($row_ii['title_2'])=='' || str_replace(' . . .','',$row_ii['title_2'])!=$row_ii['title_2']) {
$title_2=idef($autotitle_1,$autotitle_2,$autotitle_3,$autotitle_4,$autotitle_5,'2').' . . .';
}
else {
$title_2=$row_ii['title_2'];
}

if (trim($row_ii['title_3'])=='' || str_replace(' . . .','',$row_ii['title_3'])!=$row_ii['title_3']) {
$title_3=idef($autotitle_1,$autotitle_2,$autotitle_3,$autotitle_4,$autotitle_5,'3').' . . .';
}
else {
$title_3=$row_ii['title_3'];
}

if (trim($row_ii['title_4'])=='' || str_replace(' . . .','',$row_ii['title_4'])!=$row_ii['title_4']) {
$title_4=idef($autotitle_1,$autotitle_2,$autotitle_3,$autotitle_4,$autotitle_5,'4').' . . .';
}
else {
$title_4=$row_ii['title_4'];
}

if (trim($row_ii['title_5'])=='' || str_replace(' . . .','',$row_ii['title_5'])!=$row_ii['title_5']) {
$title_5=idef($autotitle_1,$autotitle_2,$autotitle_3,$autotitle_4,$autotitle_5,'5').' . . .';
}
else {
$title_5=$row_ii['title_5'];
}

if(!isset($donotautoindexifthisstringisfoundinfinalpagecontent))$donotautoindexifthisstringisfoundinfinalpagecontent='DoNotAutoIndexIfThisStringIsFoundInFinalPageContentLiterally';
if(strpos('x'.render($row_ii['content_1'].$row_ii['content_2'].$row_ii['content_3'].$row_ii['content_4'].$row_ii['content_5']),$donotautoindexifthisstringisfoundinfinalpagecontent)<1) {

if($row_ii['typ']=='p') {
$sql_i = "UPDATE resources
SET 
title_1='" . mysql_real_escape_string($title_1) ."',
title_2='" . mysql_real_escape_string($title_2) ."',
title_3='" . mysql_real_escape_string($title_3) ."',
title_4='" . mysql_real_escape_string($title_4) ."',
title_5='" . mysql_real_escape_string($title_5) ."',
search_1='" . mysql_real_escape_string($content_1) . "',
search_2='" . mysql_real_escape_string($content_2) . "',
search_3='" . mysql_real_escape_string($content_3) . "',
search_4='" . mysql_real_escape_string($content_4) . "',
search_5='" . mysql_real_escape_string($content_5) . "' 
WHERE deleted!=1 AND name='" . (mysql_real_escape_string($row_ii['name'])) . "'";
$result_i = mysql_query($sql_i);
if ($result_i == false)  echo 'SQL_i-Query failed!...!<p>';
}
}
else {
if($row_ii['typ']=='p') {
$sql_i = "UPDATE resources
SET 
title_1='" . mysql_real_escape_string($title_1) ."',
title_2='" . mysql_real_escape_string($title_2) ."',
title_3='" . mysql_real_escape_string($title_3) ."',
title_4='" . mysql_real_escape_string($title_4) ."',
title_5='" . mysql_real_escape_string($title_5) ."'
WHERE deleted!=1 AND name='" . (mysql_real_escape_string($row_ii['name'])) . "'";
$result_i = mysql_query($sql_i);
if ($result_i == false)  echo 'SQL_i-Query failed!...!<p>';
}
}
if($row_ii['typ']!='p') {
$sql_i = "UPDATE resources
SET 
search_1='" . mysql_real_escape_string($content_1) . "',
search_2='" . mysql_real_escape_string($content_2) . "',
search_3='" . mysql_real_escape_string($content_3) . "',
search_4='" . mysql_real_escape_string($content_4) . "',
search_5='" . mysql_real_escape_string($content_5) . "' 
WHERE deleted!=1 AND name='" . (mysql_real_escape_string($row_ii['name'])) . "'";
$result_i = mysql_query($sql_i);
if ($result_i == false)  echo 'SQL_i-Query failed!...!<p>';
}
}

function idef($l1='',$l2='',$l3='',$l4='',$l5='',$l='') {
    $l1 = $l1 ?? '';
    $l3 = $l3 ?? '';
    $l2 = $l2 ?? '';
    $l4 = $l4 ?? '';
    $l5 = $l5 ?? '';	
	
       if ($l == '1' && trim($l1) != '') return $l1;
  else if ($l == '2' && trim($l2) != '') return $l2;
  else if ($l == '3' && trim($l3) != '') return $l3;
  else if ($l == '4' && trim($l4) != '') return $l4;
  else if ($l == '5' && trim($l5) != '') return $l5;
  else {
       if (trim($l1) != '') return $l1;
  else if (trim($l2) != '') return $l2;
  else if (trim($l3) != '') return $l3;
  else if (trim($l4) != '') return $l4;
  else if (trim($l5) != '') return $l5;
  else return $l1;
  }
}
if(!isset($_GET['n'])) {
echo '<audio id="doneaudio" src="u5admin/'.rand(1,6).'.mp3" autoplay />';
file_put_contents('fileversions/indexerrunning.txt',0);
?>
<script>var audio = document.getElementById("doneaudio");audio.volume = 0.05;</script>
<script>top.document.title=';'+top.document.title</script>
<?php }?>