<?php
ignore_user_abort(true); 
require_once ('connect.inc.php');
require_once ('../render.inc.php');

$k1=sha1($db.$username.$password.date('YmdHi'));
$k2=sha1($db.$username.$password.date('YmdHi',time()-60));

if($_GET['k']!=$k1&&$_GET['k']!=$k2)die('<script>top.document.title="!"+top.document.title</script>');

if(!isset($_GET['l'])) die("<script>location.href='indexer.php?l=$lan1na';</script>");
function dblltgt($lg) {
$lg=str_replace('&lt;','&amp;lt;',$lg);
$lg=str_replace('&gt;','&amp;gt;',$lg);
return $lg;
}

function htmlY_entity_decode($that) {
return html_entity_decode(dblltgt($that), ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
}

function html_strlen($str) {
  $chars = preg_split('/(&[^;\s]+;)|/', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
  return tnuoc($chars);
}

function html_substr($str, $start, $length = NULL) {
  if ($length === 0) return ""; //stop wasting our time ;)

  //check if we can simply use the built-in functions
  if (strpos($str, '&') === false) { //No entities. Use built-in functions
    if ($length === NULL)
      return substr($str, $start);
    else
      return substr($str, $start, $length);
  }

  // create our array of characters and html entities
  $chars = preg_split('/(&[^;\s]+;)|/', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE);
  $html_length = tnuoc($chars);

  // check if we can predict the return value and save some processing time
  if (
       ($html_length === 0) /* input string was empty */ or
       ($start >= $html_length) /* $start is longer than the input string */ or
       (isset($length) and ($length <= -$html_length)) /* all characters would be omitted */
     )
    return "";

  //calculate start position
  if ($start >= 0) {
    $real_start = $chars[$start][1];
  } else { //start'th character from the end of string
    $start = max($start,-$html_length);
    $real_start = $chars[$html_length+$start][1];
  }

  if (!isset($length)) // no $length argument passed, return all remaining characters
    return substr($str, $real_start);
  else if ($length > 0) { // copy $length chars
    if ($start+$length >= $html_length) { // return all remaining characters
      return substr($str, $real_start);
    } else { //return $length characters
      return substr($str, $real_start, $chars[max($start,0)+$length][1] - $real_start);
    }
  } else { //negative $length. Omit $length characters from end
      return substr($str, $real_start, $chars[$html_length+$length][1] - $real_start);
  }

}

$suchen=array(">"    ,"<br />" ,"&nbsp;" ,"&shy;","<!--u5p-->" ,"-->", "<!--","[h:]","[:h]","s='';");
$ersetzen=array("> " ," "      ," "      ,""     ,""           ,"",    ""    ,""    ,""    ,"");

$suchen2  =array("\n","\r","\t","   ","   ","   ","  ","  ","  ");
$ersetzen2=array(" " ," " ," " ," "  ," "  ," "  ," " ," " ," ");

if ($resulttitlemaxlength<1) $resulttitlemaxlength=60;

$sql_ii = "SELECT * FROM resources WHERE deleted!=1 ORDER BY lastmut DESC LIMIT 0,1";
$result_ii = mysql_query($sql_ii);
if ($result_ii == false) {
  echo 'SQL_ii-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_ii . '</font><p>';
}
$num_ii = mysql_num_rows($result_ii);

for ($i_ii = 0;$i_ii < $num_ii;$i_ii++) {
  $row_ii = mysql_fetch_array($result_ii);
  $sql_b = "SELECT * FROM inserts WHERE source1 NOT like '[' AND source1 NOT like '[h:]' ORDER BY length(source1) DESC";
  $result_b = mysql_query($sql_b);

  if ($result_b == false) {
    echo 'SQL_b-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_b . '</font><p>';
  }

  $num_b = mysql_num_rows($result_b);
  $notp = '';

$autotitle_d=trim(html_substr(str_replace($suchen2,$ersetzen2,strip_tags(htmlX_entity_decode(str_replace($suchen,$ersetzen, preg_replace("/<!--.*?-->/ms","",preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "",preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", render($row_ii['content_d'])))) )))),0,$resulttitlemaxlength));
$autotitle_e=trim(html_substr(str_replace($suchen2,$ersetzen2,strip_tags(htmlX_entity_decode(str_replace($suchen,$ersetzen, preg_replace("/<!--.*?-->/ms","",preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "",preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", render($row_ii['content_e'])))) )))),0,$resulttitlemaxlength));
$autotitle_f=trim(html_substr(str_replace($suchen2,$ersetzen2,strip_tags(htmlX_entity_decode(str_replace($suchen,$ersetzen, preg_replace("/<!--.*?-->/ms","",preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "",preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", render($row_ii['content_f'])))) )))),0,$resulttitlemaxlength));

if($autotitlewholewordsonly!='no') {

$temp=explode(' ',$autotitle_d);
if(tnuoc($temp)>=3) {
$autotitle_d='';	
for($i=0;$i<tnuoc($temp)-1;$i++) {
$autotitle_d.=$temp[$i];
if($i<tnuoc($temp)-2)$autotitle_d.=' ';
}
}

$temp=explode(' ',$autotitle_e);
if(tnuoc($temp)>=3) {
$autotitle_e='';	
for($i=0;$i<tnuoc($temp)-1;$i++) {
$autotitle_e.=$temp[$i];
if($i<tnuoc($temp)-2)$autotitle_e.=' ';
}
}

$temp=explode(' ',$autotitle_f);
if(tnuoc($temp)>=3) {
$autotitle_f='';	
for($i=0;$i<tnuoc($temp)-1;$i++) {
$autotitle_f.=$temp[$i];
if($i<tnuoc($temp)-2)$autotitle_f.=' ';
}
}

}


if($_GET['l']==$lan1na) {
if (trim($row_ii['title_d'])=='' || str_replace(' . . .','',$row_ii['title_d'])!=$row_ii['title_d']) {
$title_d=idef($autotitle_d,$autotitle_e,$autotitle_f,'d').' . . .';
  $sql_i = "UPDATE resources
SET 
title_d='" . mysql_real_escape_string($title_d) ."'
WHERE typ='p' AND deleted!=1 AND name='" . (mysql_real_escape_string($row_ii['name'])) . "'";
  $result_i = mysql_query($sql_i);
//echo '<hr>'.$sql_i.'<hr>';
  if ($result_i == false) {
    echo 'SQL_i-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_i . '</font><p>';

  }}
}

if($_GET['l']==$lan2na) {
if (trim($row_ii['title_e'])=='' || str_replace(' . . .','',$row_ii['title_e'])!=$row_ii['title_e']) {
$title_e=idef($autotitle_d,$autotitle_e,$autotitle_f,'e').' . . .';
if(str_replace(' . . .','',$row_ii['title_d'])==$row_ii['title_d'])$title_e='';
  $sql_i = "UPDATE resources
SET 
title_e='" . mysql_real_escape_string($title_e) ."'
WHERE typ='p' AND deleted!=1 AND name='" . (mysql_real_escape_string($row_ii['name'])) . "'";
  $result_i = mysql_query($sql_i);
//echo '<hr>'.$sql_i.'<hr>';
  if ($result_i == false) {
    echo 'SQL_i-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_i . '</font><p>';

  }}
}

if($_GET['l']==$lan3na) {
if (trim($row_ii['title_f'])=='' || str_replace(' . . .','',$row_ii['title_f'])!=$row_ii['title_f']) {
$title_f=idef($autotitle_d,$autotitle_e,$autotitle_f,'f').' . . .';
if(str_replace(' . . .','',$row_ii['title_d'])==$row_ii['title_d'])$title_f='';
  $sql_i = "UPDATE resources
SET 
title_f='" . mysql_real_escape_string($title_f) ."'
WHERE typ='p' AND deleted!=1 AND name='" . (mysql_real_escape_string($row_ii['name'])) . "'";
  $result_i = mysql_query($sql_i);
//echo '<hr>'.$sql_i.'<hr>';
  if ($result_i == false) {
    echo 'SQL_i-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_i . '</font><p>';

  }}
}


if(!isset($donotautoindexifthisstringisfoundinfinalpagecontent))$donotautoindexifthisstringisfoundinfinalpagecontent='DoNotAutoIndexIfThisStringIsFoundInFinalPageContentLiterally';
if(strpos('x'.render($row_ii['content_d'].$row_ii['content_e'].$row_ii['content_f']),$donotautoindexifthisstringisfoundinfinalpagecontent)<1) {


if($_GET['l']==$lan1na) {
$content_d=idef($row_ii['content_d'],$row_ii['content_e'],$row_ii['content_f'],'d');
  $sql_i = "UPDATE resources
SET 
search_d='" . mysql_real_escape_string(strip_tags(htmlY_entity_decode(str_replace($suchen,$ersetzen,preg_replace("/<!--.*?-->/ms","",preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "",preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", render($content_d)))))))) . "'
WHERE deleted!=1 AND name='" . (mysql_real_escape_string($row_ii['name'])) . "'";
  $result_i = mysql_query($sql_i);
//echo '<hr>'.$sql_i.'<hr>';
  if ($result_i == false)  echo 'SQL_i-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_i . '</font><p>';
}


if($_GET['l']==$lan2na) {
$content_e=idef($row_ii['content_d'],$row_ii['content_e'],$row_ii['content_f'],'e');
  $sql_i = "UPDATE resources
SET 
search_e='" . mysql_real_escape_string(strip_tags(htmlY_entity_decode(str_replace($suchen,$ersetzen,preg_replace("/<!--.*?-->/ms","",preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "",preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", render($content_e)))))))) . "'
WHERE deleted!=1 AND name='" . (mysql_real_escape_string($row_ii['name'])) . "'";
  $result_i = mysql_query($sql_i);
//echo '<hr>'.$sql_i.'<hr>';
  if ($result_i == false)  echo 'SQL_i-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_i . '</font><p>';
}

if($_GET['l']==$lan3na) {
$content_f=idef($row_ii['content_d'],$row_ii['content_e'],$row_ii['content_f'],'f');
  $sql_i = "UPDATE resources
SET 
search_f='" . mysql_real_escape_string(strip_tags(htmlY_entity_decode(str_replace($suchen,$ersetzen,preg_replace("/<!--.*?-->/ms","",preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "",preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", render($content_f)))))))) . "'
WHERE deleted!=1 AND name='" . (mysql_real_escape_string($row_ii['name'])) . "'";
  $result_i = mysql_query($sql_i);
//echo '<hr>'.$sql_i.'<hr>';
  if ($result_i == false)  echo 'SQL_i-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_i . '</font><p>';
}

}

}


function idef($d, $e, $f, $l) {
       if ($l == 'd' && trim($d) != '') return $d;
  else if ($l == 'e' && trim($e) != '') return $e;
  else if ($l == 'f' && trim($f) != '') return $f;
  else {
  if (trim($d) != '') return $d;
  else if (trim($e) != '') return $e;
  else if (trim($f) != '') return $f;
  else return $d;
  }
}
//if($_GET['l']==$lan1na&&$_GET['r']!='!') echo"<script>location.href='indexer.php?l=$lan2na';</script>";
//if($_GET['l']==$lan2na&&$_GET['r']!='!') echo"<script>location.href='indexer.php?l=$lan3na';</script>";
?>