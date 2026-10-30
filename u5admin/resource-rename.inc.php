<?php
/* Ordinary resources: one candidate scan per table, immutable guarded checkpoints. */
require_once __DIR__.'/codeassets-migrate.inc.php';

function u5RenameName($name,$source=false) {
    if (!is_string($name) || strlen($name)<4 || strlen($name)>255 ||
        !preg_match($source ? '/^[a-zA-Z0-9_!-]+$/D' : '/^[a-zA-Z0-9_!]+$/D',$name)) throw new RuntimeException('Invalid resource name.');
    // Existing installations can have longer legacy names; keep their rename path available.
    if (!$source && strlen($name)>20) throw new RuntimeException('An ordinary resource name may contain at most 20 characters.');
    if (!$source && in_array(strtolower($name),array('meta','search','languages','content','right'),true)) throw new RuntimeException('That name is reserved for an HTML template system component.');
    return $name;
}

function u5RenameBase($root,$old,$new) {
    return $root.'/fileversions/resource-rename-'.sha1($old."\0".$new);
}

function u5RenameOptions($updateLinks=true) {
    global $ignoremanualfullpaths,$alsorenamelinksinformdatadatacsv,$lan1na,$lan2na,$lan3na,$lan4na,$lan5na;
    return array('links'=>(bool)$updateLinks,'manual'=>$ignoremanualfullpaths!=='yes',
        'formdata'=>$alsorenamelinksinformdatadatacsv==='yes',
        'template'=>u5CodeTemplateName(),'languages'=>array_values(array_unique(array_filter(array($lan1na,$lan2na,$lan3na,$lan4na,$lan5na),'strlen'))));
}

function u5RenameReplacements($old,$new,$options,$backslashes=false) {
    $map=array('['.$old.']'=>'['.$new.']',':'.$old.']'=>':'.$new.']',
        '['.$old.'?'=>'['.$new.'?',':'.$old.'?'=>':'.$new.'?',
        '[go:]'.$old.'[:go]'=>'[go:]'.$new.'[:go]','[lo:]'.$old.'[:lo]'=>'[lo:]'.$new.'[:lo]',
        'name="thanks" value="'.$old.'"'=>'name="thanks" value="'.$new.'"',
        'name="thanks" value="'.$old.'&'=>'name="thanks" value="'.$new.'&','|'.$old.'|'=>'|'.$new.'|');
    if ($options['manual']) {
        $map['r/'.$old.'/'.$old.'_']='r/'.$new.'/'.$new.'_';
        $map['r\\/'.$old.'\\/'.$old.'_']='r\\/'.$new.'\\/'.$new.'_';
        foreach (array('c','n') as $parameter) foreach (array('"',"'",'&') as $end) $map[$parameter.'='.$old.$end]=$parameter.'='.$new.$end;
        if ($backslashes) foreach (array('c','n') as $parameter) $map[$parameter.'='.$old.'\\']=$parameter.'='.$new.'\\';
    }
    return $map;
}

