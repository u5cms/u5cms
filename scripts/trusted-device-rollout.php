<?php
// Safe fleet rollout for the optional trusted-browser session hooks.
// Dry-run is the default. --apply requires an exact root count and a new backup root.

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

function u5RolloutFail($message, $code = 2) {
    fwrite(STDERR, "u5-trusted-rollout: " . $message . "\n");
    exit($code);
}

function u5RolloutRead($path) {
    $content = @file_get_contents($path);
    if ($content === false) throw new RuntimeException('Cannot read ' . $path);
    return $content;
}

function u5RolloutSessionBlock($backend, $oidc) {
    $helper = $backend
        ? "dirname(__DIR__) . '/trusteddevice.inc.php'"
        : "__DIR__ . '/trusteddevice.inc.php'";
    $validate = $oidc ? "        u5ValidateTextRequest(\$_COOKIE,array('u','p'));\n" : '';
    $cookieCondition = $oidc ? ($backend ? 'u5OidcEnabled()' : 'u5OidcRequired()') : '';
    $block = "if (\$usesessioninsteadofbasicauth != 'no') {\n"
        . "    require_once " . $helper . ";\n"
        . "    \$u5TrustedDeviceSession = u5TrustedDeviceResume();\n"
        . "    if (is_array(\$u5TrustedDeviceSession)) {\n"
        . "        \$_SERVER['PHP_AUTH_USER'] = \$u5TrustedDeviceSession['username'];\n"
        . "        \$_SERVER['PHP_AUTH_PW'] = \$u5TrustedDeviceSession['password_cookie'];\n"
        . "        \$_COOKIE['u'] = \$_SERVER['PHP_AUTH_USER'];\n"
        . "        \$_COOKIE['p'] = \$_SERVER['PHP_AUTH_PW'];\n"
        . "    } else {\n"
        . $validate
        . "        \$_SERVER['PHP_AUTH_USER'] = \$_COOKIE['u'] ?? '';\n"
        . "        \$_SERVER['PHP_AUTH_PW'] = \$_COOKIE['p'] ?? '';\n"
        . "    }\n";
    if ($oidc) $block .= "    if (!" . $cookieCondition . ") {\n";
    $indent = $oidc ? '    ' : '';
    $block .= $indent . "    setcookie('u', (\$_SERVER['PHP_AUTH_USER'] ?? ''), 0, '/', '', \$httpsisinuse, true);\n"
        . $indent . "    setcookie('p', (\$_SERVER['PHP_AUTH_PW'] ?? ''), 0, '/', '', \$httpsisinuse, true);\n";
    if ($oidc) $block .= "    }\n";
    return $block . "}";
}

// Keep byte positions in the original PHP source. Site configuration must never be executed here.
function u5RolloutPhpTokens($content) {
    $tokens = array();
    $offset = 0;
    foreach (token_get_all($content) as $token) {
        if (!is_array($token)) $token = array(null, $token);
        list($type, $text) = $token;
        if (!in_array($type, array(T_COMMENT, T_DOC_COMMENT, T_WHITESPACE), true)) {
            $tokens[] = array('type' => $type, 'text' => $text, 'offset' => $offset);
        }
        $offset += strlen($text);
    }
    return $tokens;
}

