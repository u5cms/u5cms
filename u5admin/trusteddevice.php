<?php
require_once('connect.inc.php');
require_once dirname(__DIR__) . '/trusteddevice.inc.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Content-Security-Policy: default-src \'none\'; style-src \'unsafe-inline\'; form-action \'self\'; frame-ancestors \'none\'');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');

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
        if ($result === 'ok') $message = 'This browser is now trusted.';
        elseif ($result === 'exists') $message = 'This browser is already registered.';
        elseif ($result === 'limit') $message = 'The browser limit has been reached. Revoke a registered browser to free a place.';
        else $message = 'The trusted browser could not be registered.';
    } elseif (($_POST['action'] ?? '') === 'revoke') {
        $selector = $_POST['selector'] ?? '';
        $message = u5TrustedDeviceRevoke($trustedUser, $selector)
            ? 'The browser has been revoked.' : 'The browser could not be revoked.';
    }
}

$browsers = u5TrustedDeviceBrowsers($trustedUser);
$limit = u5TrustedDeviceLimit($trustedUser);
$current = false;
if (is_array($browsers)) foreach ($browsers as $browser) if (u5TrustedDeviceIsCurrent($browser)) $current = true;
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
body{font:16px/1.5 system-ui,sans-serif;max-width:760px;margin:60px auto;padding:0 24px;color:#17202a}h1{font-size:28px}button{font:inherit;padding:10px 16px;border:0;border-radius:4px;background:#1769aa;color:#fff;cursor:pointer}.danger{background:#9b1c1c}.notice{padding:12px 14px;background:#eef5fa;border-left:4px solid #1769aa}code{overflow-wrap:anywhere}table{width:100%;border-collapse:collapse;margin:24px 0}th,td{text-align:left;padding:10px 6px;border-bottom:1px solid #ddd;vertical-align:top}.table-scroll{overflow-x:auto}
</style>
</head>
<body>
<h1>Trusted browsers</h1>
<p>Account: <code><?php echo u5TrustedDevicePageEscape($trustedUser); ?></code></p>
<?php if ($message !== '') { ?><p class="notice"><?php echo u5TrustedDevicePageEscape($message); ?></p><?php } ?>
<?php if ($browsers === false) { ?>
<p>Browser registrations could not be loaded. Please try again.</p>
<?php } else { ?>
<p><?php echo count($browsers); ?> of <?php echo $limit; ?> permitted browsers registered for this account and this u5CMS installation.</p>
<?php if ($browsers) { ?>
<div class="table-scroll"><table>
<thead><tr><th scope="col">Browser</th><th scope="col">Registered (UTC)</th><th scope="col">Last used (UTC)</th><th scope="col">Action</th></tr></thead>
<tbody>
<?php foreach ($browsers as $browser) { ?>
<tr><th scope="row">Browser <?php echo (int)$browser['slot']; ?><?php if (u5TrustedDeviceIsCurrent($browser)) echo ' (this browser)'; ?></th>
<td><?php echo gmdate('Y-m-d H:i', (int)$browser['created_at']); ?></td>
<td><?php echo gmdate('Y-m-d H:i', (int)$browser['last_used_at']); ?></td>
<td><form method="post"><input type="hidden" name="csrf" value="<?php echo u5TrustedDevicePageEscape($csrf); ?>"><input type="hidden" name="action" value="revoke"><input type="hidden" name="selector" value="<?php echo u5TrustedDevicePageEscape($browser['selector']); ?>"><button class="danger" type="submit">Revoke</button></form></td></tr>
<?php } ?>
</tbody></table></div>
<?php } ?>
<?php if ($current) { ?>
<p>This browser is already registered.</p>
<?php } elseif (count($browsers) >= $limit) { ?>
<p>The browser limit has been reached. Revoke a registered browser to free a place.</p>
<?php } else { ?>
<p>Register only a dedicated browser that is controlled by the intended operator.</p>
<form method="post"><input type="hidden" name="csrf" value="<?php echo u5TrustedDevicePageEscape($csrf); ?>"><input type="hidden" name="action" value="enrol"><button type="submit">Trust this browser</button></form>
<?php } ?>
<?php } ?>
</body>
</html>