function u5RenameText($text,$old,$new,$options,$context=array()) {
    if (!is_string($text) || $text==='') return $text;
    $format=strtolower($context['format'] ?? 'html');
    if (!in_array($format,array('html','htm'),true)) {
        $names=array();$paths=array();
        foreach ($options['names'] ?? array($old=>$new) as $from=>$to) {
            $names[strtolower($from)]=$to;
            if ($options['manual']) {
                $paths['r/'.$from.'/'.$from.'_']='r/'.$to.'/'.$to.'_';
                $paths['r\\/'.$from.'\\/'.$from.'_']='r\\/'.$to.'\\/'.$to.'_';
            }
        }
        // Code arrays and CSS selectors are not CMS links. Keep the explicit inclusion syntax.
        $text=u5CodeAssetMarkupNames($text,$names,false);
        if ($options['manual']) $text=u5CodeAssetQueryNames($text,$names,in_array($format,array('js','mjs','json','map','webmanifest','manifest'),true));
        return $paths ? strtr($text,$paths) : $text;
    }
    if (empty($context['split']) && preg_match('~<(?:script|style)\b~i',$text)) {
        $parts=preg_split('~(<(?:script|style)\b[^>]*>.*?</(?:script|style)\s*>)~is',$text,-1,PREG_SPLIT_DELIM_CAPTURE);
        if ($parts===false) throw new RuntimeException('Cannot inspect embedded resource code.');
        foreach ($parts as &$part) {
            $partContext=$context;$partContext['split']=true;
            if (preg_match('~^<(script|style)\b~i',$part,$match)) $partContext['format']=strtolower($match[1])==='script' ? 'js' : 'css';
            $part=u5RenameText($part,$old,$new,$options,$partContext);
        }
        unset($part);return implode('',$parts);
    }
    $map=array();
    foreach ($options['names'] ?? array($old=>$new) as $old=>$new) {
        $map+=u5RenameReplacements($old,$new,$options,!empty($context['content']) || !empty($context['global']));
        if (!empty($context['template'])) {
            $map['{{{'.$old.'}}}']='{{{'.$new.'}}}';$map['[_'.$old.'_]']='[_'.$new.'_]';
        }
        if (!empty($context['content']) && ($context['type'] ?? '')==='f') {
            foreach (array('','c=') as $prefix) if ($text===$prefix.$old) return $prefix.$new;
            if ($text==='c='.$old.'&') return 'c='.$new.'&';
        }
        $split=strrpos($old,'!');
        if ($split!==false && $split>0 && (!empty($context['dynamic']) ||
            (!empty($context['content']) && strpos($context['name'] ?? '',substr($old,0,$split+1))===0))) {
            $suffix=substr($old,$split+1);$newSplit=strrpos($new,'!');
            $map['[_pagename!_]!'.$suffix]='[_pagename!_]!'.($newSplit===false ? $new : substr($new,$newSplit+1));
        }
    }
    // A single simultaneous pass cannot cascade when a new name contains the old name.
    // Skip heading delimiters themselves instead of replacing them with collision-prone sentinels.
    $patterns=array();
    foreach ($map as $search=>$replace) $patterns[]=(preg_match('/^[cn]=/',$search) ? '(?<![a-zA-Z0-9_])' : '').preg_quote($search,'~').(substr($search,-1)===']' ? '(?!\])' : '');
    return u5CodeAssetReplace('~\[{2,4}[^\[\]]*\]{2,4}(*SKIP)(*F)|'.implode('|',$patterns).'~',function($match) use($map) {return $map[$match[0]];},$text);
}

function u5RenameRow($table,$before,$old,$new,$options) {
    $after=$before;
    if ($table==='formdata') {
        $names=$options['names'] ?? array($old=>$new);
        if (isset($names[$before['formname'] ?? ''])) $after['formname']=$names[$before['formname']];
        if ($options['links'] && $options['formdata']) $after['datacsv']=u5RenameText($before['datacsv'],$old,$new,$options,array('global'=>true,'dynamic'=>true));
    } elseif ($table==='loginglobals') {
        if ($options['links']) foreach (range(1,5) as $language) foreach (array('loginintro','loginoutro') as $prefix) {
            $field=$prefix.'_'.$language;
            if (array_key_exists($field,$before)) $after[$field]=u5RenameText($before[$field],$old,$new,$options,array('global'=>true));
        }
    } else {
        if ($options['links']) foreach (range(1,5) as $language) foreach (array('content','title','desc','key') as $prefix) {
            $field=$prefix.'_'.$language;if (!array_key_exists($field,$before)) continue;
            $after[$field]=u5RenameText($before[$field],$old,$new,$options,array('content'=>$prefix==='content',
                'template'=>$prefix==='content' && $language===1 && $before['name']===$options['template'],
                'format'=>$prefix==='content' && strtolower((string)$before['typ'])==='c' ? u5CodeAssetFormat($before['name']) : 'html',
                'name'=>$before['name'],'type'=>strtolower((string)$before['typ'])));
        }
        $names=$options['names'] ?? array($old=>$new);
        if (isset($names[$before['name']]) && (int)$before['deleted']!==1) {
            $after['name']=$names[$before['name']];
            if ($table==='resources' && $before['name'][0]==='!' && $after['name'][0]!=='!') $after['logins']='';
        }
        if ($table==='resources' && $after!==$before) {
            $after['lastmut']=(string)max(time(),(int)$before['lastmut']+1);
            $after['operator']='R '.($before['operator'] ?? '');
            foreach (range(1,5) as $language) if (array_key_exists('search_'.$language,$before)) $after['search_'.$language]=null;
        }
    }
    return $after;
}

