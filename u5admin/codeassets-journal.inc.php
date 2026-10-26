<?php
/* Bounded, guarded row journals. Completed journals are retained as migration backups. */
function u5CodeMigrationEncodeJournal($value) {
    return "<?php exit; __halt_compiler();\n".serialize($value);
}

function u5CodeMigrationReadGuarded($path) {
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

function u5CodeMigrationStartChanges() {
    return array('format'=>'chunked-v1','implementation'=>'12.11.2','count'=>0,'directory'=>null,'chunks'=>array(),'buffer'=>array(),'bytes'=>0);
}

function u5CodeMigrationQueueChange($root,&$journal,$change) {
    $bytes=0;
    foreach (array('before','after') as $side) foreach ($change[$side] as $value) $bytes+=is_string($value) ? strlen($value)+64 : 128;
    if ($journal['buffer'] && ($journal['bytes']+$bytes>512*1024 || count($journal['buffer'])>=100)) u5CodeMigrationFlushChanges($root,$journal);
    $journal['buffer'][]=$change;$journal['bytes']+=$bytes;$journal['count']++;
    if ($journal['bytes']>=512*1024) u5CodeMigrationFlushChanges($root,$journal);
}

function u5CodeMigrationFlushChanges($root,&$journal) {
    if (!$journal['buffer']) return;
    if ($journal['directory']===null) {
        $journal['directory']='fileversions/codeassets-12.11.1-rows-'.bin2hex(random_bytes(8));
        if (!@mkdir($root.'/'.$journal['directory'],0700)) throw new RuntimeException('Cannot create the migration row backup directory.');
    }
    $serialized=serialize($journal['buffer']);
    $codec=function_exists('gzcompress') ? 'zlib' : 'plain';
    $payload=$codec==='zlib' ? gzcompress($serialized,6) : $serialized;
    if (!is_string($payload)) throw new RuntimeException('Cannot encode the migration row backup.');
    $data=u5CodeMigrationEncodeJournal(array('version'=>'12.11.0','codec'=>$codec,'payload'=>$payload));
    $path=$journal['directory'].'/'.sprintf('%06d',count($journal['chunks'])).'.php';
    u5CodeAssetAtomicWrite($root.'/'.$path,$data);
    $journal['chunks'][]=array('path'=>$path,'sha256'=>hash('sha256',$data),'bytes'=>strlen($serialized));
    $journal['buffer']=array();$journal['bytes']=0;
}

function u5CodeMigrationFinishChanges($root,&$journal) {
    u5CodeMigrationFlushChanges($root,$journal);
    unset($journal['buffer'],$journal['bytes']);
    if ($journal['directory']!==null) u5CodeAssetAtomicWrite($root.'/'.$journal['directory'].'/manifest.php',u5CodeMigrationEncodeJournal(array('version'=>'12.11.0','journal'=>$journal)));
    return $journal;
}

function u5CodeMigrationChangesDirectory($root,$journal) {
    $directory=$journal['directory'] ?? null;
    if ($directory===null) return null;
    if (!preg_match('~^fileversions/codeassets-[0-9]+\.[0-9]+\.[0-9]+-rows-[a-f0-9]{16}$~D',$directory) || is_link($root.'/'.$directory)) throw new RuntimeException('Invalid migration row backup directory.');
    return $directory;
}

function u5CodeMigrationValidateChanges($root,$journal) {
    if (($journal['format'] ?? '')!=='chunked-v1') return;
    $directory=u5CodeMigrationChangesDirectory($root,$journal);
    foreach ($journal['chunks'] as $chunk) {
        if ($directory===null || !preg_match('~^'.preg_quote($directory,'~').'/[0-9]{6,12}\.php$~D',$chunk['path']) || !is_file($root.'/'.$chunk['path']) || !hash_equals($chunk['sha256'],hash_file('sha256',$root.'/'.$chunk['path']))) throw new RuntimeException('A migration row backup is missing or damaged; no further rows were applied.');
    }
}

function u5CodeMigrationReadChangeBlock($root,$chunk) {
    $block=u5CodeMigrationReadGuarded($root.'/'.$chunk['path']);
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

function u5CodeMigrationEachChange($root,$journal,$callback) {
    if (($journal['format'] ?? '')!=='chunked-v1') {
        foreach ($journal as $index=>$change) $callback($change,$index);
        return;
    }
    u5CodeMigrationValidateChanges($root,$journal);$index=0;
    foreach ($journal['chunks'] as $chunk) {
        $changes=u5CodeMigrationReadChangeBlock($root,$chunk);
        foreach ($changes as $position=>$change) $callback($change,$index++,array('chunk'=>$chunk,'position'=>$position));
    }
    if ($index!==(int)$journal['count']) throw new RuntimeException('Migration row backup count mismatch.');
}

function u5CodeMigrationAbortChanges($root,$journal) {
    if (($journal['format'] ?? '')!=='chunked-v1') return;
    $directory=u5CodeMigrationChangesDirectory($root,$journal);
    if ($directory===null) return;
    foreach ($journal['chunks'] as $chunk) if (preg_match('~^'.preg_quote($directory,'~').'/[0-9]{6,12}\\.php$~D',$chunk['path'])) @unlink($root.'/'.$chunk['path']);
    @unlink($root.'/'.$directory.'/manifest.php');@rmdir($root.'/'.$directory);
}

function u5CodeMigrationApplyChanges($root,$journal) {
    $columns=array();
    u5CodeMigrationEachChange($root,$journal,function($change,$index) use(&$columns) {
        $table=$change['table'];
        if (!isset($columns[$table])) $columns[$table]=u5CodeMigrationColumns($table);
        u5CodeMigrationApplyRow($change,$columns[$table]);
        u5CodeMigrationCheckpoint('row:'.$index);
    });
}
