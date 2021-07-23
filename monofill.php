<?php require_once('connect.inc.php'); ?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
</head>
<body>
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
require_once('login.inc.php');

if($_GET['id']>0) $isid="AND datacsv LIKE '·". mysql_real_escape_string($_GET['id']).";%'";
else $isid='';

$sql_a="SELECT * FROM formdata WHERE $mfwhereclause AND formname='".mysql_real_escape_string($_GET['n'])."' AND authuser!='' AND authuser='".mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."' $isid ORDER BY id DESC";
$result_a=mysql_query($sql_a);


if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

if ($_GET['s']==5) echo '<br><hr><center><small>Items are automatically removed from this recycle bin list '.$toolate.' days after their deletion was selected.</small></center><hr>';

$num_a = mysql_num_rows($result_a);
$row_a = mysql_fetch_array($result_a);
$head=explode(';',$row_a['headcsv']);

for ($i=0;$i<count($head);$i++) {

if (trim(substr($head[$i],1,strlen($head[$i])-1))!='' && trim(substr($head[$i],1,strlen($head[$i])-1))!='ed2cu') echo '<script>if(parent) if(parent.document.u5form) if (parent.document.u5form.'.substr($head[$i],1,strlen($head[$i])-1).') parent.document.u5form.'.substr($head[$i],1,strlen($head[$i])-1).'.value=""</script>';

}

mysql_data_seek ($result_a , 0 );

$row_a = mysql_fetch_array($result_a);

$head=explode(';',$row_a['headcsv']);
$data=explode(';',$row_a['datacsv']);


for ($i=0;$i<count($head);$i++) {

$data[$i]=str_replace("\r",'\r',$data[$i]);
$data[$i]=str_replace("\n",'\n',$data[$i]);
$data[$i]=str_replace("\t",'\t',$data[$i]);

if (trim(substr($head[$i],1,strlen($head[$i])-1))!='') echo '<script>if (parent) if (parent.document.u5form) if (parent.document.u5form.'.substr($head[$i],1,strlen($head[$i])-1).') parent.document.u5form.'.substr($head[$i],1,strlen($head[$i])-1).'.value=decodeCharacterReferences("'.(str_replace(',.',';',str_replace('"','\"',substr($data[$i],1)))).'")</script>';

if(  strpos(substr($data[$i],1),'/fileversions/')>0 &&  strpos('x'.substr($head[$i],1,strlen($head[$i])-1),'userupload')==1 ) {
$fpart=explode('/fileversions/',$data[$i]);
$geti=explode('userupload',$head[$i]);
$geti=explode('_',$geti[1]);
$geti=$geti[0];
$ext=explode('.',$fpart[1]);
$ext=$ext[count($ext)-1];
if (file_exists('fileversions/'.$fpart[1])) echo '
<script>
if("'.$head[$i].'".indexOf("_mandatory")>0)  setTimeout("parent.i'.substr($head[$i],1,strlen($head[$i])-1).'.location.href=\'upload_mandatory/uploadstilldone.php?e='.$ext.'&i='.$geti.'&k='.sha1(date('Ymd').$password.$sessioncookiehashsalt).'\'",1);
else setTimeout("parent.i'.substr($head[$i],1,strlen($head[$i])-1).'.location.href=\'upload/uploadstilldone.php?e='.$ext.'&i='.$geti.'&k='.sha1(date('Ymd').$password.$sessioncookiehashsalt).'\'",1);
</script>
';
}

if(  strpos(substr($data[$i],1),'/r/')>0 &&  strpos('x'.substr($head[$i],1,strlen($head[$i])-1),'userupload')==1 ) {
$fpart=explode('/r/',$data[$i]);
$geti=explode('userupload',$head[$i]);
$geti=explode('_',$geti[1]);
$geti=$geti[0];
$ext=explode('.',$fpart[1]);
$ext=$ext[count($ext)-1];
if (file_exists('r/'.$fpart[1])) echo '
<script>
if("'.$head[$i].'".indexOf("_mandatory")>0)  setTimeout("parent.i'.substr($head[$i],1,strlen($head[$i])-1).'.location.href=\'Pupload_mandatory/uploadstilldone.php?e='.$ext.'&i='.$geti.'&k='.sha1(date('Ymd').$password.$sessioncookiehashsalt).'\'",1);
else setTimeout("parent.i'.substr($head[$i],1,strlen($head[$i])-1).'.location.href=\'Pupload/uploadstilldone.php?e='.$ext.'&i='.$geti.'&k='.sha1(date('Ymd').$password.$sessioncookiehashsalt).'\'",1);
</script>
';
}
}
?>
<script>
parent.document.u5form.action=parent.document.u5form.action+'&o=<?php echo rawurlencode(pwdhsh($_SERVER['PHP_AUTH_USER'])) ?>';
if(parent)if(parent.starterscript)parent.starterscript();
</script>
</body>
</html>