<?php
// Actual include and batch lifecycle in a disposable tree; no site database.
if (PHP_SAPI !== 'cli') exit;
$root = dirname(__DIR__);
$tmp = sys_get_temp_dir().'/u5-index-notice-'.bin2hex(random_bytes(8));
mkdir($tmp,0700);
mkdir($tmp.'/fileversions',0700);
$cwd = getcwd();
$checks = 0;
function noticeCheck($value,$label) {
    global $checks;
    ++$checks;
    if (!$value) throw new RuntimeException($label);
}
function noticeBody($root) {
    ob_start();
    include $root.'/indexupdate.inc.php';
    return ob_get_clean();
}
try {
    chdir($tmp);
    foreach (array('indexupdate.inc.php','search-index.inc.php','search-core.inc.php') as $file) {
        if (is_file($root.'/'.$file)) copy($root.'/'.$file,$tmp.'/'.$file);
    }
    // The newer search tracks both its format and its successful time.
    if (is_file($tmp.'/search-index.inc.php')) file_put_contents('fileversions/search-index-v2.txt','2');
    file_put_contents('fileversions/lastsave.txt','100');
    file_put_contents('fileversions/lastindex.txt','100');
    noticeCheck(noticeBody($tmp)==='', 'A current index needs no stale-state notice.');
    if (is_file($tmp.'/search-index.inc.php')) {
        unlink('fileversions/search-index-v2.txt');
        noticeCheck(strpos(noticeBody($tmp),'not yet fully up to date')!==false,'A missing format upgrade remains visible even with current dates.');
        file_put_contents('fileversions/search-index-v2.txt','2');
        file_put_contents('fileversions/search-index-pending.txt','100');
        noticeCheck(strpos(noticeBody($tmp),'not yet fully up to date')!==false,'An explicit pending rebuild remains visible even with current dates.');
        unlink('fileversions/search-index-pending.txt');
    }
    file_put_contents('fileversions/lastsave.txt','200');
    $body = noticeBody($tmp);
    noticeCheck(strpos($body,date('Y-m-d H:i T',100))!==false,'The notice uses the successful index watermark.');
    noticeCheck(strpos($body,'will run soon')===false,'The notice makes no scheduling promise.');
    foreach (array('0','invalid',(string)(time()+86400)) as $stamp) {
        file_put_contents('fileversions/lastindex.txt',$stamp);
        noticeCheck(strpos(noticeBody($tmp),'No completed full index rebuild is recorded yet.')!==false,'An unknown watermark must not display an epoch/future date.');
    }
    unlink('fileversions/lastindex.txt');
    noticeCheck(strpos(noticeBody($tmp),'No completed full index rebuild is recorded yet.')!==false,'Missing initial state remains intelligible.');
    copy($root.'/indexer.php',$tmp.'/indexer.php');
    foreach (array('background-work.inc.php','search-index.inc.php','search-core.inc.php') as $file) if (is_file($root.'/'.$file)) copy($root.'/'.$file,$tmp.'/'.$file);
    file_put_contents('fileversions/lastindex.txt','100');
    file_put_contents('fileversions/indexerrunning.txt','0');
    $_GET = array('u5indexcontinue'=>'1');
    require $tmp.'/indexer.php';
    $slice = new U5IndexSlice($tmp.'/fileversions',false,false,false);
    noticeCheck($slice->ready(),'The full rebuild starts.');
    noticeCheck(strpos(noticeBody($tmp),date('Y-m-d H:i T',100))!==false,'Starting a rebuild retains the old public date.');
    $slice->processed(array('resource_key'=>'4142','deleted'=>0));
    ob_start();$slice->pause();ob_end_clean();$slice->release();
    noticeCheck(strpos(noticeBody($tmp),date('Y-m-d H:i T',100))!==false,'A partial rebuild retains the old date.');
    $path='fileversions/indexer-progress.php';
    $state=json_decode(explode("\n",file_get_contents($path),2)[1],true);
    $state['next']=0;
    file_put_contents($path,"<?php exit; ?>\n".json_encode($state));
    $slice = new U5IndexSlice($tmp.'/fileversions',true,false,false);
    noticeCheck(strpos(noticeBody($tmp),date('Y-m-d H:i T',100))!==false,'A resumed attempt retains the old date.');
    $started=$slice->started();
    file_put_contents('fileversions/lastsave.txt',(string)($started-2));
    $slice->complete();$slice->release();
    noticeCheck(noticeBody($tmp)==='','Only a completed rebuild clears a satisfied notice.');
    file_put_contents('fileversions/lastsave.txt',(string)$started);
    noticeCheck(strpos(noticeBody($tmp),date('Y-m-d H:i T',$started-1))!==false,'Saves during the rebuild remain visibly pending.');
    echo "Index date notice checks: $checks passed.\n";
} finally {
    chdir($cwd);
    foreach (glob($tmp.'/fileversions/*') as $file) unlink($file);
    rmdir($tmp.'/fileversions');
    foreach (glob($tmp.'/*') as $file) unlink($file);
    rmdir($tmp);
}
