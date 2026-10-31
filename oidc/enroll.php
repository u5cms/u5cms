<?php
require __DIR__.'/bootstrap.inc.php';
try {
    if (!$oidcClient->config()['auto_intranet_enrollment']) { http_response_code(404); exit; }
    $identity = u5OidcIdentity();
    if ($identity === null) throw new RuntimeException('Intranet enrollment requires OIDC authentication.');
    $target = $oidcClient->returnUrl($_GET['u'] ?? '');
    chdir(dirname(__DIR__));
    require_once 'connect.inc.php';
    $user = u5OidcNormalizeUsername($identity['username']);
    if (!u5OidcValidEmail(u5toutf8($user))) throw new RuntimeException('Intranet enrollment requires an email CMS username.');
    $lock = fopen('fileversions/oidc-enrollment.lock', 'c');
    if ($lock === false) throw new RuntimeException('Cannot open the intranet enrollment lock.');
    try {
        $busy = !flock($lock, LOCK_EX | LOCK_NB);
        $running = is_file('fileversions/htarunning.txt') ? (int)file_get_contents('fileversions/htarunning.txt') : 0;
        if ($busy || $running > time()-300) {
            http_response_code(503);
            header('Retry-After: 2');
            $retry = $oidcClient->basePath().'oidc/enroll.php?u='.rawurlencode($target);
            header('Refresh: 2; url='.$retry);
            echo '<p>Provisioning is in progress. Please wait.</p>';
            exit;
        }
        $escaped = mysql_real_escape_string($user);
        if (mysql_query("UPDATE intranetmembers SET members=CONCAT(CONCAT_WS(',',NULLIF(TRIM(BOTH ',' FROM COALESCE(members,'')),''),'".$escaped."'),',') WHERE FIND_IN_SET(BINARY '".$escaped."',BINARY COALESCE(members,''))=0") === false) {
            throw new RuntimeException('Cannot enroll the mapped OIDC identity.');
        }
        $changed = mysql_affected_rows() > 0;
        $result = mysql_query("SELECT email FROM accounts WHERE email='".$escaped."'");
        if ($result === false) throw new RuntimeException('Cannot check enrolled CMS identity.');
        $backendUser = mysql_num_rows($result) > 0;
        $missing = false;
        if (!$backendUser) {
            $result = mysql_query("SELECT name FROM resources WHERE name LIKE '!%' AND deleted!=1 AND INSTR(logins,'?".$escaped.":')=0 LIMIT 1");
            if ($result === false) throw new RuntimeException('Cannot check intranet permission publication.');
            $missing = mysql_num_rows($result) > 0;
        }
        $pending = 'fileversions/oidc-enrollment.pending.txt';
        if ($changed || $missing || is_file($pending)) {
            if (file_put_contents($pending, 'pending', LOCK_EX) === false) throw new RuntimeException('Cannot checkpoint intranet enrollment.');
            // Complete the same intranet and private-file publication as the SAML workflow.
            // Buffer the legacy progress HTML; redirect only after publication finishes.
            $level = ob_get_level();
            ob_start();
            try { require 'htaccess.php'; }
            finally { while (ob_get_level() > $level) ob_end_clean(); }
        }
        if (!$backendUser) {
            $result = mysql_query("SELECT name FROM resources WHERE name LIKE '!%' AND deleted!=1 AND INSTR(logins,'?".$escaped.":')=0 LIMIT 1");
            if ($result === false || mysql_num_rows($result) > 0) throw new RuntimeException('Intranet permission publication did not complete.');
        }
        if (is_file($pending) && !unlink($pending)) throw new RuntimeException('Cannot finish the enrollment checkpoint.');
    } finally { flock($lock, LOCK_UN); fclose($lock); }
    header('Location: '.$target, true, 303);
} catch (Throwable $error) { u5OidcFailure($error, 503); }
