<a id="vertrauenswürdige-browser-in-u5cms"></a>
<a id="configphp-anpassen"></a>
<a id="browser-registrieren"></a>
<a id="browser-widerrufen-oder-ersetzen"></a>
<a id="cloudbrowser-von-mehreren-personen"></a>
<a id="laufzeit-und-technische-voraussetzungen"></a>
<a id="manueller-rollout-nur-dieser-funktion"></a>

# Trusted Device

[Documentation home](README.md)

## Summary

Trusted Device is u5CMS's optional trusted-browser feature. A deliberately registered browser can resume the account's CMS authentication after the ordinary session ends. It retains that account's existing permissions.

The trusted item is a **browser profile**, rather than the whole physical device. A different browser, a new profile or a replacement cloud browser needs its own registration. The feature is available from **12.9.5.10** and is disabled by default. Use **12.9.5.12 or later** for the corrected account-switching behaviour.

For a stable installation, use **12.10.2 or later**. It includes the registration and database recovery fixes from 12.10.1. Opening the management page while signed out now returns you to that page after the ordinary CMS form login. See the [12.10.2 release notes](https://github.com/u5cms/u5cms/blob/v12.10.2/docs/release-12.10.2.md).

**Audience:** installation administrators and account holders. **Version:** feature from 12.9.5.10; direct form login, registration and revocation checked on the 12.10.2 update. **Scope:** browser registrations per account and installation.

## Read by task

| Task | Guide |
| --- | --- |
| Learn through a two-profile exercise | [Practical walkthrough](trusted-device/walkthrough.md) |
| Enable the feature and allocate browser places | [Configuration](trusted-device/configuration.md) |
| Register, use, replace or revoke a browser | [Everyday use](trusted-device/usage.md) |
| Prepare a cloud browser and give an agent a task | [Working with an AI agent](trusted-device/agents.md) |
| Resolve a missing page, full quota or lost registration | [Troubleshooting](trusted-device/troubleshooting.md) |
| Understand cookies, database records and authentication hooks | [Technical reference](trusted-device/reference.md) |

## How it works

1. The installation administrator enables the feature for specific existing CMS accounts and sets a maximum number of browser profiles for each account.
2. The account holder signs in through the installation's normal authentication flow and registers the intended browser.
3. u5CMS places a secret cookie in that profile and records a verifier in the database.
4. On subsequent authenticated requests, u5CMS can restore the account's session credentials. The normal permission checks still apply.
5. An explicit CMS logout revokes the current browser's registration. A registration can also be revoked individually from the management page.

Registration does not create an account, grant additional permissions or establish a session on another website. With a federated login such as AGOV through SAML, this is a local u5CMS persistence mechanism; it does not extend the identity provider's session or prove a fresh identity-provider login for each operation.

## Before you start

You need HTTPS, u5CMS session authentication, a permitted backend account and control of the intended browser profile. Trusted Device is unavailable with HTTP Basic authentication or when u5CMS OIDC is enabled. A cloud browser must retain the profile and its cookies for the registration to remain usable.

The browser is an access credential: anyone controlling that registered profile can act with the account's permissions. Use a profile controlled by the intended operator and grant only the permissions needed for the work.

The laboratory also identified and corrected a management-form origin failure in earlier source. Its [walkthrough](trusted-device/walkthrough.md#observed-results) records the correction. Compare the actual installed source as well as its version label when using an older package.
