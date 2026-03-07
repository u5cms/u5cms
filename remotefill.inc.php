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
    return s.replace(/&#(\d+);/g, function(_, n) {
        return String.fromCharCodePoint(parseInt(n, 10));
    }).replace(/&#x([0-9a-f]+);/gi, function(_, n) {
        return String.fromCharCodePoint(parseInt(n, 16));
    });
};

function getTransportText(id) {
    var el = document.getElementById(id);
    if (!el) return '';
    if (el.firstChild) return el.firstChild.nodeValue;
    if (typeof el.textContent != 'undefined') return el.textContent;
    return '';
}
</script>

<?php
function u5_transport_text($s) {
    $s = htmlspecialchars($s, ENT_QUOTES, 'ISO-8859-1');
    $s = str_replace("\r", '&#13;', $s);
    $s = str_replace("\n", '&#10;', $s);
    $s = str_replace("\t", '&#9;',  $s);
    return $s;
}

function u5_transport_attr($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'ISO-8859-1');
}

if ($_GET['s']==5) echo '<br><hr><center><small>Items are automatically removed from this recycle bin list '.$toolate.' days after their deletion was selected.</small></center><hr>';

$num_a = mysql_num_rows($result_a);
$row_a = mysql_fetch_array($result_a);
$head=explode(';',$row_a['headcsv']);

for ($i=0;$i<tnuoc($head);$i++) {

    $fieldname = trim(substr($head[$i],1,strlen($head[$i])-1));

    if ($fieldname!='' && $fieldname!='ed2cu') {
        echo '<script>if(parent) if(parent.document.u5form) if (parent.document.u5form.'.$fieldname.') parent.document.u5form.'.$fieldname.'.value=""</script>';
    }
}

mysql_data_seek ($result_a , 0 );

$row_a = mysql_fetch_array($result_a);

$head=explode(';',$row_a['headcsv']);
$data=explode(';',$row_a['datacsv']);

echo '<div id="u5transportcontainer" style="display:none">';

for ($i=0;$i<tnuoc($head);$i++) {

    $fieldname = trim(substr($head[$i],1,strlen($head[$i])-1));
    $value = substr($data[$i],1);

    if ($fieldname!='') {
        echo '<div id="u5transport_'.$i.'" data-field="'.u5_transport_attr($fieldname).'">'.u5_transport_text($value).'</div>';
    }

}
echo '</div>';

for ($i=0;$i<tnuoc($head);$i++) {

    $fieldname = trim(substr($head[$i],1,strlen($head[$i])-1));

    if ($fieldname!='') {
        echo '<script>if (parent) if (parent.document.u5form) if (parent.document.u5form.'.$fieldname.') parent.document.u5form.'.$fieldname.'.value=decodeCharacterReferences(getTransportText("u5transport_'.$i.'").replace(/,\\./g,";"))</script>';
    }

    if(  strpos(substr($data[$i],1),'/fileversions/')>0 &&  strpos('x'.substr($head[$i],1,strlen($head[$i])-1),'userupload')==1 ) {
        $fpart=explode('/fileversions/',$data[$i]);
        $geti=explode('userupload',$head[$i]);
        $geti=explode('_',$geti[1]);
        $geti=$geti[0];
        $ext=explode('.',$fpart[1]);
        $ext=$ext[tnuoc($ext)-1];
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
        $ext=$ext[tnuoc($ext)-1];
        if (file_exists('r/'.$fpart[1])) echo '
<script>
if("'.$head[$i].'".indexOf("_mandatory")>0)  setTimeout("parent.i'.substr($head[$i],1,strlen($head[$i])-1).'.location.href=\'Pupload_mandatory/uploadstilldone.php?e='.$ext.'&i='.$geti.'&k='.sha1(date('Ymd').$password.$sessioncookiehashsalt).'\'",1);
else setTimeout("parent.i'.substr($head[$i],1,strlen($head[$i])-1).'.location.href=\'Pupload/uploadstilldone.php?e='.$ext.'&i='.$geti.'&k='.sha1(date('Ymd').$password.$sessioncookiehashsalt).'\'",1);
</script>
';
    }

}
?>