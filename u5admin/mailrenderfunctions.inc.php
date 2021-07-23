<?php 
require_once('u5idn.inc.php');
function render($s) {

global $mynl2br;
global $headcsv;
global $datacsv;
global $notespart;
global $row_a;

$s=str_replace('[','|_-|-_|!_-!-_!',$s);
$s=str_replace(']','|_-|-_|',$s);

$s=explode('|_-|-_|',$s);

for($i=0;$i<count($s);$i++) {
if(strpos('x'.$s[$i],'!_-!-_!')>0) {

$s[$i]=str_replace('!_-!-_!','',$s[$i]);
$parts=explode('|',$s[$i]);
$field=str_replace('*','_mandatory',$parts[0]);
$cond=$parts[1];
$value=$parts[2];
$mynl2br=$parts[3];

$position=array_search($field,$headcsv); 

if ($position!==0) $wrongfield=1;
else $wrongfield=0;
if ($position>0) $wrongfield='';


if ($field[0]!='?' && $wrongfield==0) $s[$i]= linkify(mynl2br(str_replace('&amp;#','&#',htmlXentities(str_replace(',.',';',$datacsv[$position])))));
else $s[$i]= linkify(mynl2br(str_replace('&amp;#','&#',htmlXentities(str_replace(',.',';',$row_a[str_replace('sent','humantime',substr(strtolower($field),1))])))));

if(trim($cond)!='' && trim($s[$i]==trim($cond))) $s[$i]=$value;
else if(trim($cond)!='' && trim($s[$i]!=trim($cond))) $s[$i]='';

}
}
$s=implode('',$s);
$s=str_replace('(¨','[',$s);
$s=str_replace('¨)',']',$s);
$s=str_replace('¤','&nbsp;',$s);
return nl2br($s);
}



function subone(&$item1) {
$item1=substr($item1,1);
}


function linkify($input){
    $re = <<<'REGEX'
!
    (
      <\w++
      (?:
        \s++
      | [^"'<>]++
      | "[^"]*+"
      | '[^']*+'
      )*+
      >
    )
    |
    (\b https?://[^\s"'<>]++ )
    |
    (\b www\d*+\.\w++[^\s"'<>]++ )
!xi
REGEX;

    return preg_replace_callback($re, function($m){
        if($m[1]) return $m[1];
        $url = htmlspecialchars($m[2] ? $m[2] : "http://$m[3]");
        $text = htmlspecialchars("$m[2]$m[3]");
        return "<a target='_blank' href='$url'>".shorten($text)."</a>";
    },
    $input);
}

function shorten($text) {
    if (strlen($text) > 40) return substr($text,0,35).'&hellip;';
    else return $text;
}

function mynl2br($str) {
global $mynl2br;
if(trim($mynl2br)=='') $mynl2br=' | ';
return str_replace("\r","",str_replace("\n","",str_replace('<br />',htmlX_entity_decode($mynl2br),nl2br($str))));
}

function xm($emails) {
$emails=u5toidn($emails);	
$emails=str_replace("·",",",$emails);
$emails=str_replace(" ",",",$emails);
$emails=str_replace(";",",",$emails);
$emails=str_replace(":",",",$emails);
$emails=str_replace("!",",",$emails);
$emails=str_replace("?",",",$emails);
$emails=str_replace('"',",",$emails);
$emails=str_replace("'",",",$emails);
$emails=str_replace("%",",",$emails);
$emails=str_replace("/",",",$emails);
$emails=str_replace("\\",",",$emails);
$emails=str_replace(">",",",$emails);
$emails=str_replace("<",",",$emails);
$emails=str_replace("|",",",$emails);
$emails=str_replace("¦",",",$emails);
$emails=str_replace("#",",",$emails);
$emails=str_replace("(",",",$emails);
$emails=str_replace("[",",",$emails);
$emails=str_replace("{",",",$emails);
$emails=str_replace(")",",",$emails);
$emails=str_replace("]",",",$emails);
$emails=str_replace("}",",",$emails);
$emails=str_replace("=",",",$emails);

$emails=str_replace("\n",",",$emails);
$emails=str_replace("\r",",",$emails);
$emails=str_replace("\t",",",$emails);

$emails=str_replace(",,",",",$emails);
$emails=str_replace(",,",",",$emails);
$emails=str_replace(",,",",",$emails);

$emailsa=explode(',',$emails);
$emails='';

for ($i=0;$i<count($emailsa);$i++) {
if($i<count($emailsa)-1) $comma=',';
else $comma='';
if (strpos($emailsa[$i],'@')>0 && strpos($emailsa[$i],'.')>0 && str_replace($emailsa[$i].',','',$emails)==$emails) $emails.=$emailsa[$i].$comma;
}
return($emails);

}
?>