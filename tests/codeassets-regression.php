<?php
/* Run with mysqli loaded and U5CMS_TEST_PORT pointing at a disposable local MariaDB. */
ob_start();
require_once dirname(__DIR__).'/codeassets.inc.php';
require_once dirname(__DIR__).'/u5admin/codeassets-migrate.inc.php';
$checks=0;
function checkAsset($condition,$message) {
    global $checks; $checks++;
    if (!$condition) throw new RuntimeException('CHECK FAILED: '.$message);
}
function sameAsset($actual,$expected,$message) { checkAsset($actual===$expected,$message.' ('.substr(var_export($actual,true),0,500).')'); }
foreach (array('mobile!js'=>'mobile.js','base!css'=>'base.css','one!two!js'=>'one!two.js','x!JSON'=>'x.json') as $name=>$file) sameAsset(u5CodeAssetFilename($name),$file,'last suffix: '.$name);
foreach (array('plain','!js','a!','../a!js','a/b!css','a!php','a!phtml','a!htaccess') as $name) {
    $failed=false; try { u5CodeAssetFilename($name); } catch (RuntimeException $error) { $failed=true; }
    checkAsset($failed,'reject unsafe/ambiguous export '.$name);
}
$references=array(
    '@import url(csslayout.css);'=>'@import url(layout.css);',
    'r/cssbase.tpl'=>'r/base!css.tpl',
    'cssbase.tpl'=>'base!css.tpl',
    '@import url("cssstyle.css?123#x");'=>'@import url("style.css?123#x");',
    '<script src="https://info.agov.ch/r/jsmobilespecific.css?t=1#x"></script>'=>'<script src="https://info.agov.ch/r/mobile.js?t=1#x"></script>',
    "jQuery.loadScript('https://yuba.ch/r/jsmobilegeneral.css',f);"=>"jQuery.loadScript('r/mobilebase.js',f);",
    "jQuery.loadScript('https://info.agov.ch/r/jsmobilegeneral.css',f);"=>"jQuery.loadScript('https://info.agov.ch/r/mobilebase.js',f);",
    '[cssbase] [$$$:cssbase] [cssbase?l=de] {{{htmltemplate}}}'=>'[base!css] [$$$:base!css] [base!css?l=de] {{{htmltemplate!html}}}',
    'r/cssbase.css.backup'=>'r/cssbase.css.backup',
    'https://yuba.ch/wa'=>'https://yuba.ch/wa',
    '<script src="f.php?f=r/jsmobilespecific.css?t=1"></script>'=>'<script src="r/mobile.js?t=1"></script>',
    '<script src="https://info.agov.ch/f.php?f=r/mobile.js"></script>'=>'<script src="https://info.agov.ch/r/mobile.js"></script>',
    'f.php?f=r/private/image.svg'=>'f.php?f=r/private/image.svg',
    '&amp; &#x1F600; &eacute;'=>'&amp; &#x1F600; &eacute;'
);
foreach ($references as $before=>$after) sameAsset(u5CodeAssetReferences($before),$after,'reference '.$before);
sameAsset(u5CodeAssetReferences('<script src="r/already!js.css?t=1"></script>',array('already!js'=>'already!js')),'<script src="r/already.js?t=1"></script>','old exporter appended CSS even to explicitly suffixed names');
sameAsset(u5CodeAssetReferences('@import url(base!css.css);',array('base!css'=>'base!css')),'@import url(base.css);','already suffixed legacy stylesheet URL');
$crossMap=array('cssbase'=>'base!css','base'=>'other!css');
foreach (array('r/cssbase.css'=>'r/base.css','https://yuba.ch/r/cssbase.css'=>'r/base.css','cssbase.css'=>'base.css') as $before=>$after) sameAsset(u5CodeAssetReferences($before,$crossMap),$after,'single replacement pass: '.$before);
sameAsset(json_decode(u5CodeAssetReferences(json_encode(array('a'=>'r/cssbase.css','b'=>'r/base.css')),$crossMap),true),array('a'=>'r/base.css','b'=>'r/other.css'),'escaped JSON paths never cascade');
sameAsset(json_decode(u5CodeAssetReferences(json_encode(array('url'=>'r/cssbase.css.backup'))),true)['url'],'r/cssbase.css.backup','escaped backup paths are not source references');
sameAsset(json_decode(u5CodeAssetReferences(json_encode(array('html'=>'<script src="r/jsmobilespecific.css"></script>'))),true)['html'],'<script src="r/mobile.js"></script>','escaped HTML inside JSON');
$serialized=serialize(array('url'=>'https://elsewhere.invalid/r/cssbase.css','nested'=>serialize(array('x'=>'r/jsmobilespecific.css'))));
$rewritten=unserialize(u5CodeAssetReferences($serialized));
sameAsset($rewritten['url'],'https://elsewhere.invalid/r/base.css','serialized byte count');
sameAsset(unserialize($rewritten['nested'])['x'],'r/mobile.js','nested serialized byte count');
$json=json_encode(array('url'=>'https://yuba.ch/r/jsmobilegeneral.css'));
sameAsset(json_decode(u5CodeAssetReferences($json),true)['url'],'r/mobilebase.js','escaped JSON CDN URL');
foreach (array(
    array('abcxy','var answer = 42;','abcxy!js'),
    array('abcxy','.test { color: red; }','abcxy!css'),
    array('abcxy','<div>Hello</div>','abcxy!html'),
    array('abcxy','{"hello":true}','abcxy!json'),
    array('abcxy','','abcxy!css'),
    array('one!old','','one!old!css')
) as $case) sameAsset(u5CodeAssetGuess($case[0],$case[1])[0],$case[2],'guess '.$case[1]);
sameAsset(u5CodeAssetGuess('arrows','.test { content: "=>"; }')[0],'arrows!css','CSS arrow string is not JavaScript');
sameAsset(u5CodeAssetGuess('markup','<!-- note --><div>Hello</div>')[0],'markup!html','HTML comments before markup');
sameAsset(u5CodeAssetLegacyFilename('../outside'),null,'unsafe legacy paths are not read or removed');
sameAsset(u5CodeAssetVersionURL('r/base.css?123#x',99),'r/base.css?u5v=99#x','numeric version replaced');
sameAsset(u5CodeAssetVersionURL('r/base.css?mode=x&u5v=4#x',99),'r/base.css?mode=x&u5v=99#x','query preserved');
sameAsset(u5CodeAssetUtf8("\xDCber \x80 &amp;"),"\xC3\x9Cber \xE2\x82\xAC &amp;",'Windows-1252 output without entity decoding');
sameAsset(u5CodeAssetProxyMediaSources('<script src="r/mobile.js?u5v=1"></script>'),'<script src="r/mobile.js?u5v=1"></script>','flat code export bypasses media proxy');
sameAsset(u5CodeAssetProxyMediaSources('<img src="r/private/image.jpg?t=1">'),'<img src="f.php?f=r/private/image.jpg?t=1">','protected media keeps its proxy');
sameAsset(u5CodeAssetProxyMediaSources("<img src='r/private/image.jpg'>"),"<img src='f.php?f=r/private/image.jpg'>",'single-quoted media keeps its proxy');
sameAsset(rtrim(str_replace("\r\n","\n",file_get_contents(dirname(__DIR__).'/r/mobilebase!js.tpl'))),rtrim(u5CodeMigrationMobileSource()),'embedded updater fallback matches the packaged mobile reset template');
require_once __DIR__.'/codeassets-audit.inc.php';
u5CodeAuditCore();
require_once __DIR__.'/main-diff-audit.inc.php';u5MainDiffCore();
echo "Core checks: $checks passed.\n";
if (!getenv('U5CMS_TEST_PORT')) { echo "Database checks require U5CMS_TEST_PORT and mysqli.\n"; exit; }
require_once dirname(__DIR__).'/mysql.php';
require_once dirname(__DIR__).'/u5admin/codeassets-migrate.inc.php';
ini_set('mysqli.default_port',(string)(int)getenv('U5CMS_TEST_PORT'));
if (!mysql_connect('127.0.0.1',getenv('U5CMS_TEST_USER') ?: 'root',getenv('U5CMS_TEST_PASSWORD') ?: '')) throw new RuntimeException('Cannot connect to the disposable database.');
$testRoot=sys_get_temp_dir().'/u5-codeassets-'.bin2hex(random_bytes(6));
mkdir($testRoot,0700,true); $databases=array(); $faultPhase=null; $concurrentEdit=false; $parallelProbe=null; $templateConcurrentRoot=null;$blockedOutputRoot=null;
function u5CodeMigrationTestCheckpoint($phase) {
    global $faultPhase,$concurrentEdit,$parallelProbe,$templateConcurrentRoot,$blockedOutputRoot;
    if ($phase==='planned' && $blockedOutputRoot) { mkdir($blockedOutputRoot.'/r/mobile.js');$blockedOutputRoot=null; }
    if ($phase===$faultPhase) { $faultPhase=null; throw new RuntimeException('TEST INTERRUPTION '.$phase); }
    if ($phase==='template:0' && $templateConcurrentRoot) {
        file_put_contents($templateConcurrentRoot.'/r/cssbase.tpl','concurrent reset template edit');
        $templateConcurrentRoot=null;
    }
    if ($phase==='files' && $concurrentEdit) {
        $concurrentEdit=false;
        u5CodeMigrationQuery("UPDATE resources SET content_1=CONCAT(content_1,' concurrent edit') WHERE name='cssbase' AND deleted=0");
    }
    if ($phase==='files' && $parallelProbe) {
        $root=$parallelProbe; $parallelProbe=null;
        $code='require '.var_export(dirname(__DIR__).'/u5admin/codeassets-migrate.inc.php',true).';try {u5CodeAssetsMigrate('.var_export($root,true).');exit(2);}catch(Throwable $e){echo $e->getMessage();exit(strpos($e->getMessage(),"already running")!==false?0:3);}';
        $process=proc_open(array(PHP_BINARY,'-n','-r',$code),array(1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
        $output=stream_get_contents($pipes[1]).stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
        sameAsset(proc_close($process),0,'parallel updater is excluded: '.$output);
    }
}
function assetFixture($dump,$label) {
    global $testRoot,$databases;
    $database='u5asset_'.bin2hex(random_bytes(6)); $databases[]=$database;
    u5CodeMigrationQuery('CREATE DATABASE `'.$database.'` CHARACTER SET utf8mb4');
    mysql_select_db($database);
    $link=mysql_ensure_link(null);
    if (!mysqli_multi_query($link,$dump)) throw new RuntimeException('Cannot import fixture: '.mysqli_error($link));
    do { if ($result=mysqli_store_result($link)) mysqli_free_result($result); }
    while (mysqli_more_results($link) && mysqli_next_result($link));
    if (mysqli_errno($link)) throw new RuntimeException('Fixture SQL error: '.mysqli_error($link));
    u5CodeMigrationQuery('SET NAMES latin1');
    $root=$testRoot.'/'.$label;mkdir($root.'/r',0700,true);mkdir($root.'/fileversions',0700,true);mkdir($root.'/u5admin',0700,true);
    foreach (glob(dirname(__DIR__).'/r/*.tpl') as $source) copy($source,$root.'/r/'.basename($source));
    return $root;
}
function oldAssetDump($name) {
    $process=proc_open(array('git','show','d8ce338a329062d5a00642643bfa958553b3dd42:'.$name),array(1=>array('pipe','w'),2=>array('pipe','w')),$pipes,dirname(__DIR__));
    $dump=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
    if (proc_close($process)!==0) throw new RuntimeException('The legacy regression requires repository history: '.$error);
    return $dump;
}
function assetSnapshot() {
    $out=array();
    foreach (u5CodeMigrationRows('SHOW FULL TABLES') as $table) {
        $values=array_values($table);if (($values[1] ?? '')!=='BASE TABLE') continue;
        $rows=u5CodeMigrationReadTable($values[0],u5CodeMigrationColumns($values[0]));
        $encoded=array_map('serialize',$rows);sort($encoded);$out[$values[0]]=$encoded;
    }
    ksort($out);return $out;
}
function assertAssetInstallation($root,$label) {
    $rows=u5CodeMigrationRows("SELECT * FROM resources WHERE typ='c'");
    foreach ($rows as $row) checkAsset(u5CodeAssetIsTyped($row['name']),$label.': typed '.$row['name']);
    checkAsset(is_file($root.'/fileversions/codeassets-12.11.0.done.php'),$label.': completion marker');
    checkAsset(!is_file($root.'/fileversions/codeassets-12.11.0.pending.php'),$label.': checkpoint finalized');
    checkAsset(is_file(u5CodeTemplatePath($root)),$label.': active template exists');
    foreach (u5CodeAssetRowsToFiles($rows,1) as $file=>$data) checkAsset(is_file($root.'/r/'.$file),$label.': export '.$file);
    $template=file_get_contents(u5CodeTemplatePath($root));
    checkAsset(strpos($template,'jsmobilespecific.css')===false,$label.': template uses new mobile URL');
    $mobile=u5CodeAssetPublicPath('jsmobilespecific','r/',$root);
    checkAsset(strpos(file_get_contents($root.'/'.$mobile),'yuba.ch/r/')===false,$label.': no yuba runtime dependency');
    $before=assetSnapshot();u5CodeAssetsMigrate($root);sameAsset(assetSnapshot(),$before,$label.': repeated migration leaves database unchanged');
}
function oldAssetFiles($root) {
    static $templates=null;
    if ($templates===null) {
        $templates=array();
        foreach (u5CodeAssetDefaults() as $legacy=>$typed) {
            if ($legacy==='jsmobilegeneral' || $legacy==='cssupload' || $legacy==='csspworder') continue;
            $templates[$legacy]=oldAssetDump('r/'.$legacy.'.tpl');
        }
    }
    foreach (glob($root.'/r/*.tpl') as $file) unlink($file);
    foreach ($templates as $name=>$body) file_put_contents($root.'/r/'.$name.'.tpl',$body);
    foreach (u5CodeMigrationRows("SELECT * FROM resources WHERE typ='c' AND deleted!=1") as $row) file_put_contents($root.'/r/'.$row['name'].'.css',$row['content_1']);
}
function addAssetCode($name,$source,$deleted=0) {
    u5CodeMigrationQuery('INSERT INTO resources (name,content_1,typ,deleted,hidden,lastmut) VALUES ('.u5CodeMigrationValue($name).',_latin1 '.u5CodeMigrationValue($source).",'c',".(int)$deleted.',0,12345)');
}
function expectAssetFailure($callback,$label) {
    $failed=false;
    try { $callback(); } catch (RuntimeException $error) { $failed=true; }
    checkAsset($failed,$label);
}
function runAssetLayoutReset($root) {
    $reset=file_get_contents(dirname(__DIR__).'/u5admin/reactivateinitialu5cmslayout.php');
    $reset=preg_replace('/^die\([^\n]+\);\n/m','',$reset,1);
    file_put_contents($root.'/u5admin/reset-layout.php',$reset);
    if (!file_exists($root.'/u5admin/codeassets-migrate.inc.php')) symlink(dirname(__DIR__).'/u5admin/codeassets-migrate.inc.php',$root.'/u5admin/codeassets-migrate.inc.php');
    file_put_contents($root.'/u5admin/connect.inc.php','<?php');
    $_GET['iswear']='iknowthatthisoverwritesthecurrentlayout';
    $previous=getcwd();chdir($root.'/u5admin');ob_start();
    try { include $root.'/u5admin/reset-layout.php';return ob_get_contents(); }
    finally { ob_end_clean();chdir($previous); }
}
function runBackendAssetUpdate($root) {
    global $allowu5pwprotectedpageashomepage,$u5CodeMigrationComplete;
    $allowu5pwprotectedpageashomepage='yes';
    $_SERVER['PHP_AUTH_USER']='Temp';$_SERVER['PHP_AUTH_PW']='fixture';
    file_put_contents($root.'/u5admin/connect.inc.php','<?php');
    file_put_contents($root.'/config.php','<?php');
    foreach (array('zr.php','endrunonce.php') as $file) copy(dirname(__DIR__).'/u5admin/'.$file,$root.'/u5admin/'.$file);
    if (!file_exists($root.'/u5admin/codeassets-migrate.inc.php')) symlink(dirname(__DIR__).'/u5admin/codeassets-migrate.inc.php',$root.'/u5admin/codeassets-migrate.inc.php');
    if (!file_exists($root.'/r/runonce.php')) copy(dirname(__DIR__).'/r/runonce.php',$root.'/r/runonce.php');
    $previous=getcwd();chdir($root.'/u5admin');ob_start();
    try { include $root.'/u5admin/zr.php';return ob_get_contents(); }
    finally { ob_end_clean();chdir($previous); }
}
try {
    foreach (array('u5standard.sql','u5conference.sql') as $seed) {
        $root=assetFixture(file_get_contents(dirname(__DIR__).'/'.$seed),'fresh-'.$seed);
        $before=assetSnapshot();u5CodeAssetsMigrate($root);
        sameAsset(assetSnapshot(),$before,'fresh seed is already migrated: '.$seed);
        assertAssetInstallation($root,'fresh '.$seed);
    }
    foreach (array('u5standard.sql','u5conference.sql') as $seed) {
        $root=assetFixture(oldAssetDump($seed),'old-'.$seed);
        oldAssetFiles($root);
        u5CodeAssetsMigrate($root);assertAssetInstallation($root,'legacy '.$seed);
        checkAsset(!is_file($root.'/r/jsmobilespecific.css'),'legacy mobile file removed after completion');
    }
    $legacy=oldAssetDump('u5standard.sql');
    $root=assetFixture($legacy,'customized');
    addAssetCode('abcxy',"var greeting = '\xDCber \x80 &amp;'; document.title = greeting;");
    addAssetCode('panel','<div>Custom HTML &amp; &#x1F600;</div>');
    addAssetCode('one!legacy','.custom { color: gold; }');
    addAssetCode('emptycode','');
    addAssetCode('already!js','window.alreadyTyped=true;');
    addAssetCode('cssbase','/* a deleted version */',1);
    u5CodeMigrationQuery('CREATE TABLE custom_assets (id INT PRIMARY KEY, payload MEDIUMTEXT CHARACTER SET utf8mb4, latin_payload TEXT CHARACTER SET latin1, serialized_data MEDIUMTEXT CHARACTER SET utf8mb4, raw_data BLOB) ENGINE=MyISAM');
    $unicode="\xD0\x96\xC3\xA9 \xF0\x9F\x8C\xB7 &amp; &#123; r/cssbase.css";
    $serialized=serialize(array('url'=>'r/jsmobilespecific.css','unicode'=>$unicode));
    u5CodeMigrationQuery('INSERT INTO custom_assets VALUES (1,'.u5CodeMigrationValue($unicode).','.'_latin1 '.u5CodeMigrationValue("\xDCber \x80 r/cssbase.css").','.u5CodeMigrationValue($serialized).','.u5CodeMigrationValue('r/cssbase.css').')');
    u5CodeMigrationQuery("INSERT INTO inserts (id,target1) VALUES (999,'<link href=\"https://info.agov.ch/r/cssbase.css?x=1\">')");
    u5CodeMigrationQuery("INSERT INTO resources (name,typ,deleted) VALUES ('migration-language-fixture','p',0)");
    $languageBefore='[cssbase] <script src="https://elsewhere.invalid/subdir/r/jsmobilespecific.css?t=123#x"></script> <script src="r/already!js.css"></script>';
    $languageAfter='[base!css] <script src="https://elsewhere.invalid/subdir/r/mobile.js?t=123#x"></script> <script src="r/already.js"></script>';
    foreach (range(1,5) as $language) {
        u5CodeMigrationQuery('UPDATE resources SET content_'.$language.'='.u5CodeMigrationValue($languageBefore)." WHERE name='migration-language-fixture' AND deleted=0");
        sameAsset(mysql_affected_rows(),1,'language fixture updated: '.$language);
    }
    u5CodeMigrationInsert('resources_log',u5CodeMigrationRows("SELECT * FROM resources WHERE name='migration-language-fixture'")[0]);
    // A real shared-site script can be supplied without checking that site's custom code into this repository.
    if (getenv('U5CMS_TEST_SITE_SOURCE')) {
        $site=file_get_contents(getenv('U5CMS_TEST_SITE_SOURCE'));
        u5CodeMigrationQuery('UPDATE resources SET content_1=_latin1 '.u5CodeMigrationValue($site)." WHERE name='jsmobilespecific'");
    }
    oldAssetFiles($root);
    u5CodeAssetsMigrate($root);assertAssetInstallation($root,'customized');
    foreach (array('abcxy!js','panel!html','one!legacy!css','emptycode!css','already!js') as $expected) {
        $found=u5CodeMigrationRows('SELECT name FROM resources WHERE name='.u5CodeMigrationValue($expected));
        checkAsset(count($found)===1,'guessed object preserved as '.$expected);
    }
    foreach (array('resources','resources_log') as $table) {
        $page=u5CodeMigrationRows('SELECT * FROM '.$table." WHERE name='migration-language-fixture'");
        sameAsset(count($page),1,'language fixture retained in '.$table);
        foreach (range(1,5) as $language) sameAsset($page[0]['content_'.$language],$languageAfter,'language '.$language.' references in '.$table);
    }
    sameAsset(u5CodeMigrationRows('SELECT target1 FROM inserts WHERE id=999')[0]['target1'],'<link href="https://info.agov.ch/r/base.css?x=1">','insert definitions retain remote host and query');
    $custom=u5CodeMigrationReadTable('custom_assets',u5CodeMigrationColumns('custom_assets'))[0];
    sameAsset($custom['payload'],str_replace('r/cssbase.css','r/base.css',$unicode),'UTF-8 and entities preserved exactly');
    sameAsset($custom['latin_payload'],"\xDCber \x80 r/base.css",'Latin-1 column preserved exactly');
    sameAsset($custom['raw_data'],'r/cssbase.css','binary data is not search-replaced');
    sameAsset(unserialize($custom['serialized_data'])['url'],'r/mobile.js','serialized data remains valid after DB rewrite');
    sameAsset(unserialize($custom['serialized_data'])['unicode'],str_replace('r/cssbase.css','r/base.css',$unicode),'serialized UTF-8 byte counts remain valid');
    checkAsset(is_file($root.'/r/already.js'),'explicitly suffixed legacy object exported with real suffix');
    checkAsset(!is_file($root.'/r/already!js.css'),'old double suffix export removed');
    $script=file_get_contents($root.'/r/abcxy.js');
    checkAsset(strpos($script,"\xC3\x9Cber \xE2\x82\xAC &amp;")!==false,'JavaScript exports UTF-8 without decoding entities');
    checkAsset(is_file($root.'/r/mobilebase.js'),'common mobile code installed locally');
    $report=file_get_contents($root.'/fileversions/codeassets-12.11.0-report.php');
    checkAsset(strpos($report,'abcxy -> abcxy!js')!==false && strpos($report,'emptycode -> emptycode!css')!==false,'guesses documented');

    $root=assetFixture($legacy,'custom-reset-sources');oldAssetFiles($root);
    $activeCSS='/* active site code */ .active { color: red; }';
    $resetCSS="/* reset baseline \xDCber \x80 &amp; */\n@import url(csslayout.css?mode=reset#top);\n.reset { color: blue; }";
    $expectedResetCSS=str_replace('csslayout.css','layout.css',$resetCSS);
    u5CodeMigrationQuery('UPDATE resources SET content_1=_latin1 '.u5CodeMigrationValue($activeCSS)." WHERE name='cssbase' AND deleted=0");
    file_put_contents($root.'/r/cssbase.tpl',$resetCSS);
    $resetHTML=str_replace('<body id="body">','<body id="body" data-reset="preserved">',file_get_contents($root.'/r/htmltemplate.tpl'));
    file_put_contents($root.'/r/htmltemplate.tpl',$resetHTML);
    addAssetCode('customreset','var activeReset=1;');
    file_put_contents($root.'/r/customreset.tpl','var resetLink="r/cssbase.css";');
    u5CodeAssetsMigrate($root);
    sameAsset(u5CodeMigrationRows("SELECT content_1 FROM resources WHERE name='base!css' AND deleted=0")[0]['content_1'],$activeCSS,'migration keeps active DB layout distinct from reset baseline');
    sameAsset(file_get_contents($root.'/r/base!css.tpl'),$expectedResetCSS,'custom reset CSS and encoding survive under the new name');
    sameAsset(u5CodeMigrationFileBackup($root,'r/cssbase.tpl'),$resetCSS,'original custom reset source backed up exactly');
    checkAsset(!is_file($root.'/r/cssbase.tpl'),'legacy reset source removed after its replacement is published');
    sameAsset(file_get_contents($root.'/r/customreset!js.tpl'),'var resetLink="r/base.css";','unknown C reset source renamed and linked');
    checkAsset(strpos(u5CodeMigrationRows("SELECT content_1 FROM resources WHERE name='htmltemplate!html' AND deleted=0")[0]['content_1'],'data-reset="preserved"')===false,'HTML reset baseline is not applied by the update');
    runAssetLayoutReset($root);
    sameAsset(u5CodeMigrationRows("SELECT content_1 FROM resources WHERE name='base!css' AND deleted=0")[0]['content_1'],$expectedResetCSS,'explicit reset restores the original custom baseline');
    $resetPage=u5CodeMigrationRows("SELECT content_1 FROM resources WHERE name='htmltemplate!html' AND deleted=0")[0]['content_1'];
    checkAsset(strpos($resetPage,'data-reset="preserved"')!==false && strpos($resetPage,'r/base.css')!==false,'HTML reset restores custom markup with current references');
    assertAssetInstallation($root,'custom reset sources');

    $root=assetFixture($legacy,'collisions');
    addAssetCode('base!css','/* existing independently named base */');
    addAssetCode('htmltemplate!html','<body id="body">independent template</body>');
    u5CodeMigrationQuery("INSERT INTO resources (name,typ,deleted,content_1) VALUES ('mobile!js','p',0,'independent page')");
    oldAssetFiles($root);
    file_put_contents($root.'/r/cssbase.tpl','/* preserved role reset */ @import url(csslayout.css);');
    file_put_contents($root.'/r/base!css.tpl','/* independent reset baseline */');
    u5CodeAssetsMigrate($root);assertAssetInstallation($root,'collisions');
    $state=include $root.'/fileversions/codeassets-12.11.0.done.php';
    checkAsset($state['map']['cssbase']!=='base!css','base collision gets distinct name');
    checkAsset($state['template']!=='htmltemplate!html','active old template retained under a distinct name');
    checkAsset($state['map']['jsmobilespecific']!=='mobile!js','non-code page collision retained');
    sameAsset(u5CodeMigrationRows("SELECT content_1 FROM resources WHERE name='base!css'")[0]['content_1'],'/* existing independently named base */','independent CSS not overwritten');
    sameAsset(u5CodeMigrationRows("SELECT content_1 FROM resources WHERE name='mobile!js'")[0]['content_1'],'independent page','independent page not overwritten');
    $liveTemplate=file_get_contents(u5CodeTemplatePath($root));
    checkAsset(strpos($liveTemplate,'r/'.u5CodeAssetFilename($state['map']['cssbase']))!==false,'template points to its own preserved stylesheet');
    checkAsset(strpos($liveTemplate,'r/'.u5CodeAssetFilename($state['map']['jsmobilespecific']))!==false,'template points to its own preserved script');

    // Exercise the real layout-reset implementation in an isolated copy, including collision roles.
    runAssetLayoutReset($root);
    sameAsset(u5CodeMigrationRows("SELECT content_1 FROM resources WHERE name='base!css'")[0]['content_1'],'/* existing independently named base */','layout reset preserves independently named CSS');
    sameAsset(u5CodeMigrationRows("SELECT content_1 FROM resources WHERE name='htmltemplate!html'")[0]['content_1'],'<body id="body">independent template</body>','layout reset preserves independent template');
    $resetTemplate=file_get_contents(u5CodeTemplatePath($root));
    checkAsset(strpos($resetTemplate,'r/'.u5CodeAssetFilename($state['map']['cssbase']))!==false,'layout reset follows stylesheet collision role');
    checkAsset(strpos($resetTemplate,'r/'.u5CodeAssetFilename($state['map']['jsmobilespecific']))!==false,'layout reset follows mobile collision role');
    sameAsset(file_get_contents($root.'/r/base!css.tpl'),'/* independent reset baseline */','independent reset template is not replaced');
    sameAsset(u5CodeMigrationRows('SELECT content_1 FROM resources WHERE name='.u5CodeMigrationValue($state['map']['cssbase']))[0]['content_1'],'/* preserved role reset */ @import url(layout.css);','reset restores the correct colliding resource baseline');
    assertAssetInstallation($root,'layout reset with collisions');

    foreach (array('planned','template:0','template:3','templates-written','template-cleanup:2','templates-complete','files','row:0','row:5','database','exported','complete') as $phase) {
        $root=assetFixture($legacy,'interrupt-'.str_replace(':','-',$phase));oldAssetFiles($root);
        $faultPhase=$phase;
        expectAssetFailure(function() use($root){ u5CodeAssetsMigrate($root); },'interrupted at '.$phase);
        checkAsset(is_file($root.'/fileversions/codeassets-12.11.0.pending.php'),'checkpoint kept after '.$phase);
        if ($phase!=='complete') checkAsset(is_file($root.'/r/jsmobilespecific.css'),'old script remains until completion after '.$phase);
        u5CodeAssetsMigrate($root);assertAssetInstallation($root,'resumed '.$phase);
    }
    $root=assetFixture($legacy,'blocked-output');oldAssetFiles($root);
    $blockedOutputRoot=$root;$before=assetSnapshot();
    expectAssetFailure(function() use($root){ u5CodeAssetsMigrate($root); },'blocked file publication stops migration');
    sameAsset(assetSnapshot(),$before,'file failure leaves database untouched');
    checkAsset(is_file($root.'/r/jsmobilespecific.css'),'file failure preserves the old serving file');
    rmdir($root.'/r/mobile.js');u5CodeAssetsMigrate($root);assertAssetInstallation($root,'file failure retried');

    $root=assetFixture($legacy,'embedded-common-source');oldAssetFiles($root);
    checkAsset(!is_dir($root.'/u5admin/code-templates'),'update does not need a new template directory');
    u5CodeAssetsMigrate($root);assertAssetInstallation($root,'embedded common source');
    checkAsset(is_file($root.'/r/mobilebase!js.tpl'),'common reset source is installed under r');

    $root=assetFixture($legacy,'blocked-reset-source');oldAssetFiles($root);$before=assetSnapshot();
    mkdir($root.'/r/base!css.tpl');
    expectAssetFailure(function() use($root){u5CodeAssetsMigrate($root);},'reset template publication failure stops the update');
    sameAsset(assetSnapshot(),$before,'reset template file failure leaves DB untouched');
    checkAsset(is_file($root.'/r/cssbase.tpl'),'old reset source retained on publication failure');
    rmdir($root.'/r/base!css.tpl');u5CodeAssetsMigrate($root);assertAssetInstallation($root,'reset publication retried');

    $root=assetFixture($legacy,'concurrent-reset-source');oldAssetFiles($root);$before=assetSnapshot();$templateConcurrentRoot=$root;
    expectAssetFailure(function() use($root){u5CodeAssetsMigrate($root);},'concurrent reset template edit is detected');
    sameAsset(file_get_contents($root.'/r/cssbase.tpl'),'concurrent reset template edit','concurrent reset source is not overwritten or removed');
    sameAsset(assetSnapshot(),$before,'concurrent reset source edit leaves DB unchanged');
    checkAsset(!is_file($root.'/fileversions/codeassets-12.11.0-templates.done.php'),'concurrent reset source does not mark template migration complete');

    // Recreate the first 12.11.0 preview's completed state: old sources are only in protected backups.
    $root=assetFixture($legacy,'completed-preview-recovery');oldAssetFiles($root);
    file_put_contents($root.'/r/cssbase.tpl','/* site-specific reset from before the preview */ @import url(csslayout.css);');
    u5CodeAssetsMigrate($root);$before=assetSnapshot();
    unlink($root.'/fileversions/codeassets-12.11.0-templates.done.php');
    foreach (glob($root.'/r/*.tpl') as $file) unlink($file);
    mkdir($root.'/u5admin/code-templates');
    foreach (glob(dirname(__DIR__).'/r/*.tpl') as $source) copy($source,$root.'/u5admin/code-templates/'.basename($source));
    u5CodeAssetsMigrate($root);
    sameAsset(file_get_contents($root.'/r/base!css.tpl'),'/* site-specific reset from before the preview */ @import url(layout.css);','completed preview recovers customized reset baseline from original backup');
    checkAsset(!is_dir($root.'/u5admin/code-templates'),'obsolete preview folder removed after reset sources are recovered');
    sameAsset(assetSnapshot(),$before,'completed preview repair does not repeat DB migration');
    file_put_contents($root.'/r/base!css.tpl','/* typed reset source edited after the preview */');
    unlink($root.'/fileversions/codeassets-12.11.0-templates.done.php');
    u5CodeAssetsMigrate($root);
    sameAsset(file_get_contents($root.'/r/base!css.tpl'),'/* typed reset source edited after the preview */','post-preview edits take precedence over historical backups');
    $oldPending=array('version'=>'12.11.0','oldFiles'=>array('r/base!css.tpl','r/cssbase.tpl'));
    file_put_contents($root.'/fileversions/codeassets-12.11.0.pending.php',"<?php exit; ?>\n".serialize($oldPending));
    u5CodeAssetsMigrate($root);
    sameAsset(file_get_contents($root.'/r/base!css.tpl'),'/* typed reset source edited after the preview */','old preview cleanup cannot delete a recovered typed reset source');
    checkAsset(!is_file($root.'/fileversions/codeassets-12.11.0.pending.php'),'old preview cleanup checkpoint finishes safely');
    assertAssetInstallation($root,'completed preview recovery');
    $shared=$root.'/shared-reset-sources';mkdir($shared);
    foreach (glob(dirname(__DIR__).'/r/*.tpl') as $source) copy($source,$shared.'/'.basename($source));
    symlink($shared,$root.'/u5admin/code-templates');
    unlink($root.'/fileversions/codeassets-12.11.0-templates.done.php');
    u5CodeAssetsMigrate($root);
    checkAsset(is_file($shared.'/base!css.tpl'),'recovery never deletes templates through a shared-directory symlink');
    checkAsset(is_link($root.'/u5admin/code-templates'),'shared-directory alias is retained instead of deleting external contents');

    $root=assetFixture($legacy,'sql-error');oldAssetFiles($root);$before=assetSnapshot();
    u5CodeMigrationQuery("CREATE TRIGGER fixture_failure BEFORE UPDATE ON resources FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture failure'");
    expectAssetFailure(function() use($root){ u5CodeAssetsMigrate($root); },'SQL error is reported');
    checkAsset(is_file($root.'/fileversions/codeassets-12.11.0.pending.php'),'SQL error keeps checkpoint');
    u5CodeMigrationQuery('DROP TRIGGER fixture_failure');u5CodeAssetsMigrate($root);assertAssetInstallation($root,'SQL error retried');

    $root=assetFixture($legacy,'concurrent-edit');oldAssetFiles($root);$concurrentEdit=true;
    expectAssetFailure(function() use($root){ u5CodeAssetsMigrate($root); },'concurrent edit is detected');
    $edited=u5CodeMigrationRows("SELECT content_1 FROM resources WHERE name='cssbase' AND deleted=0");
    checkAsset(strpos($edited[0]['content_1'],'concurrent edit')!==false,'concurrent content not overwritten');
    checkAsset(!is_file($root.'/fileversions/codeassets-12.11.0.done.php'),'concurrent edit never marks migration complete');

    $root=assetFixture($legacy,'local-common-script');oldAssetFiles($root);
    file_put_contents($root.'/r/jsmobilegeneral.css',"var jsmobilegeneralloaded=true; var localCustomization='kept';");
    u5CodeAssetsMigrate($root);
    checkAsset(strpos(file_get_contents($root.'/r/mobilebase.js'),'localCustomization')!==false,'existing local common script preserved');
    assertAssetInstallation($root,'local common script');

    $root=assetFixture($legacy,'mime-rule');oldAssetFiles($root);
    $block="# These legacy CSS resources contain JavaScript. Their source and references\n# also live in the database, so keep the existing URLs when setting the MIME type.\n<FilesMatch \"^jsmobile(specific|general)\\.css$\">\n    ForceType text/javascript\n</FilesMatch>\n";
    file_put_contents($root.'/r/.htaccess',"# own rule\nOptions -Indexes\n".$block);
    u5CodeAssetsMigrate($root);
    sameAsset(file_get_contents($root.'/r/.htaccess'),"# own rule\nOptions -Indexes\n",'only the known MIME block removed');

    $root=assetFixture($legacy,'parallel-update');oldAssetFiles($root);$parallelProbe=$root;
    u5CodeAssetsMigrate($root);assertAssetInstallation($root,'parallel update');

    $root=assetFixture($legacy,'backend-update');oldAssetFiles($root);
    $faultPhase='files';$response=runBackendAssetUpdate($root);
    checkAsset(is_file($root.'/r/runonce.php'),'backend keeps runonce after failure');
    checkAsset(strpos($response,'was not completed')!==false,'backend reports failed update');
    $response=runBackendAssetUpdate($root);
    checkAsset(!is_file($root.'/r/runonce.php'),'backend removes runonce only after success: '.substr(strip_tags($response),-1800));
    checkAsset(strpos($response,'updated to 12.11.2')!==false,'backend reports successful update');
    assertAssetInstallation($root,'backend update');

    $root=assetFixture($legacy,'older-schema');oldAssetFiles($root);
    u5CodeMigrationQuery('ALTER TABLE resources DROP content_4, DROP content_5');
    u5CodeMigrationQuery('ALTER TABLE resources_log DROP content_4, DROP content_5');
    u5CodeMigrationQuery("INSERT INTO inserts (id,target1) VALUES (999,'custom insert definition')");
    $response=runBackendAssetUpdate($root);
    checkAsset(!is_file($root.'/r/runonce.php'),'older schema finishes in one backend update: '.substr(strip_tags($response),-800));
    checkAsset(!u5CodeMigrationNeedsLegacySchema(),'older schema has all current required columns');
    sameAsset(u5CodeMigrationRows('SELECT target1 FROM inserts WHERE id=999')[0]['target1'],'custom insert definition','legacy update keeps custom insert definitions');
    assertAssetInstallation($root,'older schema');

    $root=assetFixture($legacy,'rename-lifecycle');oldAssetFiles($root);u5CodeAssetsMigrate($root);
    $faultPhase='template:0';
    expectAssetFailure(function() use($root){u5CodeAssetsRename($root,'base!css','theme!custom!css');},'code rename can resume after template publication interruption');
    u5CodeAssetsRename($root,'base!css','theme!custom!css');
    checkAsset(is_file($root.'/r/theme!custom.css'),'rename uses final exclamation mark');
    checkAsset(!is_file($root.'/r/base.css'),'renamed old stylesheet removed');
    checkAsset(strpos(file_get_contents(u5CodeTemplatePath($root)),'r/theme!custom.css')!==false,'rename updates template URL');
    sameAsset(u5CodeAssetPublicPath('cssbase','r/',$root),'r/theme!custom.css','role follows renamed stylesheet');
    checkAsset(is_file($root.'/r/theme!custom!css.tpl') && !is_file($root.'/r/base!css.tpl'),'code rename also renames its reset source');
    checkAsset(strpos(file_get_contents($root.'/r/htmltemplate!html.tpl'),'r/theme!custom.css')!==false,'code rename updates reset-template dependencies');
    u5CodeAssetsRename($root,u5CodeTemplateName($root),'site!layout!html');
    sameAsset(u5CodeTemplateName($root),'site!layout!html','active template follows rename');
    checkAsset(is_file(u5CodeTemplatePath($root)),'renamed active template exists');
    u5CodeAssetsMigrate($root);
    checkAsset(is_file(u5CodeTemplatePath($root)),'repeated update keeps renamed active template');
    runAssetLayoutReset($root);
    checkAsset(is_file($root.'/r/site!layout!html.tpl'),'renamed active HTML reset source exists');
    checkAsset(strpos(file_get_contents(u5CodeTemplatePath($root)),'r/theme!custom.css')!==false,'layout reset after code renames keeps renamed dependencies');

    $root=assetFixture($legacy,'paged-history');oldAssetFiles($root);
    u5CodeMigrationQuery('CREATE TABLE paging_data (id INT PRIMARY KEY, body MEDIUMTEXT) ENGINE=MyISAM');
    for ($i=0;$i<251;$i++) u5CodeMigrationQuery('INSERT INTO paging_data VALUES ('.$i.','.u5CodeMigrationValue(str_repeat('unrelated text ',100).' r/jsmobilespecific.css?id='.$i).')');
    u5CodeAssetsMigrate($root);
    $count=u5CodeMigrationRows("SELECT COUNT(*) AS n FROM paging_data WHERE body LIKE '%r/mobile.js?id=%'");
    sameAsset((int)$count[0]['n'],251,'all pages of database rows migrated');
    assertAssetInstallation($root,'paged data');

    $root=assetFixture($legacy,'innodb');oldAssetFiles($root);
    foreach (array('resources','resources_log','inserts') as $table) u5CodeMigrationQuery('ALTER TABLE `'.$table.'` ENGINE=InnoDB');
    u5CodeAssetsMigrate($root);assertAssetInstallation($root,'InnoDB');
    foreach (array('resources','resources_log','inserts') as $table) {
        $status=u5CodeMigrationRows("SHOW TABLE STATUS LIKE '".$table."'");sameAsset($status[0]['Engine'],'InnoDB','engine retained: '.$table);
    }
    u5CodeAuditDatabase($legacy);
    u5MainDiffDatabase($legacy);
    if (getenv('U5CMS_AUDIT_STRESS')) u5CodeAuditStress($legacy);
    echo "Core and database checks: $checks passed.\n";
} finally {
    foreach ($databases as $database) mysql_query('DROP DATABASE `'.$database.'`');
    echo "Fixtures: $testRoot\n";
}
