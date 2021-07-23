<?php require('connect.inc.php'); ?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>sharing...</title>
</head>

<body>

<?php
if(!isset($_POST['xshareTYPE']))echo'<img src="upload/spinner.gif" />';

$typ=enco(($_POST['xshareTYPE']));
$tit=enco(($_POST['xshareTITLE']));
$loc=enco(($_POST['xshareLOCATION']));
$txt=enco(($_POST['xshareTEXT']));


function enco($s) {
global $quotehandling;
if($quotehandling=='on')$s=stripslashes($s);
$s=str_replace("\r",' ',$s);
$s=str_replace("\n",' ',$s);
$s=str_replace("\t",' ',$s);

$s=str_replace('  ',' ',$s);
$s=str_replace('  ',' ',$s);
$s=str_replace('  ',' ',$s);

$s=html_entity_decode(utf8_encode(htmlentities($s,ENT_QUOTES,'cp1252')), ENT_QUOTES, 'UTF-8');
return $s;	
}

//appid ******************************************************************************
if(!isset($fbappid))$fbappid='0';

//image ******************************************************************************
//&f=r/sneaker/sneaker_de.jpg">
//src="r/afreestyleimage/afreestyleimage_de.jpg?t=1311250328"

if(strpos($txt,'&amp;f=r/')>0) {
$fbimg=explode('&amp;f=r/',$txt);
$fbimg=explode('">',$fbimg[1]);
$fbimg=str_replace(basename($scripturi),'',$scripturi).'r/'.trim($fbimg[0]);
}

else if(strpos($txt,'src="r/')>0) {
$fbimg=explode('src="r/',$txt);
$fbimg=explode('?t=',$fbimg[1]);
$fbimg=str_replace(basename($scripturi),'',$scripturi).'r/'.trim($fbimg[0]);
}

else $fbimg=str_replace(basename($scripturi),'',$scripturi).$fbfallbackimg;

//title ******************************************************************************
if(strpos($txt,'</h')>0) {
$fbtit=explode('</h',$txt);
$fbtit=explode('<h',$fbtit[0]);
$fbtit=strip_tags('<'.$fbtit[1]);
$titit=$fbtit;
}
else $fbtit=$tit;

//caption ******************************************************************************
$fbcap=str_replace(basename($scripturi),'',$scripturi);

//text ******************************************************************************
if(strpos($txt,'</h')>0) {
$fbtxt=explode('</h',$txt);
$fbtxt='<'.$fbtxt[1];
}
else $fbtxt=$txt;
$fbtxt=strip_tags($fbtxt);

//text ******************************************************************************
$fbclose=str_replace(basename($scripturi),'',$scripturi).'close.php';



if ($typ=='f') {
//$fbappid='';
if($fbappid<1) {
echo "<script>location.href='xshare0.php?link=".$loc."&picture=".fb($fbimg)."&name=".fb($fbtit)."&caption=".fb($fbcap)."&description=".fb($fbtxt)."&redirect_uri=".fb($fbclose)."&display=popup';</script>";
}
else {
echo "<script>location.href='http://www.facebook.com/dialog/feed?app_id=".$fbappid."&link=".$loc."&picture=".fb($fbimg)."&name=".fb(html_entity_decode($fbtit, ENT_QUOTES, 'UTF-8'))."&caption=".fb($fbcap)."&description=".fb($fbtxt)."&redirect_uri=".fb($fbclose)."&display=popup';</script>";
}
}


if ($typ=='t') {
echo "<script>location.href='http://twitter.com/home?status=".fb($loc)." ".fb(html_entity_decode(html_substr(($titit.": ".$fbtxt),0,111),ENT_QUOTES,'UTF-8'))."';</script>";
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

function fb($s) {
$s=rawurlencode($s);
$s=str_replace('%20%20','%20',$s);
$s=str_replace('%20%20','%20',$s);
$s=str_replace('%20%20','%20',$s);
return $s;
}
?>
</body>
</html>
