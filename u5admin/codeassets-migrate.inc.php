<?php
require_once dirname(__DIR__).'/codeassets.inc.php';
require_once __DIR__.'/mobiletools-migrate.inc.php';
require_once __DIR__.'/codeassets-journal.inc.php';
require_once __DIR__.'/codeassets-schema.inc.php';

// The updater receives only r/runonce.php from r/. Keep the common mobile fallback here.
function u5CodeMigrationMobileSource() {
    $source=<<<'U5CMS_MOBILE_BASE'
if(location.href.indexOf('mobile=on')>0)document.cookie='u5cmsfrcthsvw=mobile;';
if(location.href.indexOf('mobile=off')>0)document.cookie='u5cmsfrcthsvw=desktop;';

var jsmobilegeneralloaded=true;
if(typeof webappdesktopsymbol=='undefined') webappdesktopsymbol='&#128187;';
if(typeof webappmobilesymbol=='undefined') webappmobilesymbol='&#128241;';

document.getElementById('body').style.maxWidth='600px';
document.getElementById('body').style.margin='auto';

document.getElementById('content').insertAdjacentHTML('afterbegin','<center><div id="webapp_monalink"><a style="text-decoration:none" id="webapp_monalinka" href="javascript:void(0)" onclick="this.style.display=\'none\';document.getElementById(\'webapp_mona\').style.display=\'block\'">&#9776;&nbsp;Navigation</a></div><div id="webapp_mona" style="display:none;text-align:left;padding:0 17px 0 17px"></div></center>');

if(document.getElementById('navTop'))document.getElementById('navTop').style.display='none';
if(document.getElementById('navLeftSubTop'))document.getElementById('navLeftSubTop').id='navLeft';

if(document.getElementById('webapp_mona'))if(document.getElementById('navLeft'))document.getElementById('webapp_mona').innerHTML=document.getElementById('navLeft').innerHTML;
if(document.getElementById('webapp_monalinka').style.display='block')wbppamnlnksts='block';
else wbppamnlnksts='inline';
wamonafloat='<div id="webapp_monafloat" style="width:50px;position:absolute;left:-10px"><div id="webapp_monaclose" style="width:22px;overflow:hidden;color:white;background:#e63320;cursor:pointer;padding:3px 7px 7px 7px;font-size:150%;margin-bottom:10px;" onclick="document.getElementById(\'webapp_mona\').style.display=\'none\';document.getElementById(\'webapp_monalinka\').style.display=wbppamnlnksts">&nbsp;&times;&nbsp;</div><div id="webapp_monadesktop" style="width:36px;overflow:hidden;background:transparent;padding:3px 0 3px 0;cursor:pointer;font-size:36px" onclick="document.cookie=\'u5cmsfrcthsvw=desktop;\';location.href=location.href.replace(/mobile=on/,\'\')">'+webappdesktopsymbol+'</div></div>';
document.getElementById('webapp_mona').innerHTML=wamonafloat+'<div style="margin:0;padding:0" id="navLeft">'+document.getElementById('webapp_mona').innerHTML+'</div>';
document.getElementById('navLeft').style.width='100%';
document.getElementById('navLeft').style.fontSize='80%';
document.getElementById('navLeft').style.lineHeight='90%';
document.getElementById('navLeft').style.lineHeight='90%';

e=document.getElementById('content').getElementsByTagName('table');
for(i=0;i<e.length;i++) {
if(e[i].clientWidth>400) {
e[i].style.width='100%';
}
}

e=document.getElementsByTagName('iframe');
for(i=0;i<e.length;i++) {
if(e[i].width>200) {
e[i].width='100%';
if(document.getElementById('content'))e[i].height=Math.floor(document.getElementById('content').clientWidth*0.3);
}
}

e=document.getElementsByTagName('dl');
for(i=0;i<e.length;i++) {
if(e[i].style.width.replace(/px/,'')>200) {
e[i].style.width='none';
e[i].style.maxWidth='100%';
}
}

e=document.getElementsByTagName('audio');
for(i=0;i<e.length;i++) {
if(e[i].style.width.replace(/px/,'')>200) {
e[i].style.width='100%';
}
}

e=document.getElementsByTagName('video');
for(i=0;i<e.length;i++) {
if(e[i].style.width.replace(/px/,'')>200) {
e[i].style.width='100%';
e[i].style.height=e[i].style.width;
}
}

e=document.getElementsByTagName('div');
for(i=0;i<e.length;i++) {
if(e[i].innerHTML.indexOf('albumhorizontal')>0) {
e[i].style.width='90%';
e[i].style.height='100px';
}
}

e=document.getElementsByTagName('div');
for(i=0;i<e.length;i++) {
if(e[i].innerHTML.indexOf('albumhorizontal')>0 && e[i].style.overflowY=='hidden') {
e[i].style.width='100%';
}
}

e=document.getElementsByTagName('form');
for(i=0;i<e.length;i++) {
e[i].style.width='100%';
}

e=document.getElementsByTagName('fieldset');
for(i=0;i<e.length;i++) {
e[i].style.width='87%';
}

e=document.getElementsByTagName('label');
for(i=0;i<e.length;i++) {
e[i].style.width='100%';
e[i].style.marginBottom='-7px';
e[i].style.marginTop='-10px';
e[i].style.border='none';
}

e=document.getElementsByTagName('input');
for(i=0;i<e.length;i++) {
if(e[i].id!='search_Input') {
if(e[i].clientWidth>100){
if(e[i].type=='text')e[i].style.width='100%';
if(e[i].type=='email')e[i].style.width='100%';
}
}
}

e=document.getElementsByTagName('select');
for(i=0;i<e.length;i++) {
if(e[i].clientWidth>100)e[i].style.width='100%';
}

e=document.getElementsByTagName('textarea');
for(i=0;i<e.length;i++) {
e[i].style.width='100%';
}

e=document.getElementsByClassName('showblockdivinner');
for(i=0;i<e.length;i++) {
e[i].style.width='inherit';
}

e=document.getElementsByClassName('showblock2divinner');
for(i=0;i<e.length;i++) {
e[i].style.width='inherit';
}
e=document.getElementsByClassName('stayright');
for(i=0;i<e.length;i++) {
e[i].style.marginLeft='0';
}
e=document.getElementsByClassName('imgBoxLeft');
for(i=0;i<e.length;i++) {
//e[i].style.float='none';
}

e=document.getElementsByClassName('imgBoxRight');
for(i=0;i<e.length;i++) {
//e[i].style.float='none';
}

e=document.getElementsByClassName('freeImgLeft');
for(i=0;i<e.length;i++) {
//e[i].style.float='none';
}

e=document.getElementsByClassName('freeImgRight');
for(i=0;i<e.length;i++) {
//e[i].style.float='none';
}

e=document.getElementsByTagName('img');
for(i=0;i<e.length;i++) {
if(e[i].width>290) {
e[i].style.width='100%';
}
}

e=document.getElementsByClassName('albumhorizontal');
for(i=0;i<e.length;i++) {
e[i].innerHTML=e[i].innerHTML.replace(/class=/g,'style="display:block;width:100px;height:100px;overflow:hidden" class=');
e[i].innerHTML=e[i].innerHTML.replace(/img height/g,'img style="height:100px;width:auto"');
}

frombtmwebappnavlinkscrollto=0;
document.getElementById('content').insertAdjacentHTML('beforeend','<center><div id="webapp_monalinkbtm"><a id="webapp_monalinkabtm" style="text-decoration:none" href="javascript:void(0)" onclick="window.scroll(0,frombtmwebappnavlinkscrollto);document.getElementById(\'webapp_monalinka\').click()">&#9776;&nbsp;Navigation</a></div></center>');

e=document.getElementsByTagName('button');
for(i=0;i<e.length;i++) {
ftriggerid=e[i].id
if(e[i].id.indexOf('f_trigger_b')===0)setTimeout("window[ftriggerid]();",111);
}

e=document.getElementsByTagName('iframe');
for(i=0;i<e.length;i++) {
if(e[i].name=='ifrmonofill') {
locationhref=location.href;if(locationhref.indexOf('c=')<0) locationhref+='&c=MF_NOT_ALLOWED_ON_YOUR_MAIN_PAGE';if(locationhref.indexOf('id=')<0) locationhref+='&id=0';e[i].src='monofill.php?n='+locationhref.split('c=')[1].split('&')[0]+'&id='+locationhref.split('id=')[1].split('&')[0];
}
if(e[i].name=='ifrmonofillshared') {
locationhref=location.href;if(locationhref.indexOf('c=')<0) locationhref+='&c=MF_NOT_ALLOWED_ON_YOUR_MAIN_PAGE';if(locationhref.indexOf('id=')<0) locationhref+='&id=0';e[i].src='monofillshared.php?n='+locationhref.split('c=')[1].split('&')[0]+'&id='+locationhref.split('id=')[1].split('&')[0];
}
}

document.getElementsByTagName('html')[0].style.overflowX='hidden';
document.getElementsByTagName('body')[0].style.overflowX='hidden';
U5CMS_MOBILE_BASE;
    return $source."\n";
}