function u5RenameCandidates($table,$columns,$old,$options) {
    $terms=array();$fields=array();$needle=u5CodeMigrationValue($old);
    if ($table==='resources' || $table==='resources_log') {
        foreach ($options['names'] ?? array($old=>$old) as $name=>$unused) $terms[]='name='.u5CodeMigrationColumnValue($name,$columns['name']);
        if ($options['links']) foreach (range(1,5) as $language) foreach (array('content','title','desc','key') as $prefix) $fields[]=$prefix.'_'.$language;
    } elseif ($table==='formdata') {
        foreach ($options['names'] ?? array($old=>$old) as $name=>$unused) $terms[]='formname='.u5CodeMigrationColumnValue($name,$columns['formname']);
        if ($options['links'] && $options['formdata']) $fields[]='datacsv';
    } elseif ($options['links']) foreach (range(1,5) as $language) foreach (array('loginintro','loginoutro') as $prefix) $fields[]=$prefix.'_'.$language;
    foreach ($fields as $field) if (isset($columns[$field])) {
        $identifier=u5CodeMigrationIdentifier($field);
        $terms[]='INSTR(CAST('.$identifier.' AS BINARY),'.$needle.')>0';
        // Relative suffix references do not contain the complete old resource name.
        if (strpos($old,'!')>0 && ($field==='datacsv' || strpos($field,'content_')===0)) $terms[]='INSTR(CAST('.$identifier.' AS BINARY),'.u5CodeMigrationValue('[_pagename!_]!').')>0';
    }
    return $terms ? ' WHERE ('.implode(' OR ',$terms).')' : ' WHERE 0';
}

function u5RenameFiles($root,$old,$new,$languages,$token) {
    $source=$root.'/r/'.$old;$target=$root.'/r/'.$new;
    if (is_link($source) || is_link($target)) throw new RuntimeException('A linked resource directory must be reconciled before renaming.');
    if (file_exists($source) && !is_dir($source)) throw new RuntimeException('The resource path is not a directory.');
    $files=array();$links=array();
    if (is_dir($source)) {
        $entries=scandir($source);if ($entries===false) throw new RuntimeException('Cannot read the resource directory.');
        $suffixes=array('[1-9][0-9]{2}');
        foreach ($languages as $language) $suffixes[]=preg_quote($language,'~');
        $pattern='~^'.preg_quote($old,'~').'_(?:'.implode('|',$suffixes).')\.[^.]+$~D';
        foreach ($entries as $file) if (preg_match($pattern,$file) && (is_file($source.'/'.$file) || is_link($source.'/'.$file))) {
            $destination=$new.substr($file,strlen($old));
            if (file_exists($source.'/'.$destination) || is_link($source.'/'.$destination)) throw new RuntimeException('A different file already occupies '.$destination.'. No resource rows were changed.');
            $stat=lstat($source.'/'.$file);if ($stat===false) throw new RuntimeException('Cannot inspect resource file '.$file);
            $files[$file]=array('to'=>$destination,'link'=>is_link($source.'/'.$file) ? readlink($source.'/'.$file) : null,
                'size'=>$stat['size'],'mtime'=>$stat['mtime'],
                // Rename preserves the inode's bytes; never read multi-GB media just to move its name.
                // Coordinated CMS writers are excluded by the mutation lock throughout the operation.
                'hash'=>!is_link($source.'/'.$file) && $stat['size']<=1024*1024 ? hash_file('sha256',$source.'/'.$file) : null);
            if ($files[$file]['hash']===false) throw new RuntimeException('Cannot inspect resource file '.$file);
        }
        $names=array();foreach ($files as $file=>$change) {$names[$file]=$change['to'];$names['./'.$file]='./'.$change['to'];}
        foreach ($entries as $file) if (is_link($source.'/'.$file)) {
            $before=readlink($source.'/'.$file);$after=$names[$before] ?? $before;
            if (strpos($before,$source.'/')===0) {
                $relative=substr($before,strlen($source)+1);$after=$target.'/'.($names[$relative] ?? $relative);
            } elseif (strpos($before,'../'.$old.'/')===0) {
                $relative=substr($before,strlen('../'.$old.'/'));$after='../'.$new.'/'.($names[$relative] ?? $relative);
            }
            if ($before!==$after) {
                $links[$files[$file]['to'] ?? $file]=array('before'=>$before,'after'=>$after);
                if (isset($files[$file])) $files[$file]['afterLink']=$after;
            }
        }
    }
    $protection=null;
    if (is_link($source.'/.htaccess')) throw new RuntimeException('A linked access-control file must be reconciled before renaming.');
    if (is_file($source.'/.htaccess')) {
        $before=file_get_contents($source.'/.htaccess');if (!is_string($before)) throw new RuntimeException('Cannot read the resource access-control file.');
        $protection=array('before'=>$before,'after'=>str_replace('/r/'.$old.'/.htpasswd','/r/'.$new.'/.htpasswd',$before));
    }
    return array('directory'=>is_dir($source),'archive'=>file_exists($target) ? $new.'.before-rename-'.$token : null,'files'=>$files,'links'=>$links,'token'=>$token,'protection'=>$protection);
}

