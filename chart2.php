<?php require('connect.inc.php') ?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
</head>

<body leftmargin="0" marginwidth="0" style="font-family:Arial, Helvetica, sans-serif;font-size:12px">
<script>mcalc='';</script>
<?php 
require('chartreadrights.inc.php');

$_GET['s']=$_COOKIE['dgets'];
$_GET['f']=$_COOKIE['dgetf'];

if ($_GET['s']>0) $andstatus='AND status = '.mysql_real_escape_string($_GET['s']);
else $andstatus='AND status < '.mysql_real_escape_string(5);
$toolate=30;
if ($_GET['s']==5) $andstatus.=' AND lastmut>'.(time()-$toolate*24*60*60);

  $_GET['f'] = preg_replace_callback(
    '/%u(.{4})/',
    function($match){
		return "&#".hexdec("x".$match[1]).",.";
    },
    $_GET['f']
  );



//xxxxxxxxxxxxxxxxx
if ($_GET['f']!='') {


$keywords=((str_replace('  ',' ',str_replace(' ',' ',trim($_GET['f'])))));



$keywords=str_replace('"',' ',$keywords);
$keywords=str_replace('"',' ',$keywords);
//$keywords=str_replace(',',' ',$keywords);
//$keywords=str_replace('.',' ',$keywords);
$keywords=str_replace('"',' ',$keywords);
//$keywords=str_replace('+',' ',$keywords);

$keywords=str_replace('  ',' ',$keywords);
$keywords=str_replace('  ',' ',$keywords);
$keywords=str_replace('  ',' ',$keywords);

  $keywords = preg_replace_callback(
    '/%u(.{4})/',
    function($match){
      return "&#".hexdec("x".$match[1]).";";
    },
    $keywords
  );

$keywords=explode(' ',trim($keywords));

$andfilter="AnD ( (";

$orand='oR';
if ($_COOKIE['fdbool']=='and') $orand='anD';

for ($k=0;$k<count($keywords);$k++) {
$andfilter.="datacsv='' ";
$andfilter.="OR datacsv LIKE '%".mysql_real_escape_string(str_replace(';',',.',$keywords[$k]))."%' ";
$andfilter.="OR authuser LIKE '%".mysql_real_escape_string(str_replace(';',',.',$keywords[$k]))."%' ";
$andfilter.="OR ip LIKE '%".mysql_real_escape_string(str_replace(';',',.',$keywords[$k]))."%' ";
$andfilter.="OR notes LIKE '%".mysql_real_escape_string(str_replace(';',',.',$keywords[$k]))."%' ";
$andfilter.="OR humantime LIKE '%".mysql_real_escape_string(str_replace(';',',.',$keywords[$k]))."%' ";
if ($k==count($keywords)-1) $andfilter.=')';
else $andfilter.=") $orand (";

}
$andfilter.=')';


}

//if (trim($_GET['f'])!='') $andfilter='AND datacsv LIKE \'%'.mysql_real_escape_string(str_replace(';',',.',str_replace(' ','%',$_GET['f']))).'%\'';

$timeorid='time DESC';
     if ($_COOKIE['fdorder']=='no') $timeorid='notes ASC, time DESC';
else if ($_COOKIE['fdorder']=='au') $timeorid='authuser ASC, time DESC';
else if ($_COOKIE['fdorder']=='ff') $timeorid='datacsv ASC, time DESC';

$sql_a="SELECT * FROM formdata WHERE formname='".mysql_real_escape_string($_GET['n'])."' $andstatus $andfilter ORDER BY $timeorid";

$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

if ($_GET['s']==5) echo '<br><hr><center><small>Items are automatically removed from this recycle bin list '.$toolate.' days after their deletion was selected.</small></center><hr>';

$num_a = mysql_num_rows($result_a);

$row_a = mysql_fetch_array($result_a);
$oldhead=$row_a['headcsv'];      
$head=explode(';',$row_a['headcsv']);









