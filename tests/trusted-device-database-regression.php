<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/mysql.php';
function u5TrustedDbAssert($condition, $message) { if (!$condition) throw new RuntimeException($message); }
function u5flatidnlower($value) { return strtolower((string)$value); }
function pwdcookieget($value) { return 'current-cookie:' . $value; }
function u5TrustedDbQuery($sql) {
    $result=mysql_query($sql);
    if ($result===false) throw new RuntimeException(mysql_error());
    return $result;
}
$worker=($argv[1]??'')==='worker';
$database=$worker ? $argv[2] : 'u5_trusted_' . bin2hex(random_bytes(6));
u5TrustedDbAssert(preg_match('/\Au5_trusted_[a-f0-9]{12}\z/D',$database)===1,'Invalid disposable database name.');
$link=mysql_connect('127.0.0.1:'.(getenv('U5CMS_TEST_PORT')?:'33361'),getenv('U5CMS_TEST_USER')?:'root',getenv('U5CMS_TEST_PASSWORD')?:'');
u5TrustedDbAssert($link!==false,'Disposable database server unavailable.');
if (!$worker) {
    u5TrustedDbQuery('CREATE DATABASE `'.$database.'` CHARACTER SET latin1');
    register_shutdown_function(function() use($database) { mysql_query('DROP DATABASE IF EXISTS `'.$database.'`'); });
}
mysql_select_db($database);mysql_set_charset('latin1');
$mymail='admin@example.test';$host='localhost';$username='dbuser';$password='test-config-secret';$db=$database;
$usesessioninsteadofbasicauth='yes';$u5trusteddeviceenabled='yes';
$u5trusteddeviceusers=array('alice@example.test'=>2,'bob@example.test'=>1);
$_SERVER['HTTPS']='on';$_SERVER['SERVER_PORT']=443;$_SERVER['HTTP_HOST']='cms.example.test';
require dirname(__DIR__) . '/trusteddevice.inc.php';
if ($worker) {
    $u5trusteddeviceusers[$argv[3]]=(int)$argv[4];
    $deadline=microtime(true)+10;
    while (!file_exists($argv[5]) && microtime(true)<$deadline) usleep(1000);
    u5TrustedDbAssert(file_exists($argv[5]),'Registration barrier timed out.');
    $result=u5TrustedDeviceIssue($argv[3]);
    echo json_encode(array('result'=>$result,'cookie'=>$_COOKIE[u5TrustedDeviceCookieName()]??null));
    exit;
}
function u5TrustedDbRegister($account) {
    unset($_COOKIE[u5TrustedDeviceCookieName()]);
    u5TrustedDbAssert(u5TrustedDeviceIssue($account)==='ok','Registration failed for '.$account.': '.mysql_error());
    return $_COOKIE[u5TrustedDeviceCookieName()];
}
function u5TrustedDbResume($cookie) {
    $_COOKIE[u5TrustedDeviceCookieName()]=$cookie;
    return u5TrustedDeviceResume();
}
function u5TrustedDbRace($database,$account,$limit,$workers=12) {
    $gate=sys_get_temp_dir().'/u5td-gate-'.bin2hex(random_bytes(6));
    $processes=array();
    try {
        for($i=0;$i<$workers;$i++) {
            $args=array(PHP_BINARY,__FILE__,'worker',$database,$account,(string)$limit,$gate);
            $process=proc_open($args,array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
            u5TrustedDbAssert(is_resource($process),'Cannot launch concurrent registration.');
            fclose($pipes[0]);$processes[]=array($process,$pipes);
        }
        touch($gate);$cookies=array();
        foreach($processes as $entry) {
            list($process,$pipes)=$entry;
            $output=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);
            fclose($pipes[1]);fclose($pipes[2]);$code=proc_close($process);
            u5TrustedDbAssert($code===0,'Concurrent worker failed: '.$error.$output);
            $result=json_decode($output,true);
            u5TrustedDbAssert(is_array($result)&&in_array($result['result'],array('ok','limit'),true),'Unexpected concurrent result: '.$output);
            if($result['result']==='ok') $cookies[]=$result['cookie'];
        }
        u5TrustedDbAssert(count($cookies)===$limit,'Concurrent registrations did not fill exactly the allowed capacity.');
        return $cookies;
    } finally { if(file_exists($gate))unlink($gate); }
}

