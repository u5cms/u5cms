<?php
/* Reproductions found by the second 12.11 audit. Run through codeassets-regression.php. */
function u5CodeAuditCore() {
    $cases=array(
        array('@import url("./csslayout.css");','@import url("./layout.css");',array()),
        array('@import url(../csslayout.css);','@import url(../csslayout.css);',array()),
        array('<script src="r/one%21two.css?x=1#f"></script>','<script src="r/one%21two.js?x=1#f"></script>',array('one!two'=>'one!two!js')),
        array('https://other.invalid/cms/r/one%21two.css?x=1#f','https://other.invalid/cms/r/one%21two.js?x=1#f',array('one!two'=>'one!two!js')),
        array('?l=de&amp;c=base%21css#x','?l=de&amp;c=theme%21css#x',array('base!css'=>'theme!css')),
        array('?c=CSSBASE','?c=base!css',array()),
        array("getScript('https://yuba.ch/r/mobilebase.js?x=1#f')","getScript('r/mobilebase.js?x=1#f')",array()),
        array("getScript('//WWW.YUBA.CH/cms/r/jsmobilegeneral.css')","getScript('r/mobilebase.js')",array()),
        array('https://yuba.ch/wa','https://yuba.ch/wa',array()),
        array('<script src="f.php?f=r/jsmobilespecific.css&t=3"></script>','<script src="r/mobile.js?t=3"></script>',array()),
        array('<script src="f.php?f=r%2Fjsmobilespecific.css%3Fx%3D1&amp;t=3"></script>','<script src="r/mobile.js?x=1&amp;t=3"></script>',array()),
        array('r/cssbase.css.backup','r/cssbase.css.backup',array()),
        array('<div id=cssbase></div><script>document.getElementById("cssbase");</script>','<div id=cssbase></div><script>document.getElementById("cssbase");</script>',array()),
        array('<button onclick="items[cssbase]()">[cssbase]</button>','<button onclick="items[cssbase]()">[base!css]</button>',array()),
        array('<script>[$$$:custom]; members[custom]</script>','<script>[$$$:custom!js]; members[custom]</script>',array('custom'=>'custom!js')),
    );
    foreach ($cases as $case) sameAsset(u5CodeAssetReferences($case[0],$case[2]),$case[1],'audit URI/context: '.$case[0]);
    foreach ($cases as $case) {
        $value=json_encode(array('value'=>$case[0]));
        sameAsset(json_decode(u5CodeAssetReferences($value,$case[2]),true)['value'],$case[1],'audit escaped URI/context');
    }
    sameAsset(u5CodeAssetReferences('const id="cssbase"; const item=items[cssbase]; fetch("?c=cssbase");',array(),0,'js'),'const id="cssbase"; const item=items[cssbase]; fetch("?c=base!css");','JS identifiers remain intact while actual resource URLs change');
    sameAsset(u5CodeAssetReferences('[cssbase] { content: "cssbase"; background:url(./csslayout.css); }',array(),0,'css'),'[cssbase] { content: "cssbase"; background:url(./layout.css); }','CSS identifiers remain intact');
    sameAsset(u5CodeAssetReferences('<a href="?c=shadow">[shadow]</a><link href="r/shadow.css">',array('shadow'=>'shadow!css'),0,'html',array()),'<a href="?c=shadow">[shadow]</a><link href="r/shadow.css">','active non-C namespace can remain independent');
    foreach (array('export function hello() {}','class Hello {}','console.log("hello");','import "./other.js";') as $source) sameAsset(u5CodeAssetGuess('unknown',$source)[0],'unknown!js','modern JS is identified');
    sameAsset(u5CodeAssetGuess('unknown','<section>Hello</section>')[0],'unknown!html','HTML fragment identified');
    sameAsset(u5CodeAssetGuess('unknown',"\xEF\xBB\xBFclass Hello {}")[0],'unknown!js','source BOM does not hide its type');
    $map=array();$body='';
    for ($i=0;$i<600;$i++) { $map['asset'.$i]='asset'.$i.'!js';$body.='<script src="r/asset'.$i.'.css"></script>'; }
    $rewritten=u5CodeAssetReferences($body,$map);
    sameAsset(substr_count($rewritten,'<script'),600,'many-reference rewrite never clears the field');
    checkAsset(strpos($rewritten,'.css')===false && strpos($rewritten,'r/asset599.js')!==false,'all 600 reference paths migrate');
    $name=str_repeat('a',220);
    $plan=u5CodeAssetPlanNames(array(array('name'=>$name.'!JS','typ'=>'c','deleted'=>0,'content_1'=>''),array('name'=>$name.'!js','typ'=>'c','deleted'=>0,'content_1'=>'')));
    sameAsset(count(array_unique(array_map('u5CodeAssetFilename',$plan['map']))),2,'maximum-length name collisions stay distinct and valid');
    sameAsset(u5CodeAssetReferences('{{{navigation}}} [$$$:navigation]',array('navigation'=>'navigation!css')),'{{{navigation}}} [$$$:navigation!css]','built-in template slots stay independent of similarly named C objects');
    $config='{"selector":"[cssbase]","id":"cssbase","binding":"{{{cssbase}}}","html":"<p>[$$$:cssbase]</p>","url":"?c=cssbase"}';
    $expected='{"selector":"[cssbase]","id":"cssbase","binding":"{{{cssbase}}}","html":"<p>[$$$:base!css]</p>","url":"?c=base!css"}';
    sameAsset(u5CodeAssetReferences($config),$expected,'JSON selectors and bindings remain literal while actual URLs and embedded CMS HTML migrate');
    $signed="getScript('https://cdn.example.invalid/a.js?Signature=abc')";
    sameAsset(u5CodeAssetOutput($signed,7),$signed,'external signed URLs are not altered by export versioning');
    $json='{"example":"href=\'local.css\'","literal":"&amp;"}';
    $rows=array(
        array('name'=>'icon!svg','typ'=>'c','deleted'=>0,'content_1'=>"<?xml version=\"1.0\" encoding=\"Windows-1252\"?><svg><text>\xDCber \x80 &amp;</text></svg>"),
        array('name'=>'data!json','typ'=>'c','deleted'=>0,'content_1'=>$json),
        array('name'=>'wide!js','typ'=>'c','deleted'=>0,'content_1'=>'?','u5_code_utf8'=>"window.wide='\xF0\x9F\x8C\xB7 \xD0\x96';"),
    );
    $files=u5CodeAssetRowsToFiles($rows,7);
    sameAsset($files['icon.svg'],"<?xml version=\"1.0\" encoding=\"UTF-8\"?><svg><text>\xC3\x9Cber \xE2\x82\xAC &amp;</text></svg>",'SVG and XML declaration match their UTF-8 bytes');
    sameAsset($files['data.json'],$json,'JSON data stays literal without export cache edits');
    sameAsset($files['wide.js'],"\xEF\xBB\xBFwindow.wide='\xF0\x9F\x8C\xB7 \xD0\x96';",'raw UTF-8 source keeps non-Latin1 characters');
}

