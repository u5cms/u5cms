<?php
require_once dirname(__DIR__).'/codeassets.inc.php';
require_once __DIR__.'/mobiletools-migrate.inc.php';

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

function u5CodeMigrationColumns($table) {
    $columns=array();
    foreach (u5CodeMigrationRows('SHOW FULL COLUMNS FROM '.u5CodeMigrationIdentifier($table)) as $column) $columns[$column['Field']]=$column;
    return $columns;
}

function u5CodeMigrationReadTable($table,$columns) {
    $select=array();
    foreach ($columns as $name=>$column) $select[]='CAST('.u5CodeMigrationIdentifier($name).' AS BINARY) AS '.u5CodeMigrationIdentifier($name);
    // Binary results and hex literals preserve UTF-8, Latin-1 and entity strings byte for byte.
    return u5CodeMigrationRows('SELECT '.implode(',',$select).' FROM '.u5CodeMigrationIdentifier($table));
}

function u5CodeMigrationEachRow($table,$columns,$callback,$tail='') {
    $select=array();
    foreach ($columns as $name=>$column) $select[]='CAST('.u5CodeMigrationIdentifier($name).' AS BINARY) AS '.u5CodeMigrationIdentifier($name);
    $offset=0;$batch=100;
    do {
        $rows=u5CodeMigrationRows('SELECT '.implode(',',$select).' FROM '.u5CodeMigrationIdentifier($table).$tail.' LIMIT '.$offset.','.$batch);
        foreach ($rows as $row) $callback($row);
        $offset+=count($rows);
    } while (count($rows)===$batch);
}

