<?php
ignore_user_abort(true);set_time_limit(3600); 
require_once ('connect.inc.php');
require_once ('render.inc.php');
require_once('getinserts.inc.php');

if(file_exists('fileversions/indexerrunning'.htmlspecialchars($_GET['l']).'.txt') && file_get_contents('fileversions/indexerrunning'.htmlspecialchars($_GET['l']).'.txt')!=0 && file_get_contents('fileversions/indexerrunning'.htmlspecialchars($_GET['l']).'.txt')>time()-60*15)die('<script>top.document.title="_"+top.document.title</script>');
file_put_contents('fileversions/indexerrunning'.htmlspecialchars($_GET['l']).'.txt',time());

file_put_contents('fileversions/lastindex.txt',time());

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

$sql_ii = "SELECT * FROM resources WHERE deleted!=1 ORDER BY lastmut DESC";
$result_ii = mysql_query($sql_ii);
if ($result_ii == false) echo 'SQL_ii-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_ii . '</font><p>';
$num_ii = mysql_num_rows($result_ii);

for ($i_ii = 0;$i_ii < $num_ii;$i_ii++) {
$row_ii = mysql_fetch_array($result_ii);

$autotitle_1=trim(html_substr(str_replace($suchen2,$ersetzen2,strip_tags(htmlX_entity_decode(str_replace($suchen,$ersetzen, preg_replace("/<!--.*?-->/ms","",preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "",preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", render($row_ii['content_1'])))) )))),0,$resulttitlemaxlength));
$autotitle_2=trim(html_substr(str_replace($suchen2,$ersetzen2,strip_tags(htmlX_entity_decode(str_replace($suchen,$ersetzen, preg_replace("/<!--.*?-->/ms","",preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "",preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", render($row_ii['content_2'])))) )))),0,$resulttitlemaxlength));
$autotitle_3=trim(html_substr(str_replace($suchen2,$ersetzen2,strip_tags(htmlX_entity_decode(str_replace($suchen,$ersetzen, preg_replace("/<!--.*?-->/ms","",preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "",preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", render($row_ii['content_3'])))) )))),0,$resulttitlemaxlength));
$autotitle_4=trim(html_substr(str_replace($suchen2,$ersetzen2,strip_tags(htmlX_entity_decode(str_replace($suchen,$ersetzen, preg_replace("/<!--.*?-->/ms","",preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "",preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", render($row_ii['content_4'])))) )))),0,$resulttitlemaxlength));
$autotitle_5=trim(html_substr(str_replace($suchen2,$ersetzen2,strip_tags(htmlX_entity_decode(str_replace($suchen,$ersetzen, preg_replace("/<!--.*?-->/ms","",preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "",preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", render($row_ii['content_5'])))) )))),0,$resulttitlemaxlength));

if($autotitlewholewordsonly!='no') {

    $temp = explode(' ', $autotitle_1);
    if (tnuoc($temp) >= 3) {
        $autotitle_1 = '';
        for ($i = 0; $i < tnuoc($temp) - 1; $i++) {
            $autotitle_1 .= $temp[$i];
            if ($i < tnuoc($temp) - 2) $autotitle_1 .= ' ';
        }
    }

    $temp = explode(' ', $autotitle_2);
    if (tnuoc($temp) >= 3) {
        $autotitle_2 = '';
        for ($i = 0; $i < tnuoc($temp) - 1; $i++) {
            $autotitle_2 .= $temp[$i];
            if ($i < tnuoc($temp) - 2) $autotitle_2 .= ' ';
        }
    }

    $temp = explode(' ', $autotitle_3);
    if (tnuoc($temp) >= 3) {
        $autotitle_3 = '';
        for ($i = 0; $i < tnuoc($temp) - 1; $i++) {
            $autotitle_3 .= $temp[$i];
            if ($i < tnuoc($temp) - 2) $autotitle_3 .= ' ';
        }
    }

    $temp=explode(' ',$autotitle_4);
    if(tnuoc($temp)>=3) {
        $autotitle_4='';
        for($i=0;$i<tnuoc($temp)-1;$i++) {
            $autotitle_4.=$temp[$i];
            if($i<tnuoc($temp)-2)$autotitle_4.=' ';
        }
    }

    $temp=explode(' ',$autotitle_5);
    if(tnuoc($temp)>=3) {
        $autotitle_5='';
        for($i=0;$i<tnuoc($temp)-1;$i++) {
            $autotitle_5.=$temp[$i];
            if($i<tnuoc($temp)-2)$autotitle_5.=' ';
        }
    }
}

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

$sql_i = "UPDATE resources SET 
title_1='" . mysql_real_escape_string($title_1) ."',
title_2='" . mysql_real_escape_string($title_2) ."',
title_3='" . mysql_real_escape_string($title_3) ."',
title_4='" . mysql_real_escape_string($title_4) ."',
title_5='" . mysql_real_escape_string($title_5) ."'
WHERE typ='p' AND deleted!=1 AND name='" . (mysql_real_escape_string($row_ii['name'])) . "'";
$result_i = mysql_query($sql_i);
if ($result_i == false) echo 'SQL_i-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_i . '</font><p>';

if(!isset($donotautoindexifthisstringisfoundinfinalpagecontent))$donotautoindexifthisstringisfoundinfinalpagecontent='DoNotAutoIndexIfThisStringIsFoundInFinalPageContentLiterally';
if(strpos('x'.render($row_ii['content_1'].$row_ii['content_2'].$row_ii['content_3'].$row_ii['content_4'].$row_ii['content_5']),$donotautoindexifthisstringisfoundinfinalpagecontent)<1) {

$content_1=idef($row_ii['content_1'],$row_ii['content_2'],$row_ii['content_3'],$row_ii['content_4'],$row_ii['content_5'],'1');
$content_2=idef($row_ii['content_1'],$row_ii['content_2'],$row_ii['content_3'],$row_ii['content_4'],$row_ii['content_5'],'2');
$content_3=idef($row_ii['content_1'],$row_ii['content_2'],$row_ii['content_3'],$row_ii['content_4'],$row_ii['content_5'],'3');
$content_4=idef($row_ii['content_1'],$row_ii['content_2'],$row_ii['content_3'],$row_ii['content_4'],$row_ii['content_5'],'4');
$content_5=idef($row_ii['content_1'],$row_ii['content_2'],$row_ii['content_3'],$row_ii['content_4'],$row_ii['content_5'],'5');

$sql_i = "UPDATE resources
SET 
search_1='" . mysql_real_escape_string(strip_tags(htmlY_entity_decode(str_replace($suchen,$ersetzen,preg_replace("/<!--.*?-->/ms","",preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "",preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", render($content_1)))))))) . "',
search_2='" . mysql_real_escape_string(strip_tags(htmlY_entity_decode(str_replace($suchen,$ersetzen,preg_replace("/<!--.*?-->/ms","",preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "",preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", render($content_2)))))))) . "',
search_3='" . mysql_real_escape_string(strip_tags(htmlY_entity_decode(str_replace($suchen,$ersetzen,preg_replace("/<!--.*?-->/ms","",preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "",preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", render($content_3)))))))) . "',
search_4='" . mysql_real_escape_string(strip_tags(htmlY_entity_decode(str_replace($suchen,$ersetzen,preg_replace("/<!--.*?-->/ms","",preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "",preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", render($content_4)))))))) . "',
search_5='" . mysql_real_escape_string(strip_tags(htmlY_entity_decode(str_replace($suchen,$ersetzen,preg_replace("/<!--.*?-->/ms","",preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "",preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", render($content_5)))))))) . "'
WHERE deleted!=1 AND name='" . (mysql_real_escape_string($row_ii['name'])) . "'";
$result_i = mysql_query($sql_i);
if ($result_i == false)  echo 'SQL_i-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_i . '</font><p>';
}
}

function idef($l1, $l2, $l3, $l4, $l5, $l) {
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

echo '<audio id="doneaudio" src="'.rand(1,6).'.mp3" autoplay />';
file_put_contents('fileversions/indexerrunning'.htmlspecialchars($_GET['l']).'.txt',0);
?>
<script>var audio = document.getElementById("doneaudio");audio.volume = 0.05;</script>
<script>top.document.title='] '+top.document.title</script>
<iframe style="display:none" src="htaccess.php"></iframe>