$x=rawurldecode(base64_decode($_GET['x']));



/*
Benötigte Variabelen
- Formularname
- Variablenname

- Alle möglichen Werte
- Alle möglichen Texte

*/

$formularname=$_GET['n'];

/*
$variablenname=explode('">',$x);
$variablenname=str_replace('name="','',$variablenname[0]);
*/

$variablenname=str_replace('id="','">',$x);
$variablenname=explode('">',$variablenname);
$variablenname=str_replace('name="','',$variablenname[0]);
$variablenname=str_replace('"','',$variablenname);
//echo '<hr>'.$variablenname.'<hr>';


$wertearr=explode('value="',$x);
   for ($i=0;$i<count($wertearr);$i++) {
   if (str_replace('</option>','',$wertearr[$i])!=$wertearr[$i]) {
   $w=explode('">',$wertearr[$i]);
   $werte.=$w[0].'_-_';
   }
 }
//echo htmlXentities($werte);


$textearr=explode('</option>',$x);
   for ($i=0;$i<count($textearr)-1;$i++) {
   $t=explode('">',$textearr[$i]);
   $texte.=$t[count($t)-1].'_-_';
}
//echo htmlXentities($texte);

echo '<table cellspacing="0" cellpadding="0" border="0"><tr><td align="left"><iframe scrolling="no" name="imean" frameborder="0" height="88" width="77"></iframe></td><td><table>';

$werte=explode('_-_',$werte);
$texte=explode('_-_',$texte);

   for ($i=0;$i<count($werte)-1;$i++) {
   
   if (trim($werte[$i])!='') echo '<tr><td align="right" width="200" bgcolor="white">'.$texte[$i].'&nbsp;</td><td>'.getData($variablenname,$werte[$i]).'</td></tr>';
   
   }


echo '</td></tr></table></table>';


function getData($variable,$wert) {
global $result_a;
global $row_a;
global $num_a;
global $head;
global $oldhead;
global $cmdstring;
if (strpos($cmdstring,'ANONYMIZE_'.trim($variable).'_VALUES')>0) return('anonymized');

/*
- select machen wie bei formdata2 (ausserhalb der fn, hier denn array zurücksetzen!)

- Im neusten Header auszählen, an welcher Stelle die Variable ist
- An der gleichen Stelle den Wert suchen. Wenn da, found++;
*/

mysql_data_seek ($result_a , 0 );

$found=0;

for ($i_a=0; $i_a<$num_a; $i_a++) {
$row_a = mysql_fetch_array($result_a);

if ($oldhead!=$row_a['headcsv']) echo '<font color=red><b>DATA INCONSISTENT DO NOT USE CHARTS</b><br></font>';

$data=explode(';',$row_a['datacsv']);

for ($ii=0;$ii<count($head);$ii++) {


if (trim($variable)==trim(substr($head[$ii],1,strlen($head[$ii])-1))) {

if (trim(substr($data[$ii],1,strlen($data[$ii])-1))===trim($wert)) $found++;

$prozent=round((100*$found/$num_a),1);

$pipes=round(200*($found/$num_a));

$pipgraph='';


}//if

}
      
}

if ($prozent>0) $prozent='('.$prozent.'%)';
else $prozent='';
$pxsrc='blackpx.gif';
if ($wert==='+1') $pxsrc='redpx.gif';
if ($wert==='+2') $pxsrc='orangepx.gif';
if ($wert==='+3') $pxsrc='bluepx.gif';
if ($wert==='+4') $pxsrc='yellowpx.gif';
if ($wert==='+5') $pxsrc='greenpx.gif';
return '<img src="images/'.$pxsrc.'" height="12" valign="middle" width="'.$pipes.'"><span style="font-size:80%"> '.$found.' '.$prozent.'</span><script>mcalc+=escape("'.$found.'!*!'.$wert.'!,!")</script>';

}

?>
<script>
imean.location.href='mean.php?m='+mcalc;
</script>
</body>
</html>