function u5CodeMigrationQuery($sql) {
    $result=mysql_query($sql);
    if ($result===false) throw new RuntimeException('Code asset migration: database operation failed: '.mysql_error());
    return $result;
}

function u5CodeMigrationRows($sql) {
    $result=u5CodeMigrationQuery($sql); $rows=array();
    while ($row=mysql_fetch_array($result,MYSQL_ASSOC)) $rows[]=$row;
    return $rows;
}

function u5CodeMigrationIdentifier($name) { return '`'.str_replace('`','``',$name).'`'; }
function u5CodeMigrationValue($value) { return $value===null ? 'NULL' : "X'".bin2hex((string)$value)."'"; }

function u5CodeMigrationColumnValue($value,$column) {
    $literal=u5CodeMigrationValue($value);
    if ($value===null) return $literal;
    $type=$column['Type'] ?? '';
    if (strtolower($type)==='json') return 'CONVERT('.$literal.' USING utf8mb4)';
    // MySQL treats an unintroduced hexadecimal literal as an integer in numeric context.
    if (preg_match('/^(?:(?:tiny|small|medium|big)?int|decimal|numeric|float|double|real|year)\b/i',$type)) return 'CONVERT('.$literal.' USING ascii)';
    return $literal;
}

function u5CodeMigrationColumns($table) {
    $columns=array();
    foreach (u5CodeMigrationRows('SHOW FULL COLUMNS FROM '.u5CodeMigrationIdentifier($table)) as $column) {
        if (preg_match('/(?:VIRTUAL|STORED|PERSISTENT) GENERATED/i',$column['Extra'] ?? '')) continue;
        $columns[$column['Field']]=$column;
    }
    return $columns;
}

function u5CodeMigrationSelect($columns,$bounded=false) {
    $select=array();$hashes=array();$index=0;
    foreach ($columns as $name=>$column) {
        if ($bounded && stripos($column['Extra'] ?? '','on update')!==false) continue;
        if ($bounded && preg_match('/blob|binary/i',$column['Type'])) {
            do {$alias='__u5binary_'.($index++);} while (isset($columns[$alias]));
            $select[]='SHA2(CAST('.u5CodeMigrationIdentifier($name).' AS BINARY),256) AS '.u5CodeMigrationIdentifier($alias);
            $hashes[$alias]=$name;
        } else $select[]='CAST('.u5CodeMigrationIdentifier($name).' AS BINARY) AS '.u5CodeMigrationIdentifier($name);
    }
    return array('select'=>implode(',',$select),'hashes'=>$hashes);
}

function u5CodeMigrationSnapshot($row,$hashes) {
    if ($hashes) {
        $values=array();foreach ($hashes as $alias=>$name) { $values[$name]=$row[$alias];unset($row[$alias]); }
        $row["\0u5_binary"]=$values;
    }
    return $row;
}

function u5CodeMigrationReadTable($table,$columns,$bounded=false) {
    $query=u5CodeMigrationSelect($columns,$bounded);
    $rows=u5CodeMigrationRows('SELECT '.$query['select'].' FROM '.u5CodeMigrationIdentifier($table));
    foreach ($rows as &$row) $row=u5CodeMigrationSnapshot($row,$query['hashes']);
    unset($row);return $rows;
}

function u5CodeMigrationEachRow($table,$columns,$callback,$tail='') {
    $query=u5CodeMigrationSelect($columns,true);
    $result=mysql_unbuffered_query('SELECT '.$query['select'].' FROM '.u5CodeMigrationIdentifier($table).$tail);
    if ($result===false) throw new RuntimeException('Cannot stream migration rows from '.$table.': '.mysql_error());
    try { while ($row=mysql_fetch_array($result,MYSQL_ASSOC)) $callback(u5CodeMigrationSnapshot($row,$query['hashes'])); }
    finally { mysql_free_result($result); }
}

function u5CodeMigrationWhere($row,$columns=array()) {
    $terms=array();
    foreach ($row as $field=>$value) {
        if ($field==="\0u5_binary") {
            foreach ($value as $name=>$hash) $terms[]='SHA2(CAST('.u5CodeMigrationIdentifier($name).' AS BINARY),256) <=> '.u5CodeMigrationValue($hash);
            continue;
        }
        $column=$columns[$field] ?? null;
        if ($column && strtolower($column['Type'])==='json') {
            $terms[]="BINARY JSON_EXTRACT(".u5CodeMigrationIdentifier($field).",'$') <=> BINARY JSON_EXTRACT(".u5CodeMigrationColumnValue($value,$column).",'$')";
            continue;
        }
        // Preserve exact bytes, with a separate sargable predicate for row/index identity.
        if ($column && !preg_match('/^(?:float|double|real)/i',$column['Type']) && (($column['Key'] ?? '')!=='' || preg_match('/^(?:(?:tiny|small|medium|big)?int|year)\b/i',$column['Type']))) $terms[]=u5CodeMigrationIdentifier($field).' <=> '.u5CodeMigrationColumnValue($value,$column);
        $terms[]='BINARY '.u5CodeMigrationIdentifier($field).' <=> '.u5CodeMigrationValue($value);
    }
    return implode(' AND ',$terms);
}

function u5CodeMigrationCharset($column) {
    $collation=$column['Collation'] ?? null;
    return $collation ? explode('_',$collation)[0] : 'binary';
}

function u5CodeMigrationDisplayText($bytes,$charset) {
    $rows=u5CodeMigrationRows('SELECT CONVERT('.u5CodeMigrationValue($bytes).' USING '.$charset.') AS content');
    return $rows[0]['content'];
}

