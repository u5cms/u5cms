<?php
// OIDC is opt-in. Loading this file alone starts no session and changes no login behavior.
function u5OidcEnabled()
{
    return isset($GLOBALS['u5oidc']) && is_array($GLOBALS['u5oidc']) && ($GLOBALS['u5oidc']['enabled'] ?? false) === true;
}

function u5OidcRequired($target = null, $backend = false)
{
    if (!u5OidcEnabled()) return false;
    list($client) = u5OidcServices();
    $request = $_SERVER['REQUEST_URI'] ?? '';
    // Actual backend entry points cannot be turned into local logins by a target parameter.
    $path = parse_url($request, PHP_URL_PATH) ?? '';
    $admin = $client->basePath().'u5admin';
    if ($path === $admin || strpos($path, $admin.'/') === 0) $backend = true;
    if ($target === null) {
        $script = basename($path);
        $isLogin = $script === 'loginsave.php' || ($_GET['c'] ?? '') === '_login';
        $target = $isLogin ? u5OidcTargetParameter($request) : $request;
    }
    return $client->requiresFederation($target, $backend);
}

function u5OidcTargetParameter($default = '')
{
    // Frontend san.inc.php HTML-escapes GET values. URL policy must use the original URL.
    parse_str($_SERVER['QUERY_STRING'] ?? '', $query);
    return array_key_exists('u', $query) ? $query['u'] : ($_GET['u'] ?? $default);
}

function u5OidcServices()
{
    static $services;
    if (!u5OidcEnabled()) throw new RuntimeException('OIDC is disabled.');
    if (isset($services)) return $services;
    if (PHP_VERSION_ID < 80000 || !extension_loaded('curl') || !extension_loaded('openssl') || !extension_loaded('mbstring')) {
        throw new RuntimeException('OIDC requires PHP 8.0+, cURL, OpenSSL and mbstring.');
    }
    if (($GLOBALS['usesessioninsteadofbasicauth'] ?? '') === 'no') throw new RuntimeException('OIDC requires cookie-based CMS authentication.');
    if (!empty($GLOBALS['u5samlsalt'])) throw new RuntimeException('Enable either SAML or OIDC, not both.');
    require_once __DIR__.'/oidc/vendor/autoload.php';
    require_once __DIR__.'/src/Oidc/Client.php';
    require_once __DIR__.'/src/Oidc/Session.php';
    $config = $GLOBALS['u5oidc'];
    // Keep existing site policy when switching protocols and omitting the new equivalents.
    foreach (array('frontend'=>'u5samlinfrontend',
        'frontend_enforced_unless_target_contains'=>'u5samlinfrontendyesenforcedifloginformsgetudoesnotcontain',
        'login_info'=>'u5samllogininfo','not_a_member_message'=>'u5notamembermessage',
        'logout_result_page'=>'u5samllogoutresultpage') as $key=>$legacy) {
        if (!array_key_exists($key, $config) && isset($GLOBALS[$legacy])) $config[$key] = $GLOBALS[$legacy];
    }
    if (!array_key_exists('auto_intranet_enrollment', $config) && isset($GLOBALS['u5samlautointranetenrollment'])) {
        $config['auto_intranet_enrollment'] = $GLOBALS['u5samlautointranetenrollment'] === 'yes';
    }
    $client = new \U5cms\Oidc\Client($config);
    $secret = ($GLOBALS['sessioncookiehashsalt'] ?? '').'\0'.($GLOBALS['password'] ?? '');
    $session = new \U5cms\Oidc\Session($client, $secret);
    return $services = array($client, $session);
}

function u5OidcIdentity()
{
    if (!u5OidcEnabled()) return null;
    if (!array_key_exists('u5OidcIdentity', $GLOBALS)) {
        list($client, $session) = u5OidcServices();
        $GLOBALS['u5OidcIdentity'] = $session->identity();
    }
    return $GLOBALS['u5OidcIdentity'];
}

function u5OidcClaim($name)
{
    if (!u5OidcRequired()) return null;
    $identity = u5OidcIdentity();
    return $identity !== null ? ($identity['claims'][$name] ?? null) : null;
}

/** Convert standard UTF-8 OIDC text to native CMS bytes, with numeric entity fallback. */
function u5OidcNativeText($value)
{
    if ($value === null) return '';
    require_once __DIR__.'/u5admin/u5idn.inc.php';
    if (!is_string($value) || !mb_check_encoding($value, 'UTF-8')) throw new RuntimeException('Invalid UTF-8 OIDC text.');
    return html_entity_decode(u5allnument($value), ENT_COMPAT, 'WINDOWS-1252');
}

function u5OidcNormalizeUsername($value)
{
    require_once __DIR__.'/u5admin/u5idn.inc.php';
    // Preserve the CMS encoding boundary without changing its global mbstring settings.
    $native = u5OidcNativeText(mb_strtolower(u5toutf8($value), 'UTF-8'));
    $reporting = error_reporting();
    error_reporting($reporting & ~E_DEPRECATED & ~E_USER_DEPRECATED);
    try { return u5flatidn($native); }
    finally { error_reporting($reporting); }
}

