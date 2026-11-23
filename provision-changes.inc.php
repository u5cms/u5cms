<?php
require_once __DIR__.'/provision.inc.php';

function u5ProvisionExpandTargets($root,$targets,$media) {
    $cache=u5ProvisionDir($root).'/references.php';
    $map=null;
    if (is_file($cache) && !glob(u5ProvisionDir($root).'/job-*.php')) {
        try {$map=u5ProvisionRead($cache);} catch (RuntimeException $error) {$map=null;}
    }
    $seen=array_fill_keys($targets,true);
    $queue=array_keys($seen);
    for ($i=0;$i<count($queue);$i++) {
        $name=$queue[$i];
        $out=$map[$name] ?? null;
        if ($map===null) {
            $row=u5ProvisionFetchSources(array($name));
            $out=$row[$name] ?? null;
        }
        $more=$out ? u5ProvisionCandidates(array($out),$media) : array();
        if (($media[$name] ?? '')==='v') $more[]='v'.$name;
        foreach ($more as $next) if (!isset($seen[$next])) {$seen[$next]=true;$queue[]=$next;}
    }
    return array_keys($seen);
}

// Called after the editor has validated the request, before the source write.
function u5ProvisionPrepare($root,$name,$changes=null,$copyFrom=null,$createDir=false) {
    $before=u5ProvisionFetchSources(array($name));
    $old=$before[$name] ?? null;
    $summaries=$old ? array($old) : array();
    if ($changes!==null) {
        $new=u5ProvisionSummary($changes);
        if ($old && $old===$new && !$new['fallback']) return;
        $summaries[]=$new;
    }
    if ($copyFrom!==null) {
        $copied=u5ProvisionFetchSources(array($copyFrom),$name===$copyFrom && !$old);
        $summaries=array_merge($summaries,array_values($copied));
    }
    $media=u5ProvisionMedia();
    $targets=u5ProvisionCandidates($summaries,$media);
    if ($createDir || isset($media[$name]) || $copyFrom===$name) $targets[]=$name;
    if ($copyFrom!==null && (($media[$copyFrom] ?? '')==='v' || ($copied[$copyFrom]['type'] ?? '')==='v')) $targets[]='v'.$name;
    $targets=u5ProvisionExpandTargets($root,$targets,$media);
    u5ProvisionTicket($root,array($name),$targets);
    foreach ($targets as $target) u5ProvisionGuard($root,(string)$target,$createDir && ($target===$name || $target==='v'.$name));
}

function u5ProvisionUpload($root,$name) {
    $dir=u5ProvisionMediaDir($root,$name);
    if (is_dir($dir) && glob($dir.'/*') && is_file(u5ProvisionDir($root).'/references.php')) return;
    u5ProvisionPrepare($root,$name,null,null,true);
}

function u5ProvisionIdentityChange($root,$accountsChanged,$grantOnly=false) {
    $media=u5ProvisionMedia();
    $result=u5ProvisionQuery("SELECT name FROM resources WHERE deleted!=1 AND name LIKE '!%'");
    $sources=array();
    while ($row=mysql_fetch_assoc($result)) $sources[]=$row['name'];
    $intranet=u5ProvisionFetchSources($sources);
    $targets=u5ProvisionCandidates($intranet,$media);
    foreach ($media as $name=>$type) {
        $dir=u5ProvisionMediaDir($root,(string)$name);
        if (substr((string)$name,0,1)==='!' || ($accountsChanged && is_file($dir.'/.htpasswd'))) $targets[]=(string)$name;
    }
    // Deleting a resource may leave its files and their published credentials on disk.
    if ($accountsChanged) foreach (glob($root.'/r/*/.htpasswd') ?: array() as $passwordFile) {
        $name=basename(dirname($passwordFile));
        if (!isset($media[$name]) && u5ProvisionName($name)) $targets[]=$name;
    }
    $targets=u5ProvisionExpandTargets($root,$targets,$media);
    u5ProvisionTicket($root,$sources,$targets,false,true,$grantOnly);
    if ($grantOnly) return;
    foreach ($targets as $target) u5ProvisionGuard($root,(string)$target);
    if ($accountsChanged) {
        $dir=$root.'/fileversions';
        $lock=u5ProvisionLock(u5ProvisionDir($root).'/versions.lock',LOCK_EX);
        try {
            u5ProvisionPut($dir.'/.u5-provision.php',u5ProvisionEncode(array('private'=>true)));
            u5ProvisionPut($dir.'/.htaccess',"Deny from all\n");
            u5ProvisionPut($dir.'/.htpasswd','');
        } finally {fclose($lock);}
    }
}

