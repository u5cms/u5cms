<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

class U5TrustedFakeResult {
    public $rows;
    public $index = 0;
    public function __construct($rows) { $this->rows = array_values($rows); }
}

$u5TrustedFakeAccount = array('email' => 'stefan.minder@bk.admin.ch', 'pw' => '{SHA}account-hash');
$u5TrustedFakeRow = null;

function mysql_real_escape_string($value) { return addslashes((string)$value); }
function mysql_num_rows($result) { return count($result->rows); }
function mysql_fetch_array($result) { return $result->rows[$result->index++]; }
function u5TrustedFakeSqlValue($sql, $name) {
    if (!preg_match("/" . preg_quote($name, '/') . "='([^']*)'/", $sql, $match)) return null;
    return stripslashes($match[1]);
}
function mysql_query($sql) {
    global $u5TrustedFakeAccount, $u5TrustedFakeRow;
    if (preg_match('/^(CREATE TABLE|START TRANSACTION|COMMIT|ROLLBACK)/', $sql)) return true;
    if (strpos($sql, 'SELECT email,pw FROM accounts') === 0) {
        $email = u5TrustedFakeSqlValue($sql, 'email');
        return new U5TrustedFakeResult($email === strtolower($u5TrustedFakeAccount['email']) ? array($u5TrustedFakeAccount) : array());
    }
    if (strpos($sql, 'SELECT revoked_at FROM u5trusteddevices') === 0) {
        $rows = $u5TrustedFakeRow === null ? array() : array(array('revoked_at' => $u5TrustedFakeRow['revoked_at']));
        return new U5TrustedFakeResult($rows);
    }
    if (strpos($sql, 'INSERT INTO u5trusteddevices') === 0) {
        if (!preg_match("/VALUES \('([^']*)','([^']*)','([^']*)','([^']*)','([^']*)',(\d+),(\d+),0\)/", $sql, $m)) return false;
        $u5TrustedFakeRow = array('installation_id'=>$m[1], 'username'=>$m[2], 'selector'=>$m[3],
            'validator_hash'=>$m[4], 'account_hash'=>$m[5], 'created_at'=>(int)$m[6],
            'last_used_at'=>(int)$m[7], 'revoked_at'=>0);
        return true;
    }
    if (strpos($sql, 'UPDATE u5trusteddevices SET selector=') === 0) {
        if ($u5TrustedFakeRow === null || !preg_match("/selector='([^']*)',validator_hash='([^']*)',account_hash='([^']*)',created_at=(\d+),last_used_at=(\d+),revoked_at=0/", $sql, $m)) return false;
        $u5TrustedFakeRow['selector']=$m[1];$u5TrustedFakeRow['validator_hash']=$m[2];$u5TrustedFakeRow['account_hash']=$m[3];
        $u5TrustedFakeRow['created_at']=(int)$m[4];$u5TrustedFakeRow['last_used_at']=(int)$m[5];$u5TrustedFakeRow['revoked_at']=0;
        return true;
    }
    if (strpos($sql, 'SELECT username,validator_hash,account_hash,last_used_at FROM u5trusteddevices') === 0) {
        $selector = u5TrustedFakeSqlValue($sql, 'selector');
        $rows = $u5TrustedFakeRow !== null && $u5TrustedFakeRow['selector'] === $selector && $u5TrustedFakeRow['revoked_at'] === 0
            ? array($u5TrustedFakeRow) : array();
        return new U5TrustedFakeResult($rows);
    }
    if (strpos($sql, 'SELECT selector,revoked_at FROM u5trusteddevices') === 0) {
        return new U5TrustedFakeResult($u5TrustedFakeRow === null ? array() : array($u5TrustedFakeRow));
    }
    if (strpos($sql, 'UPDATE u5trusteddevices SET last_used_at=') === 0) {
        if ($u5TrustedFakeRow !== null && preg_match('/last_used_at=(\d+)/', $sql, $m)) $u5TrustedFakeRow['last_used_at']=(int)$m[1];
        return true;
    }
    if (strpos($sql, 'UPDATE u5trusteddevices SET revoked_at=') === 0) {
        if ($u5TrustedFakeRow !== null && preg_match('/revoked_at=(\d+)/', $sql, $m)) $u5TrustedFakeRow['revoked_at']=(int)$m[1];
        return true;
    }
    return false;
}
function u5flatidnlower($value) { return strtolower((string)$value); }
function pwdcookieget($value) { return 'current-cookie:' . $value; }
function u5TrustedAssert($condition, $message) { if (!$condition) throw new RuntimeException($message); }

$mymail='admin@example.test';$host='localhost';$username='dbuser';$password='dbsecret';$db='cms';
$usesessioninsteadofbasicauth='yes';$sessioncookiehashsalt='daily';
$u5trusteddeviceenabled='no';$u5trusteddeviceusers=array('stefan.minder@bk.admin.ch');
$_SERVER['HTTPS']='on';$_SERVER['SERVER_PORT']=443;$_SERVER['HTTP_HOST']='box.example.test';
require dirname(__DIR__) . '/trusteddevice.inc.php';

u5TrustedAssert(u5TrustedDeviceResume() === false, 'Disabled feature resumed a session.');
$u5trusteddeviceenabled='yes';
u5TrustedAssert(u5TrustedDeviceAllowedUser('STEFAN.MINDER@BK.ADMIN.CH'), 'Allowed account normalization failed.');
u5TrustedAssert(!u5TrustedDeviceAllowedUser('other@example.test'), 'Unlisted account was accepted.');
u5TrustedAssert(strpos(u5TrustedDeviceCookieName(), '__Host-u5td-') === 0, 'Cookie does not use the __Host- prefix.');