function u5CodeMigrationUtf8Text($bytes,$charset) {
    $rows=u5CodeMigrationRows('SELECT CAST(CONVERT(CONVERT('.u5CodeMigrationValue($bytes).' USING '.$charset.') USING utf8mb4) AS BINARY) AS content');
    return $rows[0]['content'];
}

function u5CodeMigrationFileText($bytes,$charset) {
    return substr($bytes,0,3)==="\xEF\xBB\xBF" ? u5CodeMigrationConvertExact(substr($bytes,3),'utf8mb4',$charset) : u5CodeMigrationConvertExact($bytes,'latin1',$charset);
}

function u5CodeMigrationConvertExact($bytes,$source,$target) {
    foreach (array($source,$target) as $charset) if (!preg_match('/^[a-z0-9_]+$/iD',$charset)) throw new RuntimeException('Invalid database character set.');
    if ($bytes===null) return null;
    $value='CONVERT(CONVERT('.u5CodeMigrationValue($bytes).' USING '.$source.') USING '.$target.')';
    $rows=u5CodeMigrationRows('SELECT CAST('.$value.' AS BINARY) AS content,CAST(CONVERT('.$value.' USING '.$source.') AS BINARY) AS original');
    // MySQL can emit charset deprecation warnings even for an exact conversion.
    // The byte-for-byte round trip detects replacement/truncation without rejecting those notices.
    if ($rows[0]['original']!==$bytes) throw new RuntimeException('The source cannot be stored losslessly using '.$target.'. Adapt the database character set first; your original content has been kept.');
    return $rows[0]['content'];
}

function u5CodeMigrationReadJournal($path) {
    $plan=u5CodeMigrationReadGuarded($path);
    if (($plan['version'] ?? '')!=='12.11.0') throw new RuntimeException('Invalid migration checkpoint version.');
    return $plan;
}

function u5CodeMigrationCheckpoint($phase) {
    // An optional test hook also permits deterministic interruption/failure regression checks.
    if (function_exists('u5CodeMigrationTestCheckpoint')) u5CodeMigrationTestCheckpoint($phase);
}

function u5CodeMigrationNeedsLegacySchema() {
    $required=array(
        'resources'=>array('name','deleted','typ','logins'),
        'resources_log'=>array('name','deleted','typ','logins'),
        'languages'=>array('lan4na','lan5na','lan4name','lan5name'),
        'titlefixum'=>array('1','2','3','4','5'),
        'loginglobals'=>array(), 'loginattempts'=>array('mailed'),
        'mailing'=>array('mailtested'), 'mailingcron'=>array('id','done'),
        'sizes'=>array('tosquare','cropedge')
    );
    foreach (array('resources','resources_log') as $table) foreach (array('content','title','desc','key') as $field) foreach (range(1,5) as $language) $required[$table][]=$field.'_'.$language;
    foreach (array('logintitle','loginintro','username','password','loginbutton','loginoutro','logout','wait') as $field) foreach (range(1,5) as $language) $required['loginglobals'][]=$field.'_'.$language;
    foreach ($required as $table=>$fields) {
        $result=mysql_query('SHOW FULL COLUMNS FROM '.u5CodeMigrationIdentifier($table));
        if ($result===false) return true;
        $columns=array(); while ($row=mysql_fetch_array($result,MYSQL_ASSOC)) $columns[$row['Field']]=$row;
        foreach ($fields as $field) if (!isset($columns[$field])) return true;
    }
    return false;
}

function u5CodeMigrationOccupiedFiles($root,$resources) {
    $files=array();$entries=@scandir($root.'/r');
    if ($entries===false) throw new RuntimeException('Cannot inspect existing asset files.');
    foreach ($entries as $file) if ($file!=='.' && $file!=='..') $files[strtolower($file)]=false;
    // Existing legacy exports own their filenames before a typed export can claim them.
    foreach ($resources as $row) if (strtolower((string)$row['typ'])==='c') {
        $legacy=u5CodeAssetLegacyFilename($row['name']);
        if ($legacy!==null && is_file($root.'/r/'.$legacy) && !is_link($root.'/r/'.$legacy)) $files[strtolower($legacy)]=$row['name'];
    }
    foreach ($resources as $row) if (strtolower((string)$row['typ'])==='c' && u5CodeAssetIsTyped($row['name'])) {
        $legacy=u5CodeAssetLegacyFilename($row['name']);$file=u5CodeAssetFilename($row['name']);
        if (!is_file($root.'/r/'.$legacy) && is_file($root.'/r/'.$file) && !is_link($root.'/r/'.$file) && $files[strtolower($file)]===false) $files[strtolower($file)]=$row['name'];
    }
    return $files;
}

function u5CodeMigrationRetainedSource($root,$name,$fallback) {
    $file=u5CodeAssetLegacyFilename($name);
    if ($file===null || !is_file($root.'/r/'.$file)) return $fallback;
    $source=file_get_contents($root.'/r/'.$file);
    if (!is_string($source)) throw new RuntimeException('Cannot inspect retained code export '.$file);
    return $source;
}

// Repair only JSON changed by an earlier migration, and only if the entire row
// still matches that migration's saved result. Subsequent edits always win.
function u5CodeMigrationJsonRecovery($root,$backup) {
    $empty=function($table,$row) { return array(); };
    if (empty($backup['directory'])) return $empty;
    $directory=u5CodeMigrationChangesDirectory($root,$backup);
    if (!is_file($root.'/'.$directory.'/manifest.php')) return $empty;
    $manifest=u5CodeMigrationReadGuarded($root.'/'.$directory.'/manifest.php');
    $journal=$manifest['journal'] ?? array();
    if (($journal['format'] ?? '')!=='chunked-v1' || ($journal['directory'] ?? '')!==$directory) throw new RuntimeException('Invalid saved migration row manifest.');
    if (($journal['implementation'] ?? '')==='12.11.2') return $empty;
    $index=array();
    u5CodeMigrationEachChange($root,$journal,function($change,$number,$location) use(&$index) {
        $fields=array();
        foreach ($change['before'] as $field=>$value) {
            if (!is_string($value) || $value===($change['after'][$field] ?? null) || preg_match('//u',$value)===1) continue;
            $trimmed=ltrim($value);
            if (!in_array($trimmed[0] ?? '',array('{','[','"'),true)) continue;
            json_decode(u5CodeAssetUtf8($value),true);
            if (json_last_error()===JSON_ERROR_NONE) $fields[$field]=$value;
        }
        if (!$fields) return;
        $key=$change['table'].':'.hash('sha256',serialize($change['after']));
        $fingerprint=hash('sha256',serialize($fields));
        if (array_key_exists($key,$index) && ($index[$key]===false || $index[$key]['fingerprint']!==$fingerprint)) $index[$key]=false;
        else $index[$key]=$location+array('fingerprint'=>$fingerprint,'fields'=>array_keys($fields));
    });
    $cachedPath=null;$cached=array();
    return function($table,$row) use($root,$index,&$cachedPath,&$cached) {
        $key=$table.':'.hash('sha256',serialize($row));
        if (empty($index[$key])) return array();
        $found=$index[$key];$chunk=$found['chunk'];
        if ($cachedPath!==$chunk['path']) { $cached=u5CodeMigrationReadChangeBlock($root,$chunk);$cachedPath=$chunk['path']; }
        $change=$cached[$found['position']];
        if ($change['table']!==$table || $change['after']!==$row) return array();
        return array_intersect_key($change['before'],array_flip($found['fields']));
    };
}

