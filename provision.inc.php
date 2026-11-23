<?php
require_once __DIR__.'/provision-work.inc.php';
require_once __DIR__.'/provision-references.inc.php';
require_once __DIR__.'/provision-policy.inc.php';
require_once __DIR__.'/resource-activity.inc.php';
require_once __DIR__.'/u5admin/u5idn.inc.php';

function u5ProvisionRemove($path) {
    if ((is_file($path) || is_link($path)) && !unlink($path)) throw new RuntimeException('Cannot remove obsolete protection.');
}

// Derived visibility is a real search/backup change only when bytes actually
// change. The worker never writes a provisioning ticket for its own results.
function u5ProvisionUpdate($root,$set,$where) {
    if (!mysql_num_rows(u5ProvisionQuery('SELECT 1 FROM resources WHERE '.$where.' LIMIT 1'))) return;
    require_once __DIR__.'/index-work.inc.php';
    $release=u5ResourceWriteActivity($root);
    try {
        u5IndexMarkDirty($root);
        u5ProvisionQuery('UPDATE resources SET '.$set.' WHERE '.$where);
        u5IndexMarkDirty($root);
    } finally {$release();}
}

function u5ProvisionAdmins() {
    $result=u5ProvisionQuery('SELECT email,pw FROM accounts ORDER BY email');
    $accounts=array();
    while ($row=mysql_fetch_assoc($result)) $accounts[$row['email']]=$row['pw'];
    return $accounts;
}

function u5ProvisionDirectoryRules($dir,$basic) {
    global $u5cmsrealm;
    if (!$basic) return "Deny from all\n";
    $realm=str_replace(array('"',"\r","\n"),'',(string)($u5cmsrealm ?? 'LOGIN'));
    return 'AuthName "'.$realm."\"\nAuthType Basic\nAuthUserFile \"".str_replace('\\','/',$dir)."/.htpasswd\"\nRequire valid-user\n";
}

function u5ProvisionIntranet($root,$accounts) {
    $row=mysql_fetch_assoc(u5ProvisionQuery('SELECT salt FROM intranetsalt LIMIT 1'));
    if (!$row) throw new RuntimeException('Intranet salt missing.');
    if ((int)$row['salt']<1000) {
        u5ProvisionQuery('UPDATE intranetsalt SET salt='.random_int(1000000,9999999).' WHERE salt<1000');
        $row=mysql_fetch_assoc(u5ProvisionQuery('SELECT salt FROM intranetsalt LIMIT 1'));
    }
    $members=mysql_fetch_assoc(u5ProvisionQuery('SELECT members FROM intranetmembers LIMIT 1'));
    // A non-empty sentinel keeps an empty intranet private. Reusing it avoids
    // changing every ! resource and causing editor conflicts on every pass.
    $state=u5ProvisionDir($root).'/intranet.php';
    if (!is_file($state)) u5ProvisionPut($state,u5ProvisionEncode(array(bin2hex(random_bytes(20)),bin2hex(random_bytes(24)))),0600);
    list($user,$password)=u5ProvisionRead($state);
    $list='?'.$user.':'.$password.";\r\n";
    foreach (explode(',',(string)($members['members'] ?? '')) as $member) {
        if (trim(str_replace('&#0;@&#0;','',$member))!=='' && !array_key_exists($member,$accounts)) {
            $list.='?'.$member.':'.floor(crc32(u5flatidnlower($member).$row['salt'])).";\r\n";
        }
    }
    $value=u5ProvisionQuote($list);
    u5ProvisionUpdate($root,"logins=".$value,"name LIKE '!%' AND typ IN ('p','c','s') AND deleted!=1 AND NOT (BINARY logins <=> BINARY ".$value.")");
}

function u5ProvisionPolicy($uses) {
    $free=false;$force=false;$visible=array();$logins='';
    foreach ($uses as $row) {
        $hidden=(int)$row['hidden'];
        $visible[$hidden]=true;
        if ($hidden===2) $force=true;
        if (trim((string)$row['logins'])==='' && empty($row['restricted'])) $free=true;
        else $logins.=$row['logins'];
    }
    $hidden=null;
    foreach (array(0,-1,1) as $value) if (isset($visible[$value])) {$hidden=$value;break;}
    $public=$free && !$force;
    return array('used'=>(bool)$uses,'public'=>$public,'logins'=>$public ? '' : $logins,'hidden'=>$hidden);
}

