# Configuring an installation

[Documentation home](README.md)

## Summary

Configuration has two parts: the application version determines which features exist, and the installation's own `config.php` determines which are enabled. A setting copied from a development branch cannot add the corresponding implementation to an older installation.

**For** installation administrators. **Version** examples checked on 12.10.0; use the matching branch sample. **Scope** one installation.

## Find the right starting point

Setting up an empty database and a new web directory? Start with [Install a new u5CMS site](installation.md).

1. Identify the installed version. Recent versions include it in the backend logout tooltip; the release also identifies it in `index.php`.
2. Use the matching branch's `config.php.sample` as a reference for available settings.
3. Edit the installation's existing `config.php`, retaining its database connection, mail setup, authentication and site-specific hooks.
4. Check PHP syntax with the PHP version used by that installation.
5. Test the intended behaviour and an ordinary existing workflow before deploying the configuration.

The sample is a reference. Replacing a site's configuration wholesale can lose choices which the sample cannot know.

## Settings by task

| Task | Settings or guide |
| --- | --- |
| Connect the database | `$host`, `$username`, `$password`, `$db` in the private configuration. |
| Identify the site's system sender | `$mymail`; use a real address appropriate to the website's mail setup. |
| Select SMTP delivery | `$usesmtp` and `$mysmtpoptions`; see [email](email.md). |
| Select CMS session authentication | `$usesessioninsteadofbasicauth`; coordinate changes with the existing login integration. |
| Connect an external identity provider | [SAML and OIDC](federation.md); [SAML configuration](saml.md) for the stable bridge. OIDC requires V15 or a successor. |
| Choose eligible revisable form records | `$mfwhereclause`; see [MF and MFS](forms/mf-mfs.md). This condition is shared by those workflows. |
| Allow trusted browser profiles | `$u5trusteddeviceenabled`, `$u5trusteddeviceusers`; see [Trusted Device](trusted-browsers.md). |
| Share SELF records by organisation | `$u5onlyminescope`, `$u5onlymineadmindomains`; see [permissions](permissions.md). |
| Protect public form submissions | Turnstile settings in [forms](forms.md). |
| Control automatic background launches | `$blockautoincluderefresherforhtaccessandindex`; see [search and indexing](search-indexing.md). |
| Control database-backup compression and mail | `$backup_compress`, `$backup_send_mail`; see [updates and recovery](updates-backups.md). |

## A small configuration experiment

In a test installation, allow Alice one trusted browser. Register profile A, then sign in normally in profile B. B can use the account but cannot register another trusted profile. Change only Alice's limit from `1` to `2`: B can now register without replacing A.

This demonstrates that permission to sign in and permission to retain a browser are different settings. The [browser exercise](trusted-device/walkthrough.md) verifies the distinction through the real interface.

## Interpret values literally

The different settings have different types. Trusted Device activation uses the string `'yes'`, whereas each browser allowance uses an integer such as `2`. The string `'2'` is rejected as an allowance. Do not convert every option to a boolean or every value to a string for consistency.

Use one authoritative assignment for each setting. A later assignment in PHP can override an earlier one, making a correct-looking example ineffective.

## Check compatibility before enabling an option

Optional features can impose different operational requirements. Trusted Device requires HTTPS and session authentication and is excluded when OIDC is active. Archived sender evidence requires its own SAML archive and compatible forms. Sharing SELF records by domain requires verified email identities. Follow the relevant topic's prerequisites instead of enabling every available switch.

For an application upgrade, retain configuration and site data and follow [updates and recovery](updates-backups.md). Application sources use established Latin-1/Windows-1252 boundaries; preserve the existing file encoding when editing configuration.
