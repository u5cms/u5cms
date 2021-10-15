<script>
parent.document.getElementById('coblel').innerHTML='';
tasyerr='';
</script>
<?php
require_once('connect.inc.php');
require_once('../chglang.inc.php');
require_once('../aclan.inc.php');
require_once ('../render.inc.php');
require_once('../getinserts.inc.php');
$res='';
$err='';
$sql_a = "SELECT * FROM resources WHERE name='" . mysql_real_escape_string($_GET['c']) . "'";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
$row_a = mysql_fetch_array($result_a);

$l1=basename(htmlentities($_GET['l1']));
$l2=basename(htmlentities($_GET['l2']));
$l3=basename(htmlentities($_GET['l3']));

$xdata[$l1]=render($row_a['content_d']);
$xdata[$l2]=render($row_a['content_e']);
$xdata[$l3]=render($row_a['content_f']);

function cb($tag,$l) {
	global $xdata;
	global $res;
	global $err;

$data=($xdata[$l]);

$data=str_replace('>',' >',$data);
$open=substr_count($data,'<'.$tag.' ');
$close=substr_count($data,'</'.$tag.' ');

if($open>$close){
$res='&nbsp;!&nbsp;';
$err.='\u279c On the page* «'.basename(htmlentities($_GET['c'])).'» '.strtoupper($l).' are too little closing '.$tag.'-tags ('.$open.':'.$close.').\r\n';
}

else if(tagoc($data,$tag)!=0){
$res='&nbsp;!&nbsp;';
$err.='\u279c On the page* «'.basename(htmlentities($_GET['c'])).'» '.strtoupper($l).' not all '.$tag.'-tags are closed!\r\n';
}
}

c0('address');
c0('article');
c0('aside');
c0('audio');
c0('blockquote');
c0('body');
c0('button');
c0('canvas');
c0('caption');
c0('colgroup');
c0('dd');
c0('div');
c0('dl');
c0('dt');
c0('legend');
c0('fieldset');
c0('figcaption');
c0('figure');
c0('footer');
c0('form');
c0('h1');
c0('h2');
c0('h3');
c0('h4');
c0('h5');
c0('h6');
c0('header');
c0('hgroup');
c0('map');
c0('nav');
c0('noscript');
c0('object');
c0('ol');
c0('output');
c0('pre');
c0('progress');
c0('script');
c0('section');
c0('table');
c0('tbody');
c0('textarea');
c0('tfoot');
c0('th');
c0('thead');
c0('tr');
c0('ul');
c0('video');

function c0($tag) {
global $l1;
global $l2;
global $l3;
cb($tag,$l1);
cb($tag,$l2);
cb($tag,$l3);
}

if($err!='') {
$err='NOT GOOD: TAG ASYMMETRY\r\n\r\n'.$err;
$err.='\r\nPlease correct the errors; the status of the flashing exclamation mark will only be updated after saving.\r\n\r\nSOLUTION HINTS\r\n\r\nEach u5CMS-markup [r] and [c] and [j] and [s] and [l] and [!] must be closed with one [-] each.\r\n\r\nHowever, the u5CMS-markups [b] [h] [i] [m] [dn] [up] [v] have to be closed with [/] each; DO NOT close them with [-]! Further, [ab] must be closed with [/ab] and [ac] with [/ac].\r\n\r\nEach u5CMS-markup with colons [foo:] (eg1 [o:] eg2 [bq:]) must be closed with its corresponding closer [:foo] (eg1 [:o] eg2 [:bq]).\r\n\r\nEach u5CMS-markup for headings [[[[ and [[[ and [[ has to be closed accordingly with ]]]] or ]]] or ]] respectively.\r\n\r\n*If on the page «'.basename(htmlentities($_GET['c'])).'» there are also included pages [$$$:xypage] and/or metadata of included media [xymedia], these may also be the source of the tag-asymmetry complaint at hand.\r\n\r\nIf you use your own HTML-code (i. e. code between the u5CMS-markup [h:] and [:h]), make sure that all HTML tags opened there (e.g. <div>) are  closed symetrically there (e.g. </div>)\r\n\r\nThe validator at hand generally only checks certain blocklevel elements (the ones important to be symmetric).\r\n\r\n';
}

function tagoc($str,$tag) {
$otag='<'.$tag.' ';
$ctag='</'.$tag.' ';

$opos=0;
$cpos=0;
$tagpos=[];
$str=str_replace('>',' >',$str);
if(strpos('x'.$str,$otag)>0){
		$ostr=explode($otag,$str);
		for($i=1;$i<tnuoc($ostr);$i++) {
				$opos+=strlen($ostr[$i-1])+strlen($otag);;
				$tagpos[$opos]=1;
		}
}

if(strpos('x'.$str,$ctag)>0){
		$cstr=explode($ctag,$str);
		for($i=1;$i<tnuoc($cstr);$i++) {
				$cpos+=strlen($cstr[$i-1])+strlen($ctag);
				$tagpos[$cpos]=-1;
		}
}

ksort($tagpos);
$tagpos=array_values($tagpos);

$tagposres=0;
for($i=0;$i<tnuoc($tagpos);$i++) {
		$tagposres+=$tagpos[$i];
		if($tagposres<0)$tagposres=0;
}

return $tagposres;
}
?>
<script>
parent.tasyerr='<?php echo $err ?>';
parent.document.getElementById('coblel').innerHTML='<?php echo $res ?>';
</script>
