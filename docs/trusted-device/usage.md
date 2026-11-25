# Trusted Device: everyday use

[Documentation home](../README.md) · [Trusted Device](../trusted-browsers.md)

## Summary

Register the intended profile after a normal login, return in that profile and use the management page to replace or revoke it. CMS logout deliberately withdraws the current registration.

**Audience:** account holders. **Version:** tested on 12.10.0 with the same-origin form correction. **Scope:** the signed-in account and its browser profiles.

## Register a browser

1. Open the intended CMS in the browser profile you will continue to use.
2. Sign in through the normal login flow. If the installation uses AGOV through SAML, complete that login.
3. Open `/u5admin/trusteddevice.php` on the same public HTTPS host, adding the CMS directory if applicable.
4. Check **Account** and the number of permitted browsers.
5. Choose **Trust this browser** and confirm the success message and **(this browser)** marker.

After a password change, sign in with the new password. From 12.10.3, opening the management page frees places tied to the previous password even if those browsers are offline.

A profile already registered for that account does not use another place when you visit the page again. Each additional profile and each separate CMS installation requires its own registration. Host aliases such as `cms.example.org` and `www.cms.example.org` do not share the cookie.

## Return to work

Return to the same CMS host in the same browser profile. The authentication hooks can use the stored cookie to resume the account's credentials, including after the ordinary CMS session has ended. You continue with the account's current permissions.

Closing a tab does not deliberately revoke the registration. Persistence across a complete browser restart depends on that browser retaining its profile and cookies. Private browsing, clearing site data or replacement of a cloud browser can remove the credential.

## Review registrations

The management page lists registrations for the signed-in account in the current installation. It shows a numbered place, registration time, last-use time and a **Revoke** button. The current profile is marked **(this browser)**.

Times are displayed in **UTC**. Last-use information is updated at most once per day, so it is not a real-time activity log. Profiles have numbered places rather than custom names; keep your own operational record if you need to distinguish several similar cloud profiles.

## Replace a lost profile

1. Sign in normally with the same account in an available browser.
2. Open the management page and identify the lost registration from its place and timestamps.
3. Choose **Revoke** for that registration.
4. In the replacement profile, sign in normally and choose **Trust this browser**.

Deleting a local cookie does not free the server-side place. If the allowance is full, revoke the stale registration before registering the replacement. You can manage only registrations belonging to your own signed-in account on this page.

## Switch accounts

From 12.9.5.12, explicitly selecting another account takes precedence over the previous trusted-browser identity. The old registration for that profile is revoked; registrations in other profiles remain in place. The new account passes the normal authentication checks and needs its own permitted registration if continued access is required.

## Stop using a trusted browser

| Action | Effect |
| --- | --- |
| Close the tab | Does not deliberately revoke trust. |
| Use **Revoke** for one registration | Ends future trusted resumption for that profile. Other registrations remain. |
| Use the CMS logout function | Revokes the current profile's registration and performs the normal CMS logout. |
| Change the stored account credential | Invalidates registrations bound to its former value. |
| Clear local cookies | Removes the local credential; the server record may still occupy a place. |

Revocation ends persistent resumption. It does not itself cancel every ordinary session already established in other windows or profiles. Follow the administrator's account and session procedure if access must end immediately.

For continuing agent work, agree whether the profile should remain registered after the task. If continued access is intended, save the work and close the tab. Use CMS logout when you intend to withdraw that profile's trusted access.
