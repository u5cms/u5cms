<?php
/* Generic, bounded Rename helpers backported from 12.12.1. No schema or code migration runs here. */
if (is_file(__DIR__.'/codeassets-migrate.inc.php')) require_once __DIR__.'/codeassets-migrate.inc.php';
if (!class_exists('U5ResourceBusyException',false)) { class U5ResourceBusyException extends RuntimeException {} }

function u5RenameSyntaxReplace($pattern,$callback,$source) {
    $result=preg_replace_callback($pattern,$callback,$source);
    if ($result===null) throw new RuntimeException('Cannot rewrite code references (regular expression error '.preg_last_error().').');
    return $result;
}

function u5RenameSyntaxQueryNames($text,$names,$quotedOnly=false) {
    $unsafe=preg_quote("&\"'`# \t\r\n<>)]\\",'~');
    $rewrite=function($part) use($names,$unsafe) {
        return u5RenameSyntaxReplace('~(\?|&(?:amp;)?)(name|c|n)=([^'.$unsafe.']+)~i',function($m) use($names) {
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

function u5RenameSyntaxMarkupNames($text,$names,$simple=true) {
    return u5RenameSyntaxReplace('~\[{2,4}[^\[\]]*\]{2,4}(*SKIP)(*F)|\{\{\{([^{}\r\n]+)\}\}\}|(?<!\[)\[([^\[\]\r\n]*)\](?!\])~',function($m) use($names,$simple) {
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

function u5RenameSyntaxAtomicWrite($path,$data) {
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

function u5RenameStorageQuery($sql) {
    $result=mysql_query($sql);
    if ($result===false) throw new RuntimeException('Code asset migration: database operation failed: '.mysql_error());
    return $result;
}

function u5RenameStorageRows($sql) {
    $result=u5RenameStorageQuery($sql); $rows=array();
    while ($row=mysql_fetch_array($result,MYSQL_ASSOC)) $rows[]=$row;
    return $rows;
}

function u5RenameStorageIdentifier($name) { return '`'.str_replace('`','``',$name).'`'; }

function u5RenameStorageValue($value) { return $value===null ? 'NULL' : "X'".bin2hex((string)$value)."'"; }

function u5RenameStorageColumnValue($value,$column) {
    $literal=u5RenameStorageValue($value);
    if ($value===null) return $literal;
    $type=$column['Type'] ?? '';
    if (strtolower($type)==='json') return 'CONVERT('.$literal.' USING utf8mb4)';
    // MySQL treats an unintroduced hexadecimal literal as an integer in numeric context.
    if (preg_match('/^(?:(?:tiny|small|medium|big)?int|decimal|numeric|float|double|real|year)\b/i',$type)) return 'CONVERT('.$literal.' USING ascii)';
    return $literal;
}

function u5RenameStorageColumns($table) {
    $columns=array();
    foreach (u5RenameStorageRows('SHOW FULL COLUMNS FROM '.u5RenameStorageIdentifier($table)) as $column) {
        if (preg_match('/(?:VIRTUAL|STORED|PERSISTENT) GENERATED/i',$column['Extra'] ?? '')) continue;
        $columns[$column['Field']]=$column;
    }
    return $columns;
}

function u5RenameStorageSelect($columns,$bounded=false) {
    $select=array();$hashes=array();$index=0;
    foreach ($columns as $name=>$column) {
        if ($bounded && stripos($column['Extra'] ?? '','on update')!==false) continue;
        if ($bounded && preg_match('/blob|binary/i',$column['Type'])) {
            do {$alias='__u5binary_'.($index++);} while (isset($columns[$alias]));
            $select[]='SHA2(CAST('.u5RenameStorageIdentifier($name).' AS BINARY),256) AS '.u5RenameStorageIdentifier($alias);
            $hashes[$alias]=$name;
        } else $select[]='CAST('.u5RenameStorageIdentifier($name).' AS BINARY) AS '.u5RenameStorageIdentifier($name);
    }
    return array('select'=>implode(',',$select),'hashes'=>$hashes);
}

function u5RenameStorageSnapshot($row,$hashes) {
    if ($hashes) {
        $values=array();foreach ($hashes as $alias=>$name) { $values[$name]=$row[$alias];unset($row[$alias]); }
        $row["\0u5_binary"]=$values;
    }
    return $row;
}

function u5RenameStorageEachRow($table,$columns,$callback,$tail='') {
    $query=u5RenameStorageSelect($columns,true);
    $result=mysql_unbuffered_query('SELECT '.$query['select'].' FROM '.u5RenameStorageIdentifier($table).$tail);
    if ($result===false) throw new RuntimeException('Cannot stream migration rows from '.$table.': '.mysql_error());
    try { while ($row=mysql_fetch_array($result,MYSQL_ASSOC)) $callback(u5RenameStorageSnapshot($row,$query['hashes'])); }
    finally { mysql_free_result($result); }
}

function u5RenameStorageWhere($row,$columns=array()) {
    $terms=array();
    foreach ($row as $field=>$value) {
        if ($field==="\0u5_binary") {
            foreach ($value as $name=>$hash) $terms[]='SHA2(CAST('.u5RenameStorageIdentifier($name).' AS BINARY),256) <=> '.u5RenameStorageValue($hash);
            continue;
        }
        $column=$columns[$field] ?? null;
        if ($column && strtolower($column['Type'])==='json') {
            $terms[]="BINARY JSON_EXTRACT(".u5RenameStorageIdentifier($field).",'$') <=> BINARY JSON_EXTRACT(".u5RenameStorageColumnValue($value,$column).",'$')";
            continue;
        }
        // Preserve exact bytes, with a separate sargable predicate for row/index identity.
        if ($column && !preg_match('/^(?:float|double|real)/i',$column['Type']) && (($column['Key'] ?? '')!=='' || preg_match('/^(?:(?:tiny|small|medium|big)?int|year)\b/i',$column['Type']))) $terms[]=u5RenameStorageIdentifier($field).' <=> '.u5RenameStorageColumnValue($value,$column);
        $terms[]='BINARY '.u5RenameStorageIdentifier($field).' <=> '.u5RenameStorageValue($value);
    }
    return implode(' AND ',$terms);
}

function u5RenameStorageCharset($column) {
    $collation=$column['Collation'] ?? null;
    return $collation ? explode('_',$collation)[0] : 'binary';
}

function u5RenameStorageReadJournal($path) {
    $plan=u5RenameStorageReadGuarded($path);
    if (($plan['version'] ?? '')!=='12.11.0') throw new RuntimeException('Invalid migration checkpoint version.');
    return $plan;
}

function u5RenameStorageCheckpoint($phase) {
    // An optional test hook also permits deterministic interruption/failure regression checks.
    if (function_exists('u5RenameStorageTestCheckpoint')) u5RenameStorageTestCheckpoint($phase);
}

function u5RenameStorageApplyRow($change,$columns=null,$derivedFields=array()) {
    if ($columns===null) $columns=u5RenameStorageColumns($change['table']);
    $before=$change['before']; $after=$change['after']; $table=u5RenameStorageIdentifier($change['table']);
    $derived=array_fill_keys($derivedFields,true);
    $beforeGuard=array_diff_key($before,$derived);$afterGuard=array_diff_key($after,$derived);
    // The indexer owns these columns, including when resuming an older rename journal.
    $set=array();
    foreach ($afterGuard as $field=>$value) {
        if ($value!==$beforeGuard[$field]) $set[]=u5RenameStorageIdentifier($field).'='.u5RenameStorageColumnValue($value,$columns[$field]);
    }
    if (!$set) return;
    // Use the whole original row, including nulls, instead of a guessed primary key.
    // This also handles duplicate history rows in the legacy MyISAM tables.
    $count=u5RenameStorageRows('SELECT COUNT(*) AS n FROM '.$table.' WHERE '.u5RenameStorageWhere($beforeGuard,$columns));
    if ((int)$count[0]['n']===0) {
        $done=u5RenameStorageRows('SELECT COUNT(*) AS n FROM '.$table.' WHERE '.u5RenameStorageWhere($afterGuard,$columns));
        if ((int)$done[0]['n']>0) return;
        throw new RuntimeException('A database row changed during migration in '.$change['table'].'. The checkpoint was kept; no concurrent edit was overwritten.');
    }
    u5RenameStorageQuery('UPDATE '.$table.' SET '.implode(',',$set).' WHERE '.u5RenameStorageWhere($beforeGuard,$columns));
    if (mysql_affected_rows()!==(int)$count[0]['n']) throw new RuntimeException('A row changed while applying migration in '.$change['table']);
}

function u5RenameStorageEncodeJournal($value) {
    return "<?php exit; __halt_compiler();\n".serialize($value);
}

function u5RenameStorageReadGuarded($path) {
    $data=@file_get_contents($path);
    if ($data===false) throw new RuntimeException('Cannot read migration checkpoint.');
    foreach (array("<?php exit; __halt_compiler();\n","<?php exit; ?>\n") as $prefix) {
        if (substr($data,0,strlen($prefix))!==$prefix) continue;
        $value=@unserialize(substr($data,strlen($prefix)),array('allowed_classes'=>false));
        if (!is_array($value)) break;
        return $value;
    }
    throw new RuntimeException('Invalid migration checkpoint.');
}

function u5RenameStorageStartChanges() {
    return array('format'=>'chunked-v1','implementation'=>'12.11.2','count'=>0,'directory'=>null,'chunks'=>array(),'buffer'=>array(),'bytes'=>0);
}

function u5RenameStorageQueueChange($root,&$journal,$change) {
    $bytes=0;
    foreach (array('before','after') as $side) foreach ($change[$side] as $value) $bytes+=is_string($value) ? strlen($value)+64 : 128;
    if ($journal['buffer'] && ($journal['bytes']+$bytes>512*1024 || count($journal['buffer'])>=100)) u5RenameStorageFlushChanges($root,$journal);
    $journal['buffer'][]=$change;$journal['bytes']+=$bytes;$journal['count']++;
    if ($journal['bytes']>=512*1024) u5RenameStorageFlushChanges($root,$journal);
}

function u5RenameStorageFlushChanges($root,&$journal) {
    if (!$journal['buffer']) return;
    if ($journal['directory']===null) {
        $journal['directory']='fileversions/codeassets-12.11.1-rows-'.bin2hex(random_bytes(8));
        if (!@mkdir($root.'/'.$journal['directory'],0700)) throw new RuntimeException('Cannot create the migration row backup directory.');
    }
    $serialized=serialize($journal['buffer']);
    $codec=function_exists('gzcompress') ? 'zlib' : 'plain';
    $payload=$codec==='zlib' ? gzcompress($serialized,6) : $serialized;
    if (!is_string($payload)) throw new RuntimeException('Cannot encode the migration row backup.');
    $data=u5RenameStorageEncodeJournal(array('version'=>'12.11.0','codec'=>$codec,'payload'=>$payload));
    $path=$journal['directory'].'/'.sprintf('%06d',count($journal['chunks'])).'.php';
    u5RenameSyntaxAtomicWrite($root.'/'.$path,$data);
    $journal['chunks'][]=array('path'=>$path,'sha256'=>hash('sha256',$data),'bytes'=>strlen($serialized));
    $journal['buffer']=array();$journal['bytes']=0;
}

function u5RenameStorageFinishChanges($root,&$journal) {
    u5RenameStorageFlushChanges($root,$journal);
    unset($journal['buffer'],$journal['bytes']);
    if ($journal['directory']!==null) u5RenameSyntaxAtomicWrite($root.'/'.$journal['directory'].'/manifest.php',u5RenameStorageEncodeJournal(array('version'=>'12.11.0','journal'=>$journal)));
    return $journal;
}

function u5RenameStorageChangesDirectory($root,$journal) {
    $directory=$journal['directory'] ?? null;
    if ($directory===null) return null;
    if (!preg_match('~^fileversions/codeassets-[0-9]+\.[0-9]+\.[0-9]+-rows-[a-f0-9]{16}$~D',$directory) || is_link($root.'/'.$directory)) throw new RuntimeException('Invalid migration row backup directory.');
    return $directory;
}

function u5RenameStorageValidateChanges($root,$journal) {
    if (($journal['format'] ?? '')!=='chunked-v1') return;
    $directory=u5RenameStorageChangesDirectory($root,$journal);
    foreach ($journal['chunks'] as $chunk) {
        if ($directory===null || !preg_match('~^'.preg_quote($directory,'~').'/[0-9]{6,12}\.php$~D',$chunk['path']) || !is_file($root.'/'.$chunk['path']) || !hash_equals($chunk['sha256'],hash_file('sha256',$root.'/'.$chunk['path']))) throw new RuntimeException('A migration row backup is missing or damaged; no further rows were applied.');
    }
}

function u5RenameStorageReadChangeBlock($root,$chunk) {
    $block=u5RenameStorageReadGuarded($root.'/'.$chunk['path']);
    if (($block['version'] ?? '')!=='12.11.0') throw new RuntimeException('Invalid migration row backup version.');
    if (($block['codec'] ?? '')==='zlib') {
        if (!function_exists('gzuncompress')) throw new RuntimeException('The saved migration row backup needs PHP zlib.');
        $data=@gzuncompress($block['payload'],$chunk['bytes']+1);
    } elseif (($block['codec'] ?? '')==='plain') $data=$block['payload'];
    else throw new RuntimeException('Unsupported migration row backup encoding.');
    if (!is_string($data) || strlen($data)!==$chunk['bytes']) throw new RuntimeException('Invalid migration row backup length.');
    $changes=@unserialize($data,array('allowed_classes'=>false));
    if (!is_array($changes)) throw new RuntimeException('Invalid migration row backup data.');
    return $changes;
}

function u5RenameStorageEachChange($root,$journal,$callback) {
    if (($journal['format'] ?? '')!=='chunked-v1') {
        foreach ($journal as $index=>$change) $callback($change,$index);
        return;
    }
    u5RenameStorageValidateChanges($root,$journal);$index=0;
    foreach ($journal['chunks'] as $chunk) {
        $changes=u5RenameStorageReadChangeBlock($root,$chunk);
        foreach ($changes as $position=>$change) $callback($change,$index++,array('chunk'=>$chunk,'position'=>$position));
    }
    if ($index!==(int)$journal['count']) throw new RuntimeException('Migration row backup count mismatch.');
}

function u5RenameStorageAbortChanges($root,$journal) {
    if (($journal['format'] ?? '')!=='chunked-v1') return;
    $directory=u5RenameStorageChangesDirectory($root,$journal);
    if ($directory===null) return;
    foreach ($journal['chunks'] as $chunk) if (preg_match('~^'.preg_quote($directory,'~').'/[0-9]{6,12}\\.php$~D',$chunk['path'])) @unlink($root.'/'.$chunk['path']);
    @unlink($root.'/'.$directory.'/manifest.php');@rmdir($root.'/'.$directory);
}

function u5RenameStorageValidateRow($table,$before,$after,$columns) {
    foreach ($after as $field=>$value) {
        if (!isset($columns[$field]) || $value===($before[$field] ?? null)) continue;
        $column=$columns[$field];$type=strtolower($column['Type']);
        if ($value!==null && preg_match('/^(tinyint|smallint|mediumint|int)\b/',$type,$integer)) {
            $bits=array('tinyint'=>8,'smallint'=>16,'mediumint'=>24,'int'=>32);$unsigned=strpos($type,'unsigned')!==false;
            $maximum=pow(2,$bits[$integer[1]]-($unsigned ? 0 : 1))-1;$minimum=$unsigned ? 0 : -$maximum-1;
            if (!preg_match('/^-?[0-9]+$/D',(string)$value) || $value<$minimum || $value>$maximum) throw new RuntimeException('Updated revision/value exceeds the integer capacity of '.$table.'.'.$field.'. Enlarge that column and retry; no source rows have been changed.');
        }
        if (!is_string($value)) continue;
        if (preg_match('/^(enum|set)\(/',$type)) throw new RuntimeException('Cannot safely rewrite '.$table.'.'.$field.' inside its ENUM/SET definition. Adapt that definition first; no rows have been changed.');
        $bytes=strlen($value);$limit=null;
        if (preg_match('/^(char|varchar)\((\d+)\)/',$type,$match)) {
            $length=$bytes;$charset=u5RenameStorageCharset($column);
            if (strpos($charset,'utf8')===0) {
                $length=preg_match_all('/./us',$value,$unused);
                if ($length===false) throw new RuntimeException('Invalid UTF-8 in '.$table.'.'.$field.'. No rows have been changed.');
            }
            if ($length>(int)$match[2]) throw new RuntimeException('Rewritten reference exceeds the capacity of '.$table.'.'.$field.'. Enlarge that column and retry; no rows have been changed.');
        } elseif (preg_match('/^(tinytext|text|mediumtext|longtext)\b/',$type,$match)) {
            $limits=array('tinytext'=>255,'text'=>65535,'mediumtext'=>16777215,'longtext'=>4294967295);$limit=$limits[$match[1]];
        }
        if ($limit!==null && $bytes>$limit) throw new RuntimeException('Rewritten reference exceeds the byte capacity of '.$table.'.'.$field.'. No rows have been changed.');
    }
}

function u5RenameStorageLock($root,$renameCheckpoint=null,$allowPendingRename=false) {
    if (function_exists('u5CodeMigrationLock')) return u5CodeMigrationLock($root,$renameCheckpoint,$allowPendingRename);
    static $held=array();
    $key=realpath($root) ?: $root;
    if (isset($held[$key])) $held[$key]['depth']++;
    else {
        $handle=@fopen($root.'/fileversions/codeassets-12.11.0.lock.php','c+');
        if (!$handle) throw new RuntimeException('Cannot open the code asset update lock.');
        if (!flock($handle,LOCK_EX|LOCK_NB)) {
            fclose($handle);
            throw new U5ResourceBusyException('Another code asset update is already running. Retry the backend when it has finished.');
        }
        foreach (glob($root.'/fileversions/resource-rename-*.pending.php') as $pending) if (!$allowPendingRename && $pending!==$renameCheckpoint) {
            flock($handle,LOCK_UN);fclose($handle);
            throw new U5ResourceBusyException('An interrupted resource rename must be resumed before another resource mutation or backup.');
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

function u5RenameTemplateName($root=null) {
    if ($root===null) $root=dirname(__DIR__);
    return function_exists('u5CodeTemplateName') ? u5CodeTemplateName($root) : 'htmltemplate';
}

function u5RenameSyntaxFormat($name) {
    if (function_exists('u5CodeAssetFormat')) return u5CodeAssetFormat($name);
    if (preg_match('/!(js|mjs|css|html|htm|json|map|xml|svg|txt)$/i',$name,$match)) return strtolower($match[1]);
    if (strpos($name,'html')===0) return 'html';
    return strpos($name,'js')===0 ? 'js' : 'css';
}

function u5RenameSyntaxExport($root) {
    if (function_exists('u5CodeAssetExport')) {u5CodeAssetExport($root);return;}
    // Match the established legacy exporter, including its bytes and cache-busting suffixes.
    $stamp=time();
    $extensions=array('css"','css)','jpg)','jpe)','jpeg)','gif)','htm)','html)','svg)','ttf)','woff)');
    $before=array();$after=array();
    foreach ($extensions as $extension) {
        $before[]='.'. $extension;
        $after[]='.'.substr($extension,0,-1).'?'.$stamp.substr($extension,-1);
    }
    $rows=u5RenameStorageRows("SELECT name,content_1 FROM resources WHERE typ='c' AND deleted!=1");
    foreach ($rows as $row) {
        if (!preg_match('/^[a-zA-Z0-9_!-]+$/D',$row['name'])) throw new RuntimeException('Unsafe legacy code export name.');
        u5RenameSyntaxAtomicWrite($root.'/r/'.$row['name'].'.css',str_replace($before,$after,$row['content_1'].' '));
    }
}

function u5RenameJsString($value) {
    return "'".str_replace(array("\\","'","\r","\n","<"),array("\\\\","\\'","\\r","\\n","\\x3c"),$value)."'";
}

function u5RenameHistoryLookup() {
    // Old sites have no history lookup index. Without it, guarded row writes repeatedly scan the whole history.
    foreach (u5RenameStorageRows('SHOW INDEX FROM resources_log') as $index) {
        if ((int)$index['Seq_in_index']===1 && $index['Column_name']==='name') return;
    }
    $columns=u5RenameStorageColumns('resources_log');
    $length=150;
    if (preg_match('/^(?:var)?char\((\d+)\)/i',$columns['name']['Type'],$match)) $length=min($length,(int)$match[1]);
    // A restricted installation can still rename safely without ALTER privileges.
    mysql_query('ALTER TABLE resources_log ADD INDEX u5_rename_history_lookup (name('.$length.'),lastmut,deleted)');
}

function u5RenameLegacyCodePaths($text,$names) {
    // Relative imports in the bundled legacy stylesheets refer to exports beside them in r/.
    $prefix=<<<'REGEX'
(?:\burl\(\s*["']?|@import\s+["']|\b(?:loadScript|getScript|fetch|import|require)\s*\(\s*["'])
REGEX;
    $pattern='~((?i:'.$prefix.'))(\./)?('.implode('|',array_map(function($name){return preg_quote($name,'~');},array_keys($names))).')\.css(?=[?#\s"\'\)]|$)~';
    return u5RenameSyntaxReplace($pattern,function($match) use($names) {return $match[1].($match[2] ?? '').$names[$match[3]].'.css';},$text);
}
