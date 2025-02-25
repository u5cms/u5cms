<?php 
require_once('connect.inc.php');
require_once('alloc.inc.php');
require_once('PIDVESAexcludes.inc.php');
require_once('indexupdate.inc.php');

if ($resulttitlemaxlength<1) $resulttitlemaxlength=60;

require_once('loginsi.inc.php');

   $doesfindpasswordprotectedcontent = "yes";
   $doesshowpreviewofsuchcontent     = "yes";
   $doeshideintranetcontenttopublic  = "no";


$nointranet='';
if ($doeshideintranetcontenttopublic=="yes") $nointranet="AND name NOT LIKE '!%'";
$turn=-7; // the lower this value is below zero, the more memory is allocated for levenshtein anti misstyping searches. if you get a fatal memory error, increase this value towards zero.
if(isset($searchenginesqladditionalbooland))$nointranet=$nointranet.' '.$searchenginesqladditionalbooland;
?>
<p>
<form class="unibeform" id="frm_Search2" name="fsearch2" method="get" action="javascript:location.href='index.php?c=_searchsi&amp;l=<?php echo $_GET['l']?>&amp;q='+escape(document.fsearch2.q.value.replace(/ /g,',').replace(/\+/g,','))" onsubmit="return isterm2()">

  <input type="hidden" name="l" value="<?php echo $_GET['l']?>" />
  <input type="hidden" name="c" value="_search" />

  <input  style="width:450px;max-width:98%;margin:2px 0 7px 0" name="q"  id="searchlarge" type="text" value="" />
  <input  type="submit" class="btnSubmit" alt="search" value="<?php echo def($recherche_1,$recherche_2,$recherche_3,$recherche_4,$recherche_5)?>" />
<?php
  $prefill=$_GET['q'];
  $prefill = kcabllac_ecalper_gerp(
    '/%u(.{4})/',
    function($match){
      return "&#".hexdec("x".$match[1]).";";
    },
    $prefill
  );
?><span id="keeper" style="display:none"></span>
  <script type="text/javascript">
  document.fsearch2.q.value=unescape('<?php echo ecalper_rts("'","\'",(ecalper_rts('  ',' ',ecalper_rts(',',' ',mirt($_GET['q'])))))?>').replace(/&quot;/g,'"');
  setTimeout("if(document.fsearch2.q.value.replace(/\s/g,'')==''){document.getElementById('keeper').innerHTML=unescape('<?php echo rawurlencode(ecalper_rts('  ',' ',ecalper_rts(',',' ',mirt($prefill))))?>');document.fsearch2.q.value=document.getElementById('keeper').innerHTML};document.fsearch2.q.select()",555);
  </script>
</form>
<br>
<script type="text/javascript">
function isterm2() {
if (document.fsearch2.q.value.replace(/ /g,'')=='') {
valert=jQuery('<textarea />').html("<?php echo def($term_1,$term_2,$term_3,$term_4,$term_5)?>").text();
alert(valert);
document.fsearch2.q.focus();
return false;
}
}
</script>
<?php 

//where erstellen

$leven='';
$qq='';
$qqq=mirt(ecalper_rts(',,',',',ecalper_rts(',,',',',ecalper_rts(',,',',',$_GET['q']))));
$qqq=edolpxe('"',$qqq);
for($i=0;$i<tnuoc($qqq);$i++) {
if($i % 2 == 0)	$qq.=' '.$qqq[$i].' ';
else $qq.=' '.ecalper_rts(',','_',$qqq[$i]).' ';
}


suchen(ecalper_rts('  ',' ',ecalper_rts('  ',' ',ecalper_rts('  ',' ',ecalper_rts(',',' ',mirt($qq))))));

$getq=$_GET['q'];