function u5ProvisionUnavailable() {
    http_response_code(503);
    header('Cache-Control: private, no-store');
    header('Retry-After: 1');
    exit('Updating access to this resource. Please try again.');
}

function u5ProvisionRename($root,$plan,$old,$new,$each=null,$checkpoint=null) {
    if ($each===null) $each=function_exists('u5RenameStorageEachChange') ? 'u5RenameStorageEachChange' : 'u5CodeMigrationEachChange';
    $sources=array();
    foreach (array($old,$new) as $name) if ($name!=='') $sources[$name]=true;
    $summaries=array();
    $each($root,$plan['updates'],function($change) use (&$sources,&$summaries) {
        if ($change['table']!=='resources') return;
        foreach (array('before','after') as $side) {
            $row=$change[$side];$sources[$row['name']]=true;
            $summaries[]=u5ProvisionTokens($row);
        }
    });
    $media=u5ProvisionMedia();
    $targets=u5ProvisionCandidates($summaries,$media);
    if (isset($media[$old]) || isset($media[$new])) {$targets[]=$old;$targets[]=$new;}
    if ($plan['companionFiles']!==null) {$targets[]='v'.$old;$targets[]='v'.$new;$sources['v'.$old]=true;$sources['v'.$new]=true;}
    $targets=u5ProvisionExpandTargets($root,$targets,$media);
    foreach (array('files','companionFiles') as $key) {
        if (!empty($plan[$key]['archive'])) $targets[]=$plan[$key]['archive'];
    }
    u5ProvisionTicket($root,array_keys($sources),$targets,false,false,false,$checkpoint===null ? null : basename($checkpoint));
    foreach ($targets as $name) u5ProvisionGuard($root,(string)$name);
}

function u5ProvisionPagePending($root,$name,$retry=true) {
    if (!u5ProvisionPending($root)) return false;
    foreach (glob(u5ProvisionDir($root).'/job-*.php') ?: array() as $path) {
        if (!is_file($path)) continue;
        try {$job=u5ProvisionRead($path);}
        catch (Throwable $error) {if (!is_file($path)) continue;throw $error;}
        if (!empty($job['grantOnly'])) continue;
        $affected=$job['identity'] || in_array($name,$job['sources'],true);
        if ($name==='!') foreach ($job['sources'] as $source) if (substr((string)$source,0,1)==='!') $affected=true;
        if (!$affected) continue;
        if (!$retry) return true;
        try {u5ProvisionRun($root,false,true);}
        catch (Throwable $error) {error_log('u5CMS intranet update: '.$error->getMessage());}
        return u5ProvisionPagePending($root,$name,false);
    }
    return false;
}

function u5ProvisionFinish($root) {
    foreach ($GLOBALS['u5ProvisionOwners'] ?? array() as $owner) fclose($owner);
    $GLOBALS['u5ProvisionOwners']=array();
    return u5ProvisionRun($root,false,true);
}

// zr.php performs installation migrations and removes the initial temporary
// account once a named account has been used. Record the work before either write.
function u5ProvisionBackendStartup($root) {
    if (is_file($root.'/r/runonce.php')) u5ProvisionTicket($root,array(),array(),true,true);
    if (strtolower((string)($_SERVER['PHP_AUTH_USER'] ?? ''))==='temp') return;
    $result=u5ProvisionQuery("SELECT 1 FROM accounts WHERE email='Temp' AND EXISTS (SELECT 1 FROM accounts WHERE email!='Temp' AND lastused>0 AND email LIKE '%@%') LIMIT 1");
    if (mysql_num_rows($result)) u5ProvisionIdentityChange($root,true);
}