function u5CodeMigrationBuildPlan($root,$previousMap=array(),$previousBackup=array()) {
    u5CodeMigrationHistorySchema();
    $schema=array();
    foreach (u5CodeMigrationRows('SHOW FULL TABLES') as $tableRow) {
        $values=array_values($tableRow);
        if (isset($values[1]) && $values[1]!=='BASE TABLE') continue;
        $table=$values[0];
        $columns=u5CodeMigrationColumns($table); $hasText=false;
        foreach ($columns as $column) if (preg_match('/char|text|json|enum|set/i',$column['Type'])) $hasText=true;
        if (!$hasText) continue;
        $schema[$table]=$columns;
    }
    if (!isset($schema['resources'])) throw new RuntimeException('Resources table is missing.');
    $resources=array(); $uses='';
    u5CodeMigrationEachRow('resources',$schema['resources'],function($row) use($root,&$resources,&$uses) {
        $summary=array('name'=>$row['name'],'typ'=>$row['typ'],'deleted'=>$row['deleted']);
        if (strtolower((string)$row['typ'])==='c') $summary['content_1']=(int)$row['deleted']===1 ? u5CodeMigrationRetainedSource($root,$row['name'],$row['content_1']) : $row['content_1'];
        $resources[]=$summary;
        foreach ($row as $field=>$value) if (strpos($field,'content_')===0 && is_string($value)) {
            preg_match_all('~<script\b[^>]*>|(?:loadScript|getScript)\s*\(\s*["\'][^"\']+["\']~i',$value,$hits);
            if ($hits[0]) $uses.=implode("\n",$hits[0])."\n";
        }
    });
    $nameRows=$resources;$currentCode=array();$historicalAliases=array();$nonCode=array();
    foreach ($resources as $row) {
        if (strtolower((string)$row['typ'])==='c') $currentCode[$row['name']]=true;
        elseif ((int)$row['deleted']!==1) $nonCode[strtolower($row['name'])]=true;
    }
    $seen=$currentCode;
    // Historical C objects have their own namespace even when a current P object reused that name.
    if (isset($schema['resources_log'])) u5CodeMigrationEachRow('resources_log',$schema['resources_log'],function($row) use($root,&$seen,&$historicalAliases,&$nameRows,$currentCode,$previousMap,$uses) {
        if (isset($seen[$row['name']])) return;
        $seen[$row['name']]=true;
        $source=u5CodeMigrationRetainedSource($root,$row['name'],$row['content_1']);
        list($likely)=u5CodeAssetGuess($row['name'],(string)$source,$uses);
        $likely=$previousMap[$row['name']] ?? $likely;
        if (isset($currentCode[$likely])) $historicalAliases[$row['name']]=$likely;
        else $nameRows[]=array('name'=>$row['name'],'typ'=>'c','deleted'=>1,'content_1'=>$source);
    }," WHERE typ='c' ORDER BY lastmut DESC");
    $occupied=u5CodeMigrationOccupiedFiles($root,$nameRows);
    $names=u5CodeAssetPlanNames($nameRows,$uses,$occupied);$map=array_merge($previousMap,$names['map'],$historicalAliases);$notes=$names['notes'];
    // Follow previous role aliases if a further collision forced its typed target to move.
    foreach ($map as $old=>&$target) if (isset($names['map'][$target])) $target=$names['map'][$target];
    unset($target);
    foreach ($names['map'] as $old=>$target) if (isset(u5CodeAssetDefaults()[strtolower($old)])) $map[strtolower($old)]=$target;
    $defaults=u5CodeAssetDefaults(); $additions=array();
    $charset=u5CodeMigrationCharset($schema['resources']['content_1']);
    // Every installation gets the common mobile code. Existing local customizations win.
    foreach ($defaults as $old=>$standard) {
        $target=$map[$old] ?? $standard; $active=false;
        foreach ($resources as $row) if (strtolower((string)$row['typ'])==='c' && (int)$row['deleted']!==1 &&
            (strcasecmp($row['name'],$old)===0 || strcasecmp($row['name'],$target)===0)) $active=true;
        if ($active) continue;
        // Deleted/history-only code remains deleted. Its retained file is migrated separately.
        if (isset(array_change_key_case($seen,CASE_LOWER)[$old]) && $old!=='jsmobilegeneral') continue;
        $legacy=$root.'/r/'.$old.'.css';
        $source=null; $reason='';
        if (is_file($legacy)) { $source=file_get_contents($legacy); $reason='existing exported file imported'; }
        elseif ($old==='jsmobilegeneral') {
            $source=u5CodeMigrationMobileSource(); $reason='bundled common mobile code installed';
        }
        if ($source===null) continue;
        $candidate=array('name'=>$old,'content_1'=>u5CodeMigrationFileText($source,$charset),'typ'=>'c','deleted'=>0,
            'hidden'=>0,'ishomepage'=>0,'lastmut'=>time(),'operator'=>'u5CMS 12.11.2','ip'=>($_SERVER['REMOTE_ADDR'] ?? ''));
        if (!isset($map[$old])) {
            $probe=$nameRows; $probe[]=$candidate;
            $resolved=u5CodeAssetPlanNames($probe,$uses,$occupied);
            $target=$resolved['map'][$old]; $map[$old]=$target;
        }
        $candidate['name']=$target; $additions[]=$candidate;
        $notes[]=array('from'=>$old,'to'=>$target,'reason'=>$reason);
    }
    $effective=array_merge($defaults,$map);$objectMap=$effective;
    foreach ($objectMap as $old=>$target) if (isset($nonCode[strtolower($old)])) unset($objectMap[$old]);
    $recoverJson=u5CodeMigrationJsonRecovery($root,$previousBackup);
    $updates=u5CodeMigrationStartChanges(); $afterResources=array();
    try {
    foreach ($schema as $table=>$columns) u5CodeMigrationEachRow($table,$columns,function($before) use($schema,$table,$effective,$objectMap,$root,$recoverJson,&$updates,&$afterResources) {
        $after=$before;$originalJson=$recoverJson($table,$before);
        foreach ($schema[$table] as $field=>$column) {
            if (!array_key_exists($field,$before) || !preg_match('/char|text|json|enum|set/i',$column['Type']) || !is_string($before[$field])) continue;
            if ($field==='name' && ($table==='resources' || $table==='resources_log') && strtolower((string)($before['typ'] ?? ''))==='c') {
                $after[$field]=$effective[$before[$field]] ?? $before[$field];
            } else {
                $format=(($table==='resources' || $table==='resources_log') && strtolower((string)($before['typ'] ?? ''))==='c' && strpos($field,'content_')===0) ? u5CodeAssetFormat($before['name'],$effective) : 'html';
                $after[$field]=u5CodeAssetReferences($originalJson[$field] ?? $before[$field],$effective,0,$format,$objectMap);
            }
        }
        if ($table==='resources') u5CodeMigrationRevision($before,$after);
        u5CodeMigrationValidateRow($table,$before,$after,$schema[$table]);
        if ($after!==$before) u5CodeMigrationQueueChange($root,$updates,array('table'=>$table,'before'=>$before,'after'=>$after));
        if ($table==='resources' && strtolower((string)($after['typ'] ?? ''))==='c') $afterResources[]=$after;
    });
    foreach ($additions as &$addition) {
        $addition['content_1']=u5CodeAssetReferences($addition['content_1'],$effective,0,u5CodeAssetFormat($addition['name']),$objectMap);
        u5CodeMigrationValidateRow('resources',array(),$addition,$schema['resources']);
        $afterResources[]=$addition;
    }
    unset($addition);
    $displayRows=$afterResources;
    foreach ($displayRows as &$row) if (strtolower((string)$row['typ'])==='c') {
        $row['u5_code_utf8']=u5CodeMigrationUtf8Text((string)$row['content_1'],$charset);
        $row['content_1']=u5CodeMigrationDisplayText((string)$row['content_1'],$charset);
    }
    unset($row);
    $files=u5CodeAssetRowsToFiles($displayRows,time());$oldFiles=array();
    // A retained export of deleted code can still be linked by another installation.
    // Move its actual bytes without reactivating that database resource.
    foreach ($map as $old=>$new) {
        $oldFile=u5CodeAssetLegacyFilename($old);$newFile=u5CodeAssetFilename($new);
        if ($oldFile===null || isset($files[$newFile]) || !is_file($root.'/r/'.$oldFile)) continue;
        $body=file_get_contents($root.'/r/'.$oldFile);
        if (!is_string($body)) throw new RuntimeException('Cannot read retained code export '.$oldFile);
        $body=u5CodeAssetReferences($body,$effective,0,u5CodeAssetFormat($new),$objectMap);
        $files[$newFile]=$body;
    }
    foreach ($effective as $old=>$new) {
        $oldFile=u5CodeAssetLegacyFilename($old);
        if ($oldFile===null) continue;
        if (!isset($files[$oldFile]) && is_file($root.'/r/'.$oldFile)) $oldFiles[]='r/'.$oldFile;
    }
    $template=$effective['htmltemplate'];
    $foundTemplate=false;
    foreach ($afterResources as $row) if ($row['name']===$template && strtolower((string)$row['typ'])==='c' && (int)$row['deleted']!==1) $foundTemplate=true;
    // A typed fresh installation already has the canonical template.
    if (!$foundTemplate) {
        $template='htmltemplate!html';
        foreach ($afterResources as $row) if ($row['name']===$template && strtolower((string)$row['typ'])==='c' && (int)$row['deleted']!==1) $foundTemplate=true;
    }
    if (!$foundTemplate || !isset($files[u5CodeAssetFilename($template)])) throw new RuntimeException('The active HTML template could not be identified. No database changes were made.');
    $historyCount=u5CodeMigrationRows('SELECT COUNT(*) AS n FROM resources_log');
    $updates=u5CodeMigrationFinishChanges($root,$updates);
    return array('version'=>'12.11.0','audit'=>'12.11.1','implementation'=>'12.11.2','map'=>$effective,'notes'=>$notes,'updates'=>$updates,
        'additions'=>$additions,'files'=>$files,'oldFiles'=>array_values(array_unique($oldFiles)),
        'template'=>$template,'initializeHistory'=>(int)$historyCount[0]['n']===0,'created'=>time());
    } catch (Throwable $error) { u5CodeMigrationAbortChanges($root,$updates);throw $error; }
}

