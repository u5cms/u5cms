<?php
// Browser transport only. Work ownership, checkpoints and database locks remain
// with the existing indexer/provisioner.
function u5BackgroundWorkLaunch($url, $kind, $asset='js/u5-background-work.js', $publicOnly=false, $status=false) {
    static $loaded=false;
    $job=array('url'=>$url,'kind'=>$kind);
    if ($status) $job['status']='u5-background-status';
    $json=json_encode($job,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_INVALID_UTF8_SUBSTITUTE);
    echo '<script>' . ($publicOnly ? 'if(!opener&&top===self){' : '')
        . 'window.u5BackgroundWorkQueue=window.u5BackgroundWorkQueue||[];window.u5BackgroundWorkQueue.push('.$json.');'
        . 'if(window.u5BackgroundWork)window.u5BackgroundWork.drain();'
        . ($publicOnly ? '}' : '') . '</script>';
    if (!$loaded) {
        $loaded=true;
        echo '<script defer src="'.htmlspecialchars($asset,ENT_QUOTES,'UTF-8').'"></script>';
    }
}

function u5BackgroundWorkBootstrap($kind) {
    if (PHP_SAPI==='cli' || ($_SERVER['HTTP_X_U5_BACKGROUND_WORK'] ?? '')==='1'
        || ($_SERVER['REQUEST_METHOD'] ?? '')!=='GET') return false;
    $destination=$_SERVER['HTTP_SEC_FETCH_DEST'] ?? '';
    $accept=$_SERVER['HTTP_ACCEPT'] ?? '';
    if (!in_array($destination,array('document','iframe','frame'),true) && strpos($accept,'text/html')===false) return false;
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-store');
    $label=$kind==='index' ? 'Search index update' : 'Access update';
    echo '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>'.$label.'</title><body><p id="u5-background-status">'.$label.' pending.</p><p><a href="./">Back to website</a></p>';
    $query=$_SERVER['QUERY_STRING'] ?? '';
    $url=($kind==='index' ? 'indexer.php' : 'htaccess.php').($query!=='' ? '?'.$query : '');
    u5BackgroundWorkLaunch($url,$kind,'js/u5-background-work.js',false,true);
    echo '</body></html>';
    return true;
}

function u5BackgroundWorkBegin($kind) {
    if (($_SERVER['HTTP_X_U5_BACKGROUND_WORK'] ?? '')!=='1') return;
    $GLOBALS['u5BackgroundReply']=array('level'=>ob_get_level(),'reply'=>array('u5work'=>1,'kind'=>$kind,'state'=>'error'));
    ob_start();
    register_shutdown_function('u5BackgroundWorkFinish');
}

function u5BackgroundWorkState($state, $next=null, $delay=0, $completed=false) {
    if (!isset($GLOBALS['u5BackgroundReply'])) return;
    $reply=&$GLOBALS['u5BackgroundReply']['reply'];
    $reply['state']=$state;
    if ($next!==null) { $reply['next']=$next;$reply['delay']=$delay; }
    if ($completed) $reply['completed']=true;
}

function u5BackgroundWorkFinish() {
    if (!isset($GLOBALS['u5BackgroundReply'])) return;
    $work=$GLOBALS['u5BackgroundReply'];
    unset($GLOBALS['u5BackgroundReply']);
    while (ob_get_level()>$work['level']) ob_end_clean();
    if (!headers_sent()) {
        if ($work['reply']['state']==='error' && http_response_code()<400) http_response_code(500);
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: private, no-store');
        header('X-Content-Type-Options: nosniff');
    }
    echo json_encode($work['reply'],JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE);
}
