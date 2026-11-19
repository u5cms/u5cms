<?php
/* Process-owned coordination only. A recovery journal is data, never a lock. */
if (!class_exists('U5ResourceBusyException',false)) { class U5ResourceBusyException extends RuntimeException {} }

function u5ResourceActivityState() {
    static $state;
    if ($state===null) $state=(object)array('locks'=>array(),'rename'=>array());
    return $state;
}

function u5ResourceRenameStatus($root) {
    $path=$root.'/fileversions/resource-rename.lock.php';
    $handle=@fopen($path,'r');
    if (!$handle) return null;
    try {
        if (flock($handle,LOCK_SH|LOCK_NB)) {flock($handle,LOCK_UN);return null;}
        $data=@file_get_contents($root.'/fileversions/resource-rename-active.php');
        $prefix="<?php exit; ?>\n";
        $status=is_string($data) && substr($data,0,strlen($prefix))===$prefix ? json_decode(substr($data,strlen($prefix)),true) : null;
        return is_array($status) ? $status : array('phase'=>'starting');
    } finally {fclose($handle);}
}

function u5ResourceRenameMessage($status) {
    if (!$status) return 'No resource rename is currently running.';
    $message=isset($status['old'],$status['new']) ? 'Rename "'.$status['old'].'" to "'.$status['new'].'" is running.' : 'A resource rename is starting.';
    $phases=array('planning'=>'finding files, references and history','files'=>'updating files and templates','rows'=>'updating references and history','export'=>'publishing code assets','complete'=>'finishing');
    $message.=' Phase: '.($phases[$status['phase'] ?? ''] ?? 'starting').'.';
    if (isset($status['started'])) $message.=' Elapsed: '.max(0,(int)(microtime(true)-$status['started'])).' s.';
    if (($status['total'] ?? 0)>0) $message.=' Rows: '.(int)$status['done'].' / '.(int)$status['total'].'.';
    if (isset($status['remaining'])) $message.=' Estimated remaining row work: about '.(int)$status['remaining'].' s; final publication follows.';
    else $message.=' Remaining time depends on the site size and references; no reliable estimate is available yet.';
    return $message.' Wait for the rename result, then save again. Your editor contents remain available.';
}

function u5ResourceActivityLock($root,$rename=null) {
    $state=u5ResourceActivityState();$key=realpath($root) ?: $root;
    if (isset($state->locks[$key])) {
        if ($rename!==null && !$state->locks[$key]['exclusive']) throw new RuntimeException('Cannot start Rename inside a resource write.');
        $state->locks[$key]['depth']++;
    } else {
        $handle=@fopen($root.'/fileversions/resource-rename.lock.php','c+');
        if (!$handle && $rename===null) $handle=@fopen($root.'/fileversions/resource-rename.lock.php','r');
        if (!$handle) {
            if ($rename!==null) throw new RuntimeException('Cannot open the resource rename gate. Check fileversions permissions.');
            error_log('u5CMS: Cannot open the Rename gate; check fileversions permissions.');
            return function() {};
        }
        if (!flock($handle,($rename===null ? LOCK_SH : LOCK_EX)|LOCK_NB)) {
            fclose($handle);
            if ($rename===null) {
                $status=u5ResourceRenameStatus($root);
                throw new U5ResourceBusyException($status ? u5ResourceRenameMessage($status) : 'Rename has just finished. Save again; your editor contents remain available.');
            }
            $status=u5ResourceRenameStatus($root);
            throw new U5ResourceBusyException($status ? u5ResourceRenameMessage($status) : 'A resource save is finishing. Rename has not started; retry Rename when that save completes.');
        }
        $state->locks[$key]=array('handle'=>$handle,'depth'=>1,'exclusive'=>$rename!==null);
        if ($rename!==null) {
            u5ResourceGenerationChange($root);
            $state->rename[$key]=$rename+array('started'=>microtime(true),'phase'=>'planning','done'=>0,'total'=>0,'written'=>0);
            u5ResourceRenameProgress($root,'planning',0,0,true);
        }
    }
    $released=false;
    $release=function() use($state,$key,$root,&$released) {
        if ($released) return;$released=true;
        if (!isset($state->locks[$key])) return;
        if (--$state->locks[$key]['depth']===0) {
            if ($state->locks[$key]['exclusive']) {u5ResourceGenerationChange($root);@unlink($root.'/fileversions/resource-rename-active.php');unset($state->rename[$key]);}
            flock($state->locks[$key]['handle'],LOCK_UN);fclose($state->locks[$key]['handle']);unset($state->locks[$key]);
        }
    };
    return $release;
}

