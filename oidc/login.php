<?php
require __DIR__.'/bootstrap.inc.php';
try {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') throw new RuntimeException('GET required.');
    $target = $oidcClient->returnUrl($_GET['u'] ?? '');
    $metadata = $oidcClient->discovery();
    $transaction = $oidcSession->begin($target, $metadata);
    header('Location: '.$oidcClient->authorisation($metadata, $transaction, ($_GET['reauth'] ?? '') === '1'), true, 303);
} catch (Throwable $error) { u5OidcFailure($error); }
