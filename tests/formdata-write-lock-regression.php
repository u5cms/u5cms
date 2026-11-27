<?php
/* Verify that versioned form writes use one database-scoped advisory lock. */
$root=dirname(__DIR__);
$host=getenv('U5CMS_TEST_HOST') ?: '127.0.0.1';
$port=(int)(getenv('U5CMS_TEST_PORT') ?: 3306);
$user=getenv('U5CMS_TEST_USER') ?: 'root';
$password=getenv('U5CMS_TEST_PASSWORD') ?: '';
$database=getenv('U5CMS_TEST_DB') ?: 'test';

function connectFixture() {
    global $root,$host,$port,$user,$password,$database;
    require_once $root.'/mysql.php';
    if (!mysql_connect($host.':'.$port,$user,$password,true) || !mysql_select_db($database)) {
        throw new RuntimeException('Disposable database is unavailable.');
    }
    require_once $root.'/formdata-write-lock.inc.php';
}

connectFixture();
if (($argv[1] ?? '')==='hold') {
    $lock=u5FormDataRequireWriteLock();
    echo "ready\n";flush();
    usleep(350000);
    if (!u5FormDataWriteUnlock($lock)) throw new RuntimeException('Child could not release the form-data lock.');
    exit;
}

$process=proc_open([PHP_BINARY,__FILE__,'hold'],[1=>['pipe','w'],2=>['pipe','w']],$pipes);
if (!is_resource($process)) throw new RuntimeException('Could not start the lock holder.');
if (trim(fgets($pipes[1]))!=='ready') throw new RuntimeException('Lock holder did not become ready: '.stream_get_contents($pipes[2]));
$start=microtime(true);
$lock=u5FormDataRequireWriteLock();
$wait=microtime(true)-$start;
if ($wait<0.20 || $wait>3.0) throw new RuntimeException('The competing write was not serialised: '.$wait);
if (!u5FormDataWriteUnlock($lock)) throw new RuntimeException('Parent could not release the form-data lock.');
fclose($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[2]);
if (proc_close($process)!==0 || $error!=='') throw new RuntimeException('Lock holder failed: '.$error);
$start=microtime(true);$lock=u5FormDataRequireWriteLock();$immediate=microtime(true)-$start;
if ($immediate>0.20 || !u5FormDataWriteUnlock($lock)) throw new RuntimeException('Released lock was not immediately reusable.');
echo "Form-data write lock: 3 checks passed.\n";