function u5ProvisionPublish($root,$name,$policy,$admins,$generation,$type) {
    global $u5allowbasicauthtoprotectedfilesindirr;
    $dir=u5ProvisionMediaDir($root,$name);
    $state=u5ProvisionDir($root);
    $release=u5ResourceActivityLock($root);
    $lock=u5ProvisionLock($state.'/publish-'.(crc32($name)%67).'.lock',LOCK_EX);
    try {
        if (u5ProvisionGeneration($state)!==$generation) return false;
        $guard=$dir.'/.u5-provision.php';
        $private=is_file($guard) ? u5ProvisionRead($guard)['private'] : (is_file($dir.'/.htpasswd') || is_file($dir.'/.htaccess'));
        $public=$policy['used'] ? $policy['public'] : !$private;
        $assign=array('logins'=> $policy['logins']);
        if ($policy['hidden']!==null) $assign['hidden']=(string)$policy['hidden'];
        $set=array();$diff=array();
        foreach ($assign as $field=>$value) {
            $quoted=u5ProvisionQuote($value);
            $set[]=$field.'='.$quoted;$diff[]='NOT (BINARY '.$field.' <=> BINARY '.$quoted.')';
        }
        if ($type!==null) u5ProvisionUpdate($root,implode(',',$set),'name='.u5ProvisionQuote($name)
            .' AND typ='.u5ProvisionQuote($type).' AND deleted!=1 AND ('.implode(' OR ',$diff).')');
        if (!is_dir($dir)) return true;
        if (!$public) {
            $lines=array();
            foreach (explode(';',str_replace('?','',u5toutf8($policy['logins']))) as $pair) {
                $parts=explode(':',trim($pair),2);
                if (count($parts)!==2 || trim(str_replace('&#0;','',u5flatidn($parts[0])))==='') continue;
                $lines[]=u5flatidn($parts[0]).':{SHA}'.base64_encode(sha1(u5toutf8($parts[1]),true));
            }
            $passwords=$lines ? implode("\r\n",array_unique($lines))."\r\n" : '';
            // For a formerly protected orphan forget stale member credentials.
            // Only current backend administrators retain access.
            u5ProvisionPut($dir.'/.htpasswd',$passwords.$admins);
            u5ProvisionPut($dir.'/.htaccess',u5ProvisionDirectoryRules($dir,($u5allowbasicauthtoprotectedfilesindirr ?? '')==='yes'));
        } else {
            u5ProvisionRemove($dir.'/.htpasswd');
            u5ProvisionRemove($dir.'/.htaccess');
        }
        u5ProvisionRemove($guard);
        return true;
    } finally {fclose($lock);$release();}
}

function u5ProvisionGeneration($dir) {
    $file=$dir.'/generation';
    return is_file($file) ? file_get_contents($file) : '';
}

