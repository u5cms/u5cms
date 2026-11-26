# Trusted Device: technical reference

[Documentation home](../README.md) · [Trusted Device](../trusted-browsers.md)

## Summary

This reference maps the credential, database, authentication hooks and verification suites. Use the configuration or everyday-use guide for the shortest operational procedure.

**For** developers and installation maintainers. **Version** 12.10.0 with the same-origin form correction. **Scope** implementation and verification.

## Credential and scope

The cookie name starts with `__Host-u5td-` and includes an installation identifier. It has `Secure`, `HttpOnly`, `SameSite=Strict`, path `/` and no parent-domain scope. A value contains a random 128-bit selector and a random 256-bit validator. The database stores the selector and a SHA-256 verifier of the validator, rather than the reusable validator itself.

The installation identifier is derived from the installation's configured mail and database connection settings. Changing those settings can invalidate the association with existing cookies and records. A matching username on another installation does not share this registration.

The cookie is issued for **400 days** and renewed at most once per day during use. There is no separate fixed server-side expiry in this implementation. Browser expiry and deletion, account checks, configuration and revocation determine whether resumption is possible. Cookie retention is also subject to the browser's own policy.

## Database

`u5trusteddevices` records the installation, normalised username, numbered place, selector, validator verifier, account-credential fingerprint, creation time, last-use time and revocation time. The primary key is `(installation_id, username, slot)`; the selector is unique within the installation.

The table is created on registration. The former one-browser schema is migrated by adding `slot` and adjusting the primary key, preserving old tokens as place 1. Concurrent registrations use database uniqueness and conditional updates to avoid exceeding the allowance or replacing an active profile.

Resumption checks the token verifier, allowance, place, account existence and stored credential fingerprint. A deliberate account change takes precedence over a previous profile registration. Registration and revocation forms use account-bound CSRF protection and origin checks.

## Source map

| File | Responsibility |
| --- | --- |
| [`trusteddevice.inc.php`](../../trusteddevice.inc.php) | Configuration, cookies, database records, registration, resumption, revocation and form protection. |
| [`u5admin/trusteddevice.php`](../../u5admin/trusteddevice.php) | Account-specific registration list and management forms. |
| [`u5admin/usercheck.inc.php`](../../u5admin/usercheck.inc.php) | Backend authentication hook followed by ordinary account checks. |
| [`login.inc.php`](../../login.inc.php) | Frontend authentication hook followed by ordinary access checks. |
| [`logout.php`](../../logout.php) | Revocation of the current profile during CMS logout. |
| [`config.php.sample`](../../config.php.sample) | Disabled defaults and example settings. |

## Verification reference

The repository's existing suites cover policy and form protection in `tests/trusted-device-regression.php`, real SQL, concurrency and account switching in `tests/trusted-device-database-regression.php`, and repeatable rollout behaviour in `tests/trusted-device-rollout-regression.php`.

Run them only in an appropriate disposable test environment with their documented dependencies. Their existence is not evidence that a particular production installation has been tested. The [release notes](../release-12.9.5.10.md) record the original acceptance scope; [12.9.5.12](https://github.com/u5cms/u5cms/blob/v12.9.5.12/docs/release-12.9.5.12.md) records the account-switching correction.
