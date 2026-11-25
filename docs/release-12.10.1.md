# u5CMS 12.10.1

## Summary

Install 12.10.1 if you use Trusted Device. A browser could receive HTTP 403 when registering or revoking a profile on the management page. A database read error could also cause a valid registration to be lost.

## Changes

- The management page now sends a same-origin referrer policy. A browser submitting the page's own form can supply its HTTPS origin. u5CMS continues to check both the origin and the form token.
- If the account lookup fails, u5CMS denies trusted resumption for that request and allows the browser to try again after recovery. A completed lookup that finds the account missing, ambiguous or its credential changed still invalidates trust.
- If the registration table cannot be read when returning without a selected session account, u5CMS also retains the cookie for a later attempt. If a session account has been selected and the owner of the cookie cannot be verified, u5CMS discards the local cookie to respect the account selection.

## Upgrade and acceptance

Use the standard application upgrade procedure and preserve site configuration and data. No database migration or configuration change is required for this patch. Existing valid browser registrations continue to work. A registration already lost or revoked must be set up again through the ordinary CMS login.

On an installation with Trusted Device enabled, sign in with an eligible account in a dedicated browser profile. Choose **Trust this browser** and check the current-browser marker. Close the ordinary session without using CMS logout, then confirm that the profile resumes. Choose **Revoke**, end the ordinary session and confirm that a fresh login is required. CMS logout itself revokes the current registration.

To roll back, restore the previous application files. Rolling back to 12.10.0 also restores the two defects above.