// Patch just the action and its matching navigation script. Keep local form attributes.
function u5RolloutPatchBackendForm($content) {
    $error = 'Cannot update the customised backend login form automatically.';
    $formPattern = '~<form\b(?=[^<>]*\sname\s*=\s*"form"(?=\s|/?>))[^<>]*>~i';
    if (preg_match_all($formPattern, $content, $forms, PREG_OFFSET_CAPTURE) !== 1) throw new RuntimeException($error);
    list($form, $formOffset) = $forms[0][0];
    // The replacement below is a PHP single-quoted string concatenation in the standard prompt.
    $inPrompt = false;
    foreach (u5RolloutPhpTokens($content) as $token) {
        if ($token['offset'] > $formOffset) break;
        if ($token['offset'] + strlen($token['text']) > $formOffset) {
            $inPrompt = $token['type'] === T_CONSTANT_ENCAPSED_STRING && $token['text'][0] === "'";
            break;
        }
    }
    if (!$inPrompt || preg_match_all('~\saction\s*=~i', $form) !== 1
        || preg_match('~\saction\s*=\s*"([^"]*)"~i', $form, $actions, PREG_OFFSET_CAPTURE) !== 1) {
        throw new RuntimeException($error);
    }
    list($action, $actionOffset) = $actions[1];
    $oldAction = '../loginsave.php?u=u5admin';
    $newAction = <<<'FORM'
../loginsave.php?u='.(basename($_SERVER['SCRIPT_NAME'] ?? '') === 'trusteddevice.php' ? 'u5admin/trusteddevice.php' : 'u5admin').'
FORM;
    if ($action !== $oldAction && $action !== $newAction) throw new RuntimeException($error);
    $goto = 'if(gotopage)document.form.action="../loginsave.php?u="+escape(location.href);';
    $close = 'else if(top===self && location.href.indexOf(".php")>0&&location.href.indexOf("index.php")<0)self.close();';
    $previous = 'if(location.pathname.endsWith("/u5admin/trusteddevice.php"))document.form.action="../loginsave.php?u=u5admin/trusteddevice.php";';
    $guard = 'if(document.form.getAttribute("action")==="../loginsave.php?u=u5admin") {';
    $pair = preg_quote($goto, '~') . '\s*' . preg_quote($close, '~');
    $navigation = '~<script>\s*(' . preg_quote($guard, '~') . '\s*' . $pair . '\s*\}'
        . '|(?:' . preg_quote($previous, '~') . '\s*else )?' . $pair . ')(?=\s*' . preg_quote('if(top!=self)top.location.href=location.href;', '~') . ')~';
    if (preg_match_all($navigation, $content, $blocks, PREG_OFFSET_CAPTURE) !== 1) throw new RuntimeException($error);
    list($block, $blockOffset) = $blocks[1][0];
    if ($blockOffset <= $formOffset + strlen($form)) throw new RuntimeException($error);
    if (strpos($block, $guard) !== 0) {
        $newline = strpos($block, "\r\n") === false ? "\n" : "\r\n";
        $replacement = $guard . $newline . '                ' . $goto . $newline . '                ' . $close . $newline . '            }';
        $content = substr_replace($content, $replacement, $blockOffset, strlen($block));
    }
    return $action === $newAction ? $content : substr_replace($content, $newAction, $formOffset + $actionOffset, strlen($action));
}

function u5RolloutPatchLogin($content, $backend) {
    if ($backend) $content = u5RolloutPatchBackendForm($content);
    if (strpos($content, 'u5TrustedDeviceResume') !== false) return $content;
    $needle = "if (\$usesessioninsteadofbasicauth != 'no') {";
    $start = strpos($content, $needle);
    $end = $start === false ? false : strpos($content, "\$_SERVER['PHP_AUTH_USER'] = trim", $start);
    if ($start === false || $end === false) throw new RuntimeException('Session block boundary not found.');
    $old = substr($content, $start, $end - $start);
    if (strpos($old, "\$_COOKIE['u']") === false || strpos($old, 'setcookie') === false) {
        throw new RuntimeException('Unexpected session block.');
    }
    $oidc = strpos($old, 'u5Oidc') !== false || strpos($old, 'u5ValidateTextRequest') !== false;
    return substr($content, 0, $start) . u5RolloutSessionBlock($backend, $oidc) . "\n\n" . substr($content, $end);
}

function u5RolloutPatchLogout($content) {
    if (strpos($content, 'u5TrustedDeviceRevokeCurrent') !== false) return $content;
    $pattern = "~(require(?:_once)?\s*\(\s*['\"]connect\\.inc\\.php['\"]\s*\)\s*;)~";
    $replacement = "$1\nrequire_once __DIR__ . '/trusteddevice.inc.php';\nu5TrustedDeviceRevokeCurrent();";
    $patched = preg_replace($pattern, $replacement, $content, 1, $count);
    if ($patched === null || $count !== 1) throw new RuntimeException('Logout connection hook not found.');
    return $patched;
}