function u5CodeAuditDatabase($legacy) {
    global $faultPhase;
    $root=assetFixture($legacy,'audit-physical-collisions');oldAssetFiles($root);
    file_put_contents($root.'/r/mobile.js','independent public script');mkdir($root.'/r/base.css');
    u5CodeAssetsMigrate($root);$state=include $root.'/fileversions/codeassets-12.11.0.done.php';
    checkAsset($state['map']['jsmobilespecific']!=='mobile!js','an unmanaged script reserves its filename');
    checkAsset($state['map']['cssbase']!=='base!css','a media directory reserves its filename');
    sameAsset(file_get_contents($root.'/r/mobile.js'),'independent public script','unmanaged script bytes preserved');
    checkAsset(is_dir($root.'/r/base.css'),'unmanaged media directory preserved');
    checkAsset(is_file($root.'/r/'.u5CodeAssetFilename($state['map']['jsmobilespecific'])),'colliding code exported under its mapped name');
    checkAsset(strpos(file_get_contents(u5CodeTemplatePath($root)),u5CodeAssetFilename($state['map']['cssbase']))!==false,'template follows the physical collision mapping');
    runAssetLayoutReset($root);
    checkAsset(strpos(file_get_contents(u5CodeTemplatePath($root)),u5CodeAssetFilename($state['map']['cssbase']))!==false,'reset preserves physical collision mapping');

    $root=assetFixture($legacy,'audit-historical-namespace');oldAssetFiles($root);
    u5CodeMigrationQuery("INSERT INTO resources(name,typ,deleted,content_1) VALUES ('shadow','p',0,'<a href=\"?c=shadow\">[shadow]</a><link href=\"r/shadow.css\">'),('retired','c',1,'old deleted DB code')");
    u5CodeMigrationQuery("INSERT INTO resources_log(name,typ,deleted,content_1,lastmut) VALUES ('shadow','c',0,'var shadow=1;',1),('historyonly','c',0,'var historic=1;',1)");
    file_put_contents($root.'/r/shadow.css','var retainedShadow=1;');file_put_contents($root.'/r/retired.css','var actualDeletedExport=1;');file_put_contents($root.'/r/historyonly.css','var retainedHistory=1;');
    u5CodeAssetsMigrate($root);$state=include $root.'/fileversions/codeassets-12.11.0.done.php';
    $shadow=u5CodeMigrationRows("SELECT content_1 FROM resources WHERE name='shadow' AND typ='p'")[0]['content_1'];
    checkAsset(strpos($shadow,'?c=shadow')!==false && strpos($shadow,'[shadow]')!==false,'P object links do not bind to historical C names');
    checkAsset(strpos($shadow,'r/shadow.js')!==false,'historical C file URL follows its own type');
    sameAsset(u5CodeMigrationRows("SELECT name FROM resources_log WHERE content_1='var shadow=1;'")[0]['name'],'shadow!js','historical C name migrates despite P reuse');
    checkAsset(is_file($root.'/r/shadow.js') && is_file($root.'/r/historyonly.js'),'retained historical exports stay available');
    sameAsset((int)u5CodeMigrationRows("SELECT COUNT(*) n FROM resources WHERE typ='c' AND deleted!=1 AND name LIKE 'retired%'")[0]['n'],0,'deleted C is not reactivated');
    checkAsset(strpos(file_get_contents($root.'/r/'.u5CodeAssetFilename($state['map']['retired'])),'actualDeletedExport')!==false,'retained deleted export uses its actual bytes');

    $root=assetFixture($legacy,'audit-completed-release-repair');oldAssetFiles($root);u5CodeAssetsMigrate($root);
    u5CodeAssetsRename($root,'base!css','site-theme!css');
    unlink($root.'/fileversions/codeassets-12.11.1.done.php');
    u5CodeMigrationQuery("UPDATE resources SET content_1='@import url(\"./cssbase.css\");' WHERE name='style!css'");
    u5CodeMigrationQuery("UPDATE resources SET content_1='<script src=\"r/one%21two.css\"></script> <link href=\"r/cssbase.css\">' WHERE name='home'");
    u5CodeMigrationQuery("INSERT INTO resources(name,typ,deleted,content_1) VALUES ('one!two!js','c',0,'var two=1;')");
    $marker=$root.'/fileversions/codeassets-12.11.0.done.php';$state=include $marker;$state['map']['one!two']='one!two!js';
    u5CodeAssetAtomicWrite($marker,"<?php return ".var_export($state,true).";");
    file_put_contents($root.'/r/style!css.tpl','/* custom baseline */ @import url("./cssbase.css");');
    u5CodeAssetsMigrate($root);
    sameAsset(u5CodeMigrationRows("SELECT content_1 FROM resources WHERE name='style!css'")[0]['content_1'],'@import url("./site-theme.css");','already completed release receives the reference repair using previous roles');
    sameAsset(file_get_contents($root.'/r/style!css.tpl'),'/* custom baseline */ @import url("./site-theme.css");','patch repair keeps custom typed reset source');
    checkAsset(is_file($root.'/fileversions/codeassets-12.11.1.done.php'),'patch completion marker written');
    $before=assetSnapshot();u5CodeAssetsMigrate($root);sameAsset(assetSnapshot(),$before,'patch retry leaves the database byte-identical');

    $root=assetFixture($legacy,'audit-auto-generated-recovery');oldAssetFiles($root);
    u5CodeMigrationQuery('CREATE TABLE aaa_audit_auto (id INT PRIMARY KEY, body TEXT, generated_ref VARCHAR(255) AS (SUBSTRING(body,1,80)) STORED, touched TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB');
    u5CodeMigrationQuery("INSERT INTO aaa_audit_auto(id,body,touched) VALUES (1,'r/cssbase.css','2000-01-01 00:00:00')");
    $faultPhase='row:0';expectAssetFailure(function() use($root){u5CodeAssetsMigrate($root);},'generated/automatic row interrupted after first write');u5CodeAssetsMigrate($root);
    $row=u5CodeMigrationRows('SELECT body,generated_ref,touched FROM aaa_audit_auto')[0];
    sameAsset($row['body'],'r/base.css','automatic timestamp does not prevent exact resume');sameAsset($row['generated_ref'],'r/base.css','generated column recalculates without direct assignment');
    checkAsset($row['touched']!=='2000-01-01 00:00:00','automatic timestamp remains managed by the database');

    $root=assetFixture($legacy,'audit-native-json');oldAssetFiles($root);
    u5CodeMigrationQuery("CREATE TABLE aaa_native_json(id INT PRIMARY KEY,body JSON NOT NULL,derived VARCHAR(255) AS (JSON_UNQUOTE(JSON_EXTRACT(body,'$.url'))) STORED) ENGINE=InnoDB");
    $json='{"url":"r/cssbase.css","selector":"[cssbase]","r/cssbase.css":"URL key","longkeyname":"literal"}';
    u5CodeMigrationQuery('INSERT INTO aaa_native_json(id,body) VALUES (1,_utf8mb4 '.u5CodeMigrationValue($json).')');
    $faultPhase='row:0';expectAssetFailure(function() use($root){u5CodeAssetsMigrate($root);},'native JSON update interrupted after its first row');
    u5CodeAssetsMigrate($root);$jsonRow=u5CodeMigrationRows('SELECT body,derived FROM aaa_native_json')[0];$decoded=json_decode($jsonRow['body'],true);
    sameAsset($decoded['url'],'r/base.css','native JSON string references migrate');sameAsset($decoded['selector'],'[cssbase]','native JSON selectors stay literal');
    sameAsset($decoded['r/base.css'],'URL key','native JSON URL keys migrate without invalidating checkpoint recovery');sameAsset($jsonRow['derived'],'r/base.css','generated JSON dependency follows its source');

    foreach (array('short'=>"VARCHAR(12)",'enum'=>"ENUM('r/jsmobilespecific.css')") as $label=>$type) {
        $root=assetFixture($legacy,'audit-constrained-'.$label);oldAssetFiles($root);
        u5CodeMigrationQuery('CREATE TABLE aaa_constrained (id INT PRIMARY KEY, body '.$type.') ENGINE=MyISAM');
        $value=$label==='short' ? '?c=cssbase' : 'r/jsmobilespecific.css';
        u5CodeMigrationQuery('INSERT INTO aaa_constrained VALUES (1,'.u5CodeMigrationValue($value).')');
        // A long, collision-preserving target makes the short reference grow.
        if ($label==='short') u5CodeMigrationQuery("INSERT INTO resources(name,typ,deleted,content_1) VALUES ('base!css','p',0,'independent page')");
        $before=assetSnapshot();expectAssetFailure(function() use($root){u5CodeAssetsMigrate($root);},'constrained '.$label.' rejects truncation before DML');
        sameAsset(assetSnapshot(),$before,'constrained '.$label.' failure keeps every source byte');
        checkAsset(!is_file($root.'/fileversions/codeassets-12.11.0.pending.php'),'failed preflight does not publish a partial row plan');
    }

    $root=assetFixture($legacy,'audit-revision-capacity');oldAssetFiles($root);
    u5CodeMigrationQuery("UPDATE resources SET lastmut=2147483647 WHERE name='cssbase'");$before=assetSnapshot();
    expectAssetFailure(function() use($root){u5CodeAssetsMigrate($root);},'exhausted integer revision fails before any source DML');
    sameAsset(assetSnapshot(),$before,'revision overflow cannot silently clamp or corrupt the migration');

    $root=assetFixture($legacy,'audit-wide-code-history');oldAssetFiles($root);
    u5CodeMigrationQuery('ALTER TABLE resources MODIFY content_1 MEDIUMTEXT CHARACTER SET utf8mb4, MODIFY title_1 VARCHAR(1000) COMMENT \'custom title comment\'');
    u5CodeMigrationQuery("ALTER TABLE resources_log MODIFY title_1 VARCHAR(255) DEFAULT 'custom default' COMMENT 'history comment'");
    $source="window.message='\xE4\xB8\xAD\xE6\x96\x87 \xF0\x9F\x8C\xB7 &amp;'; // r/cssbase.css";
    u5CodeMigrationQuery("INSERT INTO resources(name,typ,deleted,content_1,title_1,lastmut) VALUES ('wide','c',0,_utf8mb4 ".u5CodeMigrationValue($source).',REPEAT(\'T\',850),10)');
    u5CodeMigrationQuery('DELETE FROM resources_log');u5CodeAssetsMigrate($root);
    $expected=str_replace('r/cssbase.css','r/base.css',$source);
    sameAsset(substr(file_get_contents($root.'/r/wide.js'),3),$expected,'UTF-8 source survives planning and final export');
    $history=u5CodeMigrationRows("SELECT CAST(content_1 AS BINARY) content_1,LENGTH(title_1) title_length FROM resources_log WHERE name='wide!js'")[0];
    sameAsset($history['content_1'],$expected,'new history preserves non-Latin1 source');sameAsset((int)$history['title_length'],850,'history initialization does not truncate long titles');
    $column=u5CodeMigrationColumns('resources_log')['title_1'];sameAsset($column['Comment'],'history comment','history widening keeps column comments');sameAsset($column['Default'],'custom default','history widening keeps column defaults');
    $column=u5CodeMigrationColumns('resources_log')['content_1'];sameAsset(u5CodeMigrationCharset($column),'utf8mb4','history charset widens to retain source characters');
    checkAsset((int)u5CodeMigrationRows("SELECT lastmut FROM resources WHERE name='wide!js'")[0]['lastmut']>10,'migration advances editor revision');
    expectAssetFailure(function(){u5CodeMigrationConvertExact("\xF0\x9F\x8C\xB7",'utf8mb4','utf8mb3');},'unrepresentable emoji is rejected before a code write');
    sameAsset(u5CodeMigrationConvertExact("\xE4\xB8\xAD\xE6\x96\x87",'utf8mb4','utf8mb3'),"\xE4\xB8\xAD\xE6\x96\x87",'representable Unicode survives a UTF8MB3 column');

    $root=assetFixture($legacy,'audit-casefold-default');oldAssetFiles($root);
    u5CodeMigrationQuery("UPDATE resources SET name='HTMLTEMPLATE' WHERE name='htmltemplate'");u5CodeAssetsMigrate($root);
    sameAsset(u5CodeTemplateName($root),'htmltemplate!html','case-insensitive standard role remains the active template');
    sameAsset((int)u5CodeMigrationRows("SELECT COUNT(*) n FROM resources WHERE name='htmltemplate!html'")[0]['n'],1,'case-insensitive default never imports a duplicate C object');

    $root=assetFixture($legacy,'audit-three-language-schema');oldAssetFiles($root);
    $legacyFields=array('resources'=>array('content','title','desc','key','search'),'resources_log'=>array('content','title','desc','key'),
        'languages'=>array('term','andhit','andhits','orhit','orhits','nohit','recherche','notpub','picsfound','morepics','czoom'),
        'loginglobals'=>array('logintitle','loginintro','username','password','loginbutton','loginoutro','logout','wait'));
    foreach ($legacyFields as $table=>$prefixes) foreach ($prefixes as $prefix) {
        foreach (array(1=>'d',2=>'e',3=>'f') as $number=>$suffix) u5CodeMigrationQuery('ALTER TABLE '.u5CodeMigrationIdentifier($table).' RENAME COLUMN '.u5CodeMigrationIdentifier($prefix.'_'.$number).' TO '.u5CodeMigrationIdentifier($prefix.'_'.$suffix));
        foreach (array(4,5) as $number) u5CodeMigrationQuery('ALTER TABLE '.u5CodeMigrationIdentifier($table).' DROP COLUMN '.u5CodeMigrationIdentifier($prefix.'_'.$number));
    }
    foreach (array('lan4na','lan5na','lan4name','lan5name') as $field) u5CodeMigrationQuery('ALTER TABLE languages DROP COLUMN '.u5CodeMigrationIdentifier($field));
    foreach (array(1=>'d',2=>'e',3=>'f') as $number=>$suffix) u5CodeMigrationQuery('ALTER TABLE titlefixum RENAME COLUMN '.u5CodeMigrationIdentifier($number).' TO '.u5CodeMigrationIdentifier($suffix));
    u5CodeMigrationQuery('ALTER TABLE titlefixum DROP COLUMN `4`,DROP COLUMN `5`');
    u5CodeMigrationQuery('ALTER TABLE loginattempts DROP COLUMN mailed');u5CodeMigrationQuery('ALTER TABLE mailing DROP COLUMN mailtested');u5CodeMigrationQuery('DROP TABLE mailingcron');
    $response=runBackendAssetUpdate($root);
    checkAsset(!is_file($root.'/r/runonce.php'),'three-language schema completes automatically: '.substr(strip_tags($response),-800));
    checkAsset(!u5CodeMigrationNeedsLegacySchema(),'all three-language names and missing tables reach the current schema');
    assertAssetInstallation($root,'three-language schema');

    $root=assetFixture($legacy,'audit-damaged-journal');oldAssetFiles($root);$faultPhase='planned';
    expectAssetFailure(function() use($root){u5CodeAssetsMigrate($root);},'stop with durable chunk journal');$before=assetSnapshot();
    $plan=u5CodeMigrationReadJournal($root.'/fileversions/codeassets-12.11.0.pending.php');$chunk=$root.'/'.$plan['updates']['chunks'][0]['path'];$bytes=file_get_contents($chunk);file_put_contents($chunk,$bytes.'damage');
    expectAssetFailure(function() use($root){u5CodeAssetsMigrate($root);},'damaged row backup is rejected before publication');sameAsset(assetSnapshot(),$before,'damaged journal never changes database rows');
    file_put_contents($chunk,$bytes);u5CodeAssetsMigrate($root);checkAsset(is_file($root.'/fileversions/codeassets-12.11.1.done.php'),'intact chunk journal can resume');

    $root=assetFixture($legacy,'audit-old-list-checkpoint');oldAssetFiles($root);$faultPhase='planned';
    expectAssetFailure(function() use($root){u5CodeAssetsMigrate($root);},'stop before simulating an older row-list checkpoint');
    $pending=$root.'/fileversions/codeassets-12.11.0.pending.php';$plan=u5CodeMigrationReadJournal($pending);$list=array();
    u5CodeMigrationEachChange($root,$plan['updates'],function($change) use(&$list){$list[]=$change;});
    $plan['updates']=$list;unset($plan['audit']);file_put_contents($pending,"<?php exit; ?>\n".serialize($plan));
    u5CodeAssetsMigrate($root);
    checkAsset(is_file($root.'/fileversions/codeassets-12.11.1.done.php'),'old row-list checkpoint finishes and receives the patch repair');
    checkAsset(!is_file($pending) && !is_file($root.'/fileversions/codeassets-12.11.1.pending.php'),'both generations of checkpoints finalize');
    checkAsset(is_file(u5CodeTemplatePath($root)),'old row-list resume leaves the active template available');

}

