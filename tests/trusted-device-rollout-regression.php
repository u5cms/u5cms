<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

function u5RolloutTestAssert($condition, $message) { if (!$condition) throw new RuntimeException($message); }
function u5RolloutTestRemove($path) {
    if (!file_exists($path) && !is_link($path)) return;
    if (is_dir($path) && !is_link($path)) {
        foreach (scandir($path) as $entry) if ($entry !== '.' && $entry !== '..') u5RolloutTestRemove($path . '/' . $entry);
        rmdir($path);
    } else unlink($path);
}
function u5RolloutTestRun($arguments, &$output = null) {
    $command = 'U5_TRUSTED_ROLLOUT_TEST=1 ' . escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(dirname(__DIR__) . '/scripts/trusted-device-rollout.php');
    foreach ($arguments as $argument) $command .= ' ' . escapeshellarg($argument);
    $lines = array(); $code = 0; exec($command . ' 2>&1', $lines, $code); $output = implode("\n", $lines);
    return $code;
}

$tmp = sys_get_temp_dir() . '/u5td-rollout-' . bin2hex(random_bytes(6));
$scan = $tmp . '/var/www/vhosts';
$source = $scan . '/example.test/source-p.example.test';
$oidc = $scan . '/oidc.test/oidc-p.example.test';
foreach (array($source, $source . '/u5admin', $oidc, $oidc . '/u5admin') as $dir) mkdir($dir, 0770, true);

$stableLogin = <<<'PHP'
<?php
if ($usesessioninsteadofbasicauth != 'no') {
    $_SERVER['PHP_AUTH_USER'] = $_COOKIE['u'] ?? '';
    $_SERVER['PHP_AUTH_PW'] = $_COOKIE['p'] ?? '';
    setcookie('u', $_SERVER['PHP_AUTH_USER'], 0, '/', '', $httpsisinuse, true);
    setcookie('p', $_SERVER['PHP_AUTH_PW'], 0, '/', '', $httpsisinuse, true);
}

$_SERVER['PHP_AUTH_USER'] = trim($_SERVER['PHP_AUTH_USER']);
$_SERVER['PHP_AUTH_PW'] = trim($_SERVER['PHP_AUTH_PW']);
PHP;
$stableBackend = str_replace("\$_COOKIE['u'] ?? ''", "\$_COOKIE['u']", str_replace("\$_COOKIE['p'] ?? ''", "\$_COOKIE['p']", $stableLogin));
$oidcLogin = <<<'PHP'
<?php
if ($usesessioninsteadofbasicauth != 'no') {
    u5ValidateTextRequest($_COOKIE,array('u','p'));
    $_SERVER['PHP_AUTH_USER'] = $_COOKIE['u'] ?? '';
    $_SERVER['PHP_AUTH_PW'] = $_COOKIE['p'] ?? '';
    if (!u5OidcRequired()) {
    setcookie('u', ($_SERVER['PHP_AUTH_USER'] ?? ''), 0, '/', '', $httpsisinuse, true);
    setcookie('p', ($_SERVER['PHP_AUTH_PW'] ?? ''), 0, '/', '', $httpsisinuse, true);
    }
}

$_SERVER['PHP_AUTH_USER'] = trim($_SERVER['PHP_AUTH_USER']);
$_SERVER['PHP_AUTH_PW'] = trim($_SERVER['PHP_AUTH_PW']);
PHP;
$oidcBackend = str_replace('u5OidcRequired()', 'u5OidcEnabled()', str_replace("\$_COOKIE['u'] ?? ''", "\$_COOKIE['u']", str_replace("\$_COOKIE['p'] ?? ''", "\$_COOKIE['p']", $oidcLogin)));
$logout = "<?php\nrequire('connect.inc.php');\nsetcookie('u', '', 0, '/', '', \$httpsisinuse, true);\n";
$config = "<?php\n   \$usesessioninsteadofbasicauth = 'yes';\n   \$sessioncookiehashsalt = 'daily';\n   \$sticksessiontoip = 'no';\n";

// Pre-12.10.2 and 12.10.2 ordinary login prompts must both converge on the release form.
$legacyForm = <<<'PHP'
echo '<form name="form" action="../loginsave.php?u=u5admin" method="post"></form><script>
            if(gotopage)document.form.action="../loginsave.php?u="+escape(location.href);
            else if(top===self && location.href.indexOf(".php")>0&&location.href.indexOf("index.php")<0)self.close();
            if(top!=self)top.location.href=location.href;</script>';
PHP;
$stableBackend .= "\n" . $legacyForm;
$oidcBackend .= "\n" . str_replace('if(gotopage)', 'if(location.pathname.endsWith("/u5admin/trusteddevice.php"))document.form.action="../loginsave.php?u=u5admin/trusteddevice.php";' . "\n            else if(gotopage)", $legacyForm);