u5TrustedAssert(u5TrustedDeviceIssue('stefan.minder@bk.admin.ch') === 'ok', 'Initial issue failed.');
$cookieName=u5TrustedDeviceCookieName();$issuedCookie=$_COOKIE[$cookieName];
u5TrustedAssert(preg_match('/^[a-f0-9]{32}\.[a-f0-9]{64}$/D',$issuedCookie)===1, 'Issued token format is invalid.');
list($issuedSelector,$issuedValidator)=explode('.',$issuedCookie,2);
u5TrustedAssert($u5TrustedFakeRow['selector']===$issuedSelector, 'Selector was not stored.');
u5TrustedAssert($u5TrustedFakeRow['validator_hash']===hash('sha256',$issuedValidator), 'Validator hash was not stored.');
u5TrustedAssert($u5TrustedFakeRow['validator_hash']!==$issuedValidator, 'Raw validator was stored.');

unset($_COOKIE[$cookieName]);
u5TrustedAssert(u5TrustedDeviceIssue('stefan.minder@bk.admin.ch') === 'exists', 'A second active browser was allowed.');
u5TrustedAssert(u5TrustedDeviceStatus('stefan.minder@bk.admin.ch') === 'other', 'Other-browser status was not detected.');

$_COOKIE[$cookieName]=$issuedCookie;
$resumed=u5TrustedDeviceResume();
u5TrustedAssert(is_array($resumed) && $resumed['username']==='stefan.minder@bk.admin.ch', 'Valid token did not resume.');
u5TrustedAssert($resumed['password_cookie']==='current-cookie:{SHA}account-hash', 'Current account session was not reconstructed.');

$_COOKIE[$cookieName]=$issuedSelector.'.'.str_repeat('0',64);
u5TrustedAssert(u5TrustedDeviceResume() === false && !isset($_COOKIE[$cookieName]), 'Tampered token was not rejected and cleared.');

$_COOKIE[$cookieName]=$issuedCookie;$u5TrustedFakeRow['revoked_at']=0;
u5TrustedDeviceRevokeCurrent();
u5TrustedAssert($u5TrustedFakeRow['revoked_at']>0 && !isset($_COOKIE[$cookieName]), 'Revocation failed.');

u5TrustedAssert(u5TrustedDeviceIssue('stefan.minder@bk.admin.ch') === 'ok', 'Re-enrolment after revocation failed.');
$issuedCookie=$_COOKIE[$cookieName];$u5TrustedFakeAccount['pw']='{SHA}changed';
u5TrustedAssert(u5TrustedDeviceResume() === false && $u5TrustedFakeRow['revoked_at']>0, 'Account change did not revoke the token.');

$_SERVER['HTTP_ORIGIN']='https://box.example.test';$_SERVER['HTTP_SEC_FETCH_SITE']='same-origin';
u5TrustedAssert(u5TrustedDeviceOriginMatches(), 'Same-origin POST was rejected.');
$csrfBefore=u5TrustedDeviceCsrfToken('stefan.minder@bk.admin.ch','cookie-one');
$sessioncookiehashsalt='changed-daily-salt';
u5TrustedAssert(hash_equals($csrfBefore,u5TrustedDeviceCsrfToken('stefan.minder@bk.admin.ch','cookie-two')), 'CSRF token changed with the ordinary session.');
$_SERVER['HTTP_HOST']='box-p.example.test';$_SERVER['HTTP_X_FORWARDED_HOST']='box.example.test';
$_SERVER['HTTP_SEC_FETCH_SITE']='same-site';
u5TrustedAssert(u5TrustedDeviceOriginMatches(), 'Forwarded public origin was rejected.');
unset($_SERVER['HTTP_X_FORWARDED_HOST']);$_SERVER['HTTP_HOST']='box.example.test';
$_SERVER['HTTP_ORIGIN']='https://attacker.example';$_SERVER['HTTP_SEC_FETCH_SITE']='cross-site';
u5TrustedAssert(!u5TrustedDeviceOriginMatches(), 'Cross-site POST was accepted.');

$_SERVER['HTTPS']='off';$_SERVER['SERVER_PORT']=80;unset($_SERVER['HTTP_X_FORWARDED_PROTO']);
u5TrustedAssert(u5TrustedDeviceIssue('stefan.minder@bk.admin.ch') === 'disabled', 'HTTP enrolment was allowed.');

$root=dirname(__DIR__);
u5TrustedAssert(strpos(file_get_contents($root.'/login.inc.php'),'u5TrustedDeviceResume')!==false, 'Frontend login hook is missing.');
u5TrustedAssert(strpos(file_get_contents($root.'/u5admin/usercheck.inc.php'),'u5TrustedDeviceResume')!==false, 'Backend login hook is missing.');
u5TrustedAssert(strpos(file_get_contents($root.'/logout.php'),'u5TrustedDeviceRevokeCurrent')!==false, 'Logout revocation hook is missing.');
u5TrustedAssert(strpos(file_get_contents($root.'/config.php.sample'),"\$u5trusteddeviceenabled = 'no'")!==false, 'Sample config is not disabled by default.');

echo "trusted-device-regression: ok\n";
