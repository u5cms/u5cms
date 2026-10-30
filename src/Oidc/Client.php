<?php
namespace U5cms\Oidc;

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use RuntimeException;

/** A deliberately narrow OIDC code-flow RP. No implicit flow or dynamic registration. */
final class Client
{
    private $config;
    private $transport;

    public function __construct(array $config, $transport = null)
    {
        $this->config = $config + array(
            'client_secret' => '', 'token_auth_method' => 'client_secret_basic',
            'scopes' => array('openid'), 'subjects' => array(),
            'allowed_endpoint_origins' => array(), 'ca_bundle' => '',
            'clock_skew' => 30, 'session_ttl' => 3600, 'idle_timeout' => 900,
            'provider_logout' => false,
            'frontend' => 'yes', 'frontend_enforced_unless_target_contains' => '',
            'auto_intranet_enrollment' => false, 'login_info' => '', 'not_a_member_message' => '',
            'logout_result_page' => 'index.php?c=loggedout', 'claims_to_keep' => array(),
            'identity_mode' => 'subject_map',
        );
        foreach (array('issuer', 'client_id', 'redirect_uri') as $key) {
            if (!isset($config[$key]) || !is_string($config[$key]) || $config[$key] === '') {
                throw new RuntimeException('Missing OIDC setting: '.$key);
            }
        }
        $issuer = self::httpsUrl($config['issuer']);
        if (isset($issuer['query'])) throw new RuntimeException('Issuer must not contain a query.');
        $redirect = self::httpsUrl($config['redirect_uri']);
        if (isset($redirect['query']) || substr($redirect['path'] ?? '', -18) !== '/oidc/callback.php') {
            throw new RuntimeException('redirect_uri must end in /oidc/callback.php without a query.');
        }
        if (!in_array($this->config['token_auth_method'], array('client_secret_basic','client_secret_post','none'), true)) {
            throw new RuntimeException('Unsupported OIDC token authentication method.');
        }
        if ($this->config['token_auth_method'] !== 'none' &&
            (!is_string($this->config['client_secret']) || $this->config['client_secret'] === '')) {
            throw new RuntimeException('OIDC client secret is missing.');
        }
        if (!is_array($this->config['scopes']) || !in_array('openid', $this->config['scopes'], true)) {
            throw new RuntimeException('OIDC scopes must include openid.');
        }
        foreach ($this->config['scopes'] as $scope) {
            if (!is_string($scope) || !preg_match('/^[\x21\x23-\x5B\x5D-\x7E]+$/D', $scope)) {
                throw new RuntimeException('Invalid OIDC scope.');
            }
        }
        if (!is_array($this->config['subjects']) || !is_array($this->config['allowed_endpoint_origins'])) {
            throw new RuntimeException('OIDC subjects and endpoint origins must be arrays.');
        }
        if (!is_bool($this->config['provider_logout'])) throw new RuntimeException('provider_logout must be boolean.');
        if (!in_array($this->config['identity_mode'], array('subject_map','verified_email'), true)) throw new RuntimeException('Invalid OIDC identity mode.');
        if (!in_array($this->config['frontend'], array('yes','no'), true) ||
            !is_string($this->config['frontend_enforced_unless_target_contains']) ||
            !is_bool($this->config['auto_intranet_enrollment']) || !is_string($this->config['login_info']) ||
            !is_string($this->config['not_a_member_message'])) throw new RuntimeException('Invalid OIDC frontend or enrollment policy.');
        $this->logoutResult();
        if (!is_array($this->config['claims_to_keep']) || count($this->config['claims_to_keep']) > 30) throw new RuntimeException('Invalid OIDC claim allowlist.');
        foreach ($this->config['claims_to_keep'] as $name) {
            if (!is_string($name) || !preg_match('/^[A-Za-z0-9_.:\/-]{1,128}$/D', $name) ||
                in_array($name, array('nonce','at_hash','c_hash'), true)) throw new RuntimeException('Invalid OIDC retained claim.');
        }
        foreach ($this->config['subjects'] as $subject => $username) {
            $identifier = is_string($username) ? preg_replace('/&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/i', '', $username) : '';
            if ((string)$subject === '' || !is_string($username) || $username === '' ||
                preg_match('/[\x00-\x20\x7F?:;,\\\\]/', $identifier)) {
                throw new RuntimeException('Invalid OIDC subject mapping. Use the existing CMS username.');
            }
        }
        foreach (array('clock_skew' => array(0,120), 'session_ttl' => array(60,28800), 'idle_timeout' => array(60,28800)) as $key => $range) {
            if (!is_int($this->config[$key]) || $this->config[$key] < $range[0] || $this->config[$key] > $range[1]) {
                throw new RuntimeException('Invalid OIDC setting: '.$key);
            }
        }
        foreach ($this->config['allowed_endpoint_origins'] as $origin) {
            if (!is_string($origin) || self::origin($origin) !== $origin) {
                throw new RuntimeException('OIDC endpoint allowlist entries must be HTTPS origins.');
            }
        }
        $this->transport = $transport;
    }

