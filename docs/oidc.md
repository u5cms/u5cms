# Optional OpenID Connect in u5CMS 12.12.0

OIDC is an opt-in alternative to the existing SAML integration. An installation
selects one federation protocol in `config.php`; it does not present a new
protocol selector to its users. Without an enabled `$u5oidc` configuration,
the existing local and SAML login paths continue to run.

The implementation is complete for the supported profile below. Enabling it
still requires a client registration at the actual identity provider and a
deployment-specific acceptance test. This repository contains no live client
credentials, issuer registration or provider-specific production configuration.

## Supported profile and requirements

* PHP 8.0 or newer, OpenSSL, cURL, mbstring, JSON and the PHP session extension.
* HTTPS on both CMS and provider, with certificate and hostname validation.
  The existing `$evaluateifhttpsisinuse` configuration must correctly recognize
  HTTPS, including behind a trusted reverse proxy. Never trust arbitrary
  forwarded headers from the public internet.
* Authorization Code Flow, `response_mode=query`, PKCE **S256**, state and nonce.
  PKCE is always sent, including for confidential clients. Where discovery omits
  its optional PKCE capabilities, verify S256 enforcement in the provider's
  client policy before enabling the integration.
* Signed **RS256** ID tokens and provider JWKS. RSA keys must satisfy the JWT
  library's minimum 2048-bit key size. No unsigned, HMAC, encrypted, implicit,
  hybrid or multi-audience token profile is accepted.
* Token endpoint authentication: `client_secret_basic`, `client_secret_post`
  or an explicitly registered public client using `none` with PKCE. Prefer a
  confidential web client. `private_key_jwt`, PAR, JAR and DPoP are not implemented.
* A reliable server-side PHP session store, shared by the CMS nodes in a cluster.
  Use a private directory outside the document root or an appropriately secured
  session backend. Configure retention/garbage collection for the intended CMS
  session lifetime. A lost session fails closed and requires a fresh login.
* Cookie-based CMS authentication (`$usesessioninsteadofbasicauth='yes'`). OIDC
  cannot protect Apache-served files through HTTP Basic authentication. Private
  files must continue to use the CMS's existing protected download endpoints.

OIDC uses its own autoloader and dependency directory under `oidc/vendor`.
The pinned `firebase/php-jwt` 7.2.1 package validates JWS signatures and JWKs;
the root mail dependencies are unchanged. Its BSD licence is retained.
Dependencies are included in the branch, following this repository's existing
distribution model. For a clean rebuild:

```sh
composer install --working-dir=oidc --no-dev --no-plugins --no-scripts --prefer-dist
composer audit --working-dir=oidc --locked
```

## Register the client and configure the CMS

1. Register a web client at the provider. Enable code flow and PKCE S256, select
   the token authentication method and RS256, and register **exactly**
   `https://cms.example.org/oidc/callback.php`. For a subdirectory installation,
   include that directory, for example `https://cms.example.org/cms/oidc/callback.php`.
   No wildcard redirect URI is used.
2. Assign the intended people to the provider client. Obtain each person's
   stable subject identifier **for this client** through the provider's trusted
   administration process. A pairwise subject for a different client is not
   interchangeable. Do not copy an unverified token from a user as identity proof.
3. Retain/create the intended backend account, intranet membership or page-group
   entry in u5CMS. Map the provider subject to that exact existing CMS username.
4. Copy the commented OIDC configuration block from `config.php.sample` to the
   real `config.php`. Fill in the actual issuer, client ID, callback URI and
   subject mappings. Obtain the client secret from the deployment's secret store
   or environment. Remove/disable `$u5samlsalt` when selecting OIDC.

```php
$usesessioninsteadofbasicauth = 'yes';
$u5oidc = array(
    'enabled' => true,
    'issuer' => 'https://idp.example.org/realms/example',
    'client_id' => 'your-registered-client-id',
    'client_secret' => getenv('U5CMS_OIDC_CLIENT_SECRET') ?: '',
    'redirect_uri' => 'https://cms.example.org/oidc/callback.php',
    'token_auth_method' => 'client_secret_basic',
    'frontend' => 'yes',
    'frontend_enforced_unless_target_contains' => '',
    'auto_intranet_enrollment' => false,
    'login_info' => '',
    'not_a_member_message' => '',
    'logout_result_page' => 'index.php?c=loggedout',
    'claims_to_keep' => array(),
    'scopes' => array('openid'),
    'subjects' => array(
        'stable-subject-for-this-client' => 'existing-user@example.org',
    ),
    'identity_mode' => 'subject_map',
    'session_ttl' => 3600,
    'idle_timeout' => 900,
    'clock_skew' => 30,
    'provider_logout' => false,
    'allowed_endpoint_origins' => array(),
    'ca_bundle' => '',
);
```