function u5CodeMigrationWhere($row) {
    $terms=array();
    foreach ($row as $field=>$value) $terms[]='BINARY '.u5CodeMigrationIdentifier($field).' <=> '.u5CodeMigrationValue($value);
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

function u5CodeMigrationFileText($bytes,$charset) {
    $source=substr($bytes,0,3)==="\xEF\xBB\xBF" ? '_utf8mb4 '.u5CodeMigrationValue(substr($bytes,3)) : '_latin1 '.u5CodeMigrationValue($bytes);
    $rows=u5CodeMigrationRows('SELECT CAST(CONVERT('.$source.' USING '.$charset.') AS BINARY) AS content');
    return $rows[0]['content'];
}

function u5CodeMigrationReadJournal($path) {
    $data=file_get_contents($path); $prefix="<?php exit; ?>\n";
    if (substr($data,0,strlen($prefix))!==$prefix) throw new RuntimeException('Invalid migration checkpoint.');
    $plan=@unserialize(substr($data,strlen($prefix)),array('allowed_classes'=>false));
    if (!is_array($plan) || ($plan['version'] ?? '')!=='12.11.0') throw new RuntimeException('Invalid migration checkpoint version.');
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

function u5CodeMigrationBuildPlan($root) {
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
    u5CodeMigrationEachRow('resources',$schema['resources'],function($row) use(&$resources,&$uses) {
        $summary=array('name'=>$row['name'],'typ'=>$row['typ'],'deleted'=>$row['deleted']);
        if (strtolower((string)$row['typ'])==='c') $summary['content_1']=$row['content_1'];
        $resources[]=$summary;
        foreach ($row as $field=>$value) if (strpos($field,'content_')===0 && is_string($value)) {
            preg_match_all('~<script\b[^>]*>|(?:loadScript|getScript)\s*\(\s*["\'][^"\']+["\']~i',$value,$hits);
            if ($hits[0]) $uses.=implode("\n",$hits[0])."\n";
        }
    });
    $nameRows=$resources; $seen=array(); $historicalAliases=array();
    foreach ($resources as $row) $seen[$row['name']]=true;
    // Historical code can be restored later; its names and links must use the same convention.
    if (isset($schema['resources_log'])) u5CodeMigrationEachRow('resources_log',$schema['resources_log'],function($row) use(&$seen,&$historicalAliases,&$nameRows,$uses) {
        if (!isset($seen[$row['name']])) {
            list($likely)=u5CodeAssetGuess($row['name'],(string)$row['content_1'],$uses);
            if (isset($seen[$likely])) $historicalAliases[$row['name']]=$likely;
            else $nameRows[]=$row;
            $seen[$row['name']]=true;
        }
    }," WHERE typ='c' ORDER BY lastmut DESC");
    $names=u5CodeAssetPlanNames($nameRows,$uses); $map=array_merge($names['map'],$historicalAliases); $notes=$names['notes'];
    $defaults=u5CodeAssetDefaults(); $additions=array();
    $charset=u5CodeMigrationCharset($schema['resources']['content_1']);
    // Every installation gets the common mobile code. Existing local customizations win.
    foreach ($defaults as $old=>$standard) {
        $target=$map[$old] ?? $standard; $active=false;
        foreach ($resources as $row) if (strtolower((string)$row['typ'])==='c' && (int)$row['deleted']!==1 &&
            ($row['name']===$old || $row['name']===$target)) $active=true;
        if ($active) continue;
        $legacy=$root.'/r/'.$old.'.css';
        $source=null; $reason='';
        if (is_file($legacy)) { $source=file_get_contents($legacy); $reason='existing exported file imported'; }
        elseif ($old==='jsmobilegeneral') {
            $source=u5CodeMigrationMobileSource(); $reason='bundled common mobile code installed';
        }
        if ($source===null) continue;
        $candidate=array('name'=>$old,'content_1'=>u5CodeMigrationFileText($source,$charset),'typ'=>'c','deleted'=>0,
            'hidden'=>0,'ishomepage'=>0,'lastmut'=>time(),'operator'=>'u5CMS 12.11.0','ip'=>($_SERVER['REMOTE_ADDR'] ?? ''));
        if (!isset($map[$old])) {
            $probe=$nameRows; $probe[]=$candidate;
            $resolved=u5CodeAssetPlanNames($probe,$uses);
            $target=$resolved['map'][$old]; $map[$old]=$target;
        }
        $candidate['name']=$target; $additions[]=$candidate;
        $notes[]=array('from'=>$old,'to'=>$target,'reason'=>$reason);
    }
    $effective=array_merge($defaults,$map); $updates=array(); $afterResources=array();
    foreach ($schema as $table=>$columns) u5CodeMigrationEachRow($table,$columns,function($before) use($schema,$table,$effective,&$updates,&$afterResources) {
        $after=$before;
        foreach ($schema[$table] as $field=>$column) {
            if (!preg_match('/char|text|json|enum|set/i',$column['Type']) || !is_string($before[$field])) continue;
            if ($field==='name' && ($table==='resources' || $table==='resources_log') && strtolower((string)($before['typ'] ?? ''))==='c') {
                $after[$field]=$effective[$before[$field]] ?? $before[$field];
            } else $after[$field]=u5CodeAssetReferences($before[$field],$effective);
        }
        if ($after!==$before) $updates[]=array('table'=>$table,'before'=>$before,'after'=>$after);
        if ($table==='resources' && strtolower((string)($after['typ'] ?? ''))==='c') $afterResources[]=$after;
    });
    foreach ($additions as &$addition) {
        $addition['content_1']=u5CodeAssetReferences($addition['content_1'],$effective);
        $afterResources[]=$addition;
    }
    unset($addition);
    $displayRows=$afterResources;
    foreach ($displayRows as &$row) if (strtolower((string)$row['typ'])==='c') $row['content_1']=u5CodeMigrationDisplayText((string)$row['content_1'],$charset);
    unset($row);
    $files=u5CodeAssetRowsToFiles($displayRows,time()); $oldFiles=array();
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
    return array('version'=>'12.11.0','map'=>$effective,'notes'=>$notes,'updates'=>$updates,
        'additions'=>$additions,'files'=>$files,'oldFiles'=>array_values(array_unique($oldFiles)),
        'template'=>$template,'initializeHistory'=>(int)$historyCount[0]['n']===0,'created'=>time());
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
    if (!is_file($backup)) u5CodeAssetAtomicWrite($backup,"<?php exit; ?>\n".serialize(array('path'=>$relative,'bytes'=>file_get_contents($root.'/'.$relative))));
}

function u5CodeMigrationFileBackup($root,$relative) {
    if (!u5CodeMigrationSafeBackupPath($relative)) throw new RuntimeException('Invalid migration backup path.');
    $path=$root.'/fileversions/codeassets-12.11.0-files/'.sha1($relative).'.php';
    if (!is_file($path)) return null;
    $data=file_get_contents($path);$prefix="<?php exit; ?>\n";
    if (substr($data,0,strlen($prefix))!==$prefix) throw new RuntimeException('Invalid migration file backup.');
    $saved=@unserialize(substr($data,strlen($prefix)),array('allowed_classes'=>false));
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
        $files[$target]=array('before'=>$before,'after'=>u5CodeAssetReferences($body,$map));
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

function u5CodeMigrationTemplates($root,$map,$preferTyped=false) {
    $pending=$root.'/fileversions/codeassets-12.11.0-templates.pending.php';
    $complete=$root.'/fileversions/codeassets-12.11.0-templates.done.php';
    if (is_file($complete)) {
        if (is_file($pending) && !@unlink($pending)) throw new RuntimeException('Cannot finish the reset template checkpoint.');
        return;
    }
    $plan=is_file($pending) ? u5CodeMigrationReadJournal($pending) : u5CodeMigrationTemplatePlan($root,$map,$preferTyped);
    if (!is_file($pending)) u5CodeAssetAtomicWrite($pending,"<?php exit; ?>\n".serialize($plan));
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
        $after=$updateLinks ? u5CodeAssetReferences($body,array($old=>$new)) : $body;
        if ($target===$relative && $after===$body) continue;
        $files[$target]=array('before'=>$target===$relative ? $body : null,'after'=>$after);
        $sources[$relative]=$body;
        if ($target!==$relative && $updateLinks) $remove[]=$relative;
    }
    return array('version'=>'12.11.0','files'=>$files,'sources'=>$sources,'remove'=>$remove);
}

function u5CodeMigrationApplyRow($change) {
    $before=$change['before']; $after=$change['after']; $table=u5CodeMigrationIdentifier($change['table']);
    $set=array(); $compare=array();
    foreach ($after as $field=>$value) if ($value!==$before[$field]) { $set[]=u5CodeMigrationIdentifier($field).'='.u5CodeMigrationValue($value); $compare[$field]=$before[$field]; }
    if (!$set) return;
    // Use the whole original row, including nulls, instead of a guessed primary key.
    // This also handles duplicate history rows in the legacy MyISAM tables.
    $count=u5CodeMigrationRows('SELECT COUNT(*) AS n FROM '.$table.' WHERE '.u5CodeMigrationWhere($before));
    if ((int)$count[0]['n']===0) {
        $done=u5CodeMigrationRows('SELECT COUNT(*) AS n FROM '.$table.' WHERE '.u5CodeMigrationWhere($after));
        if ((int)$done[0]['n']>0) return;
        throw new RuntimeException('A database row changed during migration in '.$change['table'].'. The checkpoint was kept; no concurrent edit was overwritten.');
    }
    u5CodeMigrationQuery('UPDATE '.$table.' SET '.implode(',',$set).' WHERE '.u5CodeMigrationWhere($before));
    if (mysql_affected_rows()!==(int)$count[0]['n']) throw new RuntimeException('A row changed while applying migration in '.$change['table']);
}

function u5CodeMigrationInsert($table,$row) {
    $columns=u5CodeMigrationColumns($table); $fields=array(); $values=array(); $expected=array();
    foreach ($row as $field=>$value) if (isset($columns[$field])) { $fields[]=u5CodeMigrationIdentifier($field); $values[]=u5CodeMigrationValue($value); $expected[$field]=$value; }
    if ($table==='resources_log') {
        $same=u5CodeMigrationRows('SELECT COUNT(*) AS n FROM '.u5CodeMigrationIdentifier($table).' WHERE '.u5CodeMigrationWhere($expected));
        if ((int)$same[0]['n']>0) return;
        u5CodeMigrationQuery('INSERT INTO '.u5CodeMigrationIdentifier($table).' ('.implode(',',$fields).') VALUES ('.implode(',',$values).')');
        return;
    }
    $where="BINARY name=".u5CodeMigrationValue($row['name'])." AND deleted=0";
    $count=u5CodeMigrationRows('SELECT COUNT(*) AS n FROM '.u5CodeMigrationIdentifier($table).' WHERE '.$where);
    if ((int)$count[0]['n']>0) {
        $same=u5CodeMigrationRows('SELECT COUNT(*) AS n FROM '.u5CodeMigrationIdentifier($table).' WHERE '.u5CodeMigrationWhere($expected));
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
    try { u5CodeAssetsMigrateLocked($root); u5MobileToolsMigrate($root); }
    finally { $release(); }
}

function u5CodeAssetsMigrateLocked($root) {
    $pending=$root.'/fileversions/codeassets-12.11.0.pending.php';
    $complete=$root.'/fileversions/codeassets-12.11.0.done.php';
    if (is_file($complete)) {
        $state=include $complete;
        u5CodeMigrationTemplates($root,$state['map'],true);
        u5CodeAssetExport($root);
        u5CodeMigrationRemoveMimeRule($root);
        if (is_file($pending)) {
            $unfinished=u5CodeMigrationReadJournal($pending);
            foreach ($unfinished['oldFiles'] as $relative) {
                // Earlier preview checkpoints also listed .tpl files; their own migration owns those now.
                if (substr(strtolower($relative),-4)==='.tpl') continue;
                u5CodeMigrationBackupFile($root,$relative);
                if (is_file($root.'/'.$relative) && !@unlink($root.'/'.$relative)) throw new RuntimeException('Cannot remove obsolete asset '.$relative);
            }
            if (!@unlink($pending)) throw new RuntimeException('Cannot finish the asset migration checkpoint.');
        }
        return;
    }
    $plan=is_file($pending) ? u5CodeMigrationReadJournal($pending) : u5CodeMigrationBuildPlan($root);
    if (!is_file($pending)) u5CodeAssetAtomicWrite($pending,"<?php exit; ?>\n".serialize($plan));
    u5CodeMigrationCheckpoint('planned');
    u5CodeMigrationTemplates($root,$plan['map']);
    foreach ($plan['files'] as $file=>$data) {
        u5CodeMigrationBackupFile($root,'r/'.$file);
        u5CodeAssetAtomicWrite($root.'/r/'.$file,$data);
    }
    u5CodeMigrationCheckpoint('files');
    foreach ($plan['updates'] as $index=>$change) {
        u5CodeMigrationApplyRow($change);
        u5CodeMigrationCheckpoint('row:'.$index);
    }
    foreach ($plan['additions'] as $row) {
        u5CodeMigrationInsert('resources',$row);
        u5CodeMigrationInsert('resources_log',$row);
    }
    if (!empty($plan['initializeHistory'])) {
        foreach (u5CodeMigrationReadTable('resources',u5CodeMigrationColumns('resources')) as $row) u5CodeMigrationInsert('resources_log',$row);
    }
    u5CodeMigrationCheckpoint('database');
    // Re-export through the normal application connection after the exact byte-preserving DB update.
    u5CodeAssetExport($root);
    u5CodeMigrationRemoveMimeRule($root);
    $report="u5CMS 12.11.0 code resource migration\n";
    foreach ($plan['notes'] as $note) $report.=$note['from'].' -> '.$note['to'].' ('.$note['reason'].")\n";
    u5CodeAssetAtomicWrite($root.'/fileversions/codeassets-12.11.0-report.php',"<?php exit; ?>\n".$report);
    u5CodeMigrationCheckpoint('exported');
    u5CodeAssetAtomicWrite($complete,"<?php\nreturn ".var_export(array('version'=>'12.11.0','template'=>$plan['template'],'map'=>$plan['map'],'completed'=>time()),true).";\n");
    // Old URLs remain usable until all new files and all DB references have been published.
    foreach ($plan['oldFiles'] as $relative) {
        if (substr(strtolower($relative),-4)==='.tpl') continue;
        u5CodeMigrationBackupFile($root,$relative);
        if (is_file($root.'/'.$relative) && !@unlink($root.'/'.$relative)) throw new RuntimeException('Cannot remove obsolete asset '.$relative);
    }
    u5CodeMigrationCheckpoint('complete');
    if (!@unlink($pending)) throw new RuntimeException('Cannot finish the asset migration checkpoint.');
}

function u5CodeAssetsRename($root,$old,$new,$updateLinks=true) {
    $release=u5CodeMigrationLock($root);
    try { u5CodeAssetsRenameLocked($root,$old,$new,$updateLinks); }
    finally { $release(); }
}

function u5CodeAssetsRenameLocked($root,$old,$new,$updateLinks=true) {
    u5CodeAssetFilename($old); u5CodeAssetFilename($new);
    if ($old===$new) throw new RuntimeException('The new code name must be different.');
    $pending=$root.'/fileversions/codeasset-rename-'.sha1($old."\0".$new).'.pending.php';
    if (is_file($pending)) $plan=u5CodeMigrationReadJournal($pending);
    else {
        $resources=u5CodeMigrationReadTable('resources',u5CodeMigrationColumns('resources'));$found=false;
        foreach ($resources as $row) {
            if ($row['name']===$new) throw new RuntimeException('The target code name already exists, including a retained deleted version.');
            if ($row['name']===$old && strtolower((string)$row['typ'])==='c' && (int)$row['deleted']!==1) $found=true;
        }
        if (!$found) throw new RuntimeException('The code object to rename is missing.');
        $changes=array();$afterResources=array();
        foreach (u5CodeMigrationRows('SHOW FULL TABLES') as $tableRow) {
            $values=array_values($tableRow);if (($values[1] ?? '')!=='BASE TABLE') continue;
            $table=$values[0];$columns=u5CodeMigrationColumns($table);
            foreach (u5CodeMigrationReadTable($table,$columns) as $before) {
                $after=$before;
                foreach ($columns as $field=>$column) {
                    if (!is_string($before[$field]) || !preg_match('/char|text|json|enum|set/i',$column['Type'])) continue;
                    if ($field==='name' && in_array($table,array('resources','resources_log'),true) && strtolower((string)($before['typ'] ?? ''))==='c' && $before[$field]===$old) $after[$field]=$new;
                    elseif ($updateLinks) $after[$field]=u5CodeAssetReferences($before[$field],array($old=>$new));
                }
                if ($after!==$before) $changes[]=array('table'=>$table,'before'=>$before,'after'=>$after);
                if ($table==='resources') $afterResources[]=$after;
            }
        }
        $columns=u5CodeMigrationColumns('resources');$charset=u5CodeMigrationCharset($columns['content_1']);
        foreach ($afterResources as &$row) if (strtolower((string)$row['typ'])==='c') $row['content_1']=u5CodeMigrationDisplayText((string)$row['content_1'],$charset);
        unset($row);
        $plan=array('version'=>'12.11.0','updates'=>$changes,'files'=>u5CodeAssetRowsToFiles($afterResources,time()),
            'templates'=>u5CodeMigrationRenameTemplates($root,$old,$new,$updateLinks));
        u5CodeAssetAtomicWrite($pending,"<?php exit; ?>\n".serialize($plan));
    }
    if (!isset($plan['templates'])) {
        $plan['templates']=u5CodeMigrationRenameTemplates($root,$old,$new,$updateLinks);
        u5CodeAssetAtomicWrite($pending,"<?php exit; ?>\n".serialize($plan));
    }
    // Publish the new target before replacing stylesheets/templates which reference it.
    $newFile=u5CodeAssetFilename($new);
    if (isset($plan['files'][$newFile])) u5CodeAssetAtomicWrite($root.'/r/'.$newFile,$plan['files'][$newFile]);
    foreach ($plan['files'] as $file=>$body) {
        u5CodeMigrationBackupFile($root,'r/'.$file);
        u5CodeAssetAtomicWrite($root.'/r/'.$file,$body);
    }
    u5CodeMigrationPublishTemplates($root,$plan['templates']);
    foreach ($plan['updates'] as $change) u5CodeMigrationApplyRow($change);
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
