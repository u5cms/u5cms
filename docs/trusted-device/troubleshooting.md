# Trusted Device: troubleshooting

[Documentation home](../README.md) · [Trusted Device](../trusted-browsers.md)

## Summary

Identify whether the failure concerns login, eligibility, a full allowance, form protection or a lost profile. Follow the matching check without weakening authentication safeguards.

**For** account holders and installation administrators. **Version** 12.10.2 and the corresponding maintained development lines. **Scope** registration and persistent resumption.

## The login tab closes or opens the backend home page

With 12.10.1 or earlier, a signed-out visit to the management page could close the tab or take you to the backend home page after form login. On those versions, sign in at `/u5admin/` first, then open `/u5admin/trusteddevice.php` on the same installation. From 12.10.2 the ordinary CMS login form returns directly to the management page.

## The management page returns 404

An ineligible signed-in account deliberately receives **Not found.** Check that the correct installation's `config.php` enables the feature and lists the actual CMS login with a positive integer limit. Check session authentication and the OIDC exclusion. Also verify the URL and whether the installed release contains `u5admin/trusteddevice.php`.

The page is account-specific. Being a CMS administrator does not automatically add your account to the trusted-browser configuration.

## The browser allowance is full

Open the list under the same account and revoke an obsolete registration. Removing a cookie or losing a cloud profile does not itself remove its server-side place. u5CMS will not replace an active registration automatically. Increase an allowance only through the installation administrator's configuration process.

## Registration fails

| Message | What to check |
| --- | --- |
| **The trusted browser could not be registered.** | HTTPS, database availability, table creation or migration rights, a unique valid account, and whether the browser accepts cookies. The administrator should inspect server errors. |
| **Invalid request.** with HTTP 403 | Reload the management page and submit its own form on the same host. Check that a reverse proxy preserves the correct public HTTPS host and scheme. |
| **Browser registrations could not be loaded. Please try again.** | Database availability and permissions. Avoid repeated registration attempts until the database issue is understood. |
| **The browser could not be revoked.** | Refresh the list; the entry may already have been revoked, or the database operation may have failed. |

An administrator investigating a proxy must check the deployment's trusted forwarding configuration. Do not remove origin checks, CSRF protection or the HTTPS requirement to make registration succeed.

An older management-page source using `Referrer-Policy: no-referrer` can cause its own form to submit `Origin: null`. The current page uses `same-origin`. Compare the installed file with the matching maintained source and see the [browser finding](walkthrough.md#observed-results); changing the page's header is different from accepting an unverified origin.

## A later visit asks for a login

Check these causes in order:

1. The visit uses a different browser profile, host alias or CMS installation.
2. Cookies were cleared, private browsing ended, or the cloud profile was replaced.
3. CMS logout, individual revocation or an explicit switch to another account revoked this registration.
4. The feature or account allowance was disabled, or the limit was lowered below this browser's numbered place.
5. The account's stored credential changed, or installation identity settings changed.
6. The browser's cookie expired or was removed by its own retention policy.

Complete a normal login in the intended profile and inspect the management page. Register the profile again only if it is still intended to retain access.

## The timestamp looks old

The page uses UTC and updates last use at most daily. A successful visit need not change the displayed minute. This timestamp is not evidence of continuous activity or an audit log of individual agent actions.

## Re-enabling restored an old registration

Disabling the feature or temporarily removing an allowance prevents resumption but does not permanently revoke every stored registration. Revoke unwanted profiles while their account remains eligible, then disable the feature or remove the allowance. See [configuration](configuration.md#disable-and-re-enable).

## Information for a support request

Provide the CMS version, public host and installation path, the affected login, whether the ordinary login works, the displayed message, the time including timezone, and whether the profile or configuration recently changed. Include the relevant allowance without unrelated configuration. Keep passwords, complete cookies, tokens and database credentials out of tickets and public issues.
