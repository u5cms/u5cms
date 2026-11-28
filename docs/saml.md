# Connect u5CMS to a SAML identity provider

[Documentation home](README.md) · [SAML and OIDC](federation.md)

## Summary

Install and register a SimpleSAMLphp service provider, arrange a trusted email-address attribute, then connect that service provider to the existing u5CMS accounts and page permissions. The CMS configuration selects an authentication source; it does not install SimpleSAMLphp or register the application with an identity provider. Start in a separate test installation and keep a recoverable copy of its database and configuration.

**For** installation and federation administrators. **Version** bridge implementation reviewed on stable u5CMS 12.10.14. The maintained development lines retain SAML; use their own configuration sample. **You need** server access, a provider registration, HTTPS and a compatible SimpleSAMLphp installation.

## 1. Prepare the service provider

1. Follow the upstream [installation guide](https://simplesamlphp.org/docs/stable/simplesamlphp-install.html) and [service-provider guide](https://simplesamlphp.org/docs/stable/simplesamlphp-sp.html). Configure the SP entity ID, trusted IdP metadata, signing/encryption policy and the attributes agreed with the IdP. Obtain the actual assertion-consumer and logout addresses from that SP's metadata; the CMS's `saml/login.php` is the application entry point, not the assertion-consumer endpoint.
2. Keep the SimpleSAMLphp configuration and private keys outside the public web directory. Expose only its public web directory under the CMS installation's `simplesaml/` path. The u5CMS bridge requires the local file `simplesaml/_include.php` and the `SimpleSAML\Auth\Simple` class.
3. Check the installed SimpleSAMLphp version's directory layout and PHP requirements. Its 2.x releases use `public/`; the older u5CMS sample's `www/` symlink example describes the older layout. Do not copy that historical path without checking the package. A web-server alias alone does not create the filesystem path required by PHP's `require`.
4. Define the intended authentication source in SimpleSAMLphp's `authsources.php`, for example `default-sp`. Test it at the SP before enabling the CMS bridge. Supply the private directory containing `authsources.php` and `config.php` as `$u5samlsspconfigdir` in the next step.

Use a currently maintained SimpleSAMLphp release compatible with the deployment. The CMS repository does not bundle or certify a particular SimpleSAMLphp/PHP combination. A working mock login is not a substitute for this SP test.

## 2. Agree the identity attribute and local rights

The current bridge takes the first value of each returned attribute. It derives a short name from the text after the final `/` in the attribute's key. It expects the resulting key `emailaddress`, for example from:

```text
http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress
```

Agree an unambiguous mapping at the trusted SP/IdP boundary. Verify the actual resulting key: a different attribute called `mail`, `email` or an OID is not automatically interchangeable. The selected address must be controlled and verified by the identity provider and must identify the intended CMS account. Avoid colliding short names and multivalue identities whose first value is ambiguous.

Create or retain the appropriate CMS account, intranet membership or page-login entry with that username. The SAML bridge trims and lowercases the email-address value. It does not map a SAML NameID or a provider role directly to a CMS administrator role. Keep [local permissions](permissions.md) authoritative and review existing record ownership before changing usernames.

## 3. Configure the CMS

In the installation's existing private `config.php`, add or update one authoritative block. Preserve the database, mail and other site settings. Replace the example paths, destination and both secret placeholders with deployment-specific values. Generate distinct unpredictable secrets; do not use the sample's literal `randomstring`.

```php
$usesessioninsteadofbasicauth = 'yes';
$u5samlsalt = 'REPLACE_WITH_AN_INSTALLATION_SECRET';
$u5samlnonce = 'REPLACE_WITH_A_DIFFERENT_INSTALLATION_SECRET';
$u5samlsspconfigdir = '/srv/example-saml/config';
$u5samlsspauthsourcekey = 'default-sp';
$u5samllogoutresultpage = 'https://cms.example.org/index.php?c=loggedout';
$u5samlautointranetenrollment = 'no';
$u5samlmockauth = 'no';
$u5samlinfrontend = 'yes';
$u5samlinfrontendyesenforcedifloginformsgetudoesnotcontain = '';
```

Create the `loggedout` page, or choose an existing public destination. Check that the site's `$evaluateifhttpsisinuse` recognises the actual HTTPS deployment, including its trusted reverse proxy, so authentication cookies receive the correct Secure flag. Do not accept arbitrary public forwarded headers as evidence of HTTPS.

Activation is a nonempty `$u5samlsalt`. The mock option must remain `'no'` in a real installation. On an OIDC-capable branch, disable OIDC before selecting SAML.

## 4. Verify before admitting users

| Check | Required result |
| --- | --- |
| Open `/u5admin/` in a fresh browser profile as a configured editor | The provider authenticates the person; the expected CMS account and its existing administration rights apply. |
| Sign in as a person without a backend account | Authentication does not create a backend account or higher admin rights. |
| Open a protected reader page with and without its local rights | The authorised reader succeeds; the other identity cannot read it. |
| Open a deep editor link containing query parameters, then authenticate | The intended editor resource and parameters survive the return. |
| Save a protected form and reopen an existing personal response | The stored `authuser` and personal record selection use the intended CMS username. |
| Open a protected file in the real hosting/proxy setup | The file's own access rules apply, including after logout or session expiry. |
| Log out, revisit a protected page, and try another account | Local and provider logout follow the agreed policy; no previous user's CMS identity remains. |

The real login path is provider → `saml/login.php` → CMS login completion → original target. Use the normal CMS login screens, which build this path. Do not put credentials or assertions in a manually assembled return URL.

## Frontend policy and enrolment

| Setting | Meaning |
| --- | --- |
| `$u5samlinfrontend = 'yes'` | Use SAML for protected frontend entry as well as backend entry. |
| `$u5samlinfrontend = 'no'`, empty exception marker | Keep the existing local frontend login. Backend entry still uses SAML. |
| `$u5samlinfrontend = 'no'`, nonempty exception marker | Keep local frontend login only when the return URL contains that literal, case-sensitive substring; otherwise require SAML. The marker selects a login mechanism, not an access grant. |
| `$u5samlautointranetenrollment = 'yes'` | Admit successfully authenticated identities to the ordinary intranet member list and provision its page/file rights. This broadens intranet admission; it does not create backend administrators. Default to `'no'` until that admission policy is intended. |

The separate page-logins mechanism still applies. A public page does not become restricted merely because SAML is configured. Use [page visibility and logins](navigation.md) for that decision.

## Operations, attributes and rollback

The legacy bridge places the username and returned attributes in `u5saml…` cookies. These application cookies are not archived signed SAML assertions. Custom code must not treat arbitrary attribute cookies as independent proof of identity or privilege. Limit released attributes and escape any displayed value for its output context. Debug/status endpoints can reveal attributes; keep them restricted and leave debug mode off for users.

For an existing backend account, the bridge may replace its stored password hash with a SAML-derived value. This is a material difference from OIDC and affects a return to local password login. A rollback plan therefore needs the matching database/account state or an approved password-reset procedure as well as the previous configuration. Keep an authenticated administration session and a separate recovery route while testing; do not rely on a remembered old local password.

To disable SAML, remove or empty `$u5samlsalt` in the private configuration, restore the intended local authentication policy and resolve affected account credentials. Invalidate/review existing CMS and SP sessions as part of the planned change. Test from a fresh browser profile; changing a switch alone does not prove every previously issued session has ended.

| Symptom | Check and next action |
| --- | --- |
| PHP cannot load `simplesaml/_include.php` | Check the local link/directory and the installed SP's public directory; a URL alias is insufficient. |
| The SP works but the CMS identity is empty or wrong | Inspect the agreed attribute mapping privately; ensure its short key is `emailaddress` and its first value is the intended address. |
| Authentication succeeds but the CMS denies access | Compare the actual username with CMS account, intranet and page rights. Do not enable broad enrolment to repair a misspelt account. |
| Repeated login or logout returns | Check HTTPS recognition, cookie scope, SP sessions, authentication-source key and the configured destination. |
| A custom evidence form rejects a submission | Follow [authenticated messaging](authenticated-messaging.md); ordinary SAML login does not supply the required archive. |

## Reference and evidence

Implementation: [`saml/login.php`](../saml/login.php), [`saml.inc.php`](../saml.inc.php), [`loginsave.php`](../loginsave.php), [`saml/autointranetenroll.php`](../saml/autointranetenroll.php), [`saml/logout.php`](../saml/logout.php) and [`config.php.sample`](../config.php.sample). The [backend return tests](../tests/backend-saml-return-url.cjs) exercise the CMS return path using a fixture. This guide's review does not certify a live provider or a new SimpleSAMLphp installation.
