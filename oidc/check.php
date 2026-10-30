<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$_SERVER['REQUEST_URI'] = '/oidc/check.php';
require_once dirname(__DIR__).'/config.php';
require_once dirname(__DIR__).'/oidc.inc.php';
try {
    if (!u5OidcEnabled()) throw new RuntimeException('OIDC is disabled.');
    list($client) = u5OidcServices();
    echo "OIDC configuration and dependencies: OK\n";
    echo 'Explicit subject mappings: '.count($client->config()['subjects'])."\n";
    if (in_array('--install-bindings', $argv, true)) {
        u5OidcBindingStore()->install(file_get_contents(__DIR__.'/schema.sql'));
        echo "OIDC identity binding table: installed and verified\n";
    } elseif ($client->config()['identity_mode'] === 'verified_email') {
        u5OidcBindingStore()->check();
        echo "OIDC identity binding table: OK\n";
    }
    if (in_array('--discover', $argv, true)) {
        $client->discovery();
        echo "HTTPS discovery, issuer and provider capabilities: OK\n";
    }
} catch (Throwable $error) {
    fwrite(STDERR, 'OIDC check failed: '.$error->getMessage()."\n");
    exit(1);
}
