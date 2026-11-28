# Sign in through SAML or OpenID Connect

[Documentation home](README.md) · [Accounts and permissions](permissions.md)

## Summary

An identity provider can authenticate a person for u5CMS; u5CMS still decides which pages, records and administration functions that person may use. SAML is available in the stable line. OpenID Connect (OIDC) is an optional feature of V15 and its successors. Choose the guide for the installed branch before configuring either integration.

**For** installation administrators and identity-provider administrators. **Version** stable 12.10.14 and the maintained V13–V17 development lines, reviewed on 28 November 2026.

## Choose the integration

| Question | SAML | OIDC |
| --- | --- | --- |
| Where is it available? | Stable, V13–V17 | V15, V16 and V17; absent from stable, V13 and V14 |
| What supplies the protocol implementation? | A separately installed and configured SimpleSAMLphp service provider | The branch's `oidc/` implementation and its included dependencies |
| How is the CMS identity selected? | The trusted provider's email-address attribute, interpreted by the existing SAML bridge | Explicit issuer/client subject mapping by default; an optional verified-email profile has a durable binding table |
| Where are permissions maintained? | Existing CMS accounts, intranet membership and page logins | Existing CMS accounts, intranet membership and page logins |
| Does an external login create a backend administrator? | No | No |
| Can existing password hashes change? | Yes: the SAML bridge updates an existing backend account's password hash | No: the OIDC bridge derives its CMS credentials for the current request |
| Where is the setup procedure? | [SAML integration](saml.md) | The matching branch's guide below |

| Installed development line | OIDC setup, claims, sessions and rollback |
| --- | --- |
| V15 | [OpenID Connect](https://github.com/u5cms/u5cms/blob/V15-Phrase-Search-and-OIDC/docs/oidc.md) |
| V16 | [OpenID Connect](https://github.com/u5cms/u5cms/blob/V16-Default-Design/docs/oidc.md) |
| V17 | [OpenID Connect](https://github.com/u5cms/u5cms/blob/V17-New-Responsive-Layouts/docs/oidc.md) |

The OIDC guides contain a complete configuration example, provider registration, supported token profile, claim access, session/logout behaviour, operational checks and rollback. Use the PHP requirements of the installed branch as well as those of its authentication dependencies.

## Keep authentication and ownership separate

Before switching providers or protocols, identify the exact CMS username that each person must retain. That username can own existing form records. Changing it can change what a personal `[mf]` form or a SELF list finds; authentication does not transfer those records. Review [MF and MFS](forms/mf-mfs.md) and [SELF ownership](permissions.md) as part of the migration.

Select one federation protocol per installation. OIDC configuration cannot add OIDC to a branch which lacks it. On an OIDC branch, follow its documented procedure for disabling the SAML activation variable and adapting custom SAML attribute readers.

## Check the complete path

Use a test installation with a real provider registration. Check an authorised backend user, an ordinary member and someone without local rights. Include a protected page, a form save, a private download, an expired session and logout. Record the provider and CMS versions and the observed results. A protocol fixture or successful redirect alone does not establish production acceptance.

For append-only authenticated messages, [sender evidence](authenticated-messaging.md) is a separate integration. A SAML login alone does not install an assertion archive, and an OIDC login does not produce the SAML archive required by `saml-archive-v1`.
