<?php
/* PHP 8+, disposable MariaDB via U5CMS_TEST_PORT. Never run against a CMS database. */
if (PHP_SAPI!=='cli') {http_response_code(404);exit;}

require_once dirname(__DIR__).'/u5admin/resource-rename.inc.php';
$checks=0;
function renameCheck($condition,$message) {global $checks;$checks++;if (!$condition) throw new RuntimeException('CHECK FAILED: '.$message);}
function renameSame($actual,$expected,$message) {renameCheck($actual===$expected,$message.' '.substr(var_export($actual,true),0,600));}
$options=array('links'=>true,'manual'=>true,'formdata'=>true,'template'=>'htmltemplate!html','languages'=>array('de','en','fr','it','rm'));
$old='demo';$new='newdemo';
$cases=array(
    '[demo] [Label:demo] [::::demo] [$$$:demo] [demo?l=en] [a:b:demo?x=1]'=>'[newdemo] [Label:newdemo] [::::newdemo] [$$$:newdemo] [newdemo?l=en] [a:b:newdemo?x=1]',
    '[[demo]] [[[demo]]] [[[[demo]]]] [[Label:demo]] [[demo?text]]'=>'[[demo]] [[[demo]]] [[[[demo]]]] [[Label:demo]] [[demo?text]]',
    '[[Title [Label:demo] more]]'=>'[[Title [Label:newdemo] more]]',
    '[go:]demo[:go] [lo:]demo[:lo] |demo|'=>'[go:]newdemo[:go] [lo:]newdemo[:lo] |newdemo|',
    '<input name="thanks" value="demo"> <input name="thanks" value="demo&l=en">'=>'<input name="thanks" value="newdemo"> <input name="thanks" value="newdemo&l=en">',
    'r/demo/demo_en.jpg c=demo" n=demo\' c=demo&l=en'=>'r/newdemo/newdemo_en.jpg c=newdemo" n=newdemo\' c=newdemo&l=en',
    'section=demo" abc=demo& c=democracy" [democracy] [demo2] [Demo]'=>'section=demo" abc=demo& c=democracy" [democracy] [demo2] [Demo]',
    'literal |4br*,.-;:_+/ts| |3br*,.-;:_+/ts| |2br*,.-;:_+/ts|'=>'literal |4br*,.-;:_+/ts| |3br*,.-;:_+/ts| |2br*,.-;:_+/ts|',
    "\xDCber &amp; &#x1F600; [demo]"=>"\xDCber &amp; &#x1F600; [newdemo]"
);
foreach ($cases as $before=>$after) renameSame(u5RenameText($before,$old,$new,$options),$after,'reference grammar and literal bytes');
renameSame(u5RenameText('c=demo\\" n=demo\\\'',$old,$new,$options),'c=demo\\" n=demo\\\'','backslash policy only content/login/data');
renameSame(u5RenameText('c=demo\\" n=demo\\\'',$old,$new,$options,array('content'=>true)),'c=newdemo\\" n=newdemo\\\'','escaped content query values');
renameSame(u5RenameText('{{{demo}}} [_demo_]',$old,$new,$options,array('template'=>true)),'{{{newdemo}}} [_newdemo_]','active and reset template inclusions');
renameSame(u5RenameText('{{{demo}}} [_demo_]',$old,$new,$options),'{{{demo}}} [_demo_]','template-only markers keep their context');
renameSame(u5RenameText('r\\/demo\\/demo_en.jpg',$old,$new,$options),'r\\/newdemo\\/newdemo_en.jpg','JSON escaped media paths');
$local=$options;$local['manual']=false;
renameSame(u5RenameText('[demo] r/demo/demo_en.jpg c=demo"',$old,$new,$local),'[newdemo] r/demo/demo_en.jpg c=demo"','manual path opt-out');
foreach (array('demo'=>'newdemo','c=demo'=>'c=newdemo','c=demo&'=>'c=newdemo&') as $before=>$after) renameSame(u5RenameText($before,$old,$new,$options,array('content'=>true,'type'=>'f')),$after,'free image target forms');
renameSame(u5RenameText('[_pagename!_]!old','family_name!old','family_name!new',$options,array('content'=>true,'name'=>'family_name!chapter')),'[_pagename!_]!new','family suffix dependency');
renameSame(u5RenameText('[_pagename!_]!old','family_name!old','family_name!new',$options,array('content'=>true,'name'=>'familyXname!chapter')),'[_pagename!_]!old','underscore is literal in family names');
$aliases=$options;$aliases['names']=array('demo'=>'vdemo','vdemo'=>'vvdemo');
renameSame(u5RenameText('[demo] [vdemo]','demo','vdemo',$aliases),'[vdemo] [vvdemo]','video aliases cannot cascade');
foreach (array('[[base!css]]','[[[cssbase]]]','[[[[Label:cssbase]]]]','[[cssbase?literal]]') as $title) renameSame(u5CodeAssetReferences($title),$title,'typed code migration and rename preserve title brackets');
renameSame(u5CodeAssetReferences('[[A [cssbase] B]]'),'[[A [base!css] B]]','code references nested in a heading still follow their target');
foreach (array('../demo','demo.php','new-demo','meta','search','languages','content','right','abc',str_repeat('a',21),array('demo')) as $invalid) {
    $failed=false;try {u5RenameName($invalid);}catch(RuntimeException $error){$failed=true;}renameCheck($failed,'invalid/unroutable/reserved target is rejected');
}
$random=mt_srand(725);
$fragments=array_keys($cases);$expected=array_values($cases);
for ($i=0;$i<600;$i++) {
    $before='';$after='';for ($j=0;$j<8;$j++) {$k=mt_rand(0,count($fragments)-1);$before.=$fragments[$k]."\n";$after.=$expected[$k]."\n";}
    renameSame(u5RenameText($before,$old,$new,$options),$after,'mixed randomized reference corpus');
}
echo "Rename core checks: $checks passed.\n";
if (!getenv('U5CMS_TEST_PORT')) exit;
require_once dirname(__DIR__).'/mysql.php';
ini_set('mysqli.default_port',(string)(int)getenv('U5CMS_TEST_PORT'));
mysql_connect('127.0.0.1',getenv('U5CMS_TEST_USER') ?: 'root',getenv('U5CMS_TEST_PASSWORD') ?: '');
$database='u5rename_'.bin2hex(random_bytes(6));u5CodeMigrationQuery('CREATE DATABASE `'.$database.'` CHARACTER SET utf8mb4');mysql_select_db($database);
$link=mysql_ensure_link(null);
if (!mysqli_multi_query($link,file_get_contents(dirname(__DIR__).'/u5standard.sql'))) throw new RuntimeException(mysqli_error($link));
do {if ($result=mysqli_store_result($link)) mysqli_free_result($result);} while (mysqli_more_results($link) && mysqli_next_result($link));
if (mysqli_errno($link)) throw new RuntimeException(mysqli_error($link));
u5CodeMigrationQuery('SET NAMES latin1');
$roots=array();$fault=null;$race=null;$kill=null;
function u5CodeMigrationTestCheckpoint($phase) {
    global $fault,$race,$kill;
    if ($phase===$fault) {$fault=null;throw new RuntimeException('TEST INTERRUPTION '.$phase);}
    if ($phase==='rename:planned' && $race) {$run=$race;$race=null;$run();}
    if ($phase===$kill) {posix_kill(getmypid(),9);exit(99);}
}
function renameFixture($label) {
    global $roots;
    foreach (array('resources','resources_log','formdata','loginglobals') as $table) u5CodeMigrationQuery('TRUNCATE TABLE '.$table);
    $root=sys_get_temp_dir().'/u5-rename-'.$label.'-'.bin2hex(random_bytes(6));$roots[]=$root;
    mkdir($root.'/r',0700,true);mkdir($root.'/fileversions',0700);return $root;
}
function renameInsert($table,$row) {
    $columns=u5CodeMigrationColumns($table);$row=array_intersect_key($row,$columns);$values=array();foreach ($row as $field=>$value) $values[]=u5CodeMigrationColumnValue($value,$columns[$field]);
    u5CodeMigrationQuery('INSERT INTO '.u5CodeMigrationIdentifier($table).' (`'.implode('`,`',array_keys($row)).'`) VALUES ('.implode(',',$values).')');
}
function renameRow($name,$typ='p',$deleted=0,$content='') {return array('name'=>$name,'typ'=>$typ,'deleted'=>$deleted,'lastmut'=>100,'operator'=>'original','content_1'=>$content,'logins'=>'member:password;','search_1'=>'old cached text');}
function renameOne($table,$where='1') {return u5CodeMigrationRows('SELECT '.u5CodeMigrationSelect(u5CodeMigrationColumns($table))['select'].' FROM '.$table.' WHERE '.$where.' LIMIT 1')[0] ?? null;}
function renameTreeRemove($root) {if (is_link($root) || !is_dir($root)) {@unlink($root);return;}foreach (scandir($root) as $entry) if ($entry!=='.' && $entry!=='..') renameTreeRemove($root.'/'.$entry);rmdir($root);}
try {
    $root=renameFixture('full');
    $source=renameRow('demo');foreach (range(1,5) as $l) foreach (array('content','title','desc','key') as $field) $source[$field.'_'.$l]='[[demo]] [Label:demo] |2br*,.-;:_+/ts|';
    renameInsert('resources',$source);renameInsert('resources_log',$source);renameInsert('resources_log',$source);renameInsert('resources_log',renameRow('demo','p',1,'[demo]'));
    renameInsert('resources',renameRow('newdemo','p',1,'target tombstone must remain recoverable'));
    renameInsert('resources',renameRow('unrelated','p',0,'[[demo]] no actual reference'));
    renameInsert('resources',renameRow('freeimage','f',0,'demo'));
    renameInsert('resources',renameRow('htmltemplate!html','c',0,'{{{demo}}} [_demo_] [demo]'));
    renameInsert('loginglobals',array('loginintro_1'=>'[[demo]] [demo] c=demo\\"','loginoutro_5'=>'|demo|'));
    renameInsert('formdata',array('formname'=>'demo','datacsv'=>'[[demo]] [demo] c=demo\\"','notes'=>'literal [demo]'));
    mkdir($root.'/r/demo');mkdir($root.'/r/newdemo');file_put_contents($root.'/r/newdemo/original.txt','original orphan data');
    foreach (array('demo_en.jpg','demo_de.pdf','demo_fr.webp','demo_it.mp4','demo_rm.txt','demo_100.jpg','demo_999.png','demo_099.jpg','demo_1000.jpg','demo_en.jpg.original','demo_extra.jpg','unrelated') as $file) file_put_contents($root.'/r/demo/'.$file,'exact bytes '.$file);
    symlink('demo_en.jpg',$root.'/r/demo/keep-link');
    file_put_contents($root.'/r/demo/.htpasswd','member:{SHA}original');
    file_put_contents($root.'/r/demo/.htaccess','AuthUserFile "'.$root.'/r/demo/.htpasswd"' . "\nRequire valid-user\n");
    file_put_contents($root.'/r/test!html.tpl','[demo] [[demo]]');
    $unrelated=renameOne('resources',"name='unrelated'");
    $plan=u5ResourceRename($root,'demo','newdemo',$options);
    $actual=renameOne('resources',"name='newdemo' AND deleted=0");
    foreach (range(1,5) as $l) foreach (array('content','title','desc','key') as $field) renameSame($actual[$field.'_'.$l],'[[demo]] [Label:newdemo] |2br*,.-;:_+/ts|','all 20 reference columns');
    renameSame($actual['operator'],'R original','one revision marker per changed resource');renameCheck((int)$actual['lastmut']>100,'revision changes for all ownership mutations');renameSame($actual['search_1'],null,'changed search cache invalidated');
    renameSame(renameOne('resources',"name='unrelated'"),$unrelated,'heading-only resources retain revision and bytes');
    renameSame(renameOne('resources',"name='freeimage'")['content_1'],'newdemo','free image link follows target');
    renameSame(renameOne('resources',"name='htmltemplate!html'")['content_1'],'{{{newdemo}}} [_newdemo_] [newdemo]','active template source follows target');
    renameSame(file_get_contents($root.'/r/htmltemplate.html'),'{{{newdemo}}} [_newdemo_] [newdemo]','active template export follows source');
    renameSame(file_get_contents($root.'/r/test!html.tpl'),'[newdemo] [[demo]]','reset template dependencies protected');
    renameSame(renameOne('loginglobals')['loginintro_1'],'[[demo]] [newdemo] c=newdemo\\"','global intro updates');renameSame(renameOne('loginglobals')['loginoutro_5'],'|newdemo|','global outro updates');
    $form=renameOne('formdata');renameSame($form['formname'],'newdemo','form ownership always follows');renameSame($form['datacsv'],'[[demo]] [newdemo] c=newdemo\\"','optional form payload references');renameSame($form['notes'],'literal [demo]','unrelated form columns not touched');
    renameSame((int)u5CodeMigrationRows("SELECT COUNT(*) n FROM resources_log WHERE name='newdemo' AND deleted=0")[0]['n'],2,'duplicate history identities retained');
    renameSame(renameOne('resources_log',"deleted=1")['name'],'demo','deleted history ownership retained');renameSame(renameOne('resources_log',"deleted=1")['content_1'],'[newdemo]','references within deleted history follow target');
    $tombstone=false;u5CodeMigrationEachChange($root,$plan['updates'],function($change) use(&$tombstone) {if (!empty($change['delete']) && $change['before']['content_1']==='target tombstone must remain recoverable')$tombstone=true;});renameCheck($tombstone,'reused target tombstone is backed up');
    foreach ($plan['files']['files'] as $file=>$change) renameSame(file_get_contents($root.'/r/newdemo/'.$change['to']),'exact bytes '.$file,'language/album file bytes preserved');
    foreach (array('demo_099.jpg','demo_1000.jpg','demo_en.jpg.original','demo_extra.jpg','unrelated') as $file) renameSame(file_get_contents($root.'/r/newdemo/'.$file),'exact bytes '.$file,'unrelated filenames retained');
    renameSame(file_get_contents($root.'/r/'.$plan['files']['archive'].'/original.txt'),'original orphan data','pre-existing destination directory archived without replacement');
    renameSame(readlink($root.'/r/newdemo/keep-link'),'newdemo_en.jpg','internal symbolic link target follows its file');
    renameSame(file_get_contents($root.'/r/newdemo/.htaccess'),'AuthUserFile "'.$root.'/r/newdemo/.htpasswd"'."\nRequire valid-user\n",'Basic file authentication follows directory');
    renameSame(file_get_contents($root.'/r/newdemo/.htpasswd'),'member:{SHA}original','file membership hashes preserved');
    renameCheck(is_file($root.'/fileversions/search-index-pending.txt'),'full rendered search refresh requested');
    renameCheck(!is_file(u5RenameBase($root,'demo','newdemo').'.pending.php'),'completed operation finalizes pending checkpoint');
    $before=renameOne('resources',"name='newdemo'");u5ResourceRename($root,'demo','newdemo',$options);renameSame(renameOne('resources',"name='newdemo'"),$before,'repeated completed request is idempotent');
    foreach (array(false,true) as $updateLinks) foreach (array(false,true) as $manual) foreach (array(false,true) as $formData) {
        $root=renameFixture('policies');$policy=$options;$policy['links']=$updateLinks;$policy['manual']=$manual;$policy['formdata']=$formData;
        renameInsert('resources',renameRow('demo','p',0,'[demo] r/demo/demo_en.jpg'));renameInsert('resources_log',renameRow('demo','p',0,'[demo] r/demo/demo_en.jpg'));
        renameInsert('formdata',array('formname'=>'demo','datacsv'=>'[demo] r/demo/demo_en.jpg'));
        u5ResourceRename($root,'demo','newdemo',$policy);
        $expected=($updateLinks ? '[newdemo]' : '[demo]').' '.($updateLinks && $manual ? 'r/newdemo/newdemo_en.jpg' : 'r/demo/demo_en.jpg');
        renameSame(renameOne('resources')['content_1'],$expected,'reference policy matrix');renameSame(renameOne('resources_log')['content_1'],$expected,'history policy matrix');
        renameSame(renameOne('formdata')['datacsv'],$updateLinks && $formData ? $expected : '[demo] r/demo/demo_en.jpg','form data opt-in matrix');renameSame(renameOne('formdata')['formname'],'newdemo','ownership independent of policies');
    }
    foreach (array('!newdemo','newdemo') as $target) {
        $root=renameFixture('intranet');renameInsert('resources',renameRow('!demo'));u5ResourceRename($root,'!demo',$target,$options);
        renameSame(renameOne('resources')['logins'],$target[0]==='!' ? 'member:password;' : '','intranet membership preserved unless leaving intranet');
    }
    $root=renameFixture('family');renameInsert('resources',renameRow('family_name!old'));renameInsert('resources',renameRow('family_name!chapter','p',0,'[_pagename!_]!old'));renameInsert('resources',renameRow('familyXname!chapter','p',0,'[_pagename!_]!old'));
    renameInsert('formdata',array('datacsv'=>'[_pagename!_]!old'));u5ResourceRename($root,'family_name!old','family_name!new',$options);
    renameSame(renameOne('resources',"name='family_name!chapter'")['content_1'],'[_pagename!_]!new','family relative dependencies');renameSame(renameOne('resources',"name='familyXname!chapter'")['content_1'],'[_pagename!_]!old','no SQL wildcard collateral');renameSame(renameOne('formdata')['datacsv'],'[_pagename!_]!new','relative dependencies in optional form data');
    $root=renameFixture('video');renameInsert('resources',renameRow('movie','v',0));renameInsert('resources',renameRow('vmovie','i',0));renameInsert('resources_log',renameRow('vmovie','i',0));renameInsert('resources',renameRow('viewer','p',0,'[movie] [vmovie] r/vmovie/vmovie_en.jpg'));
    mkdir($root.'/r/movie');mkdir($root.'/r/vmovie');file_put_contents($root.'/r/movie/movie_en.mp4','video bytes');file_put_contents($root.'/r/vmovie/vmovie_en.jpg','poster bytes');
    u5ResourceRename($root,'movie','film',$options);renameSame(file_get_contents($root.'/r/film/film_en.mp4'),'video bytes','video filename follows');renameSame(file_get_contents($root.'/r/vfilm/vfilm_en.jpg'),'poster bytes','video poster filename follows');renameCheck(renameOne('resources',"name='vfilm'")!==null,'video poster resource follows');renameCheck(renameOne('resources_log',"name='vfilm'")!==null,'video poster history follows');renameSame(renameOne('resources',"name='viewer'")['content_1'],'[film] [vfilm] r/vfilm/vfilm_en.jpg','video/poster references follow together');
    foreach (array('rename:planned','rename:archive','rename:directory','rename:file','rename:templates','rename:row:0','rename:database','rename:published','rename:complete') as $phase) {
        $root=renameFixture('interrupt');renameInsert('resources',renameRow('demo','p',0,'[demo]'));renameInsert('resources_log',renameRow('demo','p',0,'[demo]'));renameInsert('formdata',array('formname'=>'demo','datacsv'=>'[demo]'));
        mkdir($root.'/r/demo');mkdir($root.'/r/newdemo');file_put_contents($root.'/r/demo/demo_en.jpg','original image');file_put_contents($root.'/r/newdemo/orphan','original orphan');file_put_contents($root.'/r/test!html.tpl','[demo]');
        $fault=$phase;$failed=false;try {u5ResourceRename($root,'demo','newdemo',$options);}catch(RuntimeException $error){$failed=strpos($error->getMessage(),'TEST INTERRUPTION')===0;}
        renameCheck($failed,'deterministic interruption '.$phase);renameCheck(is_file(u5RenameBase($root,'demo','newdemo').'.pending.php'),'interruption retains checkpoint '.$phase);
        $blocked=false;try {$lock=u5CodeMigrationLock($root);$lock();}catch(RuntimeException $error){$blocked=strpos($error->getMessage(),'interrupted resource rename')!==false;}renameCheck($blocked,'incompatible mutation blocked while pending');
        $lock=u5CodeMigrationLock($root,null,true);$lock();renameCheck(true,'recovery backup can still lock pending installation');
        $retry=$options;$retry['links']=false;u5ResourceRename($root,'demo','newdemo',$retry);
        renameSame(renameOne('resources')['content_1'],'[newdemo]','resume uses immutable original policy '.$phase);renameSame(renameOne('resources_log')['content_1'],'[newdemo]','history resumes '.$phase);renameSame(renameOne('formdata')['formname'],'newdemo','form ownership resumes '.$phase);renameSame(file_get_contents($root.'/r/newdemo/newdemo_en.jpg'),'original image','file resumes '.$phase);
    }
    $root=renameFixture('collision');renameInsert('resources',renameRow('demo'));mkdir($root.'/r/demo');file_put_contents($root.'/r/demo/demo_en.jpg','original');file_put_contents($root.'/r/demo/newdemo_en.jpg','independent');$before=renameOne('resources');
    $failed=false;try {u5ResourceRename($root,'demo','newdemo',$options);}catch(RuntimeException $error){$failed=true;}renameCheck($failed,'independent file collision rejected before mutation');renameSame(renameOne('resources'),$before,'collision preserves database');renameSame(file_get_contents($root.'/r/demo/newdemo_en.jpg'),'independent','collision preserves destination bytes');
    $root=renameFixture('concurrent');renameInsert('resources',renameRow('demo','p',0,'[demo]'));
    $race=function(){u5CodeMigrationQuery("UPDATE resources SET content_1='concurrent edit [demo]' WHERE name='demo'");};
    $failed=false;try {u5ResourceRename($root,'demo','newdemo',$options);}catch(RuntimeException $error){$failed=strpos($error->getMessage(),'concurrent edit')!==false;}renameCheck($failed,'concurrent edit aborts guarded update');renameSame(renameOne('resources')['content_1'],'concurrent edit [demo]','concurrent edit is never overwritten');

    $root=renameFixture('collated-tombstone');renameInsert('resources',renameRow('demo'));renameInsert('resources',renameRow('résumé','p',1,'retained accent tombstone'));
    $plan=u5ResourceRename($root,'demo','resume',$options);renameSame(renameOne('resources')['name'],'resume','collation-equivalent tombstone is preserved and safely reused using native name bytes');
    $saved=false;u5CodeMigrationEachChange($root,$plan['updates'],function($change) use(&$saved){if(!empty($change['delete']) && $change['before']['name']==='résumé')$saved=true;});renameCheck($saved,'accented target tombstone retains exact UTF8 bytes in backup');
    $root=renameFixture('code-literals');renameInsert('resources',renameRow('demo'));
    $script='const demo=1; const list=[demo]; const selector="[demo]"; const url="?c=demo&l=en"; const inc="[$$$:demo]";';
    renameInsert('resources',renameRow('code!js','c',0,$script));renameInsert('resources',renameRow('embedded','p',0,'[demo] <script>'.$script.'</script>'));
    u5ResourceRename($root,'demo','newdemo',$options);$expected=str_replace(array('?c=demo&','[$$$:demo]'),array('?c=newdemo&','[$$$:newdemo]'),$script);
    renameSame(renameOne('resources',"name='code!js'")['content_1'],$expected,'ordinary object rename preserves JavaScript variables, arrays and selectors');
    renameSame(renameOne('resources',"name='embedded'")['content_1'],'[newdemo] <script>'.$expected.'</script>','embedded scripts retain their source grammar');
    // Exact database bytes are compared under both legacy and modern column encodings.
    foreach (array('latin1','utf8mb4') as $charset) {
        $root=renameFixture('encoding');
        foreach (array('resources','resources_log') as $table) u5CodeMigrationQuery('ALTER TABLE '.$table.' MODIFY content_1 MEDIUMTEXT CHARACTER SET '.$charset);
        $text=$charset==='latin1' ? "\xDCber \x80 [demo] [[demo]]" : "中文 🌷 Über € [demo] [[demo]]";
        renameInsert('resources',renameRow('demo','p',0,$text));renameInsert('resources_log',renameRow('demo','p',0,$text));
        u5ResourceRename($root,'demo','newdemo',$options);
        renameSame(renameOne('resources')['content_1'],str_replace('[demo] [[demo]]','[newdemo] [[demo]]',$text),'resource encoding preserved '.$charset);
        renameSame(renameOne('resources_log')['content_1'],str_replace('[demo] [[demo]]','[newdemo] [[demo]]',$text),'history encoding preserved '.$charset);
    }
    $root=renameFixture('large-video');renameInsert('resources',renameRow('movie','v'));
    mkdir($root.'/r/movie');$file=$root.'/r/movie/movie_en.mp4';$handle=fopen($file,'wb');fwrite($handle,'video header');ftruncate($handle,128*1024*1024);fclose($handle);$hash=hash_file('sha256',$file);
    u5ResourceRename($root,'movie','film',$options);
    renameSame(filesize($root.'/r/film/film_en.mp4'),128*1024*1024,'large video retains every byte of its size');
    renameSame(hash_file('sha256',$root.'/r/film/film_en.mp4'),$hash,'large video full SHA256 conservation checked independently of rename');
    $root=renameFixture('indexer-race');renameInsert('resources',renameRow('demo','p',0,'[demo]'));
    $race=function(){u5CodeMigrationQuery("UPDATE resources SET search_1='fresh derived cache' WHERE name='demo'");};
    u5ResourceRename($root,'demo','newdemo',$options);
    renameSame(renameOne('resources')['content_1'],'[newdemo]','background index refresh does not block rename');
    renameSame(renameOne('resources')['search_1'],null,'changed rendered text always invalidates concurrent derived cache');
    $root=renameFixture('capacity');renameInsert('resources',renameRow('demo'));renameInsert('resources',renameRow('dependent','p',0));
    u5CodeMigrationQuery("ALTER TABLE resources MODIFY key_1 VARCHAR(6)");u5CodeMigrationQuery("UPDATE resources SET key_1='[demo]' WHERE name='dependent'");
    $before=renameOne('resources',"name='demo'");$failed=false;
    try {u5ResourceRename($root,'demo','newdemo',$options);}catch(RuntimeException $error){$failed=strpos($error->getMessage(),'capacity')!==false;}
    renameCheck($failed,'expanding reference fails before truncation');renameSame(renameOne('resources',"name='demo'"),$before,'capacity error leaves source unchanged');renameSame(renameOne('resources',"name='dependent'")['key_1'],'[demo]','capacity error keeps full reference');
    u5CodeMigrationQuery('ALTER TABLE resources MODIFY key_1 VARCHAR(255)');
    $root=renameFixture('damaged-journal');renameInsert('resources',renameRow('demo','p',0,'[demo]'));
    $fault='rename:planned';try {u5ResourceRename($root,'demo','newdemo',$options);}catch(RuntimeException $expected){}
    $pending=u5RenameBase($root,'demo','newdemo').'.pending.php';$plan=u5CodeMigrationReadGuarded($pending);$chunk=$root.'/'.$plan['updates']['chunks'][0]['path'];$bytes=file_get_contents($chunk);file_put_contents($chunk,$bytes.'damaged');
    $failed=false;try {u5ResourceRename($root,'demo','newdemo',$options);}catch(RuntimeException $error){$failed=strpos($error->getMessage(),'damaged')!==false;}
    renameCheck($failed,'damaged guarded row journal blocks all mutations');renameSame(renameOne('resources')['name'],'demo','damaged journal keeps ownership');file_put_contents($chunk,$bytes);u5ResourceRename($root,'demo','newdemo',$options);renameSame(renameOne('resources')['content_1'],'[newdemo]','restored journal resumes');
    foreach (array('rename:planned','rename:archive','rename:directory','rename:file','rename:row:0','rename:database','rename:published','rename:complete') as $phase) {
        $root=renameFixture('sigkill');renameInsert('resources',renameRow('demo','p',0,'[demo]'));renameInsert('resources_log',renameRow('demo','p',0,'[demo]'));
        mkdir($root.'/r/demo');mkdir($root.'/r/newdemo');file_put_contents($root.'/r/demo/demo_en.jpg','original file');file_put_contents($root.'/r/newdemo/keep','independent orphan');
        $settings=array('database'=>$database,'root'=>$root,'phase'=>$phase,'options'=>$options);
        $command=array_merge(array(PHP_BINARY),json_decode(getenv('U5_TEST_PHP_ARGS') ?: '[]',true),array(__DIR__.'/rename-crash-worker.php',json_encode($settings)));
        $process=proc_open($command,array(1=>array('pipe','w'),2=>array('pipe','w')),$pipes);stream_set_timeout($pipes[1],15);
        $line=fgets($pipes[1]);renameSame($line,"KILL CHECKPOINT\n",'worker reaches actual crash point '.$phase);
        renameCheck(proc_terminate($process,9),'worker receives SIGKILL '.$phase);fclose($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[2]);$status=proc_close($process);renameCheck($status!==0,'worker did not return normally '.$error);
        u5ResourceRename($root,'demo','newdemo',$options);
        renameSame(renameOne('resources')['name'],'newdemo','SIGKILL ownership resumes '.$phase);renameSame(renameOne('resources')['operator'],'R original','SIGKILL does not increment revision twice '.$phase);renameSame(renameOne('resources_log')['name'],'newdemo','SIGKILL history resumes '.$phase);renameSame(file_get_contents($root.'/r/newdemo/newdemo_en.jpg'),'original file','SIGKILL file bytes preserved '.$phase);
    }
    echo "Rename database checks: $checks total passed.\n";
} finally {
    u5CodeMigrationQuery('DROP DATABASE `'.$database.'`');foreach ($roots as $root) renameTreeRemove($root);
}
