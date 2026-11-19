<?php
namespace U5cms\Oidc;

use RuntimeException;

/** A separate, server-side session. Locks are released before any network or file transfer. */
final class Session
{
    private $client;
    private $fingerprint;
    private $name;

    public function __construct(Client $client, $installationSecret)
    {
        $this->client = $client;
        $this->fingerprint = hash('sha256', serialize($client->config()).'\0'.$installationSecret);
        $this->name = 'u5oidc_'.substr(hash('sha256', $client->config()['redirect_uri']),0,16);
    }

    public function cookieName() { return $this->name; }

    private function open($create = false)
    {
        if (!$create && !isset($_COOKIE[$this->name])) return false;
        if (session_status() === PHP_SESSION_ACTIVE) throw new RuntimeException('Another PHP session is active.');
        if (headers_sent()) throw new RuntimeException('OIDC session must be read before response output.');
        session_name($this->name);
        $id = $_COOKIE[$this->name] ?? '';
        if (!is_string($id) || ($id !== '' && !preg_match('/^[a-zA-Z0-9,-]{22,128}$/D', $id))) {
            throw new RuntimeException('Invalid OIDC session cookie.');
        }
        session_id($id);
        if (!session_start(array(
            'use_strict_mode' => 1, 'use_only_cookies' => 1, 'use_trans_sid' => 0,
            'cookie_lifetime' => 0, 'cookie_path' => $this->client->basePath(),
            'cookie_domain' => '', 'cookie_secure' => true, 'cookie_httponly' => true,
            'cookie_samesite' => 'Lax', 'cache_limiter' => '',
        ))) throw new RuntimeException('Cannot open the OIDC session.');
        // CLI/HTTP tests and multiple sequential operations share this request's new ID too.
        $_COOKIE[$this->name] = session_id();
        if (!isset($_SESSION['generation'])) $_SESSION['generation'] = Client::random();
        return true;
    }

    public function identity()
    {
        if (!$this->open()) return null;
        try {
            $auth = $_SESSION['identity'] ?? null;
            $now = time(); $config = $this->client->config();
            if (!is_array($auth) || ($auth['fingerprint'] ?? '') !== $this->fingerprint ||
                ($auth['expires'] ?? 0) <= $now || ($auth['last_seen'] ?? 0)+$config['idle_timeout'] <= $now ||
                !is_string($auth['username'] ?? null) || $auth['username'] === '' ||
                !(isset($config['subjects'][$auth['sub'] ?? '']) && $config['subjects'][$auth['sub']] === $auth['username'] ||
                  $config['identity_mode'] === 'verified_email' && ($auth['mapping'] ?? '') === 'verified_email')) {
                unset($_SESSION['identity']);
                return null;
            }
            if ($auth['last_seen'] < $now-30) $_SESSION['identity']['last_seen'] = $now;
            return $auth;
        } finally { session_write_close(); }
    }

    public function begin($target, array $metadata)
    {
        $target = $this->client->returnUrl($target);
        $this->open(true);
        try {
            $pending = $_SESSION['pending'] ?? array();
            foreach ($pending as $state => $item) if (($item['created'] ?? 0) < time()-300) unset($pending[$state]);
            while (count($pending) >= 5) array_shift($pending);
            $transaction = array('state' => Client::random(), 'nonce' => Client::random(),
                'verifier' => Client::random(), 'created' => time(), 'target' => $target,
                'metadata' => $metadata, 'fingerprint' => $this->fingerprint,
                'generation' => $_SESSION['generation']);
            $pending[$transaction['state']] = $transaction;
            $_SESSION['pending'] = $pending;
            return $transaction;
        } finally { session_write_close(); }
    }

    /** Consume before token exchange, including provider cancellation and all failure cases. */
    public function consume(array $response)
    {
        $state = $response['state'] ?? null;
        if (!is_string($state) || !preg_match('/^[A-Za-z0-9_-]{43}$/D', $state) || !$this->open()) {
            throw new RuntimeException('Missing OIDC transaction.');
        }
        try {
            $transaction = $_SESSION['pending'][$state] ?? null;
            unset($_SESSION['pending'][$state]);
            if (!is_array($transaction) || !hash_equals($transaction['state'], $state) ||
                $transaction['created'] < time()-300 || $transaction['created'] > time()+30 ||
                $transaction['fingerprint'] !== $this->fingerprint ||
                $transaction['generation'] !== $_SESSION['generation']) {
                throw new RuntimeException('Expired or invalid OIDC transaction.');
            }
        } finally { session_write_close(); }
        $issuer = $response['iss'] ?? null;
        if (($issuer !== null && $issuer !== $this->client->config()['issuer']) ||
            (!empty($transaction['metadata']['authorization_response_iss_parameter_supported']) && $issuer === null)) {
            throw new RuntimeException('OIDC authorisation response issuer mismatch.');
        }
        if (isset($response['error']) || !is_string($response['code'] ?? null) || $response['code'] === '') {
            throw new RuntimeException('OIDC authorisation was not completed.');
        }
        return $transaction;
    }

    public function establish(array $identity, array $transaction)
    {
        if (!$this->open()) throw new RuntimeException('OIDC browser session was lost.');
        try {
            // A concurrent logout must also cancel a token exchange already in progress.
            if ($_SESSION['generation'] !== $transaction['generation'] || $transaction['fingerprint'] !== $this->fingerprint) {
                throw new RuntimeException('OIDC transaction was cancelled.');
            }
            if (!session_regenerate_id(true)) throw new RuntimeException('Cannot rotate OIDC session ID.');
            $_COOKIE[$this->name] = session_id();
            $_SESSION['identity'] = $identity + array('last_seen' => time(), 'fingerprint' => $this->fingerprint);
            // Other in-flight tabs remain usable, but every transaction itself is single-use.
        } finally { session_write_close(); }
    }

    public function logout()
    {
        if (!$this->open()) return null;
        try {
            $identity = $_SESSION['identity'] ?? null;
            $_SESSION = array();
            session_destroy();
            setcookie($this->name, '', array('expires' => time()-3600, 'path' => $this->client->basePath(),
                'secure' => true, 'httponly' => true, 'samesite' => 'Lax'));
            unset($_COOKIE[$this->name]);
            return $identity;
        } finally { if (session_status() === PHP_SESSION_ACTIVE) session_write_close(); }
    }

    public function beginLogout()
    {
        $this->open(true);
        try {
            $state = Client::random();
            $_SESSION['logout'] = array('state'=>$state, 'created'=>time());
            return $state;
        } finally { session_write_close(); }
    }

    public function finishLogout($state)
    {
        if (!is_string($state) || !$this->open()) throw new RuntimeException('Missing logout transaction.');
        try {
            $pending = $_SESSION['logout'] ?? null;
            unset($_SESSION['logout']);
            if (!is_array($pending) || !hash_equals($pending['state'], $state) || $pending['created'] < time()-300) {
                throw new RuntimeException('Invalid logout transaction.');
            }
        } finally { session_write_close(); }
        // A delayed logout response must not delete a subsequent, freshly authenticated session.
        if ($this->identity() === null) $this->logout();
    }
}
