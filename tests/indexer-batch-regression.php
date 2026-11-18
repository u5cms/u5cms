<?php
// The release entry point runs only against this disposable file tree.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
$tmp = sys_get_temp_dir().'/u5-index-batch-'.bin2hex(random_bytes(8));
mkdir($tmp,0700);
mkdir($tmp.'/fileversions',0700);
$dir = $tmp.'/fileversions';
$checks = 0;
function batchCheck($condition,$message) {
    global $checks;
    ++$checks;
    if (!$condition) throw new RuntimeException($message);
}
try {
    foreach (array('indexer.php','search-index.inc.php','search-core.inc.php') as $file) {
        if (is_file($root.'/'.$file)) copy($root.'/'.$file,$tmp.'/'.$file);
    }
    file_put_contents($dir.'/indexerrunning.txt','0');
    file_put_contents($dir.'/lastindex.txt','100');
    file_put_contents($dir.'/lastsave.txt','90');
    $_GET = array('u5indexcontinue'=>'1');
    require $tmp.'/indexer.php';
    $idle = new U5IndexSlice($dir,false,true,false);
    batchCheck(!$idle->ready(),'A current index ignores a late automatic request.');
    $idle->release();
    $active = new U5IndexSlice($dir,false,false,false);
    batchCheck($active->ready(),'A manual full run can start.');
    $other = new U5IndexSlice($dir,false,false,false);
    batchCheck(!$other->ready(),'A second worker cannot enter.');
    $active->processed(array('resource_key'=>'4142','deleted'=>0));
    ob_start(); $active->pause(); $body=ob_get_clean();
    batchCheck(strpos($body,'u5indexcontinue=1')!==false,'The browser receives a continuation.');
    $active->release();
    $file=$dir.'/indexer-progress.php';
    $guard="<?php exit; ?>\n";
    $state=json_decode(substr(file_get_contents($file),strlen($guard)),true);
    batchCheck($state['key']==='4142' && $state['next']>time(),'The cursor and idle interval are durable.');
    batchCheck((int)file_get_contents($dir.'/lastindex.txt')===100,'A partial pass keeps the last successful watermark.');
    clearstatcache(true,$file);
    batchCheck(filemtime($file)===$state['next'],'The launcher can cheaply observe the same retry time.');
    $early = new U5IndexSlice($dir,true,false,false);
    batchCheck(!$early->ready(),'An early visitor starts no database work.');
    $early->release();
    $state['next']=0;
    file_put_contents($file,$guard.json_encode($state));
    $resume = new U5IndexSlice($dir,true,false,false);
    batchCheck($resume->ready(),'The batch can resume.');
    $resume->release(); // The durable attempt marker also covers process death.
    $retry = new U5IndexSlice($dir,true,false,false);
    batchCheck(!$retry->ready(),'An interrupted attempt does not immediately retry.');
    $retry->release();
    $state=json_decode(substr(file_get_contents($file),strlen($guard)),true);
    $state['next']=0;
    file_put_contents($file,$guard.json_encode($state));
    $resume = new U5IndexSlice($dir,true,false,false);
    $started=$resume->started();
    $resume->complete();$resume->release();
    batchCheck((int)file_get_contents($dir.'/lastindex.txt')===$started-1,'The completed pass publishes its original snapshot time.');
    batchCheck(!is_file($file) && trim(file_get_contents($dir.'/indexerrunning.txt'))==='0','A completed pass leaves no active checkpoint.');
    echo "Indexer batch checks: $checks passed.\n";
} finally {
    foreach (glob($dir.'/*') as $file) unlink($file);
    rmdir($dir);
    foreach (glob($tmp.'/*') as $file) unlink($file);
    rmdir($tmp);
}
