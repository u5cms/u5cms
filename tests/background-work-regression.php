<?php
if (PHP_SAPI!=='cli') { http_response_code(404);exit; }
require dirname(__DIR__).'/background-work.inc.php';
function backgroundCheck($test,$message) { if (!$test) throw new RuntimeException($message); }
ob_start();
u5BackgroundWorkLaunch('indexer.php?n=\"</script><iframe src=x>','index');
$markup=ob_get_clean();
backgroundCheck(strpos($markup,'<iframe')===false,'A queued job created an iframe or allowed script injection.');
backgroundCheck(strpos($markup,'u5BackgroundWorkQueue')!==false && strpos($markup,'defer src="js/u5-background-work.js"')!==false,'Missing background runner.');
$_SERVER['HTTP_X_U5_BACKGROUND_WORK']='1';
foreach(array('done','idle','wait','pending','error') as $state) {
    ob_start();http_response_code(200);
    u5BackgroundWorkBegin('index');
    echo '<script>throw new Error("must never execute")</script>';
    u5BackgroundWorkState($state,$state==='wait'?'?u5indexcontinue=1':null,11000);
    u5BackgroundWorkFinish();
    $json=ob_get_clean();$reply=json_decode($json,true);
    backgroundCheck(is_array($reply) && $reply['state']===$state && $reply['kind']==='index' && $reply['u5work']===1,'Invalid structured response.');
    backgroundCheck(strpos($json,'<script>')===false,'Rendered HTML leaked into the worker protocol.');
    if($state==='wait') backgroundCheck($reply['next']==='?u5indexcontinue=1' && $reply['delay']===11000,'Continuation timing was lost.');
    if($state==='error') backgroundCheck(http_response_code()===500,'A failed worker reported HTTP success.');
}
ob_start();http_response_code(200);u5BackgroundWorkBegin('provision');u5BackgroundWorkFinish();
$reply=json_decode(ob_get_clean(),true);
backgroundCheck($reply['state']==='error','An interrupted worker defaulted to success.');
echo "background-work-regression: ok\n";
