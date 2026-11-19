<?php
// OIDC endpoints do not need a database connection or the frontend rendering bootstrap.
require_once dirname(__DIR__).'/config.php';
require_once dirname(__DIR__).'/oidc.inc.php';
u5OidcHeaders();
if (!u5OidcEnabled()) { http_response_code(404); exit; }
try {
    $httpsisinuse = false;
    $result = eval($evaluateifhttpsisinuse ?? '');
    if ($result !== null) $httpsisinuse = (bool)$result;
    if (!$httpsisinuse) throw new RuntimeException('OIDC endpoints require HTTPS.');
    list($oidcClient, $oidcSession) = u5OidcServices();
}
catch (Throwable $error) { u5OidcFailure($error, 503); }