function u5CodeMigrationSafeBackupPath($relative) {
    foreach (array('r/','u5admin/code-templates/') as $prefix) {
        if (strpos($relative,$prefix)!==0) continue;
        $name=substr($relative,strlen($prefix));
        if ($name!=='' && $name!=='.' && $name!=='..' && strpos($name,'/')===false && strpos($name,'\\')===false && strpos($name,"\0")===false) return true;
    }
    return false;
}

function u5CodeMigrationBackupFile($root,$relative) {
    if (!u5CodeMigrationSafeBackupPath($relative)) throw new RuntimeException('Invalid migration backup path.');
    if (!is_file($root.'/'.$relative)) return;
    $directory=$root.'/fileversions/codeassets-12.11.0-files';
    if (!is_dir($directory) && !@mkdir($directory,0700,true)) throw new RuntimeException('Cannot create asset migration backup directory.');
    $backup=$directory.'/'.sha1($relative).'.php';
    $bytes=file_get_contents($root.'/'.$relative);
    if (!is_string($bytes)) throw new RuntimeException('Cannot back up '.$relative);
    if (is_file($backup)) {
        $saved=u5CodeMigrationReadGuarded($backup);
        if (($saved['bytes'] ?? null)===$bytes) return;
        // Keep the first migration backup for compatibility recovery and subsequent distinct versions too.
        $backup=$directory.'/'.sha1($relative).'-'.substr(hash('sha256',$bytes),0,20).'.php';
    }
    if (!is_file($backup)) u5CodeAssetAtomicWrite($backup,u5CodeMigrationEncodeJournal(array('path'=>$relative,'bytes'=>$bytes)));
}

function u5CodeMigrationFileBackup($root,$relative) {
    if (!u5CodeMigrationSafeBackupPath($relative)) throw new RuntimeException('Invalid migration backup path.');
    $path=$root.'/fileversions/codeassets-12.11.0-files/'.sha1($relative).'.php';
    if (!is_file($path)) return null;
    $saved=u5CodeMigrationReadGuarded($path);
    if (!is_array($saved) || ($saved['path'] ?? '')!==$relative || !is_string($saved['bytes'] ?? null)) throw new RuntimeException('Invalid migration file backup.');
    return $saved['bytes'];
}

function u5CodeMigrationTemplatePlan($root,$map,$preferTyped=false) {
    $files=array();$sources=array();$remove=array();$used=array();$defaults=u5CodeAssetDefaults();
    foreach ($map as $old=>$new) {
        if (!u5CodeAssetIsTyped($new) || u5CodeAssetLegacyFilename($old)===null) continue;
        $target='r/'.$new.'.tpl';
        if (isset($files[$target])) continue;
        $legacy='r/'.$old.'.tpl';$body=null;$source=null;
        // After an earlier completed update a subsequently edited typed template wins.
        if ($preferTyped && is_file($root.'/'.$target)) { $body=file_get_contents($root.'/'.$target);$source=$target; }
        if ($body===null && is_file($root.'/'.$legacy)) { $body=file_get_contents($root.'/'.$legacy);$source=$legacy; }
        if ($body===null) $body=u5CodeMigrationFileBackup($root,$legacy);
        if ($body===null && is_file($root.'/'.$target)) { $body=file_get_contents($root.'/'.$target);$source=$target; }
        // Recover installations that already used the first 12.11.0 preview.
        $previous='u5admin/code-templates/'.($defaults[$old] ?? $new).'.tpl';
        $previousBelongsHere=isset($defaults[$old]) || !in_array($new,$defaults,true);
        if ($body===null && $previousBelongsHere && !isset($used[$previous]) && is_file($root.'/'.$previous)) { $body=file_get_contents($root.'/'.$previous);$source=$previous; }
        if ($body===null && $previousBelongsHere && !isset($used[$previous])) $body=u5CodeMigrationFileBackup($root,$previous);
        if ($body===null && $old==='jsmobilegeneral') {
            $common='r/jsmobilegeneral.css';
            if (is_file($root.'/'.$common)) $body=file_get_contents($root.'/'.$common);
            if ($body===null) $body=u5CodeMigrationFileBackup($root,$common);
            if ($body===null) {
                $rows=u5CodeMigrationRows("SELECT content_1 FROM resources WHERE typ='c' AND deleted!=1 AND (BINARY name=".u5CodeMigrationValue($old).' OR BINARY name='.u5CodeMigrationValue($new).') ORDER BY deleted=0 DESC LIMIT 1');
                $body=$rows ? (string)$rows[0]['content_1'] : u5CodeMigrationMobileSource();
            }
        }
        if ($body===null) continue;
        if (!is_string($body)) throw new RuntimeException('Cannot read reset template '.$legacy);
        $before=is_file($root.'/'.$target) ? file_get_contents($root.'/'.$target) : null;
        $files[$target]=array('before'=>$before,'after'=>u5CodeAssetReferences($body,$map,0,u5CodeAssetFormat($new)));
        if ($source!==null) {
            $used[$source]=true;$sources[$source]=$body;
            if ($source!==$target) $remove[]=$source;
        }
    }
    // The obsolete preview folder is no longer a source. Back up its known files before removal.
    foreach ($defaults as $old=>$canonical) {
        $target='r/'.($map[$old] ?? $canonical).'.tpl';
        $previous='u5admin/code-templates/'.$canonical.'.tpl';
        if (isset($files[$target]) && is_file($root.'/'.$previous)) {
            $sources[$previous]=file_get_contents($root.'/'.$previous);$remove[]=$previous;
        }
    }
    $remove=array_values(array_diff(array_unique($remove),array_keys($files)));
    return array('version'=>'12.11.0','files'=>$files,'sources'=>$sources,'remove'=>$remove);
}