u5TrustedDbQuery("CREATE TABLE accounts (email VARCHAR(255),pw VARCHAR(255)) ENGINE=MyISAM");
u5TrustedDbQuery("INSERT INTO accounts VALUES ('alice@example.test','{SHA}alice'),('bob@example.test','{SHA}bob')");
u5TrustedDbAssert(u5TrustedDeviceBrowsers('alice@example.test')===array(),'First visit should show no registrations.');
// Seed the released single-browser schema and a valid existing token.
u5TrustedDbQuery("CREATE TABLE u5trusteddevices (installation_id CHAR(16) NOT NULL,username VARCHAR(255) NOT NULL,selector CHAR(32) NOT NULL,validator_hash CHAR(64) NOT NULL,account_hash CHAR(64) NOT NULL,created_at BIGINT UNSIGNED NOT NULL,last_used_at BIGINT UNSIGNED NOT NULL,revoked_at BIGINT UNSIGNED NOT NULL DEFAULT 0,PRIMARY KEY(installation_id,username),UNIQUE KEY u5trusteddevices_selector(installation_id,selector)) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin");
$installation=u5TrustedDeviceInstallationId();$selector=bin2hex(random_bytes(16));$validator=bin2hex(random_bytes(32));$now=time();
u5TrustedDbQuery("INSERT INTO u5trusteddevices VALUES ('$installation','alice@example.test','$selector','".hash('sha256',$validator)."','".hash('sha256','{SHA}alice')."',$now,$now,0)");
$first=$selector.'.'.$validator;
u5TrustedDbAssert(is_array(u5TrustedDbResume($first)),'Existing token failed before schema migration.');
u5TrustedDbAssert(u5TrustedDeviceIssue('alice@example.test')==='exists','Existing browser consumed another place.');
u5TrustedDbAssert(u5TrustedDevicePrimaryKey()===array('installation_id','username','slot'),'Schema did not migrate.');
u5TrustedDbAssert(is_array(u5TrustedDbResume($first)),'Migration invalidated an existing token.');
$second=u5TrustedDbRegister('alice@example.test');
u5TrustedDbAssert($second!==$first,'Browsers share a secret.');
unset($_COOKIE[u5TrustedDeviceCookieName()]);
u5TrustedDbAssert(u5TrustedDeviceIssue('alice@example.test')==='limit','Third browser exceeded quota.');
u5TrustedDbAssert(is_array(u5TrustedDbResume($first))&&is_array(u5TrustedDbResume($second)),'A new registration replaced another browser.');
$bob=u5TrustedDbRegister('bob@example.test');
u5TrustedDbAssert(!u5TrustedDeviceRevoke('bob@example.test',explode('.',$first)[0]),'Cross-account revocation succeeded.');
u5TrustedDbAssert(is_array(u5TrustedDbResume($first)),'Cross-account attempt revoked the wrong browser.');
$stored=mysql_fetch_assoc(u5TrustedDbQuery("SELECT validator_hash FROM u5trusteddevices WHERE selector='".explode('.',$second)[0]."'"));
u5TrustedDbAssert($stored['validator_hash']===hash('sha256',explode('.',$second)[1]),'Server did not hash the browser secret.');
u5TrustedDbAssert(u5TrustedDbResume(explode('.',$second)[0].'.'.str_repeat('0',64))===false,'Tampered token resumed.');
$_COOKIE[u5TrustedDeviceCookieName()]=explode('.',$second)[0].'.'.str_repeat('0',64);u5TrustedDeviceRevokeCurrent();
u5TrustedDbAssert(is_array(u5TrustedDbResume($second)),'Forged logout revoked a valid browser.');
// A fresh authenticated session can remove a lost browser and reuse its place.
unset($_COOKIE[u5TrustedDeviceCookieName()]);
u5TrustedDbAssert(u5TrustedDeviceRevoke('alice@example.test',explode('.',$first)[0]),'Lost browser could not be revoked.');
$replacement=u5TrustedDbRegister('alice@example.test');
u5TrustedDbAssert(u5TrustedDbResume($first)===false,'Reusing a place restored an old secret.');
u5TrustedDbAssert(is_array(u5TrustedDbResume($second)),'Revoking one browser affected another.');
$u5trusteddeviceusers['alice@example.test']=1;
u5TrustedDbAssert(u5TrustedDbResume($second)===false,'Reduced quota did not reject a browser above the new limit.');
u5TrustedDbAssert(is_array(u5TrustedDbResume($replacement)),'Reduced quota rejected a retained browser.');
$u5trusteddeviceusers['alice@example.test']=0;
u5TrustedDbAssert(u5TrustedDbResume($replacement)===false,'Zero quota resumed a trusted browser.');
$u5trusteddeviceusers['alice@example.test']=2;
u5TrustedDbQuery("UPDATE accounts SET pw='{SHA}changed' WHERE email='alice@example.test'");
u5TrustedDbAssert(u5TrustedDbResume($replacement)===false,'Changed account credential did not revoke trust.');
$changed=u5TrustedDbRegister('alice@example.test');
u5TrustedDbAssert(u5TrustedDbResume($changed)['password_cookie']==='current-cookie:{SHA}changed','Current credentials were not reconstructed.');
$oldDb=$db;$db.='_different';
u5TrustedDbAssert(u5TrustedDbResume($changed)===false,'Token crossed installation boundaries.');$db=$oldDb;
$_COOKIE[u5TrustedDeviceCookieName()]=$bob;u5TrustedDeviceRevokeCurrent();
u5TrustedDbAssert(u5TrustedDbResume($bob)===false,'Logout did not revoke the current browser.');
u5TrustedDbAssert(u5TrustedDeviceIssue('missing@example.test')==='disabled','Unlisted account registered.');

