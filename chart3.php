<?php require('connect.inc.php');?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
</head>

<body style="font-family:Arial, Helvetica, sans-serif;font-size:12px">
<script>
String.fromCharCodePoint= function(/* codepoints */) {
    var codeunits= [];
    for (var i= 0; i<arguments.length; i++) {
        var c= arguments[i];
        if (arguments[i]<0x10000) {
            codeunits.push(arguments[i]);
        } else if (arguments[i]<0x110000) {
            c-= 0x10000;
            codeunits.push((c>>10 & 0x3FF) + 0xD800);
            codeunits.push((c&0x3FF) + 0xDC00);
        }
    }
    return String.fromCharCode.apply(String, codeunits);
};

function decodeCharacterReferences(s) {
    return s.replace(/&#(\d+);/g, function(_, n) {;
        return String.fromCharCodePoint(parseInt(n, 10));
    }).replace(/&#x([0-9a-f]+);/gi, function(_, n) {
        return String.fromCharCodePoint(parseInt(n, 16));
    });
};
</script>
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

$head=explode(';',$row_a['headcsv']);

for ($i=0;$i<count($head);$i++) {

if (trim(substr($head[$i],1,strlen($head[$i])-1))!='') echo '<script>if (parent.document.u5form.'.substr($head[$i],1,strlen($head[$i])-1).') parent.document.u5form.'.substr($head[$i],1,strlen($head[$i])-1).'.value=""</script>';

}


mysql_data_seek ($result_a , 0 );




for ($i_a=0; $i_a<$num_a; $i_a++) {
$row_a = mysql_fetch_array($result_a);

$head=explode(';',$row_a['headcsv']);
$data=explode(';',$row_a['datacsv']);


for ($i=0;$i<count($head);$i++) {

$data[$i]=str_replace("\r",'\r',$data[$i]);
$data[$i]=str_replace("\n",'\n',$data[$i]);
$data[$i]=str_replace("\t",'\t',$data[$i]);

if (strpos($cmdstring,'ANONYMIZE_'.trim(substr($head[$i],1,strlen($head[$i])-1)).'_VALUES')<1) { if (trim(substr($head[$i],1,strlen($head[$i])-1))!='') echo '<script>if (parent) if (parent.document.u5form) if (parent.document.u5form.'.substr($head[$i],1,strlen($head[$i])-1).') parent.document.u5form.'.substr($head[$i],1,strlen($head[$i])-1).'.value+=decodeCharacterReferences("'.(str_replace(',.',';',str_replace('"','\"',str_replace('','', $data[$i] )))).' ")</script>'; }

else {if (trim(substr($head[$i],1,strlen($head[$i])-1))!='') echo '<script>if (parent) if (parent.document.u5form) if (parent.document.u5form.'.substr($head[$i],1,strlen($head[$i])-1).') parent.document.u5form.'.substr($head[$i],1,strlen($head[$i])-1).'.value+=decodeCharacterReferences("'.(str_replace(',.',';',str_replace('"','\"',str_replace('','','anonymized')))).' ")</script>'; }

}
      
}
?>
</body>
</html>