function u5CodeMigrationPublishTemplates($root,$plan) {
    foreach ($plan['sources'] as $relative=>$before) {
        if (!is_file($root.'/'.$relative)) continue;
        $current=file_get_contents($root.'/'.$relative);
        if ($current!==$before && $current!==($plan['files'][$relative]['after'] ?? null)) throw new RuntimeException('A reset template changed during migration: '.$relative);
        u5CodeMigrationBackupFile($root,$relative);
    }
    foreach ($plan['files'] as $relative=>$change) {
        $current=is_file($root.'/'.$relative) ? file_get_contents($root.'/'.$relative) : null;
        if ($current===$change['after']) continue;
        if ($current!==$change['before']) throw new RuntimeException('A reset template changed during migration: '.$relative);
        u5CodeMigrationBackupFile($root,$relative);
    }
    $index=0;
    foreach ($plan['files'] as $relative=>$change) {
        $current=is_file($root.'/'.$relative) ? file_get_contents($root.'/'.$relative) : null;
        if ($current!==$change['after']) {
            if ($current!==$change['before']) throw new RuntimeException('A reset template changed during migration: '.$relative);
            u5CodeAssetAtomicWrite($root.'/'.$relative,$change['after']);
        }
        u5CodeMigrationCheckpoint('template:'.$index++);
    }
}

function u5CodeMigrationCleanupTemplates($root,$plan) {
    $index=0;
    foreach ($plan['remove'] as $relative) {
        // A legacy directory may be a link to a shared installation's templates.
        if (strpos($relative,'u5admin/code-templates/')===0 && is_link($root.'/u5admin/code-templates')) continue;
        if (!is_file($root.'/'.$relative)) continue;
        if (file_get_contents($root.'/'.$relative)!==$plan['sources'][$relative]) throw new RuntimeException('A reset template changed during migration: '.$relative);
        u5CodeMigrationBackupFile($root,$relative);
        if (!@unlink($root.'/'.$relative)) throw new RuntimeException('Cannot remove obsolete reset template '.$relative);
        u5CodeMigrationCheckpoint('template-cleanup:'.$index++);
    }
    // rmdir only succeeds for an empty directory; unrelated local files are retained.
    if (is_dir($root.'/u5admin/code-templates')) @rmdir($root.'/u5admin/code-templates');
}

function u5CodeMigrationTemplates($root,$map,$preferTyped=false,$phase='12.11.0') {
    $pending=$root.'/fileversions/codeassets-'.$phase.'-templates.pending.php';
    $complete=$root.'/fileversions/codeassets-'.$phase.'-templates.done.php';
    if (is_file($complete)) {
        if (is_file($pending) && !@unlink($pending)) throw new RuntimeException('Cannot finish the reset template checkpoint.');
        return;
    }
    $plan=is_file($pending) ? u5CodeMigrationReadJournal($pending) : u5CodeMigrationTemplatePlan($root,$map,$preferTyped);
    if (!is_file($pending)) u5CodeAssetAtomicWrite($pending,u5CodeMigrationEncodeJournal($plan));
    u5CodeMigrationPublishTemplates($root,$plan);
    u5CodeMigrationCheckpoint('templates-written');
    u5CodeMigrationCleanupTemplates($root,$plan);
    u5CodeAssetAtomicWrite($complete,"<?php\nreturn ".var_export(array('version'=>'12.11.0','completed'=>time()),true).";\n");
    u5CodeMigrationCheckpoint('templates-complete');
    if (!@unlink($pending)) throw new RuntimeException('Cannot finish the reset template checkpoint.');
}

function u5CodeMigrationRenameTemplates($root,$old,$new,$updateLinks) {
    $files=array();$sources=array();$remove=array();
    foreach (glob($root.'/r/*.tpl') as $path) {
        $relative='r/'.basename($path);$target=$relative;
        if (basename($path)===$old.'.tpl') $target='r/'.$new.'.tpl';
        if ($target!==$relative && file_exists($root.'/'.$target)) throw new RuntimeException('The target reset template already exists.');
        $body=file_get_contents($path);
        if (!is_string($body)) throw new RuntimeException('Cannot read reset template '.$relative);
        $after=$updateLinks ? u5CodeAssetReferences($body,array($old=>$new),0,u5CodeAssetFormat(basename($target,'.tpl'))) : $body;
        if ($target===$relative && $after===$body) continue;
        $files[$target]=array('before'=>$target===$relative ? $body : null,'after'=>$after);
        $sources[$relative]=$body;
        if ($target!==$relative && $updateLinks) $remove[]=$relative;
    }
    return array('version'=>'12.11.0','files'=>$files,'sources'=>$sources,'remove'=>$remove);
}

function u5CodeMigrationApplyRow($change,$columns=null) {
    if ($columns===null) $columns=u5CodeMigrationColumns($change['table']);
    $before=$change['before']; $after=$change['after']; $table=u5CodeMigrationIdentifier($change['table']);
    $set=array(); $compare=array();
    foreach ($after as $field=>$value) if ($value!==$before[$field]) { $set[]=u5CodeMigrationIdentifier($field).'='.u5CodeMigrationColumnValue($value,$columns[$field]); $compare[$field]=$before[$field]; }
    if (!$set) return;
    // Use the whole original row, including nulls, instead of a guessed primary key.
    // This also handles duplicate history rows in the legacy MyISAM tables.
    $count=u5CodeMigrationRows('SELECT COUNT(*) AS n FROM '.$table.' WHERE '.u5CodeMigrationWhere($before,$columns));
    if ((int)$count[0]['n']===0) {
        $done=u5CodeMigrationRows('SELECT COUNT(*) AS n FROM '.$table.' WHERE '.u5CodeMigrationWhere($after,$columns));
        if ((int)$done[0]['n']>0) return;
        throw new RuntimeException('A database row changed during migration in '.$change['table'].'. The checkpoint was kept; no concurrent edit was overwritten.');
    }
    u5CodeMigrationQuery('UPDATE '.$table.' SET '.implode(',',$set).' WHERE '.u5CodeMigrationWhere($before,$columns));
    if (mysql_affected_rows()!==(int)$count[0]['n']) throw new RuntimeException('A row changed while applying migration in '.$change['table']);
}