The issuer must match discovery and the token **exactly**, including its path
and any trailing slash. Endpoint origins default to that issuer's origin.
If a provider legitimately advertises a different origin, add only that
specific HTTPS origin to `allowed_endpoint_origins`. Do not populate this list
from request parameters. Redirects on discovery, token and JWKS backchannels
are refused; endpoint responses have time and size limits. `ca_bundle` can
select an organization's CA trust bundle; it never disables TLS verification.

The normal public login starts at `oidc/login.php?u=...`. Return targets stay on
the configured CMS origin and within its installation directory, including
when the CMS is installed in a subdirectory. The request's Host header does
not determine that origin. Backend and frontend login screens link there
automatically when OIDC is enabled.

## Existing SAML policy and OIDC equivalents

The frontend policy has the same precedence as SAML. Backend entry points and
backend previews always require OIDC, including when an exception marker occurs
in their target URL. The marker is a literal, case-sensitive substring of the
return URL, not a regular expression. URL policy reads the original URL before
the legacy frontend's HTML escaping, preserving markers containing `&`.

| `frontend` | Nonempty exception marker | Marker in target | Frontend authentication |
| --- | --- | --- | --- |
| `yes` | Either | Either | OIDC |
| `no` | No | Not applicable | Existing local login |
| `no` | Yes | Yes | Existing local login |
| `no` | Yes | No | OIDC |

For example, `frontend='no'` with
`frontend_enforced_unless_target_contains='&local=1'` permits local login for a
frontend return URL containing `&local=1`; other frontend targets require OIDC.
The exception selects an authentication mechanism. It does not grant access or
bypass that mechanism's existing password/permission checks. Backend OIDC and
local frontend cookies can coexist; a backend OIDC login preserves these local
frontend credentials in this mixed mode.

| Existing SAML setting or function | OIDC equivalent |
| --- | --- |
| `$u5samlinfrontend` | `frontend` |
| `$u5samlinfrontendyesenforcedifloginformsgetudoesnotcontain` | `frontend_enforced_unless_target_contains` |
| `$u5samlautointranetenrollment='yes'` | `auto_intranet_enrollment=true` |
| `$u5samllogininfo` | `login_info` |
| `$u5notamembermessage` | `not_a_member_message` |
| `$u5samllogoutresultpage` | `logout_result_page` |
| SimpleSAMLphp configuration/auth source | Exact OIDC issuer, client registration, discovery and isolated JWT dependency |
| SAML salt/nonce cookies | Server-side, browser-bound state/nonce/PKCE transaction and rotated OIDC session |
| Exported SAML attribute cookies | Explicit retained verified claims through `u5OidcClaim()`; no unverified browser attribute cookies |
| SAML mock authentication | Ephemeral HTTPS provider fixture exercising the real OIDC flow; no production authentication-bypass switch |

If an OIDC policy key in the first six rows is omitted and the corresponding
legacy variable is still set, that value is inherited. An explicitly configured
OIDC value wins. The enrollment legacy string `yes` maps to boolean `true`.
The SAML salt must still be disabled: protocol selection is explicit and both
protocols cannot be active simultaneously. Login information and nonmember
messages are trusted administrator-supplied HTML, just as in the existing SAML
flow; never populate them with unescaped provider/user input. A relative logout result stays within the CMS. A fixed administrator-configured
absolute HTTPS result may also be external, preserving the SAML landing-page
use case. Request parameters cannot override this configured destination.

With automatic intranet enrollment explicitly enabled, a successfully verified
and explicitly mapped identity with an email CMS username is appended once to
the normal intranet member list. The normal intranet/private-file provisioning
runs before the redirect, including a lock/progress retry and resumable pending
marker. Existing members do not trigger needless republication. This option
does not create backend users or grant higher admin rights. An unmapped external
subject is rejected even when enrollment is enabled.

