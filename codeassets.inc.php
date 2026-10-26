<?php
/* Code resources are static assets. The LAST exclamation mark is the suffix separator. */
function u5CodeMigrationLock($root) {
    static $held=array();
    $key=realpath($root) ?: $root;
    if (isset($held[$key])) $held[$key]['depth']++;
    else {
        $handle=@fopen($root.'/fileversions/codeassets-12.11.0.lock.php','c+');
        if (!$handle) throw new RuntimeException('Cannot open the code asset update lock.');
        if (!flock($handle,LOCK_EX|LOCK_NB)) {
            fclose($handle);
            throw new RuntimeException('Another code asset update is already running. Retry the backend when it has finished.');
        }
        if (fstat($handle)['size']===0) fwrite($handle,"<?php exit; ?>\n");
        $held[$key]=array('handle'=>$handle,'depth'=>1);
    }
    return function() use(&$held,$key) {
        if (!isset($held[$key])) return;
        $held[$key]['depth']--;
        if ($held[$key]['depth']===0) { flock($held[$key]['handle'],LOCK_UN);fclose($held[$key]['handle']);unset($held[$key]); }
    };
}

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
    if (isset($defaults[strtolower($name)])) return array($defaults[strtolower($name)], 'standard object');
    if (u5CodeAssetIsTyped($name)) return array($name, 'explicit suffix');
    $body=preg_replace('~/\*.*?\*/|<!--.*?-->|^[ \t]*//[^\r\n]*~ms', '', $source);
    if ($body===null) $body=$source;
    $body=ltrim(substr($body,0,3)==="\xEF\xBB\xBF" ? substr($body,3) : $body);
    $extension='css'; $reason='CSS fallback for an ambiguous legacy code object';
    if (preg_match('~^<\?xml\b|^<svg\b~i', $body)) {
        $extension=stripos($body,'<svg')!==false ? 'svg' : 'xml'; $reason='XML markup';
    } elseif (preg_match('~^\s*<(?:!doctype\b|[a-z][a-z0-9:-]*(?:\s|/?>))~i', $body)) {
        $extension='html'; $reason='HTML markup';
    } elseif (preg_match('~<(?:script)\b[^>]*\bsrc\s*=\s*["\'][^"\']*r/'.preg_quote($name,'~').'\.css(?:[?"\'])|(?:loadScript|getScript)\s*\(\s*["\'][^"\']*r/'.preg_quote($name,'~').'\.css~i', $uses)) {
        $extension='js'; $reason='loaded as a script';
    } elseif (preg_match('~(?:^|[;\r\n])\s*(?:var\s|let\s|const\s|function\s|class\s|(?:export|import|async|await|throw)\b|try\s*\{|(?:for|while|switch|setTimeout|setInterval|fetch)\s*\(|console\s*\.\s*(?:log|info|warn|error|debug|assert)\s*\(|\(?function\s*\(|(?:window|document|jQuery)\s*[.(]|\$\s*\()|^\s*\(?\s*(?:\([^)]*\)|[a-zA-Z_$]\w*)\s*=>|^\s*if\s*\(~', $body)) {
        $extension='js'; $reason='JavaScript syntax';
    } elseif ($body!=='' && ($body[0]==='{' || $body[0]==='[') && json_decode(preg_match('//u',$body)===1 ? $body : u5CodeAssetUtf8($body), true)!==null) {
        $extension='json'; $reason='JSON content';
    } elseif (preg_match('~(?:^|[}\r\n])\s*(?:[.#@][\w-]|[a-z][\w\s,.#:\[\]="\'()>+\~-]*\{)~i', $body)) {
        $reason='CSS rules';
    }
    $base=preg_replace('/[^a-zA-Z0-9_!-]/', '', $name);
    if ($base==='') $base='asset'.substr(sha1($name),0,10);
    if (strlen($base)>210) $base=substr($base,0,195).'!'.substr(sha1($name),0,10);
    return array($base.'!'.$extension, $reason);
}