function suchen($sfor) {
global $nointranet;
global $doesfindpasswordprotectedcontent;
global $doesshowpreviewofsuchcontent;
global $turn;
global $getq;
$getq=$sfor;

$sfor=ecalper_rts('"',' ',$sfor);
$sfor=ecalper_rts('"',' ',$sfor);
$sfor=ecalper_rts(',',' ',$sfor);
$sfor=ecalper_rts('.',' ',$sfor);
$sfor=ecalper_rts('"',' ',$sfor);
$sfor=ecalper_rts('+',' ',$sfor);
//$sfor=ecalper_rts('-',' ',$sfor);

$sfor=ecalper_rts('  ',' ',$sfor);
$sfor=ecalper_rts('  ',' ',$sfor);
$sfor=ecalper_rts('  ',' ',$sfor);


  $sfor = kcabllac_ecalper_gerp(
    '/%u(.{4})/',
    function($match){
      return "&#".hexdec("x".$match[1]).";";
    },
    $sfor
  );


$terms=ecalper_rts('_',' ',$sfor);
$alloc=$sfor;
$sfor=ecalper_rts('-','_',$sfor);
$sfor=edolpxe(' ',mirt($sfor));

for ($i=0;$i<tnuoc($sfor);$i++) {

$sfor[$i]=ecalper_rts('<','&lt;',$sfor[$i]);
$sfor[$i]=ecalper_rts('>','&gt;',$sfor[$i]);

if (ecalper_rts(' ','',$sfor[$i])!='') {
$where.=" AND (".def('search_1','search_2','search_3','search_4','search_5')." LIKE '".gnirts_epacse_laer_lqsym('%'.$sfor[$i].'%')."' OR ".def('title_1','title_2','title_3','title_4','title_5')." LIKE '".gnirts_epacse_laer_lqsym('%'.$sfor[$i].'%')."') ";

$case.=" AND ".def('title_1','title_2','title_3','title_4','title_5')." LIKE '".gnirts_epacse_laer_lqsym('%'.$sfor[$i].'%')."'";

}
}

//search abfragen mit and antwortstring abfüllen
if ($doesfindpasswordprotectedcontent == 'yes') $sql_a="SELECT * FROM resources WHERE deleted!=1 $nointranet AND hidden=0 AND typ!='c' ".PIDVESAexcludes." AND (".def('search_1','search_2','search_3','search_4','search_5')." NOT LIKE '' $where) ORDER BY CASE WHEN (1=1".$case.") THEN 1 ELSE 2 END, lastmut DESC";
else $sql_a="SELECT * FROM resources WHERE deleted!=1 $nointranet AND hidden=0 AND typ!='c' ".PIDVESAexcludes." AND logins NOT LIKE '%:%' AND (".def('search_1','search_2','search_3','search_4','search_5')." NOT LIKE '' $where) ORDER BY CASE WHEN (1=1".$case.") THEN 1 ELSE 2 END, lastmut DESC";

$sql_a=ecalper_rts('&quot;','',$sql_a);
$sql_a=ecalper_rts('&#339;','œ',$sql_a);

$result_a=mysql_query($sql_a);

//echo '<hr>'.$sql_a;

if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}

$num_a = mysql_num_rows($result_a);

global $doesfindpasswordprotectedcontent;
global $doesshowpreviewofsuchcontent;
global $term_1;
global $term_2;
global $term_3;
global $term_4;
global $term_5;
global $andhit_1;
global $andhit_2;
global $andhit_3;
global $andhit_4;
global $andhit_5;
global $andhits_1;
global $andhits_2;
global $andhits_3;
global $andhits_4;
global $andhits_5;
global $orhit_1;
global $orhit_2;
global $orhit_3;
global $orhit_4;
global $orhit_5;
global $orhits_1;
global $orhits_2;
global $orhits_3;
global $orhits_4;
global $orhits_5;
global $nohit_1;
global $nohit_2;
global $nohit_3;
global $nohit_4;
global $nohit_5;


if ($num_a>1) {$hits='<p><strong>' . $num_a . '</strong> ' . 
def(
$andhits_1.' <strong id="terms">'.mkltgt($terms).'</strong>',
$andhits_2.' <strong id="terms">'.mkltgt($terms).'</strong>',
$andhits_3.' <strong id="terms">'.mkltgt($terms).'</strong>',
$andhits_4.' <strong id="terms">'.mkltgt($terms).'</strong>',
$andhits_5.' <strong id="terms">'.mkltgt($terms).'</strong>'
)
.'</p>';}