To preserve the SAML enrollment use case for new people without manually
pre-registering every subject, select the optional **verified-email profile**:

```php
$u5oidc['identity_mode'] = 'verified_email';
$u5oidc['scopes'] = array('openid', 'email');
$u5oidc['auto_intranet_enrollment'] = true;
```

The trusted provider must put `email` and boolean `email_verified=true` in the
**signed ID token**. Merely receiving an email string is insufficient. An
explicit `subjects` entry still wins; usernames reserved by those entries
cannot be claimed by another subject through the email profile. Keep privileged
backend identities explicitly mapped when using this profile for broader
intranet enrollment.

Before enabling it, prepare the dedicated binding table using the real CMS
database configuration:

```sh
php oidc/check.php --install-bindings
php oidc/check.php --discover
```

The installer is CLI-only and idempotent. The SQL is also provided in
`oidc/schema.sql`. This profile needs mysqli and the binding table; the default
`subject_map` profile needs neither an extra table nor an OIDC database migration.
The table stores a digest of issuer/client, a digest of subject, the canonical
CMS username and timestamps. Unique keys bind one subject to one username and
one username to one subject within an issuer/client. First-login races cannot
replace an existing binding. A changed email on an existing subject or the same
email arriving from another subject fails closed for administrative review.
There is no silent relinking. Local CMS permissions remain authoritative, and
auto-enrollment still never creates a backend account or higher admin rights.

Review identity corrections administratively and invalidate existing sessions
when changing bindings. Keep the table when disabling OIDC; deleting it loses
the previous ownership information that prevents recycled-email takeover.
Switching issuer/client establishes a new binding namespace and requires the
same identity review as other provider migrations.

OIDC text is UTF-8. The bridge converts usernames to the existing native CMS
encoding/IDN convention without changing global mbstring settings. International
email domains require PHP intl. Retained application claims stay UTF-8; for
display in a native CMS PHP object, use, for example,
`ehtml(u5OidcNativeText(u5OidcClaim('given_name')))`. Escape values for their
actual output context.

To retain provider attributes for custom PHP objects, configure, for example,
`claims_to_keep=array('email','given_name','adminEmployeeNumber')` and request the
provider's corresponding scopes. Read these values with
`u5OidcClaim('adminEmployeeNumber')` in an OIDC-protected request. Only claims
from the validated ID token and this explicit allowlist are retained server-side.
They do not grant CMS rights. No UserInfo request or attribute-cookie emulation
is performed. Existing custom code reading SAML cookies needs this explicit
adaptation when switching protocols; stale SAML cookies are cleared on OIDC login.

## Identity, rights and compatibility

The external identity is the configured **issuer plus case-sensitive `sub`**.
In the default `subject_map` profile, email, groups, roles and other token
claims do not link accounts or grant CMS rights. A token with an administrator's
email and an unmapped subject is rejected. The explicitly selected
`verified_email` profile adds the durable binding described above. Changing the
CMS username or provider client requires reviewing its identity mappings.

OIDC creates no backend accounts and changes no account passwords. Existing account,
higher-admin, intranet and page-group checks remain authoritative. The bridge
derives the credentials those checks expect **only in the current PHP request**,
after validating the server-side OIDC session. It neither sends these derived
credentials to the browser nor rewrites the accounts table or `.htpasswd`.
On OIDC-protected paths, browser-supplied legacy `u`/`p` cookies and Basic
credentials cannot bypass OIDC. Explicitly local frontend paths retain their
existing credential checks. OIDC sessions have an installation-specific
Secure, HttpOnly, SameSite=Lax cookie, also scoped to the CMS directory.

The bridge covers backend login and higher-admin checks, frontend page rights,
intranet/search rights, frontend form saves, upload identity attribution and
the protected file/thumbnail authorization gate. File-specific `.htpasswd`
lists remain authoritative; maintain/regenerate those lists using the existing
CMS workflow when changing file permissions. Direct web-server rules, custom
PHP objects and integrations that implement their own authentication require
their own deployment review. They are not converted by this branch.

Password reset, intranet-password requests and backend password changes are
blocked in OIDC mode. Inviting a CMS backend user does not send a local password;
the invitation screen explains the subject mapping that is also required.
Automatic intranet enrollment requires the explicit setting above. There is no
fallback password login on an OIDC-protected path or IdP-derived role elevation.