function u5CodeAssetPlanNames($resources, $uses='', $occupiedFiles=array()) {
    $groups=array(); $reserved=array(); $files=array(); $map=array(); $notes=array();
    foreach ($occupiedFiles as $file=>$owner) $files[strtolower($file)]=$owner;
    foreach ($resources as $row) {
        $reserved[strtolower($row['name'])]=true;
        if (strtolower((string)$row['typ'])!=='c') continue;
        $name=$row['name'];
        $rank=(int)$row['deleted']===0 ? 0 : ((int)$row['deleted']===2 ? 1 : 2);
        if (!isset($groups[$name]) || $rank<$groups[$name]['rank']) $groups[$name]=array('row'=>$row,'rank'=>$rank);
        if (u5CodeAssetIsTyped($name)) {
            $file=strtolower(u5CodeAssetFilename($name));
            if (!array_key_exists($file,$files)) $files[$file]=$name;
        }
    }
    ksort($groups, SORT_STRING);
    foreach ($groups as $name=>$group) {
        list($target,$reason)=u5CodeAssetGuess($name,(string)($group['row']['content_1'] ?? ''),$uses);
        $wanted=$target; $suffix=0;
        while (($target!==$name && isset($reserved[strtolower($target)])) ||
               (isset($files[strtolower(u5CodeAssetFilename($target))]) && $files[strtolower(u5CodeAssetFilename($target))]!==$name)) {
            $split=strrpos($wanted,'!');
            $token='!migrated'.substr(sha1($name),0,8).($suffix ? $suffix : '');
            $target=substr(substr($wanted,0,$split),0,220-strlen($token)).$token.substr($wanted,$split);
            $suffix++;
        }
        $reserved[strtolower($target)]=true;
        $files[strtolower(u5CodeAssetFilename($target))]=$name;
        $map[$name]=$target;
        $notes[]=array('from'=>$name,'to'=>$target,'reason'=>$reason.($target!==$wanted ? '; collision preserved under a distinct name' : ''));
    }
    return array('map'=>$map,'notes'=>$notes);
}

function u5CodeAssetFormat($name,$map=array()) {
    $typed=$map[$name] ?? $name;
    return u5CodeAssetIsTyped($typed) ? strtolower(pathinfo(u5CodeAssetFilename($typed),PATHINFO_EXTENSION)) : 'data';
}

function u5CodeAssetReplace($pattern,$callback,$source) {
    $result=preg_replace_callback($pattern,$callback,$source);
    if ($result===null) throw new RuntimeException('Cannot rewrite code references (regular expression error '.preg_last_error().').');
    return $result;
}

function u5CodeAssetQueryNames($text,$names,$quotedOnly=false) {
    $unsafe=preg_quote("&\"'`# \t\r\n<>)]\\",'~');
    $rewrite=function($part) use($names,$unsafe) {
        return u5CodeAssetReplace('~(\?|&(?:amp;)?)(name|c|n)=([^'.$unsafe.']+)~i',function($m) use($names) {
            $old=urldecode($m[3]);$key=strtolower($old);
            if (!isset($names[$key])) return $m[0];
            $new=$names[$key];
            return $m[1].$m[2].'='.(strpos($m[3],'%')!==false || strpos($m[3],'+')!==false ? rawurlencode($new) : $new);
        },$part);
    };
    if (!$quotedOnly) return $rewrite($text);
    $out='';$offset=0;$length=strlen($text);
    while ($offset<$length) {
        $begin=$offset+strcspn($text,"\"'`",$offset);
        if ($begin===$length) break;
        $quote=$text[$begin];$end=$begin;
        do {
            $end=strpos($text,$quote,$end+1);
            if ($end===false) break 2;
            $slashes=0;for ($i=$end-1;$i>$begin && $text[$i]==='\\';$i--) $slashes++;
        } while ($slashes%2);
        $out.=substr($text,$offset,$begin-$offset).$rewrite(substr($text,$begin,$end-$begin+1));
        $offset=$end+1;
    }
    return $out.substr($text,$offset);
}

