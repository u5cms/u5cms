<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
function u5TrustedAssert($condition, $message) { if (!$condition) throw new RuntimeException($message); }
function u5flatidnlower($value) { return strtolower((string)$value); }
function u5OidcEnabled() { return $GLOBALS['trustedTestOidc'] ?? false; }
function mysql_query($sql) { throw new RuntimeException('Unexpected database access: ' . $sql); }

$mymail='admin@example.test';$host='localhost';$username='dbuser';$password='dbsecret';$db='cms';
$usesessioninsteadofbasicauth='yes';$u5trusteddeviceenabled='no';
$u5trusteddeviceusers=array('alice@example.test'=>3,'bob@example.test'=>2,'blocked@example.test'=>0);
$_SERVER['HTTPS']='on';$_SERVER['SERVER_PORT']=443;$_SERVER['HTTP_HOST']='box.example.test';
require dirname(__DIR__) . '/trusteddevice.inc.php';

u5TrustedAssert(u5TrustedDeviceResume() === false, 'Disabled feature resumed a session.');
$u5trusteddeviceenabled='yes';
u5TrustedAssert(u5TrustedDeviceLimit(' ALICE@EXAMPLE.TEST ')===3, 'Account normalization or quota failed.');
u5TrustedAssert(u5TrustedDeviceLimit('bob@example.test')===2, 'Independent account quota failed.');
u5TrustedAssert(!u5TrustedDeviceAllowedUser('other@example.test'), 'Unlisted account was accepted.');
u5TrustedAssert(!u5TrustedDeviceAllowedUser('blocked@example.test'), 'Zero quota was accepted.');
foreach (array(-1,1.5,true,'3',null,array(),PHP_INT_MAX) as $invalid) {
    $u5trusteddeviceusers=array('alice@example.test'=>$invalid);
    u5TrustedAssert(u5TrustedDeviceLimit('alice@example.test')===0, 'Invalid quota was accepted.');
}
$u5trusteddeviceusers=array('alice@example.test');
u5TrustedAssert(u5TrustedDeviceLimit('alice@example.test')===1, 'Legacy allow-list lost its one-browser limit.');
$u5trusteddeviceusers=array('alice@example.test'=>3,' ALICE@EXAMPLE.TEST '=>0);
u5TrustedAssert(u5TrustedDeviceLimit('alice@example.test')===0, 'Ambiguous keys increased access.');
$u5trusteddeviceusers=array('alice@example.test'=>3);
u5TrustedAssert(strpos(u5TrustedDeviceCookieName(), '__Host-u5td-')===0, 'Missing host-only cookie prefix.');
$name=u5TrustedDeviceCookieName();
foreach (array('bad',str_repeat('0',32).'.'.str_repeat('0',63),str_repeat('0',32).'.'.str_repeat('0',64).'x') as $invalid) {
    $_COOKIE[$name]=$invalid;
    u5TrustedAssert(u5TrustedDeviceCookieParts()===false, 'Malformed cookie was accepted.');
}
unset($_COOKIE[$name]);
$usesessioninsteadofbasicauth='no';
u5TrustedAssert(!u5TrustedDeviceEnabled(), 'Basic-auth installation enabled trusted sessions.');
$usesessioninsteadofbasicauth='yes';$trustedTestOidc=true;
u5TrustedAssert(!u5TrustedDeviceEnabled(), 'OIDC session policy was bypassed.');
$trustedTestOidc=false;
u5TrustedAssert(u5TrustedDeviceEnabled(), 'Session-based installation was disabled.');

$_SERVER['HTTP_ORIGIN']='https://box.example.test';$_SERVER['HTTP_SEC_FETCH_SITE']='same-origin';
u5TrustedAssert(u5TrustedDeviceOriginMatches(), 'Same-origin POST was rejected.');
$csrf=u5TrustedDeviceCsrfToken('alice@example.test','cookie-one');
$sessioncookiehashsalt='changed-daily-salt';
u5TrustedAssert(hash_equals($csrf,u5TrustedDeviceCsrfToken('alice@example.test','cookie-two')), 'CSRF token changed with ordinary session rotation.');
u5TrustedAssert(!hash_equals($csrf,u5TrustedDeviceCsrfToken('bob@example.test','cookie-one')), 'CSRF token crossed accounts.');
$_SERVER['HTTP_HOST']='box-p.example.test';$_SERVER['HTTP_X_FORWARDED_HOST']='box.example.test';$_SERVER['HTTP_SEC_FETCH_SITE']='same-site';
u5TrustedAssert(u5TrustedDeviceOriginMatches(), 'Forwarded public origin was rejected.');
unset($_SERVER['HTTP_X_FORWARDED_HOST']);$_SERVER['HTTP_HOST']='box.example.test';
$_SERVER['HTTP_ORIGIN']='https://attacker.example';$_SERVER['HTTP_SEC_FETCH_SITE']='cross-site';
u5TrustedAssert(!u5TrustedDeviceOriginMatches(), 'Cross-site POST was accepted.');
$_SERVER['HTTPS']='off';$_SERVER['SERVER_PORT']=80;unset($_SERVER['HTTP_X_FORWARDED_PROTO']);
u5TrustedAssert(u5TrustedDeviceIssue('alice@example.test')==='disabled', 'HTTP registration was allowed.');
$root=dirname(__DIR__);
foreach (array('login.inc.php','u5admin/usercheck.inc.php') as $path) u5TrustedAssert(strpos(file_get_contents($root.'/'.$path),'u5TrustedDeviceResume')!==false, 'Missing login hook.');
u5TrustedAssert(strpos(file_get_contents($root.'/logout.php'),'u5TrustedDeviceRevokeCurrent')!==false, 'Missing logout revocation.');
u5TrustedAssert(strpos(file_get_contents($root.'/config.php.sample'),"\$u5trusteddeviceenabled = 'no'")!==false, 'Sample configuration enables trust.');
echo "trusted-device-regression: ok\n";