function u5ResourceRenameProgress($root,$phase,$done=null,$total=null,$force=false) {
    $state=u5ResourceActivityState();$key=realpath($root) ?: $root;
    if (!isset($state->rename[$key])) return;
    $status=&$state->rename[$key];$now=microtime(true);
    if ($phase!==$status['phase']) {$force=true;$status['phaseStarted']=$now;unset($status['remaining']);}
    $status['phase']=$phase;
    if ($done!==null) $status['done']=$done;
    if ($total!==null) $status['total']=$total;
    if ($phase==='rows' && $status['done']>=5 && $status['total']>$status['done'] && $now-($status['phaseStarted'] ?? $now)>=1) {
        $status['remaining']=(int)ceil(($now-$status['phaseStarted'])/$status['done']*($status['total']-$status['done']));
    }
    // Large histories must not incur a disk publication for every changed row.
    if (!$force && $now-$status['written']<0.25) return;
    $status['written']=$now;$data="<?php exit; ?>\n".json_encode($status);
    $path=$root.'/fileversions/resource-rename-active.php';$tmp=@tempnam(dirname($path),'.rename-status-');
    if ($tmp===false) return;
    try {if (@file_put_contents($tmp,$data)===strlen($data)) {@chmod($tmp,0644);@rename($tmp,$path);}}
    finally {if (is_file($tmp)) @unlink($tmp);}
}

function u5ResourceWriteActivity($root) {
    // Unique, shared-by-nobody activity records let the indexer yield. They
    // never serialize writers, and a killed process cannot leave a live owner.
    $path=@tempnam($root.'/fileversions','.resource-write-');
    if ($path===false) return function() {};
    $handle=@fopen($path,'r+');
    if (!$handle || !flock($handle,LOCK_EX|LOCK_NB)) {if ($handle) fclose($handle);@unlink($path);return function() {};}
    u5ResourceGenerationChange($root);
    $release=function() use(&$handle,$path,$root) {
        if (!is_resource($handle)) return;
        u5ResourceGenerationChange($root);
        @unlink($path);flock($handle,LOCK_UN);fclose($handle);$handle=null;
    };
    return $release;
}

function u5ResourceWritesActive($root) {
    foreach (glob($root.'/fileversions/.resource-write-*') ?: array() as $path) {
        $handle=@fopen($path,'r');if (!$handle) continue;
        $idle=flock($handle,LOCK_SH|LOCK_NB);
        if ($idle) {flock($handle,LOCK_UN);if (@filemtime($path)<time()-60) @unlink($path);}
        fclose($handle);if (!$idle) return true;
    }
    return false;
}

function u5ResourceGenerationChange($root) {
    $path=$root.'/fileversions/resource-write-generation.php';
    $temporary=@tempnam(dirname($path),'.resource-generation-');
    if ($temporary===false) {error_log('u5CMS: Cannot track source generation; check fileversions permissions.');return false;}
    try {
        $data="<?php exit; ?>\n".bin2hex(random_bytes(16));
        if (@file_put_contents($temporary,$data)!==strlen($data) || !@chmod($temporary,0644) || !@rename($temporary,$path)) {
            error_log('u5CMS: Cannot publish source generation; check fileversions permissions.');return false;
        }
        return true;
    } catch (Throwable $error) {
        error_log('u5CMS: Cannot track source generation: '.$error->getMessage());return false;
    } finally {if (is_file($temporary)) @unlink($temporary);}
}

function u5ResourceBackupStart($root) {
    if (u5ResourceWritesActive($root) || u5ResourceRenameStatus($root)) throw new U5ResourceBusyException('A source write or Rename is active. Retry the backup after it finishes; editing remains available.');
    if (!u5ResourceGenerationChange($root)) throw new RuntimeException('Cannot track a consistent backup. Check fileversions permissions; editing remains available.');
    $generation=@file_get_contents($root.'/fileversions/resource-write-generation.php');
    if (!is_string($generation)) throw new RuntimeException('Cannot read the backup source generation.');
    u5ResourceBackupFinish($root,$generation);
    return $generation;
}

function u5ResourceBackupFinish($root,$generation) {
    // Test owners before reading the final generation. A write which starts
    // after that final read is beyond the completed backup's snapshot point.
    if (u5ResourceWritesActive($root) || u5ResourceRenameStatus($root)
        || !is_writable($root.'/fileversions')
        || @file_get_contents($root.'/fileversions/resource-write-generation.php')!==$generation) {
        throw new U5ResourceBusyException('Source content changed during the backup. This backup was not published. Retry the backup; ordinary saves remain available.');
    }
}