function u5CodeAssetMarkupNames($text,$names,$simple=true) {
    return u5CodeAssetReplace('~\{\{\{([^{}\r\n]+)\}\}\}|\[([^\[\]\r\n]*)\]~',function($m) use($names,$simple) {
        if (isset($m[1]) && $m[1]!=='') {
            if (!$simple || (preg_match('/^[a-zA-Z0-9_!-]+$/D',$m[1]) && is_file(__DIR__.'/u5sys.'.$m[1].'.php'))) return $m[0];
            $key=strtolower($m[1]);return isset($names[$key]) ? '{{{'.$names[$key].'}}}' : $m[0];
        }
        $body=$m[2];$colon=strrpos($body,':');
        $prefix=$colon===false ? '' : substr($body,0,$colon+1);
        if (!$simple && $prefix!=='$$$:') return $m[0];
        $value=substr($body,strlen($prefix));$parts=explode('?',$value,2);$key=strtolower($parts[0]);
        return isset($names[$key]) ? '['.$prefix.$names[$key].(isset($parts[1]) ? '?'.$parts[1] : '').']' : $m[0];
    },$text);
}

function u5CodeAssetJsonReferences($text,$map,$depth,$format,$objectMap) {
    $out='';$offset=0;$length=strlen($text);
    while (($begin=strpos($text,'"',$offset))!==false) {
        $end=$begin;
        do {
            $end=strpos($text,'"',$end+1);
            if ($end===false) throw new RuntimeException('Invalid JSON reference token.');
            $slashes=0;for($i=$end-1;$i>$begin && $text[$i]==='\\';$i--) $slashes++;
        } while ($slashes%2);
        $literal=substr($text,$begin,$end-$begin+1);$value=json_decode($literal,true);
        $context=in_array(strtolower($format),array('html','htm','json','map','webmanifest','manifest'),true) ? 'json' : $format;
        if (in_array(strtolower($format),array('html','htm','json','map','webmanifest','manifest'),true)) {
            if (preg_match('~^\s*<~',$value)) $context='html';
            elseif (preg_match('~^(?:[a-z][a-z0-9+.-]*:|//|/|\./|\.\./|\?|[a-z0-9_!.-]+\.php\?)~i',$value)) $context='data';
        }
        $new=u5CodeAssetReferences($value,$map,$depth+1,$context,$objectMap);
        if ($new!==$value) {
            $flags=strpos($literal,'\\/')===false ? JSON_UNESCAPED_SLASHES : 0;
            if (!preg_match('/\\\\u[0-9a-f]{4}/i',$literal)) $flags|=JSON_UNESCAPED_UNICODE;
            foreach(array('003c'=>JSON_HEX_TAG,'003e'=>JSON_HEX_TAG,'0026'=>JSON_HEX_AMP,'0027'=>JSON_HEX_APOS,'0022'=>JSON_HEX_QUOT) as $hex=>$flag) if (stripos($literal,'\\u'.$hex)!==false) $flags|=$flag;
            $literal=json_encode($new,$flags);
            if ($literal===false) throw new RuntimeException('Cannot encode JSON code references.');
        }
        $out.=substr($text,$offset,$begin-$offset).$literal;$offset=$end+1;
    }
    return $out.substr($text,$offset);
}