function u5RenameBuildPlan($root,$old,$new,$options) {
    $source=u5CodeMigrationRows('SELECT typ,deleted FROM resources WHERE BINARY name='.u5CodeMigrationValue($old).' AND deleted!=1');
    if (!$source) throw new RuntimeException('The resource to rename does not exist.');
    if (strtolower((string)$source[0]['typ'])==='c') throw new RuntimeException('Code resources require the typed code rename.');
    foreach ($source as $row) if (strcasecmp((string)$row['typ'],(string)$source[0]['typ'])!==0) throw new RuntimeException('The resource has incompatible active/archive types. Reconcile them before renaming.');
    $options['names']=array($old=>$new);$companionFiles=null;
    if (strtolower((string)$source[0]['typ'])==='v') {
        $companion=u5CodeMigrationRows('SELECT typ FROM resources WHERE name='.u5CodeMigrationValue('v'.$old).' AND deleted!=1');
        foreach ($companion as $row) if (!in_array(strtolower((string)$row['typ']),array('i','f'),true)) throw new RuntimeException('The video poster name is occupied by a different kind of resource.');
        $options['names']['v'.$old]='v'.$new;
        $companionFiles=u5RenameFiles($root,'v'.$old,'v'.$new,$options['languages'],bin2hex(random_bytes(8)));
    }
    $targetTerms=array();foreach ($options['names'] as $targetName) $targetTerms[]='name='.u5CodeMigrationValue($targetName);
    $target=u5CodeMigrationRows('SELECT CAST(name AS BINARY) AS name,deleted FROM resources WHERE '.implode(' OR ',$targetTerms));
    foreach ($target as $row) if ((int)$row['deleted']!==1) throw new RuntimeException('The target resource already exists.');
    $token=bin2hex(random_bytes(8));$files=u5RenameFiles($root,$old,$new,$options['languages'],$token);
    $changes=u5CodeMigrationStartChanges();
    try {
        foreach (array('resources','resources_log','loginglobals','formdata') as $table) {
            $columns=u5CodeMigrationColumns($table);$tail=u5RenameCandidates($table,$columns,$old,$options);
            // Deleted target tombstones are retained in the guarded backup before safe name reuse.
            if ($table==='resources' && $target) $tail=substr($tail,0,-1).' OR '.implode(' OR ',$targetTerms).')';
            $targetNames=array_column($target,'name');
            u5CodeMigrationEachRow($table,$columns,function($before) use($root,$table,$columns,$old,$new,$options,$targetNames,&$changes) {
                if ($table==='resources' && in_array($before['name'],$targetNames,true) && (int)$before['deleted']===1) {
                    u5CodeMigrationQueueChange($root,$changes,array('table'=>$table,'before'=>$before,'after'=>$before,'delete'=>true));return;
                }
                $after=u5RenameRow($table,$before,$old,$new,$options);
                u5CodeMigrationValidateRow($table,$before,$after,$columns);
                if ($after!==$before) u5CodeMigrationQueueChange($root,$changes,array('table'=>$table,'before'=>$before,'after'=>$after));
            },$tail);
        }
        $changes=u5CodeMigrationFinishChanges($root,$changes);
        $templates=array();
        if ($options['links']) foreach (glob($root.'/r/*.tpl') as $path) {
            if (is_link($path)) throw new RuntimeException('A linked reset template must be reconciled before renaming.');
            $before=file_get_contents($path);if (!is_string($before)) throw new RuntimeException('Cannot read reset template.');
            $after=u5RenameText($before,$old,$new,$options,array('content'=>true,'template'=>true,'format'=>u5CodeAssetFormat(basename($path,'.tpl'))));
            if ($before!==$after) $templates[basename($path)]=array('before'=>$before,'after'=>$after);
        }
        return array('version'=>'12.12.1','old'=>$old,'new'=>$new,'typ'=>$source[0]['typ'],'deleted'=>$source[0]['deleted'],'options'=>$options,
            'updates'=>$changes,'files'=>$files,'companionFiles'=>$companionFiles,'templates'=>$templates,'created'=>time());
    } catch (Throwable $error) {u5CodeMigrationAbortChanges($root,$changes);throw $error;}
}

