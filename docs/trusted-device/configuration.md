# Trusted Device: configuration

[Documentation home](../README.md) · [Trusted Device](../trusted-browsers.md)

## Summary

In each installation's existing private `config.php`, enable `$u5trusteddeviceenabled` and assign positive integer browser limits in `$u5trusteddeviceusers`. Use HTTPS and session authentication, with u5CMS OIDC disabled. Sign in normally, register the intended profile and check quota and revocation behaviour. Disabling an allowance prevents resumption but does not permanently revoke every retained registration.

The installation administrator makes these changes in the installation's existing `config.php`. The feature is configured separately for each u5CMS installation. Editing `config.php.sample` alone does not enable it on a running website.

## Prerequisites

| Requirement | Check |
| --- | --- |
| Supported source | Install the complete matching u5CMS release. The feature starts at 12.9.5.10; 12.9.5.12 corrects account switching. |
| HTTPS | Use the intended public HTTPS host for both registration and later access. |
| Session authentication | The installation uses `$usesessioninsteadofbasicauth = 'yes';`. |
| OIDC | u5CMS OIDC is not enabled. Keep the installation's chosen authentication policy. |
| Existing account | The person already has a valid backend account and the required permissions. |
| Database permissions | Initial registration can create `u5trusteddevices`; an older one-browser table may also need `ALTER` permission. Normal use needs the corresponding read and write permissions. |

## Enable one browser for one account

Merge these settings into the existing configuration. Replace the example login with the account's actual CMS login and replace an existing assignment rather than adding a competing second assignment.

```php
$u5trusteddeviceenabled = 'yes';
$u5trusteddeviceusers = array(
    'alice@example.org' => 1,
);
```

Preserve the installation's other settings, credentials and file encoding. The username must identify the CMS account, which may differ from a delivery email address or an identity provider's displayed name.

## Set different limits

```php
$u5trusteddeviceenabled = 'yes';
$u5trusteddeviceusers = array(
    'alice@example.org' => 3,
    'bob@example.org' => 2,
);
```

| Setting | Result |
| --- | --- |
| `'alice@example.org' => 3` | Alice can register up to three browser profiles in this installation. |
| `'bob@example.org' => 2` | Bob has an independent allowance of two profiles. |
| Account omitted or assigned `0` | That account cannot register or resume a trusted browser. |
| `$u5trusteddeviceenabled = 'no';` | Trusted-browser registration and resumption are disabled throughout the installation. |

Limits must be positive PHP integers, written without quotation marks. Strings such as `'2'`, fractions, negative values and booleans grant no allowance. Account matching uses u5CMS's existing username normalisation; conflicting case or whitespace variants use the lowest configured limit. Avoid duplicate variants.

The earlier numeric allow-list remains supported and grants one place per listed account:

```php
$u5trusteddeviceusers = array('alice@example.org', 'bob@example.org');
```

Use the explicit account-to-limit map for new configurations.

## Change an allowance

Increasing a limit adds free places without replacing existing registrations. Lowering it keeps registrations in the remaining numbered places. A browser in a place above the new limit is rejected at its next use and its registration is revoked when listed or used.

Review the browser list before lowering a limit. Revoke unwanted profiles individually so that you know which registrations remain. A new registration never silently replaces an active browser when the allowance is full.

## Register and check

1. Check the edited configuration's PHP syntax using the installation's supported PHP version.
2. Sign in normally in the intended browser profile.
3. Open `https://cms.example.org/u5admin/trusteddevice.php`, using your own host and installation path.
4. Check the displayed account and allowance, then choose **Trust this browser**.
5. Confirm **This browser is now trusted.** and the **(this browser)** marker.
6. In a test installation, confirm resumption after the ordinary session ends, quota enforcement and individual revocation. CMS logout deliberately revokes the registration, so it is not a test of ordinary session expiry.

The table is created on first deliberate registration. The earlier single-browser schema is upgraded during registration while retaining existing tokens. No cron job is required.

## Disable and re-enable

```php
$u5trusteddeviceenabled = 'no';
```

Disabling the feature stops trusted-browser resumption. It does not delete the registration records or promise immediate termination of ordinary sessions that already exist. Removing an account from the map or setting its limit to `0` likewise prevents trusted-browser use without constituting a permanent token revocation. If the previous settings are restored, a retained registration may become usable again.

For permanent withdrawal, revoke the relevant registrations while the feature and account allowance are still enabled, then disable or remove the allowance. To end access immediately, also follow the installation's account and active-session procedures.

## Deployment reference

Use the complete release and the normal update procedure. A maintainer performing a deliberately limited rollout must keep `trusteddevice.inc.php`, `u5admin/trusteddevice.php` and their matching hooks in `login.inc.php`, `u5admin/usercheck.inc.php` and `logout.php` consistent. Preserve existing OIDC guards and local configuration. The [original release notes](../release-12.9.5.10.md) describe this specialised procedure.