function u5CodeAssetReferences($text,$map=array(),$depth=0,$format='html',$objectMap=null) {
    if (!is_string($text) || $text==='' || $depth>8) return $text;
    // Preserve byte lengths in PHP serialized data without instantiating objects.
    if (preg_match('/^(?:a:\d+:\{|O:\d+:|s:\d+:"|C:\d+:)/',$text)) {
        if (strpos($text,'C:')===0) return $text;
        $out='';$cursor=0;
        while (preg_match('/s:(\d+):"/',$text,$hit,PREG_OFFSET_CAPTURE,$cursor)) {
            $start=$hit[0][1];$dataStart=$start+strlen($hit[0][0]);$length=(int)$hit[1][0];
            if (substr($text,$dataStart+$length,2)!=='";') return $text;
            $new=u5CodeAssetReferences(substr($text,$dataStart,$length),$map,$depth+1,$format,$objectMap);
            $out.=substr($text,$cursor,$start-$cursor).'s:'.strlen($new).':"'.$new.'";';
            $cursor=$dataStart+$length+2;
        }
        return $out.substr($text,$cursor);
    }
    $trimmed=ltrim($text);
    if (in_array($trimmed[0] ?? '',array('{','[','"'),true)) {
        $valid=function_exists('json_validate') ? json_validate($text) : (json_decode($text,true)!==null || json_last_error()===JSON_ERROR_NONE);
        if (!$valid && preg_match('//u',$text)!==1) {
            // Raw latin1 columns/files can contain JSON that PHP's UTF-8 JSON parser rejects.
            // Normalize only for parsing, then return its original single-byte representation.
            $utf8=u5CodeAssetUtf8($text);
            $validLatin1=function_exists('json_validate') ? json_validate($utf8) : (json_decode($utf8,true)!==null || json_last_error()===JSON_ERROR_NONE);
            if ($validLatin1) return u5CodeAssetLatin1(u5CodeAssetReferences($utf8,$map,$depth,$format,$objectMap));
        }
        $simpleMarkup=false;
        if ($valid && in_array(strtolower($format),array('html','htm'),true) && preg_match('/^\[([^\[\]"{}]+)\]$/D',$trimmed,$tag)) {
            $all=array_merge(u5CodeAssetDefaults(),$map);$simpleMarkup=isset($all[$tag[1]]);
        }
        if ($valid && !$simpleMarkup) return u5CodeAssetJsonReferences($text,$map,$depth,$format,$objectMap);
    }
    $slash='(?:/|'.preg_quote('\\/','~').')';
    $token='(?:'.preg_quote('\\/','~').'|[^'.preg_quote("\\ \t\r\n\"'<>)]",'~').'])+';
    // Flat public exports may have been copied with the old renderer's media wrapper.
    $text=u5CodeAssetReplace('~(?<![a-zA-Z0-9_.-])f{1,4}\.php\?f=('.$token.')~i',function($m) {
        $escaped=strpos($m[1],'\\/')!==false;$value=str_replace('\\/','/',$m[1]);
        $parts=preg_split('/&(?:amp;)?/',$value,2);$file=$parts[0];
        if (strpos($file,'r/')!==0) $file=rawurldecode($file);
        $path=preg_split('/[?#]/',$file,2)[0];
        if (!preg_match('~^r/[^/]+\.(?:css|js|mjs|html?|txt|json|map|xml|svg|webmanifest|manifest|csv|rss|atom|ics|md|ya?ml)$~i',$path)) return $m[0];
        if (isset($parts[1])) {
            $fragment=explode('#',$file,2);$file=$fragment[0].(strpos($fragment[0],'?')===false ? '?' : (strpos($value,'&amp;')!==false ? '&amp;' : '&')).$parts[1].(isset($fragment[1]) ? '#'.$fragment[1] : '');
        }
        return $escaped ? str_replace('/','\\/',$file) : $file;
    },$text);
    $all=array_merge(u5CodeAssetDefaults(),$map);$paths=array();$cdnPaths=array();$names=array();
    foreach ($all as $old=>$new) {
        $legacy=u5CodeAssetLegacyFilename($old);if ($legacy===null) continue;
        $target=u5CodeAssetFilename($new);
        if ($legacy!==$target) $paths[$legacy]=$target;
        if (strpos($old,'!')!==false && u5CodeAssetIsTyped($old)) {
            $current=u5CodeAssetFilename($old);
            if ($current!==$target && !isset($paths[$current])) $paths[$current]=$target;
        }
        if ($old!==$new) $paths[$old.'.tpl']=$new.'.tpl';
        $cdnPaths[$legacy]=$target;$cdnPaths[$target]=$target;
    }
    foreach (u5CodeAssetDefaults() as $legacy=>$canonical) {
        $cdnPaths[u5CodeAssetFilename($canonical)]=u5CodeAssetFilename($all[$legacy] ?? $canonical);
    }
    foreach ($objectMap===null ? $all : $objectMap as $old=>$new) if ($old!==$new) $names[strtolower($old)]=$new;
    $file='[^'.preg_quote("/\\ \t\r\n\"'<>?#&()",'~').']+';
    $origin='(?:(?:https?:)?'.$slash.$slash.'(?:www\.)?yuba\.ch'.$slash.'(?:'.$file.$slash.')*)';
    $boundary='(?=[?#&\"\'\s<>()]|$|'.preg_quote('\\','~').'[\"\']|/(?=>))';
    // One constant-size pattern handles both ordinary and JSON-escaped paths. No cascading replacements.
    $text=u5CodeAssetReplace('~(?<![a-zA-Z0-9_!.-])('.$origin.')?r('.$slash.')('.$file.')'.$boundary.'~i',function($m) use($paths,$cdnPaths) {
        $old=rawurldecode($m[3]);$cdn=$m[1]!=='';$lookup=$cdn ? $cdnPaths : $paths;
        if (!isset($lookup[$old])) return $m[0];
        $new=$lookup[$old];if (strpos($m[3],'%')!==false) $new=rawurlencode($new);
        return 'r'.$m[2].$new;
    },$text);
    // CSS imports may use sibling paths, including an explicit ./ prefix.
    $text=u5CodeAssetReplace('~(?<![a-zA-Z0-9_/!.-])((?:\.'.$slash.')?)('.$file.')'.$boundary.'~',function($m) use($paths) {
        $old=rawurldecode($m[2]);
        if (!isset($paths[$old])) return $m[0];
        $new=$paths[$old];if (strpos($m[2],'%')!==false) $new=rawurlencode($new);
        return $m[1].$new;
    },$text);
    if (!$names) return $text;
    if (!in_array(strtolower($format),array('html','htm'),true)) {
        $text=u5CodeAssetQueryNames($text,$names,in_array(strtolower($format),array('js','mjs','json','map','webmanifest','manifest'),true));
        return u5CodeAssetMarkupNames($text,$names,false);
    }
    // CMS inclusion syntax remains valid inside tags and scripts. Ordinary JS/CSS identifiers do not change.
    $parts=preg_split('~(<(?:script|style)\b[^>]*>.*?</(?:script|style)\s*>)~is',$text,-1,PREG_SPLIT_DELIM_CAPTURE);
    if ($parts===false) throw new RuntimeException('Cannot parse code reference context.');
    foreach ($parts as &$part) {
        $code=preg_match('~^<(?:script|style)\b~i',$part);
        $part=u5CodeAssetQueryNames($part,$names,$code && stripos($part,'<script')===0);
        if ($code) $part=u5CodeAssetMarkupNames($part,$names,false);
        else {
            $tags=preg_split('~(<(?:"[^"]*"|\'[^\']*\'|[^\'">])*>)~s',$part,-1,PREG_SPLIT_DELIM_CAPTURE);
            if ($tags===false) throw new RuntimeException('Cannot parse code reference tags.');
            foreach ($tags as &$tag) $tag=u5CodeAssetMarkupNames($tag,$names,substr($tag,0,1)!=='<');
            unset($tag);$part=implode('',$tags);
        }
    }
    unset($part);return implode('',$parts);
}

