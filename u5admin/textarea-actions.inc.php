<?php
/* Included only by the authenticated page editor, after its existing bootstrap. */
$u5TextareaFormats=array();$u5TextareaReserved=array('[h:]','[:h]','[/]');
$u5TextareaLabels=array('b'=>'Bold','i'=>'Italic','u'=>'Underline','s'=>'Strike','sub'=>'Subscript','sup'=>'Superscript','h1'=>'Heading 1','h2'=>'Heading 2','h3'=>'Heading 3');
$u5TextareaResult=mysql_query('SELECT source1,source2 FROM inserts');
if ($u5TextareaResult!==false) while ($u5TextareaInsert=mysql_fetch_assoc($u5TextareaResult)) {
    $open=(string)($u5TextareaInsert['source1'] ?? '');$close=(string)($u5TextareaInsert['source2'] ?? '');
    if ($open!=='') $u5TextareaReserved[]=$open;
    if ($close!=='') $u5TextareaReserved[]=$close;
    foreach ($u5TextareaLabels as $tag=>$label) if ($open==='['.$tag.']' && $close!=='')
        $u5TextareaFormats[]=array('label'=>$label,'open'=>$open,'close'=>$close);
}
$u5TextareaConfig=array('formats'=>$u5TextareaFormats,'reserved'=>array_values(array_unique($u5TextareaReserved)));
?>
<link rel="stylesheet" href="textarea-actions.css?v=<?php echo substr(hash_file('sha256',__DIR__.'/textarea-actions.css'),0,12) ?>" />
<script>window.u5TextareaConfig=<?php echo json_encode($u5TextareaConfig,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_INVALID_UTF8_SUBSTITUTE) ?>;</script>
<script defer src="textarea-actions.js?v=<?php echo substr(hash_file('sha256',__DIR__.'/textarea-actions.js'),0,12) ?>"></script>
