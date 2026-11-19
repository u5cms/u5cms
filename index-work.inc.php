<?php
// Common coordination for full and named index requests. No site configuration
// or database is loaded here, so a busy request can leave before doing SQL work.
function u5IndexMarkDirty($root) {
    $path=$root.'/fileversions/lastsave.txt';
    $temporary=tempnam(dirname($path),'.index-save-');
    if ($temporary===false) throw new RuntimeException('Cannot prepare the search update marker.');
    try {
        $bytes=(string)time();
        if (file_put_contents($temporary,$bytes)!==strlen($bytes)) throw new RuntimeException('Cannot write the search update marker.');
        $mode=is_file($path) ? fileperms($path)&0777 : 0644;
        if (!chmod($temporary,$mode) || !rename($temporary,$path)) throw new RuntimeException('Cannot publish the search update marker.');
    } finally { if (is_file($temporary)) unlink($temporary); }
}

function u5IndexMutationLock($root) {
    require_once $root.'/resource-activity.inc.php';
    $release=u5ResourceActivityLock($root);$release();
    if (u5ResourceWritesActive($root)) throw new U5ResourceBusyException('A source write is active; indexing yields.');
    // Source-digest UPDATE guards protect races after this check. Do not keep
    // a source lock while rendering: saves and Rename must remain responsive.
    return function() {};
}

function u5IndexNamedLock($root) {
    $lock=fopen($root.'/fileversions/indexerrunning.txt','c+');
    if (!$lock) throw new RuntimeException('Cannot open the index lock.');
    if (!flock($lock,LOCK_EX|LOCK_NB)) { fclose($lock); return null; }
    // Kernel ownership, not a file age, controls exclusion. Do not replace the
    // lock file or change the full runner's durable progress/watermark.
    $release=function() use(&$lock) {
        if (is_resource($lock)) { flock($lock,LOCK_UN);fclose($lock);$lock=null; }
    };
    register_shutdown_function($release);
    return $release;
}

function u5IndexDeferNamed() {
    http_response_code(409);
    header('Retry-After: 10');
    $attempt=(isset($_GET['u5indexretry']) && is_scalar($_GET['u5indexretry'])) ? (int)$_GET['u5indexretry'] : 0;
    if ($attempt>=0 && $attempt<3) {
        $url='?n='.rawurlencode($_GET['n']).'&u5indexretry='.($attempt+1);
        echo '<script>setTimeout(function(){location.replace('.json_encode($url).')},10000)</script>';
    }
    echo 'The saved resource is awaiting indexing. The full rebuild remains due.';
}
