<?php
// Integration helper for provision-regression.py, restricted to its disposable DB.
if (PHP_SAPI!=='cli') exit(1);
error_reporting(E_ALL ^ E_DEPRECATED);$_SERVER['HTTP_HOST']='localhost';$_SERVER['REQUEST_URI']='/';
$root=realpath($argv[1]);chdir($root);
require_once $root.'/config.php';
if ($host!=='127.0.0.1' || !preg_match('/^u5rename_web_[a-f0-9]+$/D',$db)) exit(2);
require_once $root.'/mysql.php';
require_once $root.'/myfunctions.inc.php';
require_once $root.'/provision-changes.inc.php';
ini_set('mysqli.default_port',(string)getenv('U5CMS_TEST_PORT'));
mysql_connect($host,$username,$password);mysql_select_db($db);u5ProvisionQuery('SET NAMES latin1');
function verify($value,$label) {
    static $checks=0;
    if (!$value) throw new RuntimeException('Failed: '.$label);
    echo (++$checks).' '.$label."\n";
}
function rowData($name,$type,$body='',$acl='',$hidden=0) {
    return array('name'=>$name,'typ'=>$type,'content_1'=>$body,'hidden'=>(string)$hidden,'logins'=>$acl,'deleted'=>'0');
}
function putRow($row) {
    $set=array();
    foreach ($row as $key=>$value) $set[]=$key.'='.u5ProvisionQuote($value);
    u5ProvisionQuery('INSERT INTO resources SET '.implode(',',$set).' ON DUPLICATE KEY UPDATE '.implode(',',$set));
}
function pwFile($name) {global $root;return $root.'/r/'.$name.'/.htpasswd';}
function hasUser($name,$user) {return strpos((string)@file_get_contents(pwFile($name)),$user.':')!==false;}
function finishWork() {global $root;verify(u5ProvisionFinish($root),'work completed');}
function editRow($row) {global $root;u5ProvisionPrepare($root,$row['name'],$row);putRow($row);finishWork();}
function usesNames($name,$sources=null) {return array_column(u5ProvisionUses($name,$sources),'name');}
$state=$root.'/fileversions';
if (($argv[2] ?? '')==='benchmark') {
    $count=75;
    for ($i=0;$i<$count;$i++) {
        putRow(rowData('benchasset'.$i,'f'));
        putRow(rowData('benchpage'.$i,'p',str_repeat('ordinary CMS page content ',50).'[benchasset'.$i.']','?bench:group;'));
    }
    $expected=array();$start=microtime(true);
    for ($i=0;$i<$count;$i++) $expected[]=usesNames('benchasset'.$i);
    $baseline=microtime(true)-$start;$start=microtime(true);
    $map=u5ProvisionFetchSources();$lookup=array();$ambiguous=array();
    foreach ($map as $source=>$row) {
        if ($row['fallback']) $ambiguous[]=$source;
        foreach ($row['names'] as $target) $lookup[$target][]=$source;
    }
    for ($i=0;$i<$count;$i++) {
        $actual=usesNames('benchasset'.$i,array_unique(array_merge($lookup['benchasset'.$i] ?? array(),$ambiguous)));
        if ($actual!==$expected[$i]) throw new RuntimeException('Benchmark reference mismatch.');
    }
    printf("75 media, 75 new pages, identical SQL results. Per-medium full scans %.3f s, catalogue and narrowed SQL %.3f s.\n",$baseline,microtime(true)-$start);
    exit;
}
if (($argv[2] ?? '')==='retry') {verify(u5ProvisionRun($root),'independent recovery');exit;}
if (($argv[2] ?? '')==='abandon') {
    u5ProvisionPrepare($root,'pa',rowData('pa','p','[photo]','?restored:qq;'));
    putRow(rowData('pa','p','[photo]','?restored:qq;'));
    echo "READY\n";fflush(STDOUT);sleep(60);exit;
}
foreach (array('photo','manual','film','vfilm','archivepdf','futurefile') as $name) {
    putRow(rowData($name,$name==='film' ? 'v' : 'f'));
    if ($name!=='futurefile') mkdir($root.'/r/'.$name);
}
putRow(rowData('pa','p','[photo] [film] [futurefile]','?alice:aaa;'));
putRow(rowData('pb','p','[Download:photo?l=fr]','?bob:bbb;'));
putRow(rowData('pc','p','[[manual]]'));
putRow(rowData('archivedsource','c','[archivepdf]','?archive:ccc;')+array('key_5'=>'r/manual/item'));
u5ProvisionQuery("UPDATE resources SET deleted=2 WHERE name='archivedsource'");
verify(u5ProvisionRun($root,true),'bootstrap without search work');
verify(hasUser('photo','alice') && hasUser('photo','bob'),'private groups combined');
verify(hasUser('film','alice') && hasUser('vfilm','alice'),'video and poster protected');
verify(hasUser('archivepdf','archive') && hasUser('manual','archive'),'archived source and fifth language metadata');
verify(!in_array('pc',usesNames('manual'),true),'escaped double brackets');
verify(!u5ProvisionPending($root),'no automatic rescheduling');
$cleanStamp=(string)(time()-10);file_put_contents($state.'/lastsave.txt',$cleanStamp);
verify(u5ProvisionRun($root,true) && file_get_contents($state.'/lastsave.txt')===$cleanStamp,'clean repair leaves the search marker intact');
u5ProvisionUpload($root,'futurefile');
verify(is_file($root.'/r/futurefile/.u5-provision.php'),'first upload guarded before publication');
file_put_contents($root.'/r/futurefile/futurefile_de.pdf','fixture');
finishWork();
verify(hasUser('futurefile','alice'),'reference predates upload');
u5ProvisionUpload($root,'futurefile');
verify(!u5ProvisionPending($root),'replacing a language variant needs no pass');
$free=rowData('freepage','p','[photo]');
editRow($free);
verify(!is_file(pwFile('photo')),'mixed public and protected uses are public');
$force=rowData('forcepage','p','[photo]','?keeper:ddd;',2);
editRow($force);
verify(hasUser('photo','keeper') && hasUser('photo','bob'),'forcer overrides free use');
editRow(rowData('forcepage','p','','?keeper:ddd;',2));
verify(!is_file(pwFile('photo')),'removing the forcer restores public access');
u5ProvisionPrepare($root,'freepage');
verify(is_file($root.'/r/photo/.u5-provision.php') && file_get_contents(pwFile('photo'))==='','last public reference guarded before delete');
u5ProvisionQuery("UPDATE resources SET deleted=1 WHERE name='freepage'");
finishWork();
verify(hasUser('photo','alice') && hasUser('photo','bob'),'deleting last free source restores both groups');
editRow(rowData('pa','p','[film] [futurefile]','?alice:aaa;'));
verify(!hasUser('photo','alice') && hasUser('photo','bob'),'old target of edited link refreshed');
editRow(rowData('pb','p','','?bob:bbb;'));
verify(!hasUser('photo','bob') && is_file(pwFile('photo')),'private orphan drops stale group');
$plain=rowData('pb','p','merely a typo','?bob:bbb;');
u5ProvisionPrepare($root,'pb',$plain);
verify(!u5ProvisionPending($root),'text-only edit schedules no provisioner');
putRow($plain);
putRow(rowData('!members','p','[photo]'));
u5ProvisionIdentityChange($root,false);
u5ProvisionQuery("UPDATE intranetmembers SET members='reader@example.org,'");
finishWork();
verify(hasUser('photo','reader@example.org'),'member grant published');
u5ProvisionIdentityChange($root,false);
u5ProvisionQuery("UPDATE intranetmembers SET members=''");
finishWork();
verify(!hasUser('photo','reader@example.org'),'member revocation published');
u5ProvisionIdentityChange($root,true);
u5ProvisionQuery("UPDATE accounts SET pw='fixture-changed' WHERE email='auditadmin'");
finishWork();
verify(strpos(file_get_contents(pwFile('film')),'auditadmin:fixture-changed')!==false,'administrator password rotation published');
foreach (array('chainparent','chainchild','circleone','circletwo') as $item) mkdir($root.'/r/'.$item);
putRow(rowData('chainchild','f'));
putRow(rowData('chainparent','f','[chainchild]'));
putRow(rowData('circleone','f','[circletwo]'));
putRow(rowData('circletwo','f','[circleone]'));
putRow(rowData('chainentry','p','[chainparent] [circleone]','?chain:pwd;'));
verify(u5ProvisionRun($root,true),'rebuild nested and cyclic resources');
verify(hasUser('chainchild','chain') && hasUser('circletwo','chain'),'nested and cyclic resources inherit external group');
editRow(rowData('chainentry','p','[chainparent] [circleone]','?next:pwd;'));
verify(hasUser('chainchild','next') && !hasUser('circleone','chain'),'revocation propagates through dependencies');
editRow(rowData('posterguard','p','[vfilm]','?poster:xyz;',2));
verify(!hasUser('vfilm','alice') && !hasUser('vfilm','poster') && hasUser('film','alice'),'independent poster restriction intersects video rights');
// An interrupted Rename prevents publication only on the named targets.
$checkpoint='resource-rename-fixture.pending.php';
file_put_contents($state.'/'.$checkpoint,'pending');
u5ProvisionTicket($root,array('pb'),array('photo'),false,false,false,$checkpoint);
u5ProvisionGuard($root,'photo');
verify(!u5ProvisionFinish($root) && is_file($root.'/r/photo/.u5-provision.php'),'interrupted Rename retains file guard');
$ordinary=rowData('unrelated','p','[manual]','?ordinary:abc;');
u5ProvisionPrepare($root,'unrelated',$ordinary);putRow($ordinary);u5ProvisionFinish($root);
verify(hasUser('manual','ordinary') && is_file($root.'/r/photo/.u5-provision.php'),'unrelated change published with older Rename checkpoint');
unlink($state.'/'.$checkpoint);finishWork();
mkdir($root.'/r/retired');
file_put_contents(pwFile('retired'),'retired-admin:fixture');
u5ProvisionIdentityChange($root,true);finishWork();
verify(!hasUser('retired','retired-admin') && hasUser('retired','auditadmin'),'retained resource directory drops obsolete account');
verify(!is_file($state.'/.u5-provision.php') && strpos(file_get_contents($state.'/.htpasswd'),'auditadmin:fixture-changed')!==false,'version archive receives current account credentials');
putRow(rowData('accented','p','[caf'.chr(233).'] [test1file] [[literal]]'));
putRow(rowData('cafe','f'));putRow(rowData('test_file','f'));
putRow(rowData('metadataonly','p')+array('desc_5'=>'[cafe?l=fr]'));
$all=u5ProvisionFetchSources();$reverse=array();$fallback=array();
foreach ($all as $source=>$row) {
    if ($row['fallback']) $fallback[]=$source;
    foreach ($row['names'] as $token) $reverse[$token][]=$source;
}
foreach (array('cafe','test_file','literal','photo','manual') as $asset) {
    $candidate=preg_match('/^[a-z0-9!]+$/iD',$asset) ? array_unique(array_merge($reverse[$asset] ?? array(),$fallback)) : null;
    $expected=usesNames($asset);$actual=usesNames($asset,$candidate);sort($expected);sort($actual);
    verify($expected===$actual,'reverse candidates agree with full SQL for '.$asset);
}
$owner=u5ProvisionLock(u5ProvisionDir($root).'/worker.lock',LOCK_EX);
$start=microtime(true);$row=rowData('pb','p','[manual]','?test:ok;');
u5ProvisionPrepare($root,'pb',$row);putRow($row);
verify(!u5ProvisionFinish($root) && microtime(true)-$start<2,'writer proceeds during provisioner contention');
fclose($owner);
verify(u5ProvisionRun($root),'pending edit resumes');
verify(hasUser('manual','test'),'competing edit persisted');