function u5RolloutPatchConfig($content, $user) {
    $enabled = "\$u5trusteddeviceenabled = 'yes';";
    $users = "\$u5trusteddeviceusers = array('" . str_replace(array('\\', "'"), array('\\\\', "\\'"), $user) . "' => 1);";
    $tokens = u5RolloutPhpTokens($content);
    $assignments = array('$u5trusteddeviceenabled' => array(), '$u5trusteddeviceusers' => array(), '$sticksessiontoip' => array());
    $hasTrustedSettings = false;
    foreach ($tokens as $index => $token) {
        if ($token['type'] !== T_VARIABLE || !isset($assignments[$token['text']])) continue;
        if ($token['text'] !== '$sticksessiontoip') $hasTrustedSettings = true;
        if (($tokens[$index + 1]['text'] ?? '') === '=' && $tokens[$index + 1]['type'] === null) {
            $assignments[$token['text']][] = $index;
        }
    }
    $flags = $assignments['$u5trusteddeviceenabled'];
    $quotas = $assignments['$u5trusteddeviceusers'];
    if ($hasTrustedSettings) {
        if (count($flags) !== 1 || count($quotas) !== 1) throw new RuntimeException('Trusted-device config is ambiguous.');
        $value = $tokens[$flags[0] + 2] ?? null;
        if (($value['type'] ?? null) !== T_CONSTANT_ENCAPSED_STRING || ($tokens[$flags[0] + 3]['text'] ?? '') !== ';') {
            throw new RuntimeException('The trusted-device switch is not a simple quoted setting.');
        }
        // Preserve the complete quota expression, quote style, spacing and comments.
        $opening = strpos($value['text'], substr($value['text'], -1));
        return substr_replace($content, 'yes', $value['offset'] + $opening + 1, strlen($value['text']) - $opening - 2);
    }
    $anchors = $assignments['$sticksessiontoip'];
    if (count($anchors) !== 1) throw new RuntimeException('Session config anchor not found or ambiguous.');
    // Older sites may compute this unrelated setting. Insert after its complete statement.
    $offset = null;
    $depth = 0;
    for ($i = $anchors[0] + 2, $count = count($tokens); $i < $count; $i++) {
        $token = $tokens[$i];
        $symbol = $token['text'];
        if ($token['type'] === T_CLOSE_TAG) break;
        if ($token['type'] !== null) {
            if ($token['type'] === T_CURLY_OPEN || $token['type'] === T_DOLLAR_OPEN_CURLY_BRACES
                || ($symbol === '#[' && token_name($token['type']) === 'T_ATTRIBUTE')) $depth++;
            continue;
        }
        if ($symbol === ';' && $depth === 0) {
            $offset = $token['offset'] + 1;
            break;
        }
        if (in_array($symbol, array('(', '[', '{'), true)) $depth++;
        if (in_array($symbol, array(')', ']', '}'), true)) $depth--;
        if ($depth < 0) break;
    }
    if ($offset === null) throw new RuntimeException('Cannot locate the end of the session setting.');
    // Keep an ordinary trailing comment attached to its setting without crossing a PHP closing tag.
    if (preg_match('~\G[ \t]*(?://|\#)(?:(?!\?>)[^\r\n])*(?=\r?\n|\z)~', $content, $comment, 0, $offset)) $offset += strlen($comment[0]);
    $insert = "\n\n   // Optional trusted browser: HTTPS-only, host- and installation-bound,\n"
        . "   // revocable, with explicit per-account trusted-browser limits.\n"
        . "   // Register or revoke at /u5admin/trusteddevice.php after a normal login.\n"
        . "   " . $enabled . "\n"
        . "   " . $users;
    return substr($content, 0, $offset) . $insert . substr($content, $offset);
}

function u5RolloutLint($content, $label) {
    $tmp = tempnam(sys_get_temp_dir(), 'u5td-lint-');
    if ($tmp === false || file_put_contents($tmp, $content, LOCK_EX) === false) throw new RuntimeException('Cannot create lint file.');
    $output = array();
    $code = 0;
    exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($tmp) . ' 2>&1', $output, $code);
    @unlink($tmp);
    if ($code !== 0) throw new RuntimeException('PHP lint failed for ' . $label . ': ' . implode(' ', $output));
}

function u5RolloutStat($path, $referencePath = null) {
    $source = is_file($path) ? $path : $referencePath;
    $stat = $source === null ? false : @stat($source);
    if ($stat === false) throw new RuntimeException('Cannot stat ' . ($source ?? $path));
    return array('mode' => $stat['mode'] & 0777, 'uid' => $stat['uid'], 'gid' => $stat['gid']);
}