function u5CodeMigrationInsert($table,$row) {
    $columns=u5CodeMigrationColumns($table); $fields=array(); $values=array(); $expected=array();
    foreach ($row as $field=>$value) if (isset($columns[$field])) { $fields[]=u5CodeMigrationIdentifier($field); $values[]=u5CodeMigrationColumnValue($value,$columns[$field]); $expected[$field]=$value; }
    if ($table==='resources_log') {
        $same=u5CodeMigrationRows('SELECT COUNT(*) AS n FROM '.u5CodeMigrationIdentifier($table).' WHERE '.u5CodeMigrationWhere($expected,$columns));
        if ((int)$same[0]['n']>0) return;
        u5CodeMigrationQuery('INSERT INTO '.u5CodeMigrationIdentifier($table).' ('.implode(',',$fields).') VALUES ('.implode(',',$values).')');
        return;
    }
    $where="BINARY name=".u5CodeMigrationValue($row['name'])." AND deleted=0";
    $count=u5CodeMigrationRows('SELECT COUNT(*) AS n FROM '.u5CodeMigrationIdentifier($table).' WHERE '.$where);
    if ((int)$count[0]['n']>0) {
        $same=u5CodeMigrationRows('SELECT COUNT(*) AS n FROM '.u5CodeMigrationIdentifier($table).' WHERE '.u5CodeMigrationWhere($expected,$columns));
        if (!(int)$same[0]['n']) throw new RuntimeException('A different resource already occupies '.$row['name']);
        return;
    }
    u5CodeMigrationQuery('INSERT INTO '.u5CodeMigrationIdentifier($table).' ('.implode(',',$fields).') VALUES ('.implode(',',$values).')');
}

function u5CodeMigrationRemoveMimeRule($root) {
    $path=$root.'/r/.htaccess';
    if (!is_file($path)) return;
    $data=file_get_contents($path);
    $block="# These legacy CSS resources contain JavaScript. Their source and references\n# also live in the database, so keep the existing URLs when setting the MIME type.\n<FilesMatch \"^jsmobile(specific|general)\\.css$\">\n    ForceType text/javascript\n</FilesMatch>\n";
    $unix=str_replace("\r\n","\n",$data);
    if (strpos($unix,$block)===false) return;
    u5CodeMigrationBackupFile($root,'r/.htaccess');
    $new=str_replace($block,'',$unix);
    if (trim($new)==='') { if (!@unlink($path)) throw new RuntimeException('Cannot remove the obsolete mobile MIME rule.'); }
    else u5CodeAssetAtomicWrite($path,strpos($data,"\r\n")!==false ? str_replace("\n","\r\n",$new) : $new);
}

function u5CodeAssetsMigrate($root) {
    $release=u5CodeMigrationLock($root);
    try {
        // Finish an older immutable checkpoint before applying the next repair.
        u5CodeAssetsMigrateLocked($root);
        if (!is_file($root.'/fileversions/codeassets-12.11.2.done.php') || is_file($root.'/fileversions/codeassets-12.11.2.pending.php')) u5CodeAssetsMigrateLocked($root,'12.11.2');
        u5MobileToolsMigrate($root);
    }
    finally { $release(); }
}

function u5CodeMigrationCleanupAssets($root,$plan) {
    $active=array();
    $result=u5CodeMigrationQuery("SELECT name FROM resources WHERE typ='c' AND deleted!=1");
    while ($row=mysql_fetch_array($result,MYSQL_ASSOC)) if (u5CodeAssetIsTyped($row['name'])) $active['r/'.u5CodeAssetFilename($row['name'])]=true;
    foreach ($plan['oldFiles'] as $relative) {
        // Old preview journals also listed templates; the template phase owns those.
        if (substr(strtolower($relative),-4)==='.tpl' || isset($active[$relative])) continue;
        u5CodeMigrationBackupFile($root,$relative);
        if (is_file($root.'/'.$relative) && !@unlink($root.'/'.$relative)) throw new RuntimeException('Cannot remove obsolete asset '.$relative);
    }
}

function u5CodeAssetsMigrateLocked($root,$patchVersion='12.11.1') {
    $base=$root.'/fileversions/codeassets-12.11.0';$patch=$root.'/fileversions/codeassets-'.$patchVersion;
    $complete=$base.'.done.php';$pending=$base.'.pending.php';$previous=array();$repair=false;
    if (is_file($complete)) {
        $previous=include $complete;
        u5CodeMigrationTemplates($root,$previous['map'],true);
        if (is_file($pending)) {
            u5CodeMigrationCleanupAssets($root,u5CodeMigrationReadJournal($pending));
            if (!@unlink($pending)) throw new RuntimeException('Cannot finish the asset migration checkpoint.');
        }
        if (is_file($patch.'.done.php')) {
            // A crash after writing the completion marker can leave only cleanup outstanding.
            if (is_file($patch.'.pending.php')) {
                u5CodeMigrationCleanupAssets($root,u5CodeMigrationReadJournal($patch.'.pending.php'));
                if (!@unlink($patch.'.pending.php')) throw new RuntimeException('Cannot finalize the patch checkpoint.');
            }
            u5CodeAssetExport($root);u5CodeMigrationRemoveMimeRule($root);return;
        }
        $pending=$patch.'.pending.php';$repair=true;
    }
    if (is_file($pending)) $plan=u5CodeMigrationReadJournal($pending);
    else {
        $plan=u5CodeMigrationBuildPlan($root,$previous['map'] ?? array(),$previous['row_backup'] ?? array());
        try { u5CodeAssetAtomicWrite($pending,u5CodeMigrationEncodeJournal($plan)); }
        catch (Throwable $error) { u5CodeMigrationAbortChanges($root,$plan['updates']);throw $error; }
    }
    u5CodeMigrationCheckpoint('planned');
    u5CodeMigrationValidateChanges($root,$plan['updates']);
    u5CodeMigrationTemplates($root,$plan['map'],$repair,$repair ? $patchVersion : '12.11.0');
    foreach ($plan['files'] as $file=>$data) {
        u5CodeMigrationBackupFile($root,'r/'.$file);u5CodeAssetAtomicWrite($root.'/r/'.$file,$data);
    }
    u5CodeMigrationCheckpoint('files');
    u5CodeMigrationApplyChanges($root,$plan['updates']);
    foreach ($plan['additions'] as $row) { u5CodeMigrationInsert('resources',$row);u5CodeMigrationInsert('resources_log',$row); }
    if (!empty($plan['initializeHistory'])) u5CodeMigrationInitializeHistory();
    u5CodeMigrationCheckpoint('database');
    u5CodeAssetExport($root);u5CodeMigrationRemoveMimeRule($root);
    $report='u5CMS '.($plan['implementation'] ?? $plan['audit'] ?? '12.11.0')." code resource migration\n";
    foreach ($plan['notes'] as $note) $report.=$note['from'].' -> '.$note['to'].' ('.$note['reason'].")\n";
    u5CodeAssetAtomicWrite($base.'-report.php',"<?php exit; ?>\n".$report);
    u5CodeMigrationCheckpoint('exported');
    $state=array('version'=>'12.11.0','template'=>$plan['template'],'map'=>$plan['map'],'completed'=>time());
    if (($plan['updates']['format'] ?? '')==='chunked-v1') $state['row_backup']=array('directory'=>$plan['updates']['directory'],'count'=>$plan['updates']['count']);
    u5CodeAssetAtomicWrite($complete,"<?php\nreturn ".var_export($state,true).";\n");
    if (($plan['audit'] ?? '')==='12.11.1') {
        $patchState=array('version'=>$patchVersion,'completed'=>time(),'row_backup'=>$state['row_backup'] ?? null);
        u5CodeAssetAtomicWrite($patch.'.done.php',"<?php\nreturn ".var_export($patchState,true).";\n");
        if (($plan['implementation'] ?? '')==='12.11.2') {
            $patchState['version']='12.11.2';
            u5CodeAssetAtomicWrite($root.'/fileversions/codeassets-12.11.2.done.php',"<?php\nreturn ".var_export($patchState,true).";\n");
        }
    }
    // Keep old URLs until the new files and all database references have been published.
    u5CodeMigrationCleanupAssets($root,$plan);u5CodeMigrationCheckpoint('complete');
    if (!@unlink($pending)) throw new RuntimeException('Cannot finish the asset migration checkpoint.');
    // An interrupted older release can finish its own immutable plan before the patch repair.
    if (($plan['audit'] ?? '')!=='12.11.1') u5CodeAssetsMigrateLocked($root,$patchVersion);
}