function u5OidcBindingStore()
{
    static $store;
    if ($store !== null) return $store;
    list($client) = u5OidcServices();
    if (!extension_loaded('mysqli')) throw new RuntimeException('OIDC verified_email requires mysqli.');
    require_once __DIR__.'/src/Oidc/Bindings.php';
    try {
        $connection = mysqli_init();
        if ($connection === false) throw new RuntimeException('OIDC binding database is unavailable.');
        $connection->options(MYSQLI_OPT_CONNECT_TIMEOUT,5);
        if (defined('MYSQLI_OPT_READ_TIMEOUT')) $connection->options(MYSQLI_OPT_READ_TIMEOUT,5);
        if (!$connection->real_connect($GLOBALS['host'], $GLOBALS['username'], $GLOBALS['password'], $GLOBALS['db']) ||
            !$connection->set_charset('latin1')) throw new RuntimeException('OIDC binding database is unavailable.');
    } catch (Throwable $error) { throw new RuntimeException('OIDC binding database is unavailable.'); }
    return $store = new \U5cms\Oidc\Bindings($connection, $client->config()['issuer'], $client->config()['client_id']);
}

/** Validate UTF-8 email syntax without changing the stored CMS identity key. */
function u5OidcValidEmail($email)
{
    if (!is_string($email) || !mb_check_encoding($email,'UTF-8') || preg_match('/[\x00-\x20\x7F?:;,\\\\]/',$email) || substr_count($email,'@') !== 1) return false;
    list($local,$domain)=explode('@',$email,2);
    if (function_exists('idn_to_ascii')) $domain=idn_to_ascii($domain,IDNA_DEFAULT,INTL_IDNA_VARIANT_UTS46);
    elseif (preg_match('/[^\x00-\x7F]/',$domain)) return false;
    return $domain && filter_var($local.'@'.$domain, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE) !== false;
}

function u5OidcResolveIdentity(array $identity)
{
    if ($identity['username'] !== null) { unset($identity['email']); return $identity; }
    list($client) = u5OidcServices();
    if ($client->config()['identity_mode'] !== 'verified_email' || ($identity['mapping'] ?? '') !== 'verified_email') {
        throw new RuntimeException('OIDC identity has not been mapped.');
    }
    require_once __DIR__.'/u5admin/u5idn.inc.php';
    $email = $identity['email'] ?? '';
    if (!u5OidcValidEmail($email)) {
        throw new RuntimeException('Invalid verified OIDC email.');
    }
    $user = u5OidcNormalizeUsername(u5OidcNativeText(mb_strtolower($email,'UTF-8')));
    foreach ($client->config()['subjects'] as $reserved) {
        if (u5OidcNormalizeUsername($reserved) === $user) throw new RuntimeException('OIDC email is reserved by an explicit subject mapping.');
    }
    $identity['username'] = u5OidcBindingStore()->resolve($identity['sub'], $user);
    unset($identity['email']);
    return $identity;
}

/** Supply request-local credentials to existing CMS rights checks; never change passwords. */
function u5OidcApplyCredentials($backend = false)
{
    if (!u5OidcEnabled()) return;
    try {
        if (!u5OidcRequired(null, $backend)) return;
        $identity = u5OidcIdentity();
        if ($identity !== null || ($_GET['c'] ?? '') === '_login') u5OidcHeaders();
        // Ignore all legacy/browser/Basic credentials in an OIDC installation.
        $_COOKIE['u'] = $_COOKIE['p'] = '';
        $_SERVER['PHP_AUTH_USER'] = $_SERVER['PHP_AUTH_PW'] = '';
        if ($identity === null) return;
        $user = u5OidcNormalizeUsername($identity['username']);
        $_COOKIE['u'] = $_SERVER['PHP_AUTH_USER'] = $user;
        // A nonempty session-bound sentinel preserves form tokens without granting any rights.
        $credential = hash('sha256', $identity['sub'].'\0'.$identity['fingerprint']);
        if (function_exists('mysql_query')) {
            $result = mysql_query("SELECT pw FROM accounts WHERE email='".mysql_real_escape_string($user)."' AND TRIM(pw)!=''");
            if ($result === false) throw new RuntimeException('Cannot look up the CMS account.');
            if (mysql_num_rows($result) === 1) $credential = pwdcookieget(mysql_fetch_array($result)['pw']);
            else {
                $page = isset($_GET['n']) && $_GET['n'] !== '' ? $_GET['n'] : ($_GET['c'] ?? '');
                if (!is_string($page)) $page = '';
                if (strpos($page, '!') === 0 || $page === '_searchsi' || basename($_SERVER['SCRIPT_NAME'] ?? '') === 'u5sys._searchsi.php') {
                    $result = mysql_query('SELECT salt FROM intranetsalt LIMIT 1');
                    if ($result === false) throw new RuntimeException('Cannot look up the CMS intranet salt.');
                    if ($row = mysql_fetch_array($result)) $credential = pwdcookie((string)floor(crc32($user.$row['salt'])));
                } elseif ($page !== '') {
                    $result = mysql_query("SELECT logins FROM resources WHERE name='".mysql_real_escape_string($page)."' AND deleted!=1 LIMIT 1");
                    if ($result === false) throw new RuntimeException('Cannot look up CMS page permissions.');
                    if ($row = mysql_fetch_array($result)) {
                        $password = u5OidcPagePassword($row['logins'], $user);
                        if ($password !== null) $credential = pwdcookie($password);
                    }
                }
            }
        }
        $_COOKIE['p'] = $_SERVER['PHP_AUTH_PW'] = $credential;
    } catch (Throwable $error) { u5OidcFailure($error, 503); }
}