// Real independent database connections race for initially empty and revoked places.
u5TrustedDbQuery('TRUNCATE TABLE u5trusteddevices');
$race=u5TrustedDbRace($database,'alice@example.test',2);
u5TrustedDbAssert(count(u5TrustedDeviceBrowsers('alice@example.test'))===2,'Race exceeded the database quota.');
foreach($race as $cookie)u5TrustedDbAssert(is_array(u5TrustedDbResume($cookie)),'Race returned an unusable token.');
foreach(u5TrustedDeviceBrowsers('alice@example.test') as $row)u5TrustedDbAssert(u5TrustedDeviceRevoke('alice@example.test',$row['selector']),'Cannot free a raced place.');
u5TrustedDbRace($database,'alice@example.test',2);
// Concurrent first registrations also migrate the original table safely.
u5TrustedDbQuery('DROP TABLE u5trusteddevices');
u5TrustedDbQuery("CREATE TABLE u5trusteddevices (installation_id CHAR(16) NOT NULL,username VARCHAR(255) NOT NULL,selector CHAR(32) NOT NULL,validator_hash CHAR(64) NOT NULL,account_hash CHAR(64) NOT NULL,created_at BIGINT UNSIGNED NOT NULL,last_used_at BIGINT UNSIGNED NOT NULL,revoked_at BIGINT UNSIGNED NOT NULL DEFAULT 0,PRIMARY KEY(installation_id,username),UNIQUE KEY u5trusteddevices_selector(installation_id,selector)) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin");
u5TrustedDbRace($database,'alice@example.test',2);
echo "trusted-device-database-regression: ok (migration, quotas, revocation, account isolation, 36 concurrent registrations)\n";