## Session and logout behavior

* Login transactions last five minutes and are single-use, including cancelled
  or failed callbacks. Up to five transactions support separate browser tabs.
* The session ID rotates after successful authentication. State, nonce and
  PKCE verifier stay server-side and are bound to the browser transaction.
* Absolute RP session lifetime defaults to one hour. ID token expiry is checked
  at login; it does not shorten the separately configured CMS session. Idle
  timeout defaults to 15 minutes, with activity written at most every 30 seconds.
* No access or refresh token is persisted. There is no background refresh,
  silent login iframe, front-channel logout or back-channel logout receiver.
* The local session is removed before logout completes; pending logins and
  an in-flight token exchange cannot restore it. PHP session locks are released
  before provider requests and before streaming protected downloads.
* With `provider_logout=false`, logout ends the CMS session; the provider's SSO
  session remains. To request provider logout too, set `provider_logout=true`
  and register `https://cms.example.org/oidc/loggedout.php` (including any CMS
  subdirectory) as the exact post-logout redirect URI at the provider. Discovery
  must then advertise a trusted `end_session_endpoint`. Only in this mode is
  the ID token retained server-side for `id_token_hint`; no access token is
  retained. The logout return uses a separate one-time state. If the provider
  is unavailable during logout, the CMS session is still gone.

Changing the OIDC configuration, mappings, client secret, CMS
`$sessioncookiehashsalt` or database password invalidates existing OIDC sessions.
The application still checks local rights on subsequent requests. Provider-side
account suspension alone does not immediately terminate an existing CMS session;
its maximum validity is the lifetime above. For immediate revocation, remove
the local rights/mapping or rotate `$sessioncookiehashsalt`. With automatic
intranet admission enabled, a still-eligible identity can enroll again; durable
revocation therefore also requires removing its provider/client eligibility or
disabling that admission policy.

## Verification, operation and rollback

Run from the installation directory, with its actual runtime and environment:

```sh
php oidc/check.php
php oidc/check.php --discover
```

In the default profile, the first command checks configuration and dependencies
without connecting to the database or provider. In `verified_email` mode it
also checks the required binding table and unique indexes. The second additionally checks HTTPS discovery,
issuer, endpoint trust and advertised capabilities. Neither prints secrets,
tokens or individual subject mappings. The command is unavailable over HTTP.
Before enabling the option for users, verify a real provider login and logout
in a separate test instance, including a backend user, an intranet member and
a person without the requested rights. Test private downloads in the actual
web-server/proxy setup and check its HTTPS/session-store configuration.

Authentication errors have a generic screen and correlation reference. PHP's
error log contains that reference and a bounded, non-sensitive failure reason.
Do not enable request-body/token logging at the reverse proxy. As with other
code-flow clients, callback query parameters and logout `id_token_hint` values
must be excluded/redacted in access logs and observability tooling. OIDC
protocol responses use no-store and no-referrer headers. No debug route exposes claims.

Disable OIDC by removing its block or setting `'enabled' => false`. Re-enable
the previous SAML configuration if applicable, and rotate
`$sessioncookiehashsalt` to invalidate old sessions before a later re-enable.
The default profile requires no OIDC schema migration, rollback SQL or password
restoration. Keep an optional verified-email binding table across rollbacks.
The inherited 12.11 code-asset migrations remain unchanged apart from their
displayed target version.

The reproducible regression commands and observed results are recorded in
[12.12.0-oidc-validation.md](12.12.0-oidc-validation.md).

## Protocol references

* [OpenID Connect Core, code flow and ID token validation](https://openid.net/specs/openid-connect-core-1_0.html#CodeFlowAuth)
* [OpenID Connect Discovery](https://openid.net/specs/openid-connect-discovery-1_0.html)
* [OAuth security best current practice, RFC 9700](https://www.rfc-editor.org/rfc/rfc9700.html)
* [PKCE, RFC 7636](https://www.rfc-editor.org/rfc/rfc7636.html)
* [Authorization response issuer identification, RFC 9207](https://www.rfc-editor.org/rfc/rfc9207.html)
* [OpenID Connect RP-initiated logout](https://openid.net/specs/openid-connect-rpinitiated-1_0.html)
* [JWT validation dependency and upstream documentation](https://github.com/googleapis/php-jwt)