function u5CodeAssetVersionURL($url,$stamp) {
    if (preg_match('~^(?:[a-z][a-z0-9+.-]*:|//)~i',$url)) return $url;
    if (!preg_match('~\.(?:css|js|mjs|png|jpe?g|gif|webp|avif|svg|ttf|woff2?)(?:[?#]|$)~i',$url) || preg_match('~^(?:data|javascript):~i',$url)) return $url;
    $parts=explode('#',$url,2); $fragment=isset($parts[1]) ? '#'.$parts[1] : '';
    $parts=explode('?',$parts[0],2); $query=isset($parts[1]) ? $parts[1] : '';
    if (preg_match('/^\d+$/D',$query)) $query='';
    $query=preg_replace('/(?:^|&)u5v=[^&]*/','',$query);
    $query=trim($query,'&');
    return $parts[0].'?'.($query!=='' ? $query.'&' : '').'u5v='.$stamp.$fragment;
}

function u5CodeAssetOutput($source,$stamp) {
    $source=u5CodeAssetReplace('~(\b(?:src|href)\s*=\s*)(["\'])(.*?)\2~is',function($m) use($stamp) {
        return $m[1].$m[2].u5CodeAssetVersionURL($m[3],$stamp).$m[2];
    },$source);
    $source=u5CodeAssetReplace('~(\burl\(\s*)(["\']?)([^\s\)"\']+)\2(\s*\))~i',function($m) use($stamp) {
        return $m[1].$m[2].u5CodeAssetVersionURL($m[3],$stamp).$m[2].$m[4];
    },$source);
    return u5CodeAssetReplace('~((?:loadScript|getScript)\s*\(\s*)(["\'])(.*?)\2~s',function($m) use($stamp) {
        return $m[1].$m[2].u5CodeAssetVersionURL($m[3],$stamp).$m[2];
    },$source);
}

