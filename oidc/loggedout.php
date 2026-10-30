<?php
require __DIR__.'/bootstrap.inc.php';
try {
    $oidcSession->finishLogout($_GET['state'] ?? null);
    header('Location: '.$oidcClient->logoutResult(), true, 303);
} catch (Throwable $error) { u5OidcFailure($error); }
