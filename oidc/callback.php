<?php
require __DIR__.'/bootstrap.inc.php';
try {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') throw new RuntimeException('GET required.');
    $transaction = $oidcSession->consume($_GET);
    $identity = $oidcClient->exchange($transaction['metadata'], $_GET['code'], $transaction);
    $identity = u5OidcResolveIdentity($identity);
    $oidcSession->establish($identity, $transaction);
    // Clear local credentials when switching identity; they are never OIDC proof.
    if ($oidcClient->config()['frontend'] !== 'no') {
        foreach (array('u','p') as $name) setcookie($name, '', array('expires'=>time()-3600, 'path'=>'/', 'secure'=>true, 'httponly'=>true, 'samesite'=>'Lax'));
    }
    foreach (array_keys($_COOKIE) as $name) {
        if (strpos($name, 'u5saml') === 0) setcookie($name, '', array('expires'=>time()-3600, 'path'=>'/', 'secure'=>true, 'httponly'=>true, 'samesite'=>'Lax'));
    }
    $target = $oidcClient->returnUrl($transaction['target']);
    if ($oidcClient->config()['auto_intranet_enrollment']) $target = $oidcClient->basePath().'oidc/enroll.php?u='.rawurlencode($target);
    header('Location: '.$target, true, 303);
} catch (Throwable $error) { u5OidcFailure($error); }