function u5CodeAssetAtomicWrite($path,$data) {
    $temporary=@tempnam(dirname($path),'.u5asset-');
    if ($temporary===false) throw new RuntimeException('Cannot create asset in '.dirname($path));
    try {
        if (realpath(dirname($temporary))!==realpath(dirname($path))) throw new RuntimeException('Cannot create an atomic file in '.dirname($path));
        if (file_put_contents($temporary,$data)!==strlen($data)) throw new RuntimeException('Cannot write '.$path);
        $permissions=is_file($path) ? @fileperms($path) : 0644;
        if ($permissions===false || !@chmod($temporary,$permissions&0777)) throw new RuntimeException('Cannot set permissions on '.$path);
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

// Localization, orphan scans and repository highlighting share this pattern.
// Callers normalize JSON's escaped slashes before matching.
function u5CodeAssetUsagePattern($name) {
    $file=u5CodeAssetFilename($name);
    $files=array_unique(array($file,rawurlencode($file)));
    $literal='(?:'.implode('|',array_map(function($file){return str_replace('.','[.]',$file);},$files)).')';
    $space='[ \\t\\r\\n]';
    $css='(?:@import(?:'.$space.'+|'.$space.'*url[(]'.$space.'*)|url[(]'.$space.'*)["\']?(?:[.]/)?'.$literal;
    $module='(?:(?:from|import)'.$space.'+["\']|(?:import|require|fetch|getScript|loadScript|URL)'.$space.'*[(]'.$space.'*["\'])(?:[.]/)?'.$literal;
    return '(?:^|[^A-Za-z0-9_!.%-])(?:r/'.$literal.'|'.$css.'|'.$module.')(?=[^A-Za-z0-9_!./%-]|$)';
}

function u5CodeAssetLatin1($source) {
    static $characters=null;
    if ($characters===null) {
        $characters=array();
        for ($value=128;$value<256;$value++) $characters[u5CodeAssetUtf8(chr($value))]=chr($value);
    }
    $out='';
    for ($index=0,$length=strlen($source);$index<$length;) {
        $first=ord($source[$index]);
        if ($first<128) { $out.=$source[$index++];continue; }
        $size=$first<224 ? 2 : ($first<240 ? 3 : 4);$character=substr($source,$index,$size);
        if (!isset($characters[$character])) throw new RuntimeException('A rewritten Latin1 JSON value cannot be represented losslessly.');
        $out.=$characters[$character];$index+=$size;
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
        $row=$value['row'];$extension=strtolower(pathinfo($file,PATHINFO_EXTENSION));
        $source=(string)($row['content_1'] ?? '');
        if (in_array($extension,array('html','htm'),true)) {
            $data=u5CodeAssetOutput($source,$stamp);
        } else {
            $utf8=isset($row['u5_code_utf8']) ? (string)$row['u5_code_utf8'] : u5CodeAssetUtf8(substr($source,0,3)==="\xEF\xBB\xBF" ? substr($source,3) : $source);
            if (substr($utf8,0,3)==="\xEF\xBB\xBF") $utf8=substr($utf8,3);
            if (in_array($extension,array('js','mjs','css'),true)) $data="\xEF\xBB\xBF".u5CodeAssetOutput($utf8,$stamp);
            else {
                $data=$utf8;
                if (in_array($extension,array('xml','svg','rss','atom'),true)) {
                    $data=u5CodeAssetReplace('~^(<\\?xml\\b[^?]*\\bencoding\\s*=\\s*)(["\'])[^"\']*\\2~i',function($m) { return $m[1].$m[2].'UTF-8'.$m[2]; },$data);
                }
            }
        }
        $files[$file]=$data;
    }
    return $files;
}

function u5CodeAssetExport($root) {
    $release=u5CodeMigrationLock($root);
    try {
    $result=mysql_query("SELECT *,CAST(CONVERT(content_1 USING utf8mb4) AS BINARY) AS u5_code_utf8 FROM resources WHERE typ='c' AND deleted!=1");
    if ($result===false) throw new RuntimeException('Cannot read code objects for export.');
    $rows=array(); while ($row=mysql_fetch_array($result,MYSQL_ASSOC)) $rows[]=$row;
    foreach (u5CodeAssetRowsToFiles($rows,time()) as $file=>$data) u5CodeAssetAtomicWrite($root.'/r/'.$file,$data);
    } finally { $release(); }
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
