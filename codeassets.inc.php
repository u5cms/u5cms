<?php
/* Code resources are static assets. The LAST exclamation mark is the suffix separator. */
function u5CodeAssetDefaults() {
    return array(
        'cssauthuser'=>'authuser!css', 'cssbase'=>'base!css', 'cssform'=>'form!css',
        'csshot'=>'hot!css', 'csslayout'=>'layout!css', 'cssnavleft'=>'navleft!css',
        'cssnavleftsubtop'=>'navleftsubtop!css', 'cssnavtop'=>'navtop!css',
        'cssstyle'=>'style!css', 'csstable'=>'table!css', 'cssupload'=>'upload!css',
        'csspworder'=>'pworder!css', 'htmltemplate'=>'htmltemplate!html',
        'jsmobilespecific'=>'mobile!js', 'jsmobilegeneral'=>'mobilebase!js'
    );
}

function u5CodeAssetFilename($name) {
    $split=strrpos($name, '!');
    if ($split===false) throw new RuntimeException('Code object needs a file extension after its last !: '.$name);
    $base=substr($name, 0, $split);
    $extension=strtolower(substr($name, $split+1));
    if ($base==='' || strlen($base)>220 || !preg_match('/^[a-zA-Z0-9_!-]+$/D', $base)) {
        throw new RuntimeException('Invalid code object name: '.$name);
    }
    // Code editors may publish client-side code, but must never create server-side executables.
    $static=array('css','js','mjs','html','htm','txt','json','map','xml','svg','csv',
        'webmanifest','manifest','rss','atom','ics','md','yaml','yml');
    if (!in_array($extension, $static, true)) throw new RuntimeException('Unsupported static file extension: '.$extension);
    return $base.'.'.$extension;
}

function u5CodeAssetIsTyped($name) {
    try { u5CodeAssetFilename($name); return true; }
    catch (RuntimeException $error) { return false; }
}

function u5CodeAssetLegacyFilename($name) {
    if ($name==='' || strlen($name)>245 || strpos($name,'/')!==false || strpos($name,'\\')!==false || strpos($name,"\0")!==false) return null;
    return $name.'.css';
}

function u5CodeAssetGuess($name, $source, $uses='') {
    $defaults=u5CodeAssetDefaults();
    if (isset($defaults[$name])) return array($defaults[$name], 'standard object');
    if (u5CodeAssetIsTyped($name)) return array($name, 'explicit suffix');
    $body=preg_replace('~/\*.*?\*/|<!--.*?-->|^[ \t]*//[^\r\n]*~ms', '', $source);
    $body=ltrim($body);
    $extension='css'; $reason='CSS fallback for an ambiguous legacy code object';
    if (preg_match('~^\s*<(?:!doctype|html|head|body|div|script|style|meta|link)\b~i', $body)) {
        $extension='html'; $reason='HTML markup';
    } elseif (preg_match('~<(?:script)\b[^>]*\bsrc\s*=\s*["\'][^"\']*r/'.preg_quote($name,'~').'\.css(?:[?"\'])|(?:loadScript|getScript)\s*\(\s*["\'][^"\']*r/'.preg_quote($name,'~').'\.css~i', $uses)) {
        $extension='js'; $reason='loaded as a script';
    } elseif (preg_match('~(?:^|[;\r\n])\s*(?:var\s|let\s|const\s|function\s|\(?function\s*\(|(?:window|document|jQuery)\s*[.(]|\$\s*\()|^\s*\(?\s*(?:\([^)]*\)|[a-zA-Z_$]\w*)\s*=>|^\s*if\s*\(~', $body)) {
        $extension='js'; $reason='JavaScript syntax';
    } elseif ($body!=='' && ($body[0]==='{' || $body[0]==='[') && json_decode($body, true)!==null) {
        $extension='json'; $reason='JSON content';
    } elseif (preg_match('~^<\?xml\b|^<svg\b~i', $body)) {
        $extension=stripos($body,'<svg')!==false ? 'svg' : 'xml'; $reason='XML markup';
    } elseif (preg_match('~(?:^|[}\r\n])\s*(?:[.#@][\w-]|[a-z][\w\s,.#:\[\]="\'()>+\~-]*\{)~i', $body)) {
        $reason='CSS rules';
    }
    $base=preg_replace('/[^a-zA-Z0-9_!-]/', '', $name);
    if ($base==='') $base='asset'.substr(sha1($name),0,10);
    if (strlen($base)>210) $base=substr($base,0,195).'!'.substr(sha1($name),0,10);
    return array($base.'!'.$extension, $reason);
}

