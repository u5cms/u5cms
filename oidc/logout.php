<?php
require __DIR__.'/bootstrap.inc.php';
try {
    $identity = $oidcSession->logout();
    foreach (array('u','p') as $name) setcookie($name, '', array('expires'=>time()-3600, 'path'=>'/', 'secure'=>true, 'httponly'=>true, 'samesite'=>'Lax'));
    $url = null;
    if ($identity !== null && isset($identity['logout_token'])) {
        $url = $oidcClient->logoutUrl($identity, $oidcSession->beginLogout());
    }
    header('Location: '.($url ?? $oidcClient->logoutResult()), true, 303);
} catch (Throwable $error) { u5OidcFailure($error); }
