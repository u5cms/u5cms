<?php
/* Read-only backend information. Existing write endpoints retain their policies. */
require_once __DIR__.'/connect.inc.php';
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
function u5TextareaReply($data,$status=200) {
    http_response_code($status);
    echo json_encode($data,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_INVALID_UTF8_SUBSTITUTE);exit;
}
function u5TextareaName($value) {
    return is_string($value) && strlen($value)>0 && strlen($value)<=255 &&
        (preg_match('/^[A-Za-z0-9_!]+$/D',$value) || u5CodeAssetIsTyped($value));
}
function u5TextareaEditUrl($name,$type,$language='') {
    $n=rawurlencode($name);$t=rawurlencode($type);
    if ($type==='p') return '../edit.php?n='.$n.($language!=='' ? '&l='.rawurlencode($language) : '');
    if ($type==='c') return 'coding.php?name='.$n;
    $forms=array('d'=>'metad.php','e'=>'metae.php','v'=>'metav.php','y'=>'metay.php');
    return ($forms[$type] ?? 'meta.php').'?name='.$n.'&typ='.$t;
}
$mode=$_GET['mode'] ?? '';
if (!is_string($mode) || !in_array($mode,array('','search','usages'),true)) u5TextareaReply(array('error'=>'Invalid action.'),400);
if ($mode==='search') {
    $term=$_GET['term'] ?? '';
    if (!is_string($term) || strlen($term)>64 || !preg_match('/^[A-Za-z0-9_!-]*$/D',$term)) u5TextareaReply(array('error'=>'Invalid object search.'),400);
    $term=str_replace(array('=','_','%'),array('==','=_','=%'),$term);
    $result=mysql_query("SELECT name,typ FROM resources WHERE deleted!=1 AND name LIKE '%".mysql_real_escape_string($term)."%' ESCAPE '=' ORDER BY name LIMIT 30");
    if ($result===false) u5TextareaReply(array('error'=>'Object search unavailable.'),503);
    $rows=array();while ($row=mysql_fetch_assoc($result)) if (u5TextareaName($row['name'])) $rows[]=array('name'=>$row['name'],'type'=>$row['typ']);
    u5TextareaReply(array('results'=>$rows));
}
$name=$_GET['object'] ?? '';
if (!u5TextareaName($name)) u5TextareaReply(array('error'=>'Invalid object name.'),400);
$language=$_GET['language'] ?? '';
if (!is_string($language) || !in_array($language,array($lan1na,$lan2na,$lan3na,$lan4na,$lan5na),true)) $language=$lan1na;
if ($mode==='usages') {
    $like=str_replace(array('=','_','%'),array('==','=_','=%'),$name);
    $where=array();foreach (array('content','title','desc','key') as $field) foreach (range(1,5) as $l)
        $where[]='`'.$field.'_'.$l."` LIKE '%".mysql_real_escape_string($like)."%' ESCAPE '='";
    $result=mysql_query('SELECT * FROM resources WHERE deleted!=1 AND ('.implode(' OR ',$where).') ORDER BY name LIMIT 201');
    if ($result===false) u5TextareaReply(array('error'=>'Usage lookup unavailable.'),503);
    $rows=array();$candidates=0;
    while ($row=mysql_fetch_assoc($result)) {
        if (++$candidates>200) break;
        $used=false;
        foreach (array('content','title','desc','key') as $field) foreach (range(1,5) as $l) {
            $source=(string)($row[$field.'_'.$l] ?? '');
            if (u5CodeAssetMarkupNames($source,array(strtolower($name)=>$name.'__u5_usage_probe'),$row['typ']!=='c')!==$source) $used=true;
        }
        if ($used) $rows[]=array('name'=>$row['name'],'url'=>u5TextareaEditUrl($row['name'],$row['typ'],$language));
    }
    u5TextareaReply(array('results'=>$rows,'more'=>$candidates>200));
}
$result=mysql_query("SELECT name,typ,deleted FROM resources WHERE deleted!=1 AND name='".mysql_real_escape_string($name)."' LIMIT 1");
if ($result===false) u5TextareaReply(array('error'=>'Object lookup unavailable.'),503);
$object=mysql_fetch_assoc($result);
if (!$object) u5TextareaReply(array('found'=>false));
$name=$object['name'];$type=strtolower((string)$object['typ']);$archived=(int)$object['deleted']===2;
require __DIR__.'/accadminquery.inc.inc.php';
$mayEdit=!(($type==='c' && $cssRqHIADRI!=='no') || ($archived && $archiveRqHIADRI!=='no')) || $knownamdin==='ok';
$meta=u5TextareaEditUrl($name,$type,$language);
if ($type==='p') $meta='meta.php?name='.rawurlencode($name).'&typ=p';
if ($type==='c') $meta='meta.php?name='.rawurlencode($name).'&typ=c';
$data=array('found'=>true,'name'=>$name,'type'=>$type,'archived'=>$archived,
    'edit'=>$mayEdit ? u5TextareaEditUrl($name,$type,$language) : null,
    'metadata'=>$mayEdit ? $meta : null,'preview'=>$type==='p' ? '../?c='.rawurlencode($name).'&l='.rawurlencode($language) : null);
if ($type==='c' && u5CodeAssetIsTyped($name)) $data['original']='../r/'.rawurlencode(u5CodeAssetFilename($name));
elseif (in_array($type,array('d','i','a','f','v'),true)) {
    $directory=dirname(__DIR__).'/r/'.$name;
    $files=is_dir($directory) ? scandir($directory) : array();$candidates=array();
    foreach ($files ?: array() as $file) if ($file!=='' && $file[0]!=='.' && is_file($directory.'/'.$file) && !is_link($directory.'/'.$file) &&
        strpos($file,$name)===0 && !preg_match('/\.(php[0-9]*|phtml|phar|htaccess|htpasswd)$/iD',$file)) $candidates[]=$file;
    usort($candidates,function($a,$b) use($name,$language) {
        $prefix=$name.'_'.$language;return (int)(strpos($b,$prefix)===0)-(int)(strpos($a,$prefix)===0) ?: strcmp($a,$b);
    });
    if ($candidates) $data['original']='../f.php?f='.rawurlencode('r/'.$name.'/'.$candidates[0]);
}
u5TextareaReply($data);