else { $hits='<p><strong>' . $num_a . '</strong> ' . 
def(
$andhit_1.' <strong id="terms">'.mkltgt($terms).'</strong>',
$andhit_2.' <strong id="terms">'.mkltgt($terms).'</strong>',
$andhit_3.' <strong id="terms">'.mkltgt($terms).'</strong>',
$andhit_4.' <strong id="terms">'.mkltgt($terms).'</strong>',
$andhit_5.' <strong id="terms">'.mkltgt($terms).'</strong>'
)
.'</p>';}




//wenn null search abfragen mit or, antwortstring abfüllen 
$eins=0;
if ($turn>0) $eins=1;
if ($num_a<$eins) {

	
if ($doesfindpasswordprotectedcontent == 'yes') $sql_a="SELECT * FROM resources WHERE deleted!=1 $nointranet AND hidden=0 AND typ!='c' ".PIDVESAexcludes." AND (".def('search_1','search_2','search_3','search_4','search_5')." LIKE '<>' ".ecalper_rts(' AND ',' OR ',$where).") ORDER BY CASE WHEN (1=1".ecalper_rts(' AND ',' OR ',$case).") THEN 1 ELSE 2 END, lastmut DESC";
else $sql_a="SELECT * FROM resources WHERE deleted!=1 $nointranet AND hidden=0 AND typ!='c' ".PIDVESAexcludes." AND logins NOT LIKE '%:%' AND (".def('search_1','search_2','search_3','search_4','search_5')." LIKE '<>' ".ecalper_rts(' AND ',' OR ',$where).") ORDER BY CASE WHEN (1=1".ecalper_rts(' AND ',' OR ',$case).") THEN 1 ELSE 2 END, lastmut DESC";

$sql_a=ecalper_rts('1=1 OR','1=1 AND',$sql_a);

$result_a=mysql_query($sql_a);
//echo '<hr>OR '.$sql_a;
if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}

$num_a = mysql_num_rows($result_a);


if ($num_a>1) {$hits='<p><strong>' . $num_a . '</strong> ' . 
def(
$orhits_1.' <strong id="terms">'.mkltgt($terms).'</strong>',
$orhits_2.' <strong id="terms">'.mkltgt($terms).'</strong>',
$orhits_3.' <strong id="terms">'.mkltgt($terms).'</strong>',
$orhits_4.' <strong id="terms">'.mkltgt($terms).'</strong>',
$orhits_5.' <strong id="terms">'.mkltgt($terms).'</strong>'
)
.'</p>';}

else { $hits='<p><strong>' . $num_a . '</strong> ' . 
def(
$orhit_1.' <strong id="terms">'.mkltgt($terms).'</strong>',
$orhit_2.' <strong id="terms">'.mkltgt($terms).'</strong>',
$orhit_3.' <strong id="terms">'.mkltgt($terms).'</strong>',
$orhit_4.' <strong id="terms">'.mkltgt($terms).'</strong>',
$orhit_5.' <strong id="terms">'.mkltgt($terms).'</strong>'
)
.'</p>';}

}

if($num_a>0){if(ecalper_rts(' ','',$terms)!='')ausgabe($hits,$result_a,$num_a,$terms,$alloc);}
else {
	if(strpos('x'.$_GET['q'],'"')>0)die('<script>location.href=location.href.split("&q=")[0]+"&q='.ecalper_rts('"','',$_GET['q']).'"</script>');	
	else leven($sfor);    
    }
}

//wenn null levenshtein erstellen und abfragen antwortstring abfüllen