function u5RolloutAtomicWrite($path, $content, $stat) {
    $tmp = $path . '.u5td-' . bin2hex(random_bytes(6));
    if (file_put_contents($tmp, $content, LOCK_EX) !== strlen($content)) {
        @unlink($tmp);
        throw new RuntimeException('Cannot write temporary file for ' . $path);
    }
    if (!@chmod($tmp, $stat['mode']) || !@chown($tmp, $stat['uid']) || !@chgrp($tmp, $stat['gid'])) {
        @unlink($tmp);
        throw new RuntimeException('Cannot preserve ownership for ' . $path);
    }
    if (!@rename($tmp, $path)) {
        @unlink($tmp);
        throw new RuntimeException('Cannot replace ' . $path);
    }
    clearstatcache(true, $path);
    if (!hash_equals(hash('sha256', $content), hash_file('sha256', $path))) throw new RuntimeException('Verification failed for ' . $path);
}

function u5RolloutBackupPath($backupRoot, $absolutePath) {
    return rtrim($backupRoot, '/') . '/' . ltrim($absolutePath, '/');
}

$options = getopt('', array('apply', 'scan-root:', 'source-root:', 'backup-root:', 'user:', 'expect-roots:'));
$apply = array_key_exists('apply', $options);
$testMode = getenv('U5_TRUSTED_ROLLOUT_TEST') === '1';
$scanRoot = rtrim((string)($options['scan-root'] ?? '/var/www/vhosts'), '/');
$sourceRoot = realpath((string)($options['source-root'] ?? ''));
$backupRoot = rtrim((string)($options['backup-root'] ?? ''), '/');
$user = strtolower(trim((string)($options['user'] ?? '')));
$expectedRoots = isset($options['expect-roots']) ? (int)$options['expect-roots'] : 0;

if ($sourceRoot === false || !is_dir($sourceRoot)) u5RolloutFail('A valid --source-root is required.');
if ($user === '' || filter_var($user, FILTER_VALIDATE_EMAIL) === false) u5RolloutFail('A valid exact --user is required.');
if ($apply) {
    if ($expectedRoots < 1) u5RolloutFail('--apply requires --expect-roots.');
    if ($backupRoot === '' || (!$testMode && strpos($backupRoot, '/root/u5trusteddevice-backup-') !== 0)) u5RolloutFail('--apply requires a dedicated /root/u5trusteddevice-backup-* path.');
    if (file_exists($backupRoot)) u5RolloutFail('Backup root already exists: ' . $backupRoot);
}

$sourceHelper = u5RolloutRead($sourceRoot . '/trusteddevice.inc.php');
$sourcePage = u5RolloutRead($sourceRoot . '/u5admin/trusteddevice.php');
if (strpos($sourceHelper, 'function u5TrustedDeviceResume') === false || strpos($sourcePage, 'u5TrustedDeviceIssue') === false) {
    u5RolloutFail('Source root does not contain the verified trusted-device files.');
}

$roots = array();
foreach (glob($scanRoot . '/*/*', GLOB_ONLYDIR) ?: array() as $candidate) {
    $root = realpath($candidate);
    if ($root === false) continue;
    $required = array('/login.inc.php', '/u5admin/usercheck.inc.php', '/logout.php', '/config.php');
    $valid = true;
    foreach ($required as $relative) if (!is_file($root . $relative)) { $valid = false; break; }
    if ($valid) $roots[$root] = true;
}
$roots = array_keys($roots);
sort($roots, SORT_STRING);
if ($expectedRoots > 0 && count($roots) !== $expectedRoots) {
    u5RolloutFail('Root-count mismatch: found ' . count($roots) . ', expected ' . $expectedRoots . '. No files changed.');
}

