<?php
/* Application-owned collections passed through main's bootstrap. Keep that
 * extension contract while rejecting malformed shared CMS route/auth fields. */
$checks=0;
function requestCompatibility(array $query,array $cookies,$expectedStatus=200) {
    global $checks;
    $code='$_GET='.var_export($query,true).';$_COOKIE='.var_export($cookies,true).';';
    $code.='register_shutdown_function(function(){echo "|status=".(http_response_code() ?: 200);});';
    $code.='require '.var_export(dirname(__DIR__).'/request.inc.php',true).';';
    $code.='echo json_encode(array("query"=>$_GET,"cookies"=>$_COOKIE));';
    $process=proc_open(array(PHP_BINARY,'-r',$code),array(1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
    if (!is_resource($process)) throw new RuntimeException('Cannot start request fixture.');
    $out=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);
    fclose($pipes[1]);fclose($pipes[2]);$status=proc_close($process);$checks++;
    $expected=$expectedStatus===200 ? json_encode(array('query'=>$query,'cookies'=>$cookies)).'|status=200' : 'Invalid request parameter.|status='.$expectedStatus;
    if ($status!==0 || $error!=='' || $out!==$expected) throw new RuntimeException('Request compatibility: '.var_export(array($query,$cookies,$out,$error),true));
}
requestCompatibility(array(),array());
requestCompatibility(array('c'=>'customapp','l'=>'de','selection'=>array('first','second')),array());
requestCompatibility(array('c'=>'customapp','filters'=>array('country'=>array('CH','LI'),'range'=>array('0','10'))),array());
requestCompatibility(array('selection'=>array()),array('application'=>array('preferences'=>array('compact'=>'1','language'=>'de'))));
requestCompatibility(array('q'=>array('term')),array());
requestCompatibility(array('c'=>'0','n'=>'','l'=>null,'p'=>false),array('u'=>'','p'=>'','hp'=>'0'));
foreach (array('c','n','l','p','u5prvldd') as $key) requestCompatibility(array($key=>array('invalid')),array(),400);
foreach (array('u','p','hp') as $key) requestCompatibility(array(),array($key=>array('invalid')),400);
echo 'Request extension compatibility: '.$checks.' checks passed on '.PHP_VERSION.".\n";
