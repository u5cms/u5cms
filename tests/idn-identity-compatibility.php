<?php
/* Stored CMS identities must not change just because a dependency adds global
 * IDN polyfills. Exercise both the absent and explicitly provided API profiles. */
$checks=0;
function identityChild(array $arguments) {
    $process=proc_open(array_merge(array(PHP_BINARY),$arguments),array(1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
    if (!is_resource($process)) throw new RuntimeException('Cannot start IDN fixture.');
    $out=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);
    fclose($pipes[1]);fclose($pipes[2]);$status=proc_close($process);
    if ($status!==0 || $error!=='') throw new RuntimeException('IDN fixture failed: '.$out.$error);
    return json_decode($out,true,512,JSON_THROW_ON_ERROR);
}
$bare=array('-n');
$available=identityChild(array_merge($bare,array('-r','echo json_encode(array("idn"=>function_exists("idn_to_ascii"),"mbstring"=>extension_loaded("mbstring"),"iconv"=>extension_loaded("iconv"),"ctype"=>extension_loaded("ctype")));')));
if ($available['idn']) throw new RuntimeException('Run this missing-capability regression on a PHP profile without statically compiled intl; -n disables shared intl.');
foreach (array('iconv','ctype','mbstring') as $extension) if (!$available[$extension]) {
    $bare[]='-d';$bare[]='extension_dir='.ini_get('extension_dir');$bare[]='-d';$bare[]='extension='.$extension;
}
$root=dirname(__DIR__);
$cases=array(
    'alice@xn--bcher-kva.example'=>"alice@b\xFCcher.example",
    'alice@example.invalid'=>'alice@example.invalid',
    'alice@b&#252;cher.example'=>"alice@b\xFCcher.example",
    'one@example.invalid,two@xn--bcher-kva.example'=>"one@example.invalid,two@b\xFCcher.example"
);
foreach (array(false,true) as $provided) foreach ($cases as $input=>$converted) {
    $code='$_SERVER["REQUEST_URI"]="/";$_GET=array();$_COOKIE=array();';
    if ($provided) $code.='require '.var_export($root.'/vendor/autoload.php',true).';';
    $code.='require '.var_export($root.'/myfunctions.inc.php',true).';require '.var_export($root.'/u5admin/u5idn.inc.php',true).';';
    $code.='$input='.var_export($input,true).';ob_start();require '.var_export($root.'/u5admin/checkidn.inc.php',true).';$warning=ob_get_clean();';
    $code.='echo json_encode(array("flat"=>bin2hex(u5flatidn($input)),"idn_api"=>function_exists("idn_to_ascii") && function_exists("idn_to_utf8"),"warning"=>$warning!==""));';
    $result=identityChild(array_merge($bare,array('-r',$code)));
    foreach (array('flat'=>bin2hex($provided ? $converted : $input),'idn_api'=>true,'warning'=>!$provided) as $field=>$expected) {
        $checks++;
        if ($result[$field]!==$expected) throw new RuntimeException('IDN identity contract ('.($provided?'provided API':'legacy missing API').', '.$input.', '.$field.'): '.var_export($result,true));
    }
}
echo 'IDN identity compatibility: '.$checks.' checks passed on '.PHP_VERSION.".\n";