    public function config() { return $this->config; }

    public static function httpsUrl($url)
    {
        if (!is_string($url) || preg_match('/[\x00-\x20\x7F\\\\]/', $url)) throw new RuntimeException('Invalid HTTPS URL.');
        $parts = parse_url($url);
        if ($parts === false || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host']) ||
            isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])) {
            throw new RuntimeException('An absolute HTTPS URL without credentials or fragment is required.');
        }
        return $parts;
    }

    public static function origin($url)
    {
        $p = self::httpsUrl($url);
        return 'https://'.strtolower($p['host']).(isset($p['port']) && $p['port'] !== 443 ? ':'.$p['port'] : '');
    }

    public function basePath()
    {
        return substr(parse_url($this->config['redirect_uri'], PHP_URL_PATH), 0, -17);
    }

    public function logoutResult()
    {
        $target=$this->config['logout_result_page'];
        if (!is_string($target) || strlen($target)>2000 || preg_match('/[\x00-\x20\x7F\\\\]/',$target)) throw new RuntimeException('Invalid configured logout destination.');
        $parts=parse_url($target);
        if ($parts!==false && isset($parts['scheme'])) {
            // A fixed administrator-configured HTTPS destination may be external, as in SAML.
            self::httpsUrl(explode('#',$target,2)[0]);
            return $target;
        }
        return $this->returnUrl($target);
    }

    /** Same yes/no/substring precedence as the existing SAML frontend policy. */
    public function requiresFederation($target, $backend = false)
    {
        $target = $this->returnUrl($target);
        $path = parse_url($target, PHP_URL_PATH);
        $admin = $this->basePath().'u5admin';
        if ($backend || $path === $admin || strpos($path, $admin.'/') === 0) return true;
        parse_str(parse_url($target, PHP_URL_QUERY) ?? '', $query);
        if ($path === $this->basePath().'index.php' && in_array($query['p'] ?? '', array('1','2'), true)) return true;
        if ($this->config['frontend'] !== 'no') return true;
        $exception = $this->config['frontend_enforced_unless_target_contains'];
        return $exception !== '' && strpos(self::origin($this->config['redirect_uri']).$target, $exception) === false;
    }

    /** Resolve against the configured CMS origin, never the request Host header. */
    public function returnUrl($target)
    {
        if (!is_string($target) || strlen($target) > 2000) throw new RuntimeException('Invalid return URL.');
        if ($target === '') return $this->basePath().'index.php';
        $check = $target;
        for ($i=0; $i<5; $i++) {
            if (preg_match('/[\x00-\x1F\x7F\\\\]/', $check) || substr($check,0,2) === '//') {
                throw new RuntimeException('Invalid return URL.');
            }
            $decoded = rawurldecode($check);
            if ($decoded === $check) break;
            $check = $decoded;
        }
        if (rawurldecode($check) !== $check) throw new RuntimeException('Excessively encoded return URL.');
        $parts = parse_url($target);
        if ($parts === false || isset($parts['user']) || isset($parts['pass'])) throw new RuntimeException('Invalid return URL.');
        if (isset($parts['host']) || isset($parts['scheme'])) {
            // Fragments are legitimate CMS scroll targets, not part of an origin.
            $withoutFragment = explode('#', $target, 2)[0];
            if (self::origin($withoutFragment) !== self::origin($this->config['redirect_uri'])) {
                throw new RuntimeException('Return URL must belong to this CMS.');
            }
        }
        $path = $parts['path'] ?? '';
        if (substr($path,0,1) !== '/') $path = $this->basePath().$path;
        $decodedPath = rawurldecode(rawurldecode(rawurldecode(rawurldecode(rawurldecode($path)))));
        if (strpos($decodedPath, $this->basePath()) !== 0 || strpos($decodedPath, '//') !== false ||
            preg_match('#(?:^|/)\.{1,2}(?:/|$)#', $decodedPath)) {
            throw new RuntimeException('Return URL must stay inside this CMS installation.');
        }
        return $path.(isset($parts['query']) ? '?'.$parts['query'] : '').(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
    }

    private function endpoint($url)
    {
        $origin = self::origin($url);
        if ($origin !== self::origin($this->config['issuer']) && !in_array($origin, $this->config['allowed_endpoint_origins'], true)) {
            throw new RuntimeException('OIDC endpoint origin is not trusted.');
        }
        return $url;
    }

    /** Bounded TLS backchannel, without redirects, credentials in URLs, or TLS bypass. */
    private function json($url, $form = null, array $headers = array())
    {
        $this->endpoint($url);
        if ($this->transport !== null) return call_user_func($this->transport, $url, $form, $headers);
        $body = '';
        $curl = curl_init($url);
        $options = array(
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS, CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => array_merge(array('Accept: application/json'), $headers),
            CURLOPT_WRITEFUNCTION => function ($handle, $chunk) use (&$body) {
                if (strlen($body)+strlen($chunk) > 262144) return 0;
                $body .= $chunk;
                return strlen($chunk);
            },
        );
        if ($this->config['ca_bundle'] !== '') $options[CURLOPT_CAINFO] = $this->config['ca_bundle'];
        if ($form !== null) {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($form, '', '&', PHP_QUERY_RFC3986);
        }
        curl_setopt_array($curl, $options);
        $ok = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $type = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        curl_close($curl);
        if ($ok === false || $status !== 200 || !is_string($type) ||
            !preg_match('#^application/(?:[a-z0-9.+-]+\+)?json(?:\s*;|$)#i', $type)) {
            throw new RuntimeException('OIDC provider request failed.');
        }
        $data = json_decode($body, true, 32, JSON_BIGINT_AS_STRING);
        if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) throw new RuntimeException('Invalid OIDC JSON response.');
        return $data;
    }

    public function discovery()
    {
        $metadata = $this->json(rtrim($this->config['issuer'],'/').'/.well-known/openid-configuration');
        if (($metadata['issuer'] ?? null) !== $this->config['issuer']) throw new RuntimeException('OIDC discovery issuer mismatch.');
        foreach (array('authorization_endpoint','token_endpoint','jwks_uri') as $key) {
            if (!isset($metadata[$key])) throw new RuntimeException('OIDC discovery endpoint is missing.');
            $this->endpoint($metadata[$key]);
        }
        foreach (array('response_types_supported' => 'code', 'id_token_signing_alg_values_supported' => 'RS256') as $key => $value) {
            if (!isset($metadata[$key]) || !is_array($metadata[$key]) || !in_array($value, $metadata[$key], true)) {
                throw new RuntimeException('OIDC provider does not advertise '.$value.'.');
            }
        }
        if (isset($metadata['code_challenge_methods_supported']) &&
            (!is_array($metadata['code_challenge_methods_supported']) || !in_array('S256', $metadata['code_challenge_methods_supported'], true))) {
            throw new RuntimeException('OIDC provider does not support PKCE S256.');
        }
        $methods = $metadata['token_endpoint_auth_methods_supported'] ?? array('client_secret_basic');
        if (!is_array($methods) || !in_array($this->config['token_auth_method'], $methods, true)) {
            throw new RuntimeException('OIDC token authentication method is not supported by the provider.');
        }
        if ($this->config['provider_logout']) {
            if (!isset($metadata['end_session_endpoint'])) throw new RuntimeException('OIDC provider does not advertise RP-initiated logout.');
            $this->endpoint($metadata['end_session_endpoint']);
        }
        return $metadata;
    }

    public static function random() { return self::base64url(random_bytes(32)); }
    public static function base64url($bytes) { return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '='); }

    public function authorisation(array $metadata, array $transaction, $reauthenticate = false)
    {
        $params = array(
            'response_type' => 'code', 'response_mode' => 'query',
            'client_id' => $this->config['client_id'], 'redirect_uri' => $this->config['redirect_uri'],
            'scope' => implode(' ', $this->config['scopes']), 'state' => $transaction['state'],
            'nonce' => $transaction['nonce'], 'code_challenge_method' => 'S256',
            'code_challenge' => self::base64url(hash('sha256', $transaction['verifier'], true)),
        );
        if ($reauthenticate) $params['prompt'] = 'login';
        return $this->endpoint($metadata['authorization_endpoint']).
            (strpos($metadata['authorization_endpoint'],'?') === false ? '?' : '&').http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    public function exchange(array $metadata, $code, array $transaction)
    {
        if (!is_string($code) || $code === '' || strlen($code) > 8192 || preg_match('/[\x00-\x20\x7F]/', $code)) {
            throw new RuntimeException('Invalid OIDC authorisation code.');
        }
        $form = array('grant_type' => 'authorization_code', 'code' => $code,
            'redirect_uri' => $this->config['redirect_uri'], 'code_verifier' => $transaction['verifier']);
        $headers = array();
        if ($this->config['token_auth_method'] === 'client_secret_basic') {
            $headers[] = 'Authorization: Basic '.base64_encode(urlencode($this->config['client_id']).':'.urlencode($this->config['client_secret']));
        } else {
            $form['client_id'] = $this->config['client_id'];
            if ($this->config['token_auth_method'] === 'client_secret_post') $form['client_secret'] = $this->config['client_secret'];
        }
        $tokens = $this->json($metadata['token_endpoint'], $form, $headers);
        if (isset($tokens['error']) || !is_string($tokens['id_token'] ?? null) ||
            !is_string($tokens['access_token'] ?? null) || $tokens['access_token'] === '' ||
            !is_string($tokens['token_type'] ?? null) || strcasecmp($tokens['token_type'],'Bearer') !== 0) {
            throw new RuntimeException('OIDC provider did not return the required tokens.');
        }
        // Fetch at callback time: key rotation works without a shared writable web cache.
        $identity = $this->validate($tokens['id_token'], $this->json($metadata['jwks_uri']), $transaction['nonce'], $tokens['access_token']);
        if ($this->config['provider_logout']) {
            $identity['logout_token'] = $tokens['id_token'];
            $identity['logout_endpoint'] = $this->endpoint($metadata['end_session_endpoint']);
        }
        return $identity;
    }

    public function logoutUrl(array $identity, $state)
    {
        if (!$this->config['provider_logout'] || !isset($identity['logout_token'], $identity['logout_endpoint'])) return null;
        $url = $this->endpoint($identity['logout_endpoint']);
        return $url.(strpos($url,'?') === false ? '?' : '&').http_build_query(array(
            'id_token_hint'=>$identity['logout_token'], 'client_id'=>$this->config['client_id'],
            'post_logout_redirect_uri'=>self::origin($this->config['redirect_uri']).$this->basePath().'oidc/loggedout.php',
            'state'=>$state,
        ), '', '&', PHP_QUERY_RFC3986);
    }

    public function validate($token, array $jwks, $nonce, $accessToken = null)
    {
        if (!is_string($token) || strlen($token) > 65536) throw new RuntimeException('Invalid ID token.');
        $parts = explode('.', $token);
        if (count($parts) !== 3) throw new RuntimeException('A signed ID token is required.');
        $header = json_decode(JWT::urlsafeB64Decode($parts[0]), true);
        if (!is_array($header) || ($header['alg'] ?? '') !== 'RS256' || isset($header['crit']) ||
            isset($header['jku']) || isset($header['x5u']) || isset($header['jwk'])) {
            throw new RuntimeException('Unsupported ID token header.');
        }
        $keys = array();
        foreach ($jwks['keys'] ?? array() as $key) {
            if (!is_array($key) || ($key['kty'] ?? '') !== 'RSA' || ($key['use'] ?? 'sig') !== 'sig' ||
                ($key['alg'] ?? 'RS256') !== 'RS256' || (isset($key['key_ops']) && !in_array('verify', $key['key_ops'], true))) continue;
            if (isset($key['kid']) && !is_string($key['kid'])) throw new RuntimeException('Invalid OIDC key ID.');
            $kid = isset($key['kid']) ? $key['kid'] : '';
            if (isset($keys[$kid])) throw new RuntimeException('Ambiguous OIDC signing key.');
            $keys[$kid] = JWK::parseKey($key, 'RS256');
        }
        if (isset($header['kid']) && !is_string($header['kid'])) throw new RuntimeException('Invalid ID token key ID.');
        $kid = isset($header['kid']) ? $header['kid'] : null;
        if ($kid === null && count($keys) === 1) $key = reset($keys);
        elseif ($kid !== null && isset($keys[$kid])) $key = $keys[$kid];
        else throw new RuntimeException('OIDC signing key not found.');
        $previousLeeway = JWT::$leeway;
        JWT::$leeway = $this->config['clock_skew'];
        try { $claims = (array)JWT::decode($token, $key); }
        finally { JWT::$leeway = $previousLeeway; }
        $now = time(); $skew = $this->config['clock_skew'];
        if (($claims['iss'] ?? null) !== $this->config['issuer'] || !is_string($claims['sub'] ?? null) ||
            $claims['sub'] === '' || strlen($claims['sub']) > 255 || preg_match('/[^\x21-\x7E]/', $claims['sub'])) {
            throw new RuntimeException('Invalid OIDC issuer or subject.');
        }
        // Only this client is a trusted audience. Multi-audience tokens require explicit future support.
        $aud = $claims['aud'] ?? null;
        if ($aud !== $this->config['client_id'] && $aud !== array($this->config['client_id'])) throw new RuntimeException('Invalid ID token audience.');
        if (isset($claims['azp']) && $claims['azp'] !== $this->config['client_id']) throw new RuntimeException('Invalid ID token authorised party.');
        if (!is_int($claims['exp'] ?? null) || !is_int($claims['iat'] ?? null) ||
            $claims['exp'] <= $now-$skew || $claims['iat'] > $now+$skew ||
            $claims['iat'] < $now-600-$skew || $claims['exp'] <= $claims['iat'] ||
            (isset($claims['nbf']) && (!is_int($claims['nbf']) || $claims['nbf'] > $now+$skew))) {
            throw new RuntimeException('Invalid ID token lifetime.');
        }
        if (!is_string($claims['nonce'] ?? null) || !hash_equals($nonce, $claims['nonce'])) throw new RuntimeException('Invalid ID token nonce.');
        if (isset($claims['at_hash'])) {
            if (!is_string($claims['at_hash']) || !is_string($accessToken) ||
                !hash_equals(self::base64url(substr(hash('sha256', $accessToken, true),0,16)), $claims['at_hash'])) {
                throw new RuntimeException('Invalid access token hash.');
            }
        }
        $mapping = 'subject_map';
        $email = null;
        if (array_key_exists($claims['sub'], $this->config['subjects'])) $username = $this->config['subjects'][$claims['sub']];
        elseif ($this->config['identity_mode'] === 'verified_email' && is_string($claims['email'] ?? null) &&
            $claims['email'] !== '' && strlen($claims['email']) <= 254 && ($claims['email_verified'] ?? false) === true) {
            $username = null;$email = $claims['email'];$mapping = 'verified_email';
        } else throw new RuntimeException('OIDC subject has no CMS identity mapping or verified email.');
        $kept = array();
        foreach ($this->config['claims_to_keep'] as $name) {
            if (array_key_exists($name, $claims)) $kept[$name] = json_decode(json_encode($claims[$name]), true);
        }
        return array('username' => $username, 'sub' => $claims['sub'],
            'issuer' => $claims['iss'], 'claims' => $kept, 'mapping' => $mapping, 'email' => $email,
            // The ID token proves the login event; RP session lifetime is the CMS policy.
            'expires' => $now+$this->config['session_ttl']);
    }
}