$plans = array();
$errors = array();
foreach ($roots as $root) {
    try {
        $old = array(
            'login.inc.php' => u5RolloutRead($root . '/login.inc.php'),
            'u5admin/usercheck.inc.php' => u5RolloutRead($root . '/u5admin/usercheck.inc.php'),
            'logout.php' => u5RolloutRead($root . '/logout.php'),
            'config.php' => u5RolloutRead($root . '/config.php'),
            'trusteddevice.inc.php' => is_file($root . '/trusteddevice.inc.php') ? u5RolloutRead($root . '/trusteddevice.inc.php') : null,
            'u5admin/trusteddevice.php' => is_file($root . '/u5admin/trusteddevice.php') ? u5RolloutRead($root . '/u5admin/trusteddevice.php') : null
        );
        $new = array(
            'login.inc.php' => u5RolloutPatchLogin($old['login.inc.php'], false),
            'u5admin/usercheck.inc.php' => u5RolloutPatchLogin($old['u5admin/usercheck.inc.php'], true),
            'logout.php' => u5RolloutPatchLogout($old['logout.php']),
            'config.php' => u5RolloutPatchConfig($old['config.php'], $user),
            'trusteddevice.inc.php' => $sourceHelper,
            'u5admin/trusteddevice.php' => $sourcePage
        );
        $changed = array();
        foreach ($new as $relative => $content) {
            u5RolloutLint($content, $root . '/' . $relative);
            if ($old[$relative] === null || !hash_equals(hash('sha256', $old[$relative]), hash('sha256', $content))) $changed[] = $relative;
        }
        $plans[] = array('root' => $root, 'old' => $old, 'new' => $new, 'changed' => $changed);
    } catch (Throwable $exception) {
        $errors[] = $root . ': ' . $exception->getMessage();
    }
}
if ($errors) u5RolloutFail("Preflight failed; no files changed.\n" . implode("\n", $errors));

$changedRoots = 0;
$changedFiles = 0;
foreach ($plans as $plan) {
    if ($plan['changed']) $changedRoots++;
    $changedFiles += count($plan['changed']);
}
if (!$apply) {
    echo 'u5-trusted-rollout: ' . json_encode(array('mode'=>'dry-run','roots'=>count($roots),'changed_roots'=>$changedRoots,'changed_files'=>$changedFiles,'failed'=>0), JSON_UNESCAPED_SLASHES) . "\n";
    exit(0);
}

if (!@mkdir($backupRoot, 0700, true)) u5RolloutFail('Cannot create backup root.');
@chmod($backupRoot, 0700);
$written = array();
try {
    foreach ($plans as $plan) {
        foreach ($plan['changed'] as $relative) {
            $target = $plan['root'] . '/' . $relative;
            if ($plan['old'][$relative] !== null) {
                $backup = u5RolloutBackupPath($backupRoot, $target);
                if (!is_dir(dirname($backup)) && !@mkdir(dirname($backup), 0700, true)) throw new RuntimeException('Cannot create backup directory.');
                if (!@copy($target, $backup)) throw new RuntimeException('Cannot back up ' . $target);
                @chmod($backup, 0600);
            }
        }
    }
    foreach ($plans as $planIndex => $plan) {
        foreach ($plan['changed'] as $relative) {
            $path = $plan['root'] . '/' . $relative;
            $reference = strpos($relative, 'u5admin/') === 0
                ? $plan['root'] . '/u5admin/usercheck.inc.php'
                : $plan['root'] . '/login.inc.php';
            $stat = u5RolloutStat($path, $reference);
            u5RolloutAtomicWrite($path, $plan['new'][$relative], $stat);
            $written[] = array($planIndex, $relative, $stat);
        }
    }
    $manifest = array('created_at'=>gmdate('c'),'scan_root'=>$scanRoot,'source_root'=>$sourceRoot,'user'=>$user,'roots'=>count($roots),'changed_roots'=>$changedRoots,'changed_files'=>$changedFiles);
    if (file_put_contents($backupRoot . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n", LOCK_EX) === false) throw new RuntimeException('Cannot write backup manifest.');
    @chmod($backupRoot . '/manifest.json', 0600);
} catch (Throwable $exception) {
    for ($i = count($written) - 1; $i >= 0; $i--) {
        list($planIndex, $relative, $stat) = $written[$i];
        $plan = $plans[$planIndex];
        $path = $plan['root'] . '/' . $relative;
        if ($plan['old'][$relative] === null) @unlink($path);
        else {
            try { u5RolloutAtomicWrite($path, $plan['old'][$relative], $stat); } catch (Throwable $ignored) {}
        }
    }
    u5RolloutFail('Apply failed and rollback was attempted: ' . $exception->getMessage());
}

echo 'u5-trusted-rollout: ' . json_encode(array('mode'=>'apply','roots'=>count($roots),'changed_roots'=>$changedRoots,'changed_files'=>$changedFiles,'failed'=>0,'backup'=>$backupRoot), JSON_UNESCAPED_SLASHES) . "\n";

