# u5CMS 12.9.5.10 — revocable trusted-browser sessions

This release adds an optional exception for a deliberately registered browser without weakening the ordinary u5CMS session policy. The feature is disabled by default.

## Security model

- An explicit maximum number of active trusted browsers per backend account and installation.
- A 256-bit random validator remains only in the browser; the database stores its SHA-256 hash.
- The cookie uses the `__Host-` prefix, `Secure`, `HttpOnly`, `SameSite=Strict`, a host-only scope and path `/`.
- The selector is installation-specific. There is no parent-domain cookie and no IP bypass.
- The current account password hash is fingerprinted. A password, SAML salt or derived account credential change invalidates the trusted browser.
- Registration requires an already authenticated backend session, a positive integer quota for the exact account, HTTPS, same-origin form submission and a server-derived CSRF token.
- The CSRF HMAC is stable across ordinary session rotation and accepts the verified public host supplied by a trusted reverse proxy without accepting cross-site submissions.
- The database assigns a unique place to each browser within the account's configured quota. Concurrent registrations cannot exceed that limit or replace an active browser.
- The authenticated account can list and revoke its own browsers individually, including lost profiles. Deliberate logout revokes only the current browser server-side.
- The browser cookie is renewed at most once per day and uses the browser maximum of 400 days. Regular use therefore keeps it alive without a fixed server-side expiry; it remains revocable at any time.

Possession of the dedicated browser profile remains the security boundary, as with every persistent web session. Anyone who controls that exact profile controls its authenticated cookies.

## Configuration

Keep the feature disabled unless a specific operational exception is required:

```php
$u5trusteddeviceenabled = 'no';
$u5trusteddeviceusers = array();
```

To enable it, set `yes` and map exact backend logins to their maximum number of active browsers:

```php
$u5trusteddeviceenabled = 'yes';
$u5trusteddeviceusers = array(
    'alice@example.org' => 3,
    'bob@example.org' => 2,
);
```

Missing users, zero and invalid values grant no trusted-browser access. Values must be integers, not numeric strings. Legacy numeric allow-lists still grant one browser per listed account. Conflicting case/whitespace variants use the lowest configured limit.

Sign in normally and open `/u5admin/trusteddevice.php` in each browser to be registered. This page shows usage against the quota, registration and last-use times and an individual revoke action. An already registered browser cannot consume another place. A full quota requires revocation of an existing browser before a new one can be registered. Lowering the limit rejects places above the new limit at their next use and revokes them when listed or used. Surviving places retain their browser tokens; no active browser is silently replaced.

[General configuration and user guide (German)](trusted-browsers.md)

## Deployment

Retain the installation's `config.php`, database, `r` and `fileversions` directories. No cron job is required. The small `u5trusteddevices` table is created only when the first browser is deliberately registered. The original one-browser table is upgraded on registration while preserving existing browser secrets. Existing tokens also work before that migration. Existing installations and sessions are unchanged while the feature is disabled.

For a manual rollout of this feature alone, install `trusteddevice.inc.php` and `u5admin/trusteddevice.php`, add the trusted-session hooks to the existing `login.inc.php`, `u5admin/usercheck.inc.php` and `logout.php`, and edit the existing `config.php` with the intended account quotas. Preserve the installation's other code, configuration and version. Retain OIDC policy guards: trusted sessions are disabled when OIDC is enabled. This manual procedure requires no helper scripts, persistent backup copies or temporary deployment files on the target server.

The separate opt-in `scripts/trusted-device-rollout.php` defaults to a read-only dry-run and requires explicit backup settings for its automated apply mode. It preserves existing account quotas. It is not needed for the manual procedure above.

## Verification

`tests/trusted-device-regression.php` exercises disabled-by-default behaviour, quota validation, legacy configuration, OIDC and Basic-auth exclusions, cookie parsing, account-bound CSRF, same-origin enforcement, HTTPS-only registration and both login hooks.

`tests/trusted-device-database-regression.php` uses real SQL and independent connections to verify schema migration without token loss, multiple browsers, account isolation, hashed secrets, tamper rejection, quota reduction, individual revocation, lost-profile replacement, credential changes and 36 simultaneous registration attempts against empty, revoked and legacy places. `tests/trusted-device-rollout-regression.php` also verifies that a repeated code rollout preserves configured quotas.