function u5CodeAssetPlanNames($resources, $uses='') {
    $groups=array(); $reserved=array(); $files=array(); $map=array(); $notes=array();
    foreach ($resources as $row) {
        $reserved[strtolower($row['name'])]=true;
        if (strtolower((string)$row['typ'])!=='c') continue;
        $name=$row['name'];
        $rank=(int)$row['deleted']===0 ? 0 : ((int)$row['deleted']===2 ? 1 : 2);
        if (!isset($groups[$name]) || $rank<$groups[$name]['rank']) $groups[$name]=array('row'=>$row,'rank'=>$rank);
        if (u5CodeAssetIsTyped($name)) $files[strtolower(u5CodeAssetFilename($name))]=$name;
    }
    ksort($groups, SORT_STRING);
    foreach ($groups as $name=>$group) {
        list($target,$reason)=u5CodeAssetGuess($name,(string)($group['row']['content_1'] ?? ''),$uses);
        $wanted=$target; $suffix=0;
        while (($target!==$name && isset($reserved[strtolower($target)])) ||
               (isset($files[strtolower(u5CodeAssetFilename($target))]) && $files[strtolower(u5CodeAssetFilename($target))]!==$name)) {
            $split=strrpos($wanted,'!');
            $target=substr($wanted,0,$split).'!migrated'.substr(sha1($name),0,8).($suffix ? $suffix : '').substr($wanted,$split);
            $suffix++;
        }
        $reserved[strtolower($target)]=true;
        $files[strtolower(u5CodeAssetFilename($target))]=$name;
        $map[$name]=$target;
        $notes[]=array('from'=>$name,'to'=>$target,'reason'=>$reason.($target!==$wanted ? '; collision preserved under a distinct name' : ''));
    }
    return array('map'=>$map,'notes'=>$notes);
}

function u5CodeAssetReferences($text, $map=array(), $depth=0) {
    if (!is_string($text) || $text==='' || $depth>8) return $text;
    // Serialized strings use byte counts. Rewrite their payloads, never their raw representation.
    if (preg_match('/^(?:a:\d+:\{|O:\d+:|s:\d+:"|C:\d+:)/', $text)) {
        if (strpos($text,'C:')===0) return $text;
        $out=''; $cursor=0;
        while (preg_match('/s:(\d+):"/', $text, $hit, PREG_OFFSET_CAPTURE, $cursor)) {
            $start=$hit[0][1]; $dataStart=$start+strlen($hit[0][0]); $length=(int)$hit[1][0];
            if (substr($text,$dataStart+$length,2)!=='";') return $text;
            $body=substr($text,$dataStart,$length);
            $new=u5CodeAssetReferences($body,$map,$depth+1);
            $out.=substr($text,$cursor,$start-$cursor).'s:'.strlen($new).':"'.$new.'";';
            $cursor=$dataStart+$length+2;
        }
        return $out.substr($text,$cursor);
    }
    // Older copied markup may already contain the renderer's media wrapper.
    // Only flat static code exports are unwrapped; protected resource folders are untouched.
    $text=preg_replace('~(?<![a-zA-Z0-9_.-])f{1,4}\.php\?f=(r/[^/\s"\'<>?#]+\.(?:css|js|mjs|html?|txt|json|map|xml|svg|webmanifest|manifest|csv|rss|atom|ics|md|ya?ml))(?=[?&#"\'\s<>)]|$)~i','$1',$text);
    $allNames=array_merge(u5CodeAssetDefaults(),$map);
    $paths=array();$names=array();
    foreach ($allNames as $old=>$new) {
        $oldFile=u5CodeAssetLegacyFilename($old);
        if ($oldFile===null) continue;
        // Before 12.11.0 even an object already named example!js exported as example!js.css.
        $candidates=array($oldFile);
        if (strpos($old,'!')!==false && u5CodeAssetIsTyped($old)) $candidates[]=u5CodeAssetFilename($old);
        $present=strpos($text,$old)!==false;
        foreach ($candidates as $candidate) if (strpos($text,$candidate)!==false) $present=true;
        if (!$present) continue;
        $target=u5CodeAssetFilename($new);
        foreach ($candidates as $index=>$candidate) if ($candidate!==$target && ($index===0 || !isset($paths[$candidate]))) $paths[$candidate]=$target;
        if ($old!==$new) { $names[$old]=$new; $paths[$old.'.tpl']=$new.'.tpl'; }
    }
    if (!$names && !$paths) return $text;
    // Sharing a site-specific mobile script with another CMS domain remains supported.
    // Only the former yuba.ch code CDN is localized; links to its documentation are untouched.
    $text=preg_replace_callback('~(?:https?:)?//(?:www\.)?yuba\.ch/(?:[^\s"\'<>?#]*/)?r/([^/\s"\'<>?#]+)(?=[?\#"\'\s<>)]|$)~i', function($m) use ($paths) {
        return isset($paths[$m[1]]) ? 'r/'.$m[1] : $m[0];
    },$text);
    $text=preg_replace_callback('~(?<![a-zA-Z0-9_!.-])r/([^/\s"\'<>?#)]+)(?=[?\#"\'\s<>)]|$)~', function($m) use($paths) {
        return isset($paths[$m[1]]) ? 'r/'.$paths[$m[1]] : $m[0];
    },$text);
    // Stylesheets import siblings relative to r/, without an r/ prefix.
    $text=preg_replace_callback('~(?<![a-zA-Z0-9_/!.-])([^/\s"\'<>?#(]+\.(?:css|tpl))(?=[?\#"\'\s<>)]|$)~',function($m) use($paths) {
        return $paths[$m[1]] ?? $m[0];
    },$text);
    // JSON commonly escapes slashes; preserve that representation.
    $escaped=array();
    foreach ($paths as $old=>$new) {
        foreach (array('https://yuba.ch/r/','http://yuba.ch/r/','//yuba.ch/r/','https://www.yuba.ch/r/','http://www.yuba.ch/r/','//www.yuba.ch/r/') as $cdn) {
            $escaped[str_replace('/','\\/',$cdn.$old)]=str_replace('/','\\/','r/'.$new);
        }
        $escaped['r\\/'.$old]='r\\/'.$new;
    }
    // A single replacement pass avoids cascading when a new path is another object's old path.
    if ($escaped) {
        $keys=array(); foreach ($escaped as $key=>$value) $keys[]=preg_quote($key,'~');
        $pattern='~(?:'.implode('|',$keys).')(?=[?&#"\'\s<>)]|$|'.preg_quote('\\','~').'["\'])~';
        $text=preg_replace_callback($pattern,function($match) use($escaped) { return $escaped[$match[0]]; },$text);
    }
    foreach ($names as $old=>$new) {
        if ($old===$new) continue;
        $quoted=preg_quote($old,'~');
        $text=preg_replace_callback('~(?:(?<=[\[:])'.$quoted.'(?=[\]?])|(?<=\{\{\{)'.$quoted.'(?=\}\}\})|(?<=["\'])'.$quoted.'(?=["\'])|(?<=[?&])(?:name|c|n)='.$quoted.'(?=[&\#"\'\s]|$))~', function($m) use($old,$new) {
            return substr($m[0],0,strlen($m[0])-strlen($old)).$new;
        },$text);
    }
    return $text;
}