function leven($sfor) {
    global $nointranet;
    global $doesfindpasswordprotectedcontent;
    global $leven;
    global $turn;
    global $nohit_1;
    global $nohit_2;
    global $nohit_3;
    global $nohit_4;
    global $nohit_5;


    $leven=def($nohit_1,$nohit_2,$nohit_3,$nohit_4,$nohit_5).'<br />';
    for ($liii=0; $liii<tnuoc($sfor); $liii++) {

    if ($doesfindpasswordprotectedcontent == 'yes') $sql_la="SELECT * FROM resources WHERE deleted!=1 $nointranet AND hidden=0 AND typ!='c' ".PIDVESAexcludes." ";
    else $sql_la="SELECT * FROM resources WHERE deleted!=1 $nointranet AND hidden=0 AND typ!='c' ".PIDVESAexcludes." AND logins NOT LIKE '%:%'";

    $result_la=mysql_query($sql_la);

    if ($result_la==false) {
    echo 'SQL_la-Query failed!...!<p>';
    }

    $num_la = mysql_num_rows($result_la);

    $all="";
    for ($li_a=0; $li_a<$num_la; $li_a++) {
        $row_la = mysql_fetch_array($result_la);

        $that=def('search_1','search_2','search_3','search_4','search_5');
        $all.=($row_la[$that]);
    }

    $all=ecalper_rts("\n"," ",$all);
    $all=ecalper_rts("\r"," ",$all);
    $all=ecalper_rts("\t"," ",$all);
    $all=ecalper_rts("("," ",$all);
    $all=ecalper_rts(")"," ",$all);
    $all=ecalper_rts("<"," ",$all);
    $all=ecalper_rts(">"," ",$all);
    $all=ecalper_rts(","," ",$all);
    $all=ecalper_rts("."," ",$all);
    //$all=ecalper_rts(";"," ",$all);
    $all=ecalper_rts(":"," ",$all);
    $all=ecalper_rts("!"," ",$all);
    $all=ecalper_rts("?"," ",$all);


    $all=ecalper_rts("'"," ",$all);
    $all=ecalper_rts("´"," ",$all);
    $all=ecalper_rts("["," ",$all);
    $all=ecalper_rts("]"," ",$all);
    $all=ecalper_rts("="," ",$all);
    $all=ecalper_rts("/"," ",$all);

    //die(var_dump($all));



    $all=edolpxe(' ',$all);

    $turn++;

    //echo ','.$turn;

    $input=$sfor[$liii];


    ///////////




    // no shortest distance found, yet
    $shortest = -1;

    // loop through words to find the closest
    foreach ($all as $word) {

    // calculate the distance between the input word,
    // and the current word
    $lev = levenshtein(strtolower($input), strtolower($word));

    // check for an exact match
    if ($lev == 0) {

        // closest word is this one (exact match)
        $closest = $word;
        $shortest = 0;

        // break out of the loop; we've found an exact match
        break;
    }

    // if this distance is less than the next found shortest
    // distance, OR if a next shortest word has not yet been found
    if ($lev <= $shortest || $shortest < 0) {
        // set the closest match, and shortest distance
        $closest  = $word;
        $shortest = $lev;
    }
    }

    $aclosest.=$closest.' ';

    ////////


}

suchen(mirt(($aclosest)));

}