function u5CodeAssetsRename($root,$old,$new,$updateLinks=true) {
    $release=u5CodeMigrationLock($root);
    try { u5CodeAssetsRenameLocked($root,$old,$new,$updateLinks); }
    finally { $release(); }
}

function u5CodeAssetsRenameLocked($root,$old,$new,$updateLinks=true) {
    u5CodeAssetFilename($old); u5CodeAssetFilename($new);
    u5CodeMigrationHistorySchema();
    if ($old===$new) throw new RuntimeException('The new code name must be different.');
    $pending=$root.'/fileversions/codeasset-rename-'.sha1($old."\0".$new).'.pending.php';
    if (is_file($pending)) $plan=u5CodeMigrationReadJournal($pending);
    else {
        if (u5CodeAssetFilename($new)!==u5CodeAssetFilename($old) && file_exists($root.'/r/'.u5CodeAssetFilename($new))) throw new RuntimeException('The target code file already exists.');
        $resources=u5CodeMigrationRows('SELECT name,typ,deleted FROM resources');$found=false;
        foreach ($resources as $row) {
            if ($row['name']===$new) throw new RuntimeException('The target code name already exists, including a retained deleted version.');
            if ($row['name']===$old && strtolower((string)$row['typ'])==='c' && (int)$row['deleted']!==1) $found=true;
        }
        if (!$found) throw new RuntimeException('The code object to rename is missing.');
        $changes=u5CodeMigrationStartChanges();$afterResources=array();
        try {
        foreach (u5CodeMigrationRows('SHOW FULL TABLES') as $tableRow) {
            $values=array_values($tableRow);if (($values[1] ?? '')!=='BASE TABLE') continue;
            $table=$values[0];$columns=u5CodeMigrationColumns($table);
            u5CodeMigrationEachRow($table,$columns,function($before) use($root,$table,$columns,$old,$new,$updateLinks,&$changes,&$afterResources) {
                $after=$before;
                foreach ($columns as $field=>$column) {
                    if (!array_key_exists($field,$before) || !is_string($before[$field]) || !preg_match('/char|text|json|enum|set/i',$column['Type'])) continue;
                    if ($field==='name' && in_array($table,array('resources','resources_log'),true) && strtolower((string)($before['typ'] ?? ''))==='c' && $before[$field]===$old) $after[$field]=$new;
                    elseif ($updateLinks) {
                        $format=(in_array($table,array('resources','resources_log'),true) && strtolower((string)($before['typ'] ?? ''))==='c' && strpos($field,'content_')===0) ? u5CodeAssetFormat($before['name'],array($old=>$new)) : 'html';
                        $after[$field]=u5CodeAssetReferences($before[$field],array($old=>$new),0,$format);
                    }
                }
                if ($table==='resources') u5CodeMigrationRevision($before,$after);
                u5CodeMigrationValidateRow($table,$before,$after,$columns);
                if ($after!==$before) u5CodeMigrationQueueChange($root,$changes,array('table'=>$table,'before'=>$before,'after'=>$after));
                if ($table==='resources' && strtolower((string)($after['typ'] ?? ''))==='c') $afterResources[]=$after;
            });
        }
        $columns=u5CodeMigrationColumns('resources');$charset=u5CodeMigrationCharset($columns['content_1']);
        foreach ($afterResources as &$row) if (strtolower((string)$row['typ'])==='c') {
            $row['u5_code_utf8']=u5CodeMigrationUtf8Text((string)$row['content_1'],$charset);
            $row['content_1']=u5CodeMigrationDisplayText((string)$row['content_1'],$charset);
        }
        unset($row);
        $changes=u5CodeMigrationFinishChanges($root,$changes);
        $plan=array('version'=>'12.11.0','updates'=>$changes,'files'=>u5CodeAssetRowsToFiles($afterResources,time()),
            'templates'=>u5CodeMigrationRenameTemplates($root,$old,$new,$updateLinks));
        u5CodeAssetAtomicWrite($pending,u5CodeMigrationEncodeJournal($plan));
        } catch (Throwable $error) { u5CodeMigrationAbortChanges($root,$changes);throw $error; }
    }
    u5CodeMigrationValidateChanges($root,$plan['updates']);
    if (!isset($plan['templates'])) {
        $plan['templates']=u5CodeMigrationRenameTemplates($root,$old,$new,$updateLinks);
        u5CodeAssetAtomicWrite($pending,u5CodeMigrationEncodeJournal($plan));
    }
    // Publish the new target before replacing stylesheets/templates which reference it.
    $newFile=u5CodeAssetFilename($new);
    if (isset($plan['files'][$newFile])) u5CodeAssetAtomicWrite($root.'/r/'.$newFile,$plan['files'][$newFile]);
    foreach ($plan['files'] as $file=>$body) {
        u5CodeMigrationBackupFile($root,'r/'.$file);
        u5CodeAssetAtomicWrite($root.'/r/'.$file,$body);
    }
    u5CodeMigrationPublishTemplates($root,$plan['templates']);
    u5CodeMigrationApplyChanges($root,$plan['updates']);
    $marker=$root.'/fileversions/codeassets-12.11.0.done.php';
    $state=is_file($marker) ? include $marker : array('version'=>'12.11.0','template'=>'htmltemplate!html','map'=>array());
    if ($state['template']===$old) $state['template']=$new;
    foreach ($state['map'] as &$mapped) if ($mapped===$old) $mapped=$new;
    unset($mapped);$state['map'][$old]=$new;
    u5CodeAssetAtomicWrite($marker,"<?php\nreturn ".var_export($state,true).";\n");
    $oldFile=u5CodeAssetFilename($old);
    if ($updateLinks && $oldFile!==$newFile && is_file($root.'/r/'.$oldFile)) {
        u5CodeMigrationBackupFile($root,'r/'.$oldFile);
        if (!@unlink($root.'/r/'.$oldFile)) throw new RuntimeException('Cannot remove the old code export.');
    }
    u5CodeMigrationCleanupTemplates($root,$plan['templates']);
    if (!@unlink($pending)) throw new RuntimeException('Cannot finalize code rename.');
}