function u5CodeAuditStress($legacy) {
    $root=assetFixture($legacy,'audit-bounded-memory');oldAssetFiles($root);
    u5CodeMigrationQuery('CREATE TABLE audit_digits (n INT PRIMARY KEY) ENGINE=MyISAM');
    u5CodeMigrationQuery('INSERT INTO audit_digits VALUES (0),(1),(2),(3),(4),(5),(6),(7),(8),(9)');
    u5CodeMigrationQuery('CREATE TABLE audit_bulk (id INT PRIMARY KEY, body MEDIUMTEXT, generated_ref VARCHAR(255) AS (SUBSTRING(body,1,80)) STORED, touched TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=MyISAM');
    u5CodeMigrationQuery("INSERT INTO audit_bulk (id,body,touched) SELECT a.n+10*b.n+100*c.n+1000*d.n,CONCAT('r/cssbase.css ',REPEAT('x',16000)),'2000-01-01 00:00:00' FROM audit_digits a CROSS JOIN audit_digits b CROSS JOIN audit_digits c CROSS JOIN audit_digits d WHERE a.n+10*b.n+100*c.n+1000*d.n<2500");
    u5CodeMigrationQuery("INSERT INTO resources_log(name,typ,deleted,lastmut,content_1) SELECT 'audit-history','p',0,a.n+10*b.n+100*c.n+1000*d.n,CONCAT('r/cssbase.css ',REPEAT('h',2000)) FROM audit_digits a CROSS JOIN audit_digits b CROSS JOIN audit_digits c CROSS JOIN audit_digits d WHERE a.n+10*b.n+100*c.n+1000*d.n<5000");
    u5CodeMigrationQuery('CREATE TABLE audit_blob (id INT PRIMARY KEY, body TEXT, payload LONGBLOB) ENGINE=MyISAM');
    u5CodeMigrationQuery("INSERT INTO audit_blob VALUES (1,'r/cssbase.css',REPEAT('opaque',700000))");
    $database=u5CodeMigrationRows('SELECT DATABASE() AS name')[0]['name'];
    $script='require '.var_export(dirname(__DIR__).'/mysql.php',true).';require '.var_export(dirname(__DIR__).'/u5admin/codeassets-migrate.inc.php',true).';ini_set("mysqli.default_port",getenv("U5CMS_TEST_PORT"));if(!mysql_connect("127.0.0.1",getenv("U5CMS_TEST_USER")?:"root",getenv("U5CMS_TEST_PASSWORD")?:""))exit(2);mysql_select_db('.var_export($database,true).');mysql_query("SET NAMES latin1");u5CodeAssetsMigrate('.var_export($root,true).');echo json_encode(array("peak"=>memory_get_peak_usage(true)));';
    $arguments=json_decode(getenv('U5_TEST_PHP_ARGS') ?: '[]',true);
    $command=array_merge(array(PHP_BINARY),$arguments,array('-d','memory_limit=64M','-r',$script));
    $log=tempnam(sys_get_temp_dir(),'u5-audit-memory-');
    $process=proc_open($command,array(1=>array('file',$log,'w'),2=>array('redirect',1)),$pipes,dirname(__DIR__));
    $status=proc_close($process);$output=file_get_contents($log);unlink($log);
    checkAsset($status===0,'bounded-memory worker completes: '.substr($output,-2500));
    $result=json_decode($output,true);
    checkAsset(is_array($result) && $result['peak']<64*1024*1024,'migration memory stays below 64 MiB');
    echo 'Stress worker peak: '.(int)$result['peak']." bytes (64 MiB limit; 2500 large rows, 5000 history rows, 4.2 MB BLOB).\n";
    sameAsset((int)u5CodeMigrationRows("SELECT COUNT(*) AS n FROM audit_bulk WHERE body LIKE 'r/base.css %' AND generated_ref LIKE 'r/base.css %'")[0]['n'],2500,'all large rows and generated references migrate');
    sameAsset((int)u5CodeMigrationRows("SELECT COUNT(*) n FROM resources_log WHERE name='audit-history' AND content_1 LIKE 'r/base.css %'")[0]['n'],5000,'all history versions migrate with indexed row identity');
    sameAsset((int)u5CodeMigrationRows('SELECT LENGTH(payload) AS n FROM audit_blob')[0]['n'],4200000,'large opaque BLOB is not rewritten or truncated');
    sameAsset(u5CodeMigrationRows('SELECT body FROM audit_blob')[0]['body'],'r/base.css','text beside an opaque BLOB migrates');
}