function u5RenameFileMatches($path,$file,$finished=false) {
    if ($file['link']!==null) return is_link($path) && in_array(readlink($path),$finished ? array($file['link'],$file['afterLink'] ?? $file['link']) : array($file['link']),true);
    clearstatcache(true,$path);
    return !is_link($path) && is_file($path) && filesize($path)===$file['size'] &&
        ($file['hash']!==null ? hash_file('sha256',$path)===$file['hash'] : filemtime($path)===$file['mtime']);
}

function u5RenamePublishFiles($root,$plan) {
    $old=$plan['old'];$new=$plan['new'];$files=$plan['files'];
    $source=$root.'/r/'.$old;$target=$root.'/r/'.$new;
    $marker='/.u5rename-'.$files['token'].'.php';$body="<?php exit; ?>\n".$files['token'];
    if (is_link($source) || is_link($target)) throw new RuntimeException('A resource directory became a symbolic link.');
    if (is_dir($source) || !$files['directory']) {
        if ($files['directory']) foreach ($files['files'] as $file=>$change) if (!u5RenameFileMatches($source.'/'.$file,$change)) throw new RuntimeException('A resource file changed before rename: '.$file);
        if (file_exists($target)) {
            $archive=$files['archive'] ? $root.'/r/'.$files['archive'] : null;
            if ($archive===null || file_exists($archive) || !@rename($target,$archive)) throw new RuntimeException('Cannot preserve the pre-existing target directory.');
            u5CodeMigrationCheckpoint('rename:archive');
        }
        if (!$files['directory']) return;
        u5CodeAssetAtomicWrite($source.$marker,$body);
        if (!@rename($source,$target)) throw new RuntimeException('Cannot move the resource directory.');
        u5CodeMigrationCheckpoint('rename:directory');
    }
    if (!is_dir($target) || @file_get_contents($target.$marker)!==$body) throw new RuntimeException('Cannot identify the renamed directory; checkpoint retained.');
    foreach ($files['files'] as $file=>$change) {
        $from=$target.'/'.$file;$to=$target.'/'.$change['to'];
        if (file_exists($from) || is_link($from)) {
            if (file_exists($to) || is_link($to) || !u5RenameFileMatches($from,$change)) throw new RuntimeException('A resource file changed or its destination is occupied: '.$file);
            if (!@rename($from,$to)) throw new RuntimeException('Cannot rename resource file '.$file);
        } elseif (!u5RenameFileMatches($to,$change,true)) throw new RuntimeException('A renamed resource file is missing or changed: '.$file);
        u5CodeMigrationCheckpoint('rename:file');
    }
    foreach ($files['links'] as $file=>$change) {
        $path=$target.'/'.$file;$current=is_link($path) ? readlink($path) : null;
        if ($current===$change['after']) continue;
        if ($current!==$change['before']) throw new RuntimeException('A resource symbolic link changed during rename.');
        $temporary=$target.'/.u5link-'.bin2hex(random_bytes(8));
        if (!@symlink($change['after'],$temporary)) throw new RuntimeException('Cannot prepare a renamed symbolic link.');
        if (!@rename($temporary,$path)) {@unlink($temporary);throw new RuntimeException('Cannot update a resource symbolic link.');}
    }
    if ($files['protection']!==null) {
        $path=$target.'/.htaccess';$before=@file_get_contents($path);$change=$files['protection'];
        if (is_link($path) || ($before!==$change['before'] && $before!==$change['after'])) throw new RuntimeException('The resource access-control file changed during rename.');
        if ($before!==$change['after']) u5CodeAssetAtomicWrite($path,$change['after']);
    }
}

