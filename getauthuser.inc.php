<?php
require_once('config.php');
require_once __DIR__.'/oidc.inc.php';
if (u5OidcRequired()) {
    require_once __DIR__.'/u5admin/u5idn.inc.php';
    try {
        $identity=u5OidcIdentity();
        $_COOKIE['u']=$identity !== null ? u5OidcNormalizeUsername($identity['username']) : '';
        $_COOKIE['p']=$identity !== null ? hash('sha256',$identity['sub'].'\0'.$identity['fingerprint']) : '';
    } catch (Throwable $error) { u5OidcFailure($error,503); }
}

if ($autoresolvefastcgiproblems == 'yes') {
    if ($basicauthvarname == '') $basicauthvarname = 'HTTP_AUTHORIZATION';
    list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($_SERVER[$basicauthvarname], 6)));
}

if ($usesessioninsteadofbasicauth != 'no') {
    u5ValidateTextRequest($_COOKIE,array('u','p'));
    $_SERVER['PHP_AUTH_USER'] = $_COOKIE['u'];
    $_SERVER['PHP_AUTH_PW'] = $_COOKIE['p'];
}

$_SERVER['PHP_AUTH_USER'] = trim(($_SERVER['PHP_AUTH_USER'] ?? ''));
$_SERVER['PHP_AUTH_PW'] = trim(($_SERVER['PHP_AUTH_PW'] ?? ''));
?>