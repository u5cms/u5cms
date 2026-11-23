<?php
require_once('connect.inc.php');
require_once dirname(__DIR__) . '/trusteddevice.inc.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Content-Security-Policy: default-src \'none\'; style-src \'unsafe-inline\'; form-action \'self\'; frame-ancestors \'none\'');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');

$trustedUser = $_SERVER['PHP_AUTH_USER'] ?? '';
if (!u5TrustedDeviceEnabled() || !u5TrustedDeviceAllowedUser($trustedUser)) {
    http_response_code(404);
    exit('Not found.');
}

$message = '';
$csrf = u5TrustedDeviceCsrfToken($trustedUser, $_SERVER['PHP_AUTH_PW'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!u5TrustedDeviceOriginMatches()
        || !isset($_POST['csrf'])
        || !is_string($_POST['csrf'])
        || !hash_equals($csrf, $_POST['csrf'])) {
        http_response_code(403);
        exit('Invalid request.');
    }
    if (($_POST['action'] ?? '') === 'enrol') {
        $result = u5TrustedDeviceIssue($trustedUser);
        if ($result === 'ok') $message = 'This browser is now the trusted browser.';
        elseif ($result === 'exists') $message = 'Another browser is already registered. Revoke it before registering a replacement.';
        else $message = 'The trusted browser could not be registered.';
    } elseif (($_POST['action'] ?? '') === 'revoke') {
        u5TrustedDeviceRevokeCurrent();
        $message = 'This browser has been revoked.';
    }
}

$status = u5TrustedDeviceStatus($trustedUser);
function u5TrustedDevicePageEscape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML401, 'WINDOWS-1252');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="WINDOWS-1252">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Trusted browser</title>
<style>
body{font:16px/1.5 system-ui,sans-serif;max-width:660px;margin:60px auto;padding:0 24px;color:#17202a}h1{font-size:28px}button{font:inherit;padding:10px 16px;border:0;border-radius:4px;background:#1769aa;color:#fff;cursor:pointer}.danger{background:#9b1c1c}.notice{padding:12px 14px;background:#eef5fa;border-left:4px solid #1769aa}code{overflow-wrap:anywhere}
</style>
</head>
<body>
<h1>Trusted browser</h1>
<p>Account: <code><?php echo u5TrustedDevicePageEscape($trustedUser); ?></code></p>
<?php if ($message !== '') { ?><p class="notice"><?php echo u5TrustedDevicePageEscape($message); ?></p><?php } ?>
<?php if ($status === 'current') { ?>
<p>This is the one trusted browser for this account and this u5CMS installation.</p>
<form method="post"><input type="hidden" name="csrf" value="<?php echo u5TrustedDevicePageEscape($csrf); ?>"><input type="hidden" name="action" value="revoke"><button class="danger" type="submit">Revoke this browser</button></form>
<?php } elseif ($status === 'other') { ?>
<p>Another browser is already registered. This browser cannot replace it.</p>
<?php } else { ?>
<p>Register only a dedicated browser that is controlled by the intended operator. One active browser is allowed for this account.</p>
<form method="post"><input type="hidden" name="csrf" value="<?php echo u5TrustedDevicePageEscape($csrf); ?>"><input type="hidden" name="action" value="enrol"><button type="submit">Trust this browser</button></form>
<?php } ?>
</body>
</html>