foreach (array($source => array($stableLogin,$stableBackend), $oidc => array($oidcLogin,$oidcBackend)) as $root => $pair) {
    file_put_contents($root . '/login.inc.php', $pair[0]);
    file_put_contents($root . '/u5admin/usercheck.inc.php', $pair[1]);
    file_put_contents($root . '/logout.php', $logout);
    file_put_contents($root . '/config.php', $config);
}
copy(dirname(__DIR__) . '/trusteddevice.inc.php', $source . '/trusteddevice.inc.php');
copy(dirname(__DIR__) . '/u5admin/trusteddevice.php', $source . '/u5admin/trusteddevice.php');

$common = array('--scan-root=' . $scan, '--source-root=' . $source, '--user=owner@example.test', '--expect-roots=2');
$before = hash_file('sha256', $oidc . '/login.inc.php');
u5RolloutTestAssert(u5RolloutTestRun($common, $dry) === 0 && strpos($dry, '"mode":"dry-run"') !== false, 'Dry-run failed.');
u5RolloutTestAssert(hash_equals($before, hash_file('sha256', $oidc . '/login.inc.php')), 'Dry-run changed a file.');

$backup = $tmp . '/backup';
$apply = array_merge($common, array('--apply', '--backup-root=' . $backup));
u5RolloutTestAssert(u5RolloutTestRun($apply, $applied) === 0 && strpos($applied, '"mode":"apply"') !== false, 'Apply failed: ' . $applied);
foreach (array($source, $oidc) as $root) {
    u5RolloutTestAssert(strpos(file_get_contents($root . '/login.inc.php'), 'u5TrustedDeviceResume') !== false, 'Frontend hook missing.');
    u5RolloutTestAssert(strpos(file_get_contents($root . '/u5admin/usercheck.inc.php'), 'u5TrustedDeviceResume') !== false, 'Backend hook missing.');
    $backend = file_get_contents($root . '/u5admin/usercheck.inc.php');
    $releaseBackend = file_get_contents(dirname(__DIR__) . '/u5admin/usercheck.inc.php');
    foreach (array('~<form name="form" action="[^"\n]+"~', '~if\(document\.form\.getAttribute\("action"\).*?\n            \}~s') as $pattern) {
        u5RolloutTestAssert(preg_match($pattern, $releaseBackend, $expected) === 1, 'Release login form missing.');
        u5RolloutTestAssert(strpos($backend, $expected[0]) !== false, 'Feature-only rollout differs from the release login form.');
    }
    u5RolloutTestAssert(strpos(file_get_contents($root . '/logout.php'), 'u5TrustedDeviceRevokeCurrent') !== false, 'Logout hook missing.');
    u5RolloutTestAssert(strpos(file_get_contents($root . '/config.php'), "\$u5trusteddeviceenabled = 'yes'") !== false, 'Enable flag missing.');
    u5RolloutTestAssert(strpos(file_get_contents($root . '/config.php'), "array('owner@example.test' => 1)") !== false, 'Exact account quota missing.');
    u5RolloutTestAssert(is_file($root . '/trusteddevice.inc.php') && is_file($root . '/u5admin/trusteddevice.php'), 'New files missing.');
}
u5RolloutTestAssert(strpos(file_get_contents($oidc . '/login.inc.php'), 'u5ValidateTextRequest') !== false, 'OIDC validation was lost.');
u5RolloutTestAssert(strpos(file_get_contents($oidc . '/login.inc.php'), 'u5OidcRequired') !== false, 'OIDC frontend guard was lost.');
u5RolloutTestAssert(strpos(file_get_contents($oidc . '/u5admin/usercheck.inc.php'), 'u5OidcEnabled') !== false, 'OIDC backend guard was lost.');
u5RolloutTestAssert(is_file($backup . '/manifest.json'), 'Backup manifest missing.');

// Updating the feature must not overwrite deliberately configured account limits.
$configured = "\$u5trusteddeviceusers = array('owner@example.test' => 3, 'other@example.test' => 2);";
$path = $oidc . '/config.php';
file_put_contents($path, str_replace("\$u5trusteddeviceusers = array('owner@example.test' => 1);", $configured, file_get_contents($path)));
$again = array_merge($common, array('--apply', '--backup-root=' . $tmp . '/backup-again'));
u5RolloutTestAssert(u5RolloutTestRun($again, $reapplied) === 0, 'Repeat apply failed: ' . $reapplied);
u5RolloutTestAssert(strpos(file_get_contents($path), $configured) !== false, 'Code rollout overwrote configured quotas.');

u5RolloutTestRemove($tmp);
u5RolloutTestRemove($backup);
echo "trusted-device-rollout-regression: ok\n";

