# u5CMS 12.9.5.10 — revocable trusted-browser sessions

This release adds an optional exception for a deliberately registered browser without weakening the ordinary u5CMS session policy. The feature is disabled by default.

## Security model

- One active trusted browser per explicitly allowed backend account and installation.
- A 256-bit random validator remains only in the browser; the database stores its SHA-256 hash.
- The cookie uses the `__Host-` prefix, `Secure`, `HttpOnly`, `SameSite=Strict`, a host-only scope and path `/`.
- The selector is installation-specific. There is no parent-domain cookie and no IP bypass.
- The current account password hash is fingerprinted. A password, SAML salt or derived account credential change invalidates the trusted browser.
- Registration requires an already authenticated backend session, an exact account allow-list match, HTTPS, same-origin form submission and a server-derived CSRF token.
- The CSRF HMAC is stable across ordinary session rotation and accepts the verified public host supplied by a trusted reverse proxy without accepting cross-site submissions.
- A second browser cannot replace an active browser. Deliberate logout revokes the current browser server-side.
- The browser cookie is renewed at most once per day and uses the browser maximum of 400 days. Regular use therefore keeps it alive without a fixed server-side expiry; it remains revocable at any time.

Possession of the dedicated browser profile remains the security boundary, as with every persistent web session. Anyone who controls that exact profile controls its authenticated cookies.

## Configuration

Keep the feature disabled unless a specific operational exception is required:

```php
$u5trusteddeviceenabled = 'no';
$u5trusteddeviceusers = array();
```

To enable it for a specific account, set `yes` and list the exact backend login. Then sign in normally and open `/u5admin/trusteddevice.php` in the browser to be registered. The page is not linked from the backend. It reports an existing different browser but does not replace it.

## Deployment

Deploy the distribution as one consistent version and retain the installation's `config.php`, database, `r` and `fileversions` directories. No cron job is required. The small `u5trusteddevices` table is created only when the first browser is deliberately registered. Existing installations and sessions are unchanged while the feature is disabled.

For a controlled multi-installation rollout, `scripts/trusted-device-rollout.php` defaults to a read-only dry-run, validates the exact installation count, lints every prospective PHP file, requires a new root-only backup directory for `--apply`, writes atomically and rolls back written files if an apply step fails.

## Verification

`tests/trusted-device-regression.php` exercises disabled-by-default behaviour, exact allow-listing, HTTPS-only registration, host-bound cookie format, hashed server storage, single-browser enforcement, valid resume, token-tamper rejection, logout revocation, re-enrolment after revocation, account-change invalidation, same-origin enforcement and both login hooks.
