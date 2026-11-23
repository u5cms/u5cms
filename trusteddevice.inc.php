<?php
// Optional, revocable trusted-browser sessions. Disabled unless explicitly
// enabled in config.php and restricted to an exact account allow-list.

if (!function_exists('u5TrustedDeviceEnabled')) {
    function u5TrustedDeviceEnabled() {
        global $u5trusteddeviceenabled, $usesessioninsteadofbasicauth;
        return isset($u5trusteddeviceenabled)
            && $u5trusteddeviceenabled === 'yes'
            && $usesessioninsteadofbasicauth !== 'no';
    }

    function u5TrustedDeviceNormaliseUser($username) {
        $username = trim((string)$username);
        return function_exists('u5flatidnlower') ? u5flatidnlower($username) : strtolower($username);
    }

    function u5TrustedDeviceAllowedUser($username) {
        global $u5trusteddeviceusers;
        if (!is_array($u5trusteddeviceusers)) return false;
        $candidate = u5TrustedDeviceNormaliseUser($username);
        if ($candidate === '') return false;
        foreach ($u5trusteddeviceusers as $allowed) {
            $allowed = u5TrustedDeviceNormaliseUser($allowed);
            if ($allowed !== '' && hash_equals($allowed, $candidate)) return true;
        }
        return false;
    }

    function u5TrustedDeviceIsHttps() {
        if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') return true;
        if ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443) return true;
        $forwarded = explode(',', (string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
        return strtolower(trim($forwarded[0])) === 'https';
    }

    function u5TrustedDeviceInstallationId() {
        global $mymail, $host, $username, $password, $db;
        return substr(hash('sha256', (string)$mymail."\0".(string)$host."\0".(string)$username."\0".(string)$password."\0".(string)$db), 0, 16);
    }

    function u5TrustedDeviceCookieName() {
        return '__Host-u5td-' . u5TrustedDeviceInstallationId();
    }

    function u5TrustedDeviceCookieParts() {
        $value = (string)($_COOKIE[u5TrustedDeviceCookieName()] ?? '');
        if (!preg_match('/\A([a-f0-9]{32})\.([a-f0-9]{64})\z/D', $value, $match)) return false;
        return array('selector' => $match[1], 'validator' => $match[2], 'value' => $value);
    }

    function u5TrustedDeviceSetCookie($value) {
        if (headers_sent() || !u5TrustedDeviceIsHttps()) return false;
        $ok = setcookie(u5TrustedDeviceCookieName(), (string)$value, array(
            'expires' => time() + 34560000,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ));
        if ($ok) $_COOKIE[u5TrustedDeviceCookieName()] = (string)$value;
        return $ok;
    }

    function u5TrustedDeviceClearCookie() {
        if (!headers_sent()) {
            setcookie(u5TrustedDeviceCookieName(), '', array(
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ));
        }
        unset($_COOKIE[u5TrustedDeviceCookieName()]);
    }

    function u5TrustedDeviceEnsureTable() {
        $sql = "CREATE TABLE IF NOT EXISTS u5trusteddevices ("
             . "installation_id CHAR(16) NOT NULL,"
             . "username VARCHAR(255) NOT NULL,"
             . "selector CHAR(32) NOT NULL,"
             . "validator_hash CHAR(64) NOT NULL,"
             . "account_hash CHAR(64) NOT NULL,"
             . "created_at BIGINT UNSIGNED NOT NULL,"
             . "last_used_at BIGINT UNSIGNED NOT NULL,"
             . "revoked_at BIGINT UNSIGNED NOT NULL DEFAULT 0,"
             . "PRIMARY KEY (installation_id,username),"
             . "UNIQUE KEY u5trusteddevices_selector (installation_id,selector)"
             . ") ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin";
        return @mysql_query($sql) !== false;
    }

    function u5TrustedDeviceAccount($username) {
        $username = u5TrustedDeviceNormaliseUser($username);
        if ($username === '') return false;
        $sql = "SELECT email,pw FROM accounts WHERE TRIM(email)!='' AND TRIM(pw)!=''"
             . " AND email='" . mysql_real_escape_string($username) . "' LIMIT 2";
        $result = @mysql_query($sql);
        if ($result === false || @mysql_num_rows($result) !== 1) return false;
        return mysql_fetch_array($result);
    }

    function u5TrustedDeviceResume() {
        if (!u5TrustedDeviceEnabled() || !u5TrustedDeviceIsHttps()) return false;
        $token = u5TrustedDeviceCookieParts();
        if ($token === false) return false;
        $installation = u5TrustedDeviceInstallationId();
        $sql = "SELECT username,validator_hash,account_hash,last_used_at FROM u5trusteddevices"
             . " WHERE installation_id='" . mysql_real_escape_string($installation) . "'"
             . " AND selector='" . mysql_real_escape_string($token['selector']) . "'"
             . " AND revoked_at=0 LIMIT 1";
        $result = @mysql_query($sql);
        if ($result === false || @mysql_num_rows($result) !== 1) {
            u5TrustedDeviceClearCookie();
            return false;
        }
        $row = mysql_fetch_array($result);
        if (!hash_equals((string)$row['validator_hash'], hash('sha256', $token['validator']))
            || !u5TrustedDeviceAllowedUser($row['username'])) {
            u5TrustedDeviceClearCookie();
            return false;
        }
        $account = u5TrustedDeviceAccount($row['username']);
        if ($account === false || !hash_equals((string)$row['account_hash'], hash('sha256', (string)$account['pw']))) {
            @mysql_query("UPDATE u5trusteddevices SET revoked_at=" . time()
                . " WHERE installation_id='" . mysql_real_escape_string($installation) . "'"
                . " AND selector='" . mysql_real_escape_string($token['selector']) . "'");
            u5TrustedDeviceClearCookie();
            return false;
        }
        if ((int)$row['last_used_at'] < time() - 86400) {
            @mysql_query("UPDATE u5trusteddevices SET last_used_at=" . time()
                . " WHERE installation_id='" . mysql_real_escape_string($installation) . "'"
                . " AND selector='" . mysql_real_escape_string($token['selector']) . "' AND revoked_at=0");
            u5TrustedDeviceSetCookie($token['value']);
        }
        return array(
            'username' => (string)$account['email'],
            'password_cookie' => pwdcookieget($account['pw'])
        );
    }

    function u5TrustedDeviceIssue($username) {
        if (!u5TrustedDeviceEnabled() || !u5TrustedDeviceIsHttps() || !u5TrustedDeviceAllowedUser($username)) return 'disabled';
        $account = u5TrustedDeviceAccount($username);
        if ($account === false || !u5TrustedDeviceEnsureTable()) return 'error';
        $installation = u5TrustedDeviceInstallationId();
        $username = u5TrustedDeviceNormaliseUser($account['email']);
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $validatorHash = hash('sha256', $validator);
        $accountHash = hash('sha256', (string)$account['pw']);
        $now = time();
        if (@mysql_query('START TRANSACTION') === false) return 'error';
        $existing = @mysql_query("SELECT revoked_at FROM u5trusteddevices"
            . " WHERE installation_id='" . mysql_real_escape_string($installation) . "'"
            . " AND username='" . mysql_real_escape_string($username) . "' FOR UPDATE");
        if ($existing === false) {
            @mysql_query('ROLLBACK');
            return 'error';
        }
        if (@mysql_num_rows($existing) === 1) {
            $current = mysql_fetch_array($existing);
            if ((int)$current['revoked_at'] === 0) {
                @mysql_query('ROLLBACK');
                return 'exists';
            }
            $sql = "UPDATE u5trusteddevices SET selector='" . mysql_real_escape_string($selector)
                 . "',validator_hash='" . mysql_real_escape_string($validatorHash)
                 . "',account_hash='" . mysql_real_escape_string($accountHash)
                 . "',created_at=$now,last_used_at=$now,revoked_at=0"
                 . " WHERE installation_id='" . mysql_real_escape_string($installation) . "'"
                 . " AND username='" . mysql_real_escape_string($username) . "' AND revoked_at!=0";
        } else {
            $sql = "INSERT INTO u5trusteddevices"
                 . " (installation_id,username,selector,validator_hash,account_hash,created_at,last_used_at,revoked_at) VALUES ('"
                 . mysql_real_escape_string($installation) . "','" . mysql_real_escape_string($username) . "','"
                 . mysql_real_escape_string($selector) . "','" . mysql_real_escape_string($validatorHash) . "','"
                 . mysql_real_escape_string($accountHash) . "',$now,$now,0)";
        }
        if (@mysql_query($sql) === false || @mysql_query('COMMIT') === false) {
            @mysql_query('ROLLBACK');
            return 'error';
        }
        if (!u5TrustedDeviceSetCookie($selector . '.' . $validator)) {
            @mysql_query("UPDATE u5trusteddevices SET revoked_at=" . time()
                . " WHERE installation_id='" . mysql_real_escape_string($installation) . "'"
                . " AND selector='" . mysql_real_escape_string($selector) . "'");
            return 'error';
        }
        return 'ok';
    }

    function u5TrustedDeviceRevokeCurrent() {
        $token = u5TrustedDeviceCookieParts();
        if ($token !== false) {
            $installation = u5TrustedDeviceInstallationId();
            @mysql_query("UPDATE u5trusteddevices SET revoked_at=" . time()
                . " WHERE installation_id='" . mysql_real_escape_string($installation) . "'"
                . " AND selector='" . mysql_real_escape_string($token['selector']) . "' AND revoked_at=0");
        }
        u5TrustedDeviceClearCookie();
    }

    function u5TrustedDeviceStatus($username) {
        if (!u5TrustedDeviceEnabled() || !u5TrustedDeviceAllowedUser($username)) return 'disabled';
        $installation = u5TrustedDeviceInstallationId();
        $sql = "SELECT selector,revoked_at FROM u5trusteddevices"
             . " WHERE installation_id='" . mysql_real_escape_string($installation) . "'"
             . " AND username='" . mysql_real_escape_string(u5TrustedDeviceNormaliseUser($username)) . "' LIMIT 1";
        $result = @mysql_query($sql);
        if ($result === false || @mysql_num_rows($result) === 0) return 'none';
        $row = mysql_fetch_array($result);
        if ((int)$row['revoked_at'] !== 0) return 'revoked';
        $token = u5TrustedDeviceCookieParts();
        return $token !== false && hash_equals((string)$row['selector'], $token['selector']) ? 'current' : 'other';
    }

    function u5TrustedDeviceCsrfToken($username, $passwordCookie) {
        global $sessioncookiehashsalt, $password, $db;
        $key = hash('sha256', (string)$sessioncookiehashsalt."\0".(string)$password."\0".(string)$db, true);
        return hash_hmac('sha256', 'trusted-device\0' . u5TrustedDeviceInstallationId() . "\0"
            . u5TrustedDeviceNormaliseUser($username) . "\0" . (string)$passwordCookie, $key);
    }

    function u5TrustedDeviceOriginMatches() {
        if (isset($_SERVER['HTTP_SEC_FETCH_SITE']) && $_SERVER['HTTP_SEC_FETCH_SITE'] !== 'same-origin') return false;
        if (empty($_SERVER['HTTP_ORIGIN'])) return true;
        $origin = parse_url((string)$_SERVER['HTTP_ORIGIN']);
        if ($origin === false || strtolower((string)($origin['scheme'] ?? '')) !== 'https') return false;
        $originHost = strtolower((string)($origin['host'] ?? ''));
        if (isset($origin['port'])) $originHost .= ':' . (int)$origin['port'];
        return hash_equals(strtolower((string)($_SERVER['HTTP_HOST'] ?? '')), $originHost);
    }
}
