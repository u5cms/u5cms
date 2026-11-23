<?php
// Optional, revocable trusted-browser sessions. Disabled unless explicitly
// enabled in config.php and restricted by exact per-account browser limits.

if (!function_exists('u5TrustedDeviceEnabled')) {
    function u5TrustedDeviceEnabled() {
        global $u5trusteddeviceenabled, $usesessioninsteadofbasicauth;
        return isset($u5trusteddeviceenabled)
            && $u5trusteddeviceenabled === 'yes'
            && $usesessioninsteadofbasicauth !== 'no'
            && (!function_exists('u5OidcEnabled') || !u5OidcEnabled());
    }

    function u5TrustedDeviceNormaliseUser($username) {
        $username = trim((string)$username);
        return function_exists('u5flatidnlower') ? u5flatidnlower($username) : strtolower($username);
    }

    function u5TrustedDeviceLimit($username) {
        global $u5trusteddeviceusers;
        if (!is_array($u5trusteddeviceusers)) return 0;
        $candidate = u5TrustedDeviceNormaliseUser($username);
        if ($candidate === '') return 0;
        $limit = null;
        foreach ($u5trusteddeviceusers as $allowed => $maximum) {
            // Preserve old numeric allow-lists as a quota of one.
            if (is_int($allowed)) { $allowed = $maximum; $maximum = 1; }
            if (!is_string($allowed)) continue;
            $allowed = u5TrustedDeviceNormaliseUser($allowed);
            if ($allowed === '' || !hash_equals($allowed, $candidate)) continue;
            $maximum = is_int($maximum) && $maximum > 0 && $maximum <= 4294967295 ? $maximum : 0;
            // Ambiguous case/whitespace variants use the most restrictive value.
            $limit = $limit === null ? $maximum : min($limit, $maximum);
        }
        return $limit ?? 0;
    }

    function u5TrustedDeviceAllowedUser($username) {
        return u5TrustedDeviceLimit($username) > 0;
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
             . "slot INT UNSIGNED NOT NULL DEFAULT 1,"
             . "selector CHAR(32) NOT NULL,"
             . "validator_hash CHAR(64) NOT NULL,"
             . "account_hash CHAR(64) NOT NULL,"
             . "created_at BIGINT UNSIGNED NOT NULL,"
             . "last_used_at BIGINT UNSIGNED NOT NULL,"
             . "revoked_at BIGINT UNSIGNED NOT NULL DEFAULT 0,"
             . "PRIMARY KEY (installation_id,username,slot),"
             . "UNIQUE KEY u5trusteddevices_selector (installation_id,selector)"
             . ") ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin";
        if (@mysql_query($sql) === false) return false;
        $primary = u5TrustedDevicePrimaryKey();
        if ($primary === array('installation_id', 'username', 'slot')) return true;
        if ($primary !== array('installation_id', 'username')) return false;
        // Existing tokens become slot 1 without changing their secrets or cookies.
        $sql = "ALTER TABLE u5trusteddevices ADD COLUMN slot INT UNSIGNED NOT NULL DEFAULT 1,"
             . " DROP PRIMARY KEY, ADD PRIMARY KEY (installation_id,username,slot)";
        if (@mysql_query($sql) !== false) return true;
        // Another first registration may have completed the same migration.
        return u5TrustedDevicePrimaryKey() === array('installation_id', 'username', 'slot');
    }

    function u5TrustedDevicePrimaryKey() {
        $result = @mysql_query("SHOW INDEX FROM u5trusteddevices WHERE Key_name='PRIMARY'");
        if ($result === false) return false;
        $columns = array();
        while ($row = mysql_fetch_array($result)) $columns[(int)$row['Seq_in_index']] = $row['Column_name'];
        ksort($columns);
        return array_values($columns);
    }

    function u5TrustedDeviceBrowsers($username) {
        if (!u5TrustedDeviceEnabled() || !u5TrustedDeviceAllowedUser($username)) return false;
        $installation = u5TrustedDeviceInstallationId();
        // SELECT * also reads the original one-browser table before its migration.
        $result = @mysql_query("SELECT * FROM u5trusteddevices WHERE installation_id='"
            . mysql_real_escape_string($installation) . "' AND username='"
            . mysql_real_escape_string(u5TrustedDeviceNormaliseUser($username))
            . "' AND revoked_at=0 ORDER BY created_at,selector");
        if ($result === false) return mysql_errno() === 1146 ? array() : false;
        $rows = array();
        while ($row = mysql_fetch_array($result)) {
            $row['slot'] = (int)($row['slot'] ?? 1);
            if ($row['slot'] < 1 || $row['slot'] > u5TrustedDeviceLimit($username)) {
                if (!u5TrustedDeviceRevoke($username, $row['selector'])) return false;
                continue;
            }
            $rows[] = $row;
        }
        return $rows;
    }

    function u5TrustedDeviceIsCurrent($row) {
        $token = u5TrustedDeviceCookieParts();
        return $token !== false && hash_equals((string)$row['selector'], $token['selector'])
            && hash_equals((string)$row['validator_hash'], hash('sha256', $token['validator']));
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
        $sql = "SELECT * FROM u5trusteddevices"
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
        // An explicitly selected session account takes precedence. Keeping the
        // old token would silently restore its identity now or after a restart.
        $sessionUser = $_COOKIE['u'] ?? '';
        if (is_string($sessionUser) && trim($sessionUser) !== ''
            && u5TrustedDeviceNormaliseUser($sessionUser) !== u5TrustedDeviceNormaliseUser($row['username'])) {
            u5TrustedDeviceRevokeCurrent();
            return false;
        }
        $slot = (int)($row['slot'] ?? 1);
        if ($slot < 1 || $slot > u5TrustedDeviceLimit($row['username'])) {
            u5TrustedDeviceRevoke($row['username'], $row['selector']);
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
        $where = "installation_id='" . mysql_real_escape_string($installation)
               . "' AND username='" . mysql_real_escape_string($username) . "'";
        $limit = u5TrustedDeviceLimit($username);
        if (@mysql_query("UPDATE u5trusteddevices SET revoked_at=$now WHERE $where AND revoked_at=0"
            . " AND (slot>$limit OR account_hash!='" . mysql_real_escape_string($accountHash) . "')") === false) return 'error';
        $rows = u5TrustedDeviceBrowsers($username);
        if ($rows === false) return 'error';
        $occupied = array();
        foreach ($rows as $row) {
            if (u5TrustedDeviceIsCurrent($row)) return 'exists';
            $occupied[$row['slot']] = true;
        }
        for ($slot = 1; $slot <= $limit; $slot++) {
            if (isset($occupied[$slot])) continue;
            // A conditional update reuses only revoked slots. The primary key
            // arbitrates concurrent inserts; an active browser is never replaced.
            $sql = "UPDATE u5trusteddevices SET selector='" . mysql_real_escape_string($selector)
                 . "',validator_hash='" . mysql_real_escape_string($validatorHash)
                 . "',account_hash='" . mysql_real_escape_string($accountHash)
                 . "',created_at=$now,last_used_at=$now,revoked_at=0"
                 . " WHERE $where AND slot=$slot AND revoked_at!=0";
            if (@mysql_query($sql) === false) return 'error';
            if (mysql_affected_rows() !== 1) {
                $sql = "INSERT INTO u5trusteddevices"
                     . " (installation_id,username,slot,selector,validator_hash,account_hash,created_at,last_used_at,revoked_at) VALUES ('"
                     . mysql_real_escape_string($installation) . "','" . mysql_real_escape_string($username) . "',$slot,'"
                     . mysql_real_escape_string($selector) . "','" . mysql_real_escape_string($validatorHash) . "','"
                     . mysql_real_escape_string($accountHash) . "',$now,$now,0)";
                if (@mysql_query($sql) === false) {
                    if (mysql_errno() === 1062) continue;
                    return 'error';
                }
            }
            if (!u5TrustedDeviceSetCookie($selector . '.' . $validator)) {
                u5TrustedDeviceRevoke($username, $selector);
                return 'error';
            }
            return 'ok';
        }
        return 'limit';
    }

    function u5TrustedDeviceRevoke($username, $selector) {
        if (!u5TrustedDeviceAllowedUser($username) || !is_string($selector)
            || !preg_match('/\A[a-f0-9]{32}\z/D', $selector)) return false;
        $sql = "UPDATE u5trusteddevices SET revoked_at=" . time()
             . " WHERE installation_id='" . mysql_real_escape_string(u5TrustedDeviceInstallationId()) . "'"
             . " AND username='" . mysql_real_escape_string(u5TrustedDeviceNormaliseUser($username)) . "'"
             . " AND selector='" . mysql_real_escape_string($selector) . "' AND revoked_at=0";
        if (@mysql_query($sql) === false || mysql_affected_rows() !== 1) return false;
        $token = u5TrustedDeviceCookieParts();
        if ($token !== false && hash_equals($selector, $token['selector'])) u5TrustedDeviceClearCookie();
        return true;
    }

    function u5TrustedDeviceRevokeCurrent() {
        $token = u5TrustedDeviceCookieParts();
        if ($token !== false) {
            $installation = u5TrustedDeviceInstallationId();
            @mysql_query("UPDATE u5trusteddevices SET revoked_at=" . time()
                . " WHERE installation_id='" . mysql_real_escape_string($installation) . "'"
                . " AND selector='" . mysql_real_escape_string($token['selector']) . "'"
                . " AND validator_hash='" . mysql_real_escape_string(hash('sha256', $token['validator'])) . "' AND revoked_at=0");
        }
        u5TrustedDeviceClearCookie();
    }

    function u5TrustedDeviceStatus($username) {
        if (!u5TrustedDeviceEnabled() || !u5TrustedDeviceAllowedUser($username)) return 'disabled';
        $rows = u5TrustedDeviceBrowsers($username);
        if ($rows === false || !$rows) return 'none';
        foreach ($rows as $row) if (u5TrustedDeviceIsCurrent($row)) return 'current';
        return 'other';
    }

    function u5TrustedDeviceCsrfToken($username, $passwordCookie) {
        global $password, $db;
        // Deliberately independent from the ordinary, potentially daily session salt and
        // the reconstructed session cookie: a GET and its POST must agree across proxies.
        // The HMAC remains secret, installation-bound and account-bound.
        $key = hash('sha256', (string)$password."\0".(string)$db."\0u5-trusted-device-csrf", true);
        return hash_hmac('sha256', 'trusted-device\0' . u5TrustedDeviceInstallationId() . "\0"
            . u5TrustedDeviceNormaliseUser($username), $key);
    }

    function u5TrustedDeviceOriginMatches() {
        if (isset($_SERVER['HTTP_SEC_FETCH_SITE'])) {
            $fetchSite = strtolower(trim((string)$_SERVER['HTTP_SEC_FETCH_SITE']));
            if (!in_array($fetchSite, array('same-origin', 'same-site', 'none'), true)) return false;
        }
        if (empty($_SERVER['HTTP_ORIGIN'])) return true;
        $origin = parse_url((string)$_SERVER['HTTP_ORIGIN']);
        if ($origin === false || strtolower((string)($origin['scheme'] ?? '')) !== 'https') return false;
        $originHost = strtolower((string)($origin['host'] ?? ''));
        if (isset($origin['port'])) $originHost .= ':' . (int)$origin['port'];
        $candidateHosts = array(
            $_SERVER['HTTP_HOST'] ?? '',
            $_SERVER['HTTP_X_FORWARDED_HOST'] ?? '',
            $_SERVER['HTTP_X_ORIGINAL_HOST'] ?? '',
            $_SERVER['SERVER_NAME'] ?? ''
        );
        foreach ($candidateHosts as $candidateHost) {
            $candidateHost = strtolower(trim(explode(',', (string)$candidateHost)[0]));
            if ($candidateHost !== '' && hash_equals($candidateHost, $originHost)) return true;
        }
        return false;
    }
}
