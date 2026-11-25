# u5CMS 12.10.3

## Trusted browser management

After a password change, the management page now frees places belonging to the previous credential when the account holder next opens the list. The previous browsers can be offline. An account with a one-browser allowance can register its replacement immediately after signing in with the new password. The same rule applies when another supported login mechanism changes the stored account credential. A failed account read leaves the registrations available for a later retry.

The ordinary login form now includes the return address for trusted-browser management in its HTML. Opening `u5admin/trusteddevice.php` while signed out returns to that page with JavaScript enabled or disabled, including on installations below the website root. The feature-only rollout script updates the standard login prompt from both earlier layouts and preserves the target installation's other login hooks.

## Upgrade and verification

Use the normal application update and preserve site data and private configuration. The patch requires no database migration or configuration change. Existing registrations for current credentials remain usable.

On a disposable installation, give an account one place and register a browser. Leave that browser closed. Change the account password in a second profile, sign in with the new password and open the management page. It should show zero of one places before replacement and one of one afterwards. The old profile should require a normal login. In a separate signed-out profile disable JavaScript, open the management address and submit the ordinary login form. Check the resulting account and address.

To roll back, restore the previous application files. Registrations already revoked remain revoked. Re-register a browser with the current account credential if needed.
