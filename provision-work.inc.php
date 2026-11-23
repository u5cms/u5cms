<?php
// Durable work is independent of search freshness. A ticket outlives a killed
// request, whereas flock ownership disappears when that request exits.
function u5ProvisionDir($root) {
    return $root.'/fileversions/.u5-provision';
}

function u5ProvisionDirectory($path) {
    if (!is_dir($path) && !@mkdir($path, 0755) && !is_dir($path)) {
        throw new RuntimeException('Cannot create provisioning directory.');
    }
    if (is_link($path)) throw new RuntimeException('Unexpected linked provisioning directory.');
}

function u5ProvisionPut($path, $bytes, $mode=0644) {
    if (is_link($path)) throw new RuntimeException('Unexpected linked protection file.');
    if (is_file($path) && file_get_contents($path)===$bytes) return;
    $dir=dirname($path);
    $tmp=tempnam($dir,'.ht-u5-write-');
    if ($tmp===false || dirname($tmp)!==realpath($dir)) {
        if ($tmp!==false) unlink($tmp);
        throw new RuntimeException('Cannot stage protection in its destination directory.');
    }
    try {
        if (file_put_contents($tmp,$bytes)!==strlen($bytes) || !chmod($tmp,$mode) || !rename($tmp,$path)) {
            throw new RuntimeException('Cannot publish protection file.');
        }
    } finally {
        if (is_file($tmp)) unlink($tmp);
    }
}

function u5ProvisionEncode($value) {
    return "<?php exit; ?>\n".base64_encode(serialize($value));
}

function u5ProvisionRead($file) {
    $bytes=file_get_contents($file);
    $prefix="<?php exit; ?>\n";
    if (!is_string($bytes) || strpos($bytes,$prefix)!==0) throw new RuntimeException('Invalid provisioning state.');
    $value=unserialize(base64_decode(substr($bytes,strlen($prefix)),true),array('allowed_classes'=>false));
    if (!is_array($value)) throw new RuntimeException('Invalid provisioning records.');
    return $value;
}

function u5ProvisionPending($root) {
    $dir=u5ProvisionDir($root);
    return !is_file($dir.'/references.php') || (bool)glob($dir.'/job-*.php');
}

function u5ProvisionLock($path,$operation) {
    $file=fopen($path,'c+');
    if (!$file) throw new RuntimeException('Cannot open provisioning lock.');
    if (flock($file,$operation)) return $file;
    fclose($file);
    return null;
}

function u5ProvisionName($name) {
    return is_string($name) && $name!=='' && $name!=='.' && $name!=='..'
        && !preg_match('~[/\\\\\x00-\x1f]~',$name);
}

function u5ProvisionMediaDir($root,$name) {
    if (!u5ProvisionName($name)) throw new RuntimeException('Invalid resource directory.');
    $path=$root.'/r/'.$name;
    if (is_link($path) || is_link(dirname($path))) throw new RuntimeException('Linked resource directory requires review.');
    return $path;
}

// A per-directory publication lock covers only local file replacement and
// the final guarded UPDATE. It never spans a site scan or an editor session.
function u5ProvisionGuard($root,$name,$create=false) {
    $dir=u5ProvisionMediaDir($root,$name);
    if (!$create && !is_dir($dir)) return;
    if ($create) u5ProvisionDirectory($dir);
    $state=u5ProvisionDir($root);
    $lock=u5ProvisionLock($state.'/publish-'.(crc32($name)%67).'.lock',LOCK_EX);
    try {
        $guard=$dir.'/.u5-provision.php';
        if (!is_file($guard)) {
            $private=is_file($dir.'/.htpasswd') || is_file($dir.'/.htaccess');
            u5ProvisionPut($guard,u5ProvisionEncode(array('private'=>$private)));
        }
        u5ProvisionPut($dir.'/.htaccess',"Deny from all\n");
        u5ProvisionPut($dir.'/.htpasswd','');
    } finally {fclose($lock);}
}

function u5ProvisionTicket($root,$sources,$targets,$full=false,$identity=false,$grantOnly=false,$checkpoint=null) {
    $dir=u5ProvisionDir($root);
    u5ProvisionDirectory($dir);
    $token=bin2hex(random_bytes(16));
    $path=$dir.'/job-'.$token.'.php';
    $owner=u5ProvisionLock($dir.'/owner-'.$token,LOCK_EX);
    $job=array('sources'=>array_values(array_unique($sources)),'targets'=>array_values(array_unique($targets)),
        'full'=>$full,'identity'=>$identity,'grantOnly'=>$grantOnly,'checkpoint'=>$checkpoint);
    try {
        u5ProvisionPut($path,u5ProvisionEncode($job));
        u5ProvisionPut($dir.'/generation',$token);
    } catch (Throwable $error) {fclose($owner);throw $error;}
    $GLOBALS['u5ProvisionOwners'][]=$owner;
    if (count($GLOBALS['u5ProvisionOwners'])===1) {
        register_shutdown_function(function() use ($root) {
            foreach ($GLOBALS['u5ProvisionOwners'] as $handle) fclose($handle);
            $GLOBALS['u5ProvisionOwners']=array();
            if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
            try {u5ProvisionRun($root,false,true);} catch (Throwable $error) {
                error_log('u5CMS provisioning pending: '.$error->getMessage());
            }
        });
    }
    return $job;
}

function u5ProvisionRecoverFile($root,$marker) {
    if (!is_file($marker)) return;
    require_once __DIR__.'/provision.inc.php';
    try {u5ProvisionRun($root,false,true);}
    catch (Throwable $error) {error_log('u5CMS file access update: '.$error->getMessage());}
    clearstatcache(true,$marker);
    if (is_file($marker)) {
        http_response_code(503);
        header('Cache-Control: private, no-store');
        header('Retry-After: 1');
        exit('Updating access to this resource. Please try again.');
    }
}
