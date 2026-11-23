<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);



ignore_user_abort(true);set_time_limit(36000);
require_once __DIR__ . '/connect.inc.php';

if($_GET['k']!=sha1(date('Ymd').$password.$sessioncookiehashsalt.$_GET['e'])&&$_GET['k']!=sha1(date('Ymd',time()-24*60*60).$password.$sessioncookiehashsalt.$_GET['e']))die('ERROR: Authorization failed.');

if (!isset($_GET['e'])) {
die('ERROR: not enough data.');
}

$safe_mail = strtolower(mysql_real_escape_string(str_replace(',&#0;@&#0;','',base64_decode(($_GET['e'] ?? '')))));

require_once dirname(__DIR__).'/myfunctions.inc.php';
require_once dirname(__DIR__).'/provision-changes.inc.php';
$exists=u5ProvisionQuery("SELECT 1 FROM intranetmembers WHERE FIND_IN_SET(BINARY '$safe_mail',COALESCE(members,''))>0 LIMIT 1");
if (!mysql_num_rows($exists)) {
    u5ProvisionIdentityChange(dirname(__DIR__),false,true);
    u5ProvisionQuery("UPDATE intranetmembers SET members = CONCAT(CONCAT_WS(',',NULLIF(TRIM(BOTH ',' FROM COALESCE(members,'')),''),'$safe_mail'),',') WHERE FIND_IN_SET(BINARY '$safe_mail',COALESCE(members,''))=0");
}
$needsRights="SELECT 1 FROM resources WHERE name LIKE '!%' AND typ='p' AND deleted!=1 AND INSTR(logins,'?$safe_mail:')=0 AND NOT EXISTS (SELECT 1 FROM accounts WHERE email='$safe_mail') LIMIT 1";
if (mysql_num_rows(u5ProvisionQuery($needsRights))) {
    $root=dirname(__DIR__);
    // A first login can coincide with another writer. Retry this enrolment
    // without holding an editor or routing existing members through a site-wide wait.
    if (!u5ProvisionPending($root)) u5ProvisionIdentityChange($root,false,true);
    u5ProvisionFinish($root);
    if (mysql_num_rows(u5ProvisionQuery($needsRights))) {
        http_response_code(202);header('Cache-Control: no-store');header('Retry-After: 2');
        echo '<script>setTimeout(function(){location.reload()},2000);</script>';
        exit;
    }
}
echo '<script>parent.loginsave();</script>';