function u5CodeAssetVersionURL($url,$stamp) {
    if (!preg_match('~\.(?:css|js|mjs|png|jpe?g|gif|webp|avif|svg|ttf|woff2?)(?:[?#]|$)~i',$url) || preg_match('~^(?:data|javascript):~i',$url)) return $url;
    $parts=explode('#',$url,2); $fragment=isset($parts[1]) ? '#'.$parts[1] : '';
    $parts=explode('?',$parts[0],2); $query=isset($parts[1]) ? $parts[1] : '';
    if (preg_match('/^\d+$/D',$query)) $query='';
    $query=preg_replace('/(?:^|&)u5v=[^&]*/','',$query);
    $query=trim($query,'&');
    return $parts[0].'?'.($query!=='' ? $query.'&' : '').'u5v='.$stamp.$fragment;
}

function u5CodeAssetOutput($source,$stamp) {
    $source=preg_replace_callback('~(\b(?:src|href)\s*=\s*)(["\'])(.*?)\2~is',function($m) use($stamp) {
        return $m[1].$m[2].u5CodeAssetVersionURL($m[3],$stamp).$m[2];
    },$source);
    $source=preg_replace_callback('~(\burl\(\s*)(["\']?)([^\s\)"\']+)\2(\s*\))~i',function($m) use($stamp) {
        return $m[1].$m[2].u5CodeAssetVersionURL($m[3],$stamp).$m[2].$m[4];
    },$source);
    return preg_replace_callback('~((?:loadScript|getScript)\s*\(\s*)(["\'])(.*?)\2~s',function($m) use($stamp) {
        return $m[1].$m[2].u5CodeAssetVersionURL($m[3],$stamp).$m[2];
    },$source);
}

function u5CodeAssetAtomicWrite($path,$data) {
    $temporary=@tempnam(dirname($path),'.u5asset-');
    if ($temporary===false) throw new RuntimeException('Cannot create asset in '.dirname($path));
    try {
        if (file_put_contents($temporary,$data)!==strlen($data)) throw new RuntimeException('Cannot write '.$path);
        @chmod($temporary,is_file($path) ? (fileperms($path)&0777) : 0644);
        if (!@rename($temporary,$path)) throw new RuntimeException('Cannot publish '.$path);
    } finally { if (is_file($temporary)) @unlink($temporary); }
}

