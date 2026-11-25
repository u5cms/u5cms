# u5CMS 12.10.2

## Summary

Opening `/u5admin/trusteddevice.php` while signed out now keeps the login tab open and returns the account holder to trusted-browser management after the ordinary CMS form login. On 12.10.1 Firefox could close a newly opened tab. In Chromium, logging in could take the user to the backend home page.

## Upgrade and verification

Apply the usual application update, preserving site data and private configuration. This patch requires no database migration, configuration change or re-registration of existing browsers.

On an installation with Trusted Device enabled, open the management address in a signed-out profile. Include the installation directory if the CMS runs below the website root. Sign in using an eligible account and check that the management page shows that account. Register the profile and confirm the current-browser marker. Choose **Revoke** to release the place when the check is complete.

The automated acceptance exercise opens the page while signed out, enters the credentials in the rendered form, submits it and checks the resulting URL before proceeding with registration. It runs in both Chromium and Firefox. The [Trusted Device guide](trusted-browsers.md) covers eligibility, quotas, registration and revocation.

## Rollback

Restore the previous application files. On 12.10.1, sign in through the backend first and then open the management page.