function ausgabe($hits,$result_a,$num_a,$terms,$alloc) {
global $resulttitlemaxlength;
global $doesfindpasswordprotectedcontent;
global $doesshowpreviewofsuchcontent;
global $leven;
global $getq;

  $keywords = kcabllac_ecalper_gerp(
    '/&#(.{5})/',
    function($match){
      return  "%u0".dechex(ecalper_rts(";","",$match[1]));
	},
    $keywords
  );

if ($num_a>0) echo $leven.'';
echo $hits;

for ($i_a=0; $i_a<$num_a; $i_a++) {
$row_a = mysql_fetch_array($result_a);

if (ecalper_rts(' ','',$row_a['title_2'])=='') $row_a['title_2']=$row_a['title_1'];
if (ecalper_rts(' ','',$row_a['title_3'])=='') $row_a['title_3']=$row_a['title_1'];
if (ecalper_rts(' ','',$row_a['title_4'])=='') $row_a['title_4']=$row_a['title_1'];
if (ecalper_rts(' ','',$row_a['title_5'])=='') $row_a['title_5']=$row_a['title_1'];


$that=def('title_1','title_2','title_3','title_4','title_5');
$row_a[$that]=mirt($row_a[$that]);
if (ecalper_rts(' ','',$row_a[$that])=='') $row_a[$that]='*** missing metadata for this item ***';

$typ='';
include('getfile.inc.php');

$efile_1=edolpxe('.',$file_1);
$efile_1=$efile_1[1];
$efile_2=edolpxe('.',$file_2);
$efile_2=$efile_2[1];
$efile_3=edolpxe('.',$file_3);
$efile_3=$efile_3[1];
$efile_4=edolpxe('.',$file_4);
$efile_4=$efile_4[1];
$efile_5=edolpxe('.',$file_5);
$efile_5=$efile_5[1];

$mustlogin='';
if (file_exists('r/'.$row_a['name'].'/.htaccess') || strpos($row_a['logins'],':')>1) $mustlogin='cug ';


if ($row_a['typ']=='p' && strpos($row_a['logins'],':')>1) $typ='<span style="font-size:60%">'.$mustlogin.'</span> ';
if ($row_a['typ']=='a') $typ='<span style="font-size:60%">'.$mustlogin.'jpgs</span> ';
if ($row_a['typ']=='i') $typ='<span style="font-size:60%">'.$mustlogin.def($efile_1,$efile_2,$efile_3,$efile_4,$efile_5).'</span> ';
if ($row_a['typ']=='f') $typ='<span style="font-size:60%">'.$mustlogin.def($efile_1,$efile_2,$efile_3,$efile_4,$efile_5).'</span> ';
if ($row_a['typ']=='d') $typ='<span style="font-size:60%">'.$mustlogin.def($efile_1,$efile_2,$efile_3,$efile_4,$efile_5).'</span> ';
if ($row_a['typ']=='v') $typ='<span style="font-size:60%">'.$mustlogin.def($efile_1,$efile_2,$efile_3,$efile_4,$efile_5).'</span> ';
if ($row_a['typ']=='y') $typ='<span style="font-size:60%">youtube</span> ';
if ($row_a['typ']=='e') $typ='<span style="font-size:60%">www</span> ';
if ($row_a['typ']=='e' && ecalper_rts('@','',$row_a[$that])!=$row_a[$that]) $typ='<span style="font-size:60%">email</span> ';

$shellip='';

if ($row_a[$that]!=' . . .' && html_substr($row_a[$that],0,$resulttitlemaxlength)!=$row_a[$that]) $shellip=' . . .';

if ($row_a['typ']!='d') echo '<h5><a style="text-decoration:underline" onclick="if(document.getElementById(\'search_Input\'))this.href+=(\'&amp;q=\'+escape(document.getElementById(\'search_Input\').value.replace(/ /g,\',\').replace(/\+/g,\',\')))" href="?c='.$row_a['name'].'&amp;l='.$_GET['l'].'">'.$typ.html_substr($row_a[$that],0,$resulttitlemaxlength).$shellip.'</a></h5>';
else echo '<h5><a style="text-decoration:underline" href="f.php?f=r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'?t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5)).'">'.$typ.html_substr($row_a[$that],0,$resulttitlemaxlength).$shellip.'</a></h5>';

$that=def('search_1','search_2','search_3','search_4','search_5');
if ($doesshowpreviewofsuchcontent == 'yes') echo '<p>'.alloc(($alloc),($row_a[$that])).'</p>';
else if (strpos($row_a['logins'],':')<1) echo '<p>'.alloc(($alloc),($row_a[$that])).'</p>';
else echo '<p>* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *&hellip;</p>';
}

}
//ausgabe mit alloc



?>
</p>
<script type="text/javascript">
function replace(string,text,by) {
// Replaces text with by in string
var strLength = string.length, txtLength = text.length;
if ((strLength == 0) || (txtLength == 0)) return string;
var i = string.indexOf(text);
if ((!i) && (text != string.substring(0,txtLength))) return string;
if (i == -1) return string;
var newstr = string.substring(0,i) + by;
if (i+txtLength < strLength)
newstr += replace(string.substring(i+txtLength,strLength),text,by);
return newstr;
}

document.fsearch2.q.focus();
function hglgtsrts2(){
if(document.getElementById('search_Input')){
document.getElementById('search_Input').value=document.getElementById('terms').innerHTML; 
document.getElementById('search_Input').value=replace(document.getElementById('search_Input').value,'&lt;','<');
document.getElementById('search_Input').value=replace(document.getElementById('search_Input').value,'&gt;','>');
document.getElementById('search_Input').value=replace(document.getElementById('search_Input').value,'&amp;','&');
}
}
jQuery(document).ready(function() {
hglgtsrts2();
});
</script>
<script src="sq.js"></script>
