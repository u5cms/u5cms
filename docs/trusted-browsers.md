# Trusted Device

[Documentation home](README.md)

Trusted Device is u5CMS's optional trusted-browser feature. A deliberately registered browser can resume the account's CMS authentication after the ordinary session ends. It retains that account's existing permissions.

The trusted item is a **browser profile**, rather than the whole physical device. A different browser, a new profile or a replacement cloud browser needs its own registration. The feature is available from **12.9.5.10** and is disabled by default. Use **12.9.5.12 or later** for the corrected account-switching behaviour.

## Read by task

| Task | Guide |
| --- | --- |
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