function u5RenameApplyChanges($root,$journal) {
    $schema=array();
    // Remove preserved target tombstones first so resource PK order never matters.
    foreach (array(true,false) as $deletions) u5CodeMigrationEachChange($root,$journal,function($change,$index) use(&$schema,$deletions) {
        if (!empty($change['delete'])!==$deletions) return;
        $table=$change['table'];if (!isset($schema[$table])) $schema[$table]=u5CodeMigrationColumns($table);
        if ($deletions) u5CodeMigrationQuery('DELETE FROM '.u5CodeMigrationIdentifier($table).' WHERE '.u5CodeMigrationWhere($change['before'],$schema[$table]));
        else u5CodeMigrationApplyRow($change,$schema[$table],$table==='resources' ? array('search_1','search_2','search_3','search_4','search_5') : array());
        u5CodeMigrationCheckpoint('rename:row:'.$index);
    });
}

function u5ResourceRename($root,$old,$new,$options=null) {
    u5RenameName($old,true);u5RenameName($new);
    if ($old===$new || strcasecmp($old,$new)===0) throw new RuntimeException('The new resource name must be different.');
    if ($options===null) $options=u5RenameOptions();
    $base=u5RenameBase($root,$old,$new);$pending=$base.'.pending.php';$release=u5CodeMigrationLock($root,$pending);
    try {
        if (is_file($pending)) {
            $plan=u5CodeMigrationReadGuarded($pending);
            if (($plan['version'] ?? '')!=='12.12.1' || $plan['old']!==$old || $plan['new']!==$new) throw new RuntimeException('Invalid resource rename checkpoint.');
        } else {
            if (is_file($base.'.done.php') && !u5CodeMigrationRows('SELECT name FROM resources WHERE BINARY name='.u5CodeMigrationValue($old).' AND deleted!=1')) return u5CodeMigrationReadGuarded($base.'.done.php');
            $plan=u5RenameBuildPlan($root,$old,$new,$options);
            u5CodeAssetAtomicWrite($pending,u5CodeMigrationEncodeJournal($plan));
        }
        u5CodeMigrationValidateChanges($root,$plan['updates']);
        u5CodeMigrationCheckpoint('rename:planned');
        u5RenamePublishFiles($root,$plan);
        if ($plan['companionFiles']!==null) u5RenamePublishFiles($root,array('old'=>'v'.$old,'new'=>'v'.$new,'files'=>$plan['companionFiles']));
        foreach ($plan['templates'] as $file=>$change) {
            $path=$root.'/r/'.$file;$body=@file_get_contents($path);
            if ($body===$change['after']) continue;
            if ($body!==$change['before'] || is_link($path)) throw new RuntimeException('A reset template changed during rename.');
            u5CodeAssetAtomicWrite($path,$change['after']);
        }
        u5CodeMigrationCheckpoint('rename:templates');
        u5RenameApplyChanges($root,$plan['updates']);
        u5CodeMigrationCheckpoint('rename:database');
        u5CodeAssetExport($root);
        // A name change can alter rendered dependants which do not contain a rewritten link.
        u5CodeAssetAtomicWrite($root.'/fileversions/search-index-pending.txt',(string)time());
        u5CodeAssetAtomicWrite($root.'/fileversions/lastsave.txt',(string)time());
        u5CodeMigrationCheckpoint('rename:published');
        $completed=u5CodeMigrationEncodeJournal($plan+array('completed'=>time()));
        u5CodeAssetAtomicWrite($base.'-'.$plan['files']['token'].'.done.php',$completed);
        u5CodeAssetAtomicWrite($base.'.done.php',$completed);
        u5CodeMigrationCheckpoint('rename:complete');
        if (!@unlink($pending)) throw new RuntimeException('Cannot finalize the resource rename checkpoint.');
        if ($plan['files']['directory']) @unlink($root.'/r/'.$new.'/.u5rename-'.$plan['files']['token'].'.php');
        if ($plan['companionFiles']!==null && $plan['companionFiles']['directory']) @unlink($root.'/r/v'.$new.'/.u5rename-'.$plan['companionFiles']['token'].'.php');
        return $plan;
    } finally {$release();}
}