function u5ProvisionRun($root,$force=false,$foreground=false) {
    global $u5allowbasicauthtoprotectedfilesindirr;
    $dir=u5ProvisionDir($root);
    u5ProvisionDirectory($dir);
    $worker=u5ProvisionLock($dir.'/worker.lock',LOCK_EX|LOCK_NB);
    if (!$worker) return false;
    try {
        $generation=u5ProvisionGeneration($dir);
        $jobs=glob($dir.'/job-*.php') ?: array();
        $cache=$dir.'/references.php';
        $unbuilt=!is_file($cache);
        $partial=$foreground && $unbuilt && !$force;
        $full=$force || ($unbuilt && !$partial);
        if (!$jobs && !$full) return true;
        $sources=array();$targets=array();$identity=$full;
        $blocked=array();$completed=array();
        foreach ($jobs as $file) {
            $owner=$dir.'/owner-'.substr(basename($file),4,-4);
            $probe=u5ProvisionLock($owner,LOCK_SH|LOCK_NB);
            if (!$probe) return false;
            fclose($probe);
            $job=u5ProvisionRead($file);
            if (!empty($job['checkpoint']) && is_file($root.'/fileversions/'.$job['checkpoint'])) {
                foreach ($job['targets'] as $name) $blocked[$name]=true;
                continue;
            }
            $completed[]=$file;
            $full=$full || $job['full'];$identity=$identity || $job['identity'];
            foreach ($job['sources'] as $source) $sources[$source]=true;
            foreach ($job['targets'] as $target) $targets[$target]=true;
        }
        if (!$completed && !$full) return false;
        // Rename has its own short-lived exclusive gate. Concurrent ordinary
        // saves take shared access and never queue behind a worker.
        $accounts=u5ProvisionAdmins();
        $admins='';
        foreach ($accounts as $email=>$pw) $admins.=$email.':'.$pw."\r\n";
        $admins=u5toutf8($admins);
        if ($identity || $full || array_filter(array_keys($sources),function($n){return substr((string)$n,0,1)==='!';})) {
            $release=u5ResourceActivityLock($root);
            try {if (u5ProvisionGeneration($dir)!==$generation) return false;u5ProvisionIntranet($root,$accounts);}
            finally {$release();}
        }
        if ($identity || $full) {
            $versions=$root.'/fileversions';
            $lock=u5ProvisionLock($dir.'/versions.lock',LOCK_EX);
            try {
                if (u5ProvisionGeneration($dir)!==$generation) return false;
                u5ProvisionPut($versions.'/.htpasswd',$admins);
                u5ProvisionPut($versions.'/.htaccess',u5ProvisionDirectoryRules($versions,($u5allowbasicauthtoprotectedfilesindirr ?? '')==='yes'));
                u5ProvisionRemove($versions.'/.u5-provision.php');
            } finally {fclose($lock);}
        }
        $media=u5ProvisionMedia();
        $map=array();
        if (!$full && !$partial) {
            try {$map=u5ProvisionRead($cache);}
            catch (RuntimeException $error) {$full=true;}
        }
        if ($identity && !$full) foreach ($map as $name=>$unused) if (substr((string)$name,0,1)==='!') $sources[$name]=true;
        $changed=u5ProvisionFetchSources($full ? null : array_keys($sources));
        $affected=array();
        foreach ($sources as $name=>$unused) {
            if (isset($map[$name])) $affected[]=$map[$name];
            unset($map[$name]);
            if (isset($changed[$name])) $affected[]=$changed[$name];
        }
        $map=$changed+$map;
        if (!$full) foreach (u5ProvisionCandidates($affected,$media) as $name) $targets[$name]=true;
        $reverse=array();$ambiguous=array();
        foreach ($map as $name=>$source) {
            if ($source['fallback']) $ambiguous[]=(string)$name;
            foreach ($source['names'] as $token) $reverse[$token][]=(string)$name;
        }
        // A medium can itself refer to another medium. Revisit its outgoing
        // dependencies in the same order as the legacy full calculation.
        $queue=array_keys($targets);
        for ($i=0;$i<count($queue);$i++) {
            $name=$queue[$i];
            if (!isset($map[$name])) continue;
            foreach (u5ProvisionCandidates(array($map[$name]),$media) as $other) {
                if (!isset($targets[$other])) {$targets[$other]=true;$queue[]=$other;}
            }
        }
        // A poster can have its own row or just a sibling directory. The
        // video's effective protection also applies to that directory.
        $posters=array();
        foreach ($media as $name=>$type) if ($type==='v') {
            $posters['v'.$name]=$name;
            if (isset($targets['v'.$name])) $targets[$name]=true;
        }
        $selected=array();
        foreach ($media as $name=>$type) {
            if ($full || isset($targets[$name]) || ($identity && is_file(u5ProvisionMediaDir($root,(string)$name).'/.htpasswd'))) {
                $selected[$name]=$type;
                if ($type==='v') $selected['v'.$name]=$media['v'.$name] ?? 'i';
                if (isset($posters[$name])) $selected[$posters[$name]]='v';
            }
        }
        $policies=u5ProvisionPolicies($root,$selected,$reverse,$ambiguous,$partial,$posters);
        foreach ($policies as $name=>$policy) {
            if (isset($blocked[$name])) continue;
            if (u5ProvisionGeneration($dir)!==$generation) return false;
            if (!u5ProvisionPublish($root,(string)$name,$policy,$admins,$generation,$media[$name] ?? null)) return false;
        }
        foreach ($targets as $name=>$unused) {
            if (isset($blocked[$name]) || isset($media[$name]) || isset($posters[$name])) continue;
            $path=u5ProvisionMediaDir($root,(string)$name);
            if (is_file($path.'/.u5-provision.php') && !u5ProvisionPublish($root,(string)$name,u5ProvisionPolicy(array()),$admins,$generation,null)) return false;
        }
        if (u5ProvisionGeneration($dir)!==$generation) return false;
        if (!$partial || $full) u5ProvisionPut($cache,u5ProvisionEncode($map),0600);
        foreach ($completed as $file) {
            u5ProvisionRemove($file);
            u5ProvisionRemove($dir.'/owner-'.substr(basename($file),4,-4));
        }
        return count($completed)===count($jobs);
    } finally {fclose($worker);}
}