function u5CodeAssetUtf8($source) {
    // The CMS connection/editor uses Windows-1252. No entity decoding is performed.
    $windows=array(0x20ac,0x81,0x201a,0x192,0x201e,0x2026,0x2020,0x2021,
        0x2c6,0x2030,0x160,0x2039,0x152,0x8d,0x17d,0x8f,
        0x90,0x2018,0x2019,0x201c,0x201d,0x2022,0x2013,0x2014,
        0x2dc,0x2122,0x161,0x203a,0x153,0x9d,0x17e,0x178);
    $out='';
    for ($i=0,$length=strlen($source);$i<$length;$i++) {
        $point=ord($source[$i]);
        if ($point<128) { $out.=$source[$i]; continue; }
        if ($point<160) $point=$windows[$point-128];
        if ($point<2048) $out.=chr(0xc0|($point>>6)).chr(0x80|($point&63));
        else $out.=chr(0xe0|($point>>12)).chr(0x80|(($point>>6)&63)).chr(0x80|($point&63));
    }
    return $out;
}

function u5CodeAssetRowsToFiles($rows,$stamp) {
    $chosen=array();
    foreach ($rows as $row) {
        if (strtolower((string)($row['typ'] ?? ''))!=='c' || (int)$row['deleted']===1) continue;
        $file=u5CodeAssetFilename($row['name']);
        $rank=(int)$row['deleted']===0 ? 0 : 1;
        if (!isset($chosen[$file]) || $rank<$chosen[$file]['rank']) $chosen[$file]=array('row'=>$row,'rank'=>$rank);
    }
    $files=array();
    foreach ($chosen as $file=>$value) {
        $data=u5CodeAssetOutput((string)$value['row']['content_1'],$stamp);
        $extension=strtolower(pathinfo($file,PATHINFO_EXTENSION));
        if (in_array($extension,array('js','mjs','css'),true)) $data="\xEF\xBB\xBF".u5CodeAssetUtf8($data);
        elseif (in_array($extension,array('json','map','webmanifest','manifest'),true)) $data=u5CodeAssetUtf8($data);
        $files[$file]=$data;
    }
    return $files;
}

function u5CodeAssetExport($root) {
    $result=mysql_query("SELECT * FROM resources WHERE typ='c' AND deleted!=1");
    if ($result===false) throw new RuntimeException('Cannot read code objects for export.');
    $rows=array(); while ($row=mysql_fetch_array($result,MYSQL_ASSOC)) $rows[]=$row;
    foreach (u5CodeAssetRowsToFiles($rows,time()) as $file=>$data) u5CodeAssetAtomicWrite($root.'/r/'.$file,$data);
}

function u5CodeTemplateName($root=null) {
    if ($root===null) $root=__DIR__;
    $marker=$root.'/fileversions/codeassets-12.11.0.done.php';
    if (is_file($marker)) {
        $state=include $marker;
        if (is_array($state) && isset($state['template']) && u5CodeAssetIsTyped($state['template'])) return $state['template'];
    }
    return 'htmltemplate!html';
}

function u5CodeAssetName($legacy,$root=null) {
    if ($root===null) $root=__DIR__;
    $marker=$root.'/fileversions/codeassets-12.11.0.done.php';
    if (is_file($marker)) {
        $state=include $marker;
        if (isset($state['map'][$legacy])) return $state['map'][$legacy];
    }
    $defaults=u5CodeAssetDefaults();
    return $defaults[$legacy] ?? $legacy;
}

function u5CodeTemplatePath($root=null) {
    if ($root===null) $root=__DIR__;
    if (!is_file($root.'/fileversions/codeassets-12.11.0.done.php') && is_file($root.'/r/htmltemplate.css')) return $root.'/r/htmltemplate.css';
    return $root.'/r/'.u5CodeAssetFilename(u5CodeTemplateName($root));
}

function u5CodeAssetPublicPath($name,$prefix='r/',$root=null) {
    if ($root===null) $root=__DIR__;
    $marker=$root.'/fileversions/codeassets-12.11.0.done.php';
    if (is_file($marker)) {
        $state=include $marker;
        if (isset($state['map'][$name])) $name=$state['map'][$name];
    } elseif (!u5CodeAssetIsTyped($name) && is_file($root.'/r/'.$name.'.css')) {
        return $prefix.$name.'.css';
    }
    $defaults=u5CodeAssetDefaults();
    return $prefix.u5CodeAssetFilename($defaults[$name] ?? $name);
}

function u5CodeAssetProxyMediaSources($html) {
    return preg_replace_callback('~(\bsrc\s*=\s*)(["\'])(r/[^"\']*)\2~i',function($match) {
        $path=explode('?',explode('#',$match[3],2)[0],2)[0];
        // Public C exports live directly in r/. f.php expects r/object/filename.
        if (strpos($path,'r/')!==0 || strpos(substr($path,2),'/')===false) return $match[0];
        return $match[1].$match[2].'f.php?f='.$match[3].$match[2];
    },$html);
}
