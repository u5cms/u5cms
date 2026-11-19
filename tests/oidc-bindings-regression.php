<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__).'/src/Oidc/Bindings.php';
use U5cms\Oidc\Bindings;

$db=new mysqli('127.0.0.1',getenv('U5CMS_TEST_USER') ?: 'root',getenv('U5CMS_TEST_PASSWORD') ?: '', '', (int)getenv('U5CMS_TEST_PORT'));
$db->set_charset('latin1');
if (($argv[1] ?? '') === 'worker') {
    $name=$argv[2];
    if (!preg_match('/^u5oidc_bind_[a-f0-9]+$/D',$name)) throw new RuntimeException('Invalid test database.');
    $db->select_db($name);
    $store=new Bindings($db,'https://issuer.example','race-client');
    file_put_contents($argv[4].'.'.$argv[3], 'ready');
    $deadline=microtime(true)+10;
    while (!is_file($argv[4]) && microtime(true)<$deadline) usleep(1000);
    if (!is_file($argv[4])) exit(3);
    try { $store->resolve($argv[3],'race@example.org');exit(0); }
    catch (RuntimeException $error) { exit(2); }
}
$name='u5oidc_bind_'.bin2hex(random_bytes(6));
$db->query('CREATE DATABASE `'.$name.'`');$db->select_db($name);
$checks=0;
function bindingCheck($condition,$message) { global $checks;if (!$condition) throw new RuntimeException('FAILED: '.$message);$checks++; }
function bindingReject($callback,$message) {try {$callback();}catch(Throwable $e){bindingCheck(true,$message);return;}bindingCheck(false,$message);}
$schema=file_get_contents(dirname(__DIR__).'/oidc/schema.sql');
$tmp=sys_get_temp_dir().'/u5-oidc-bind-'.bin2hex(random_bytes(6));mkdir($tmp,0700);
try {
    $store=new Bindings($db,'https://issuer.example','client');
    bindingReject(function () use ($store) {$store->check();},'missing binding storage fails closed');
    $store->install($schema);
    bindingCheck($store->resolve('subject-1','one@example.org')==='one@example.org','initial verified binding');
    bindingCheck($store->resolve('subject-1','one@example.org')==='one@example.org','stable repeated binding');
    bindingReject(function () use ($store) {$store->resolve('subject-2','one@example.org');},'recycled email cannot switch subject');
    bindingReject(function () use ($store) {$store->resolve('subject-1','two@example.org');},'subject cannot silently switch username');
    $otherClient=new Bindings($db,'https://issuer.example','other-client');
    bindingCheck($otherClient->resolve('subject-1','one@example.org')==='one@example.org','client namespaces remain separate');
    $otherIssuer=new Bindings($db,'https://other-issuer.example','client');
    bindingCheck($otherIssuer->resolve('subject-1','one@example.org')==='one@example.org','issuer namespaces remain separate');
    bindingCheck($store->resolve('unicode','m'.chr(252).'ller@example.org')==='m'.chr(252).'ller@example.org','native CMS bytes persist losslessly');
    bindingCheck($store->resolve('plain','muller@example.org')==='muller@example.org','accent-distinct usernames remain distinct');
    $count=(int)$db->query('SELECT COUNT(*) n FROM u5oidc_bindings')->fetch_assoc()['n'];
    $store->install($schema);
    bindingCheck((int)$db->query('SELECT COUNT(*) n FROM u5oidc_bindings')->fetch_assoc()['n']===$count,'schema installation is idempotent and preserves bindings');
    $barrier=$tmp.'/go';$workers=array();
    foreach (array('race-a','race-b') as $subject) {
        $command=array_merge(array(PHP_BINARY),json_decode(getenv('U5_TEST_PHP_ARGS') ?: '[]',true),array(__FILE__,'worker',$name,$subject,$barrier));
        $worker=proc_open($command,array(1=>array('file',$tmp.'/'.$subject.'.log','w'),2=>array('redirect',1)),$pipes);
        if (!is_resource($worker)) throw new RuntimeException('Cannot start the binding race worker.');
        $workers[]=$worker;
    }
    try {
        $deadline=microtime(true)+10;
        while ((!is_file($barrier.'.race-a') || !is_file($barrier.'.race-b')) && microtime(true)<$deadline) usleep(1000);
        bindingCheck(is_file($barrier.'.race-a') && is_file($barrier.'.race-b'),'both concurrent first-logins reach the barrier');
        file_put_contents($barrier,'go');
        $statuses=array_map('proc_close',$workers);$workers=array();sort($statuses);
        bindingCheck($statuses===array(0,2),'exactly one concurrent subject can bind the same email');
        bindingCheck((int)$db->query("SELECT COUNT(*) n FROM u5oidc_bindings WHERE username='race@example.org'")->fetch_assoc()['n']===1,'first-login race leaves one complete binding');
    } finally {foreach($workers as $worker){proc_terminate($worker);proc_close($worker);}}
    $db->query('ALTER TABLE u5oidc_bindings DROP INDEX issuer_username');
    bindingReject(function () use ($store) {$store->check();},'missing uniqueness constraint is rejected');
    bindingReject(function () use ($store) {$store->resolve('subject-1','one@example.org');},'existing identity also fails closed with an invalid binding schema');
    echo "OIDC database bindings: $checks checks passed\n";
} finally {
    $db->query('DROP DATABASE `'.$name.'`');$db->close();
    foreach(glob($tmp.'/*') as $file)unlink($file);rmdir($tmp);
}