function u5OidcPagePassword($logins, $user)
{
    // Mirror login.idn.inc.php's legacy encoding conversion before matching an exact identity.
    $logins = str_replace(array('&#140;','&#156;'), '&oelig;', $logins);
    $logins = u5toutf8($logins);
    foreach (explode(';', $logins) as $entry) {
        $entry = html_entity_decode(html_entity_decode(u5allnument(trim($entry)), ENT_COMPAT, 'WINDOWS-1252'), ENT_COMPAT, 'WINDOWS-1252');
        $parts = explode(':', $entry, 2);
        if (count($parts) === 2 && u5flatidnlower(ltrim($parts[0], '?')) === $user) return $parts[1];
    }
    return null;
}

/** The real file-specific .htpasswd remains the authority, including access revocation. */
function u5OidcPrivateCredentials($passwd)
{
    if (!u5OidcRequired() || ($identity = u5OidcIdentity()) === null) return;
    $user = u5OidcNormalizeUsername($identity['username']);
    $_COOKIE['p'] = $_SERVER['PHP_AUTH_PW'] = '';
    foreach (preg_split('/\r?\n/', $passwd) as $line) {
        $parts = explode(':', trim($line), 2);
        $principal = isset($parts[0]) && mb_check_encoding($parts[0],'UTF-8') ? u5OidcNativeText($parts[0]) : ($parts[0] ?? '');
        if (count($parts) === 2 && $parts[1] !== '' && u5OidcNormalizeUsername($principal) === $user) {
            $_COOKIE['p'] = $_SERVER['PHP_AUTH_PW'] = pwdcookieget($parts[1]);
            return;
        }
    }
}

function u5OidcHeaders()
{
    if (headers_sent()) return;
    header('Cache-Control: no-store');
    header('Pragma: no-cache');
    header('Referrer-Policy: no-referrer');
    header('X-Content-Type-Options: nosniff');
}

function u5OidcFailure($error, $status = 400)
{
    $reference = bin2hex(random_bytes(6));
    // Do not log tokens, callback parameters, subjects, claims, or provider response bodies.
    $reason = $error instanceof RuntimeException ? preg_replace('/[\r\n]/', ' ', $error->getMessage()) : get_class($error);
    error_log('u5CMS OIDC '.$reference.': '.substr($reason,0,180));
    http_response_code($status);
    u5OidcHeaders();
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><html lang="en"><meta name="viewport" content="width=device-width"><title>Login</title><main style="font-family:sans-serif;max-width:38em;margin:8vh auto;padding:1em"><h1>Login could not be completed</h1><p>Please start the login again. If the problem persists, contact the website administrator.</p><p>Reference: '.htmlspecialchars($reference, ENT_QUOTES, 'UTF-8').'</p></main></html>';
    exit;
}

function u5OidcLoginPrompt($target, $label = 'LOGIN')
{
    try {
        list($client) = u5OidcServices();
        $target = $client->returnUrl($target);
        $identity = u5OidcIdentity();
        u5OidcHeaders();
        if ($identity !== null) http_response_code(403);
        $url = $client->basePath().'oidc/login.php?u='.rawurlencode($target).($identity !== null ? '&reauth=1' : '');
        echo '<div style="font-family:sans-serif;text-align:center;padding:2em">';
        if ($identity !== null) echo '<p>'.htmlspecialchars(u5OidcNormalizeUsername($identity['username']),ENT_QUOTES,'WINDOWS-1252').'</p>';
        if ($identity !== null) echo $client->config()['not_a_member_message'] !== '' ?
            $client->config()['not_a_member_message'] : '<p>This identity has no access to the requested area. Contact the website administrator.</p>';
        echo '<a target="_top" href="'.htmlspecialchars($url, ENT_QUOTES, 'WINDOWS-1252').'">'.htmlspecialchars($label, ENT_QUOTES, 'WINDOWS-1252').'</a></div>';
        echo $client->config()['login_info'];
        exit;
    } catch (Throwable $error) { u5OidcFailure($error); }
}
