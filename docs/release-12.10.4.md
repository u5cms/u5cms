# u5CMS 12.10.4

## Who needs this update

12.10.4 corrects the optional command-line installer for sites that deploy Trusted Device separately. Sites already running the complete 12.10.3 release can keep using it. The corrections require no database migration, configuration edit or browser re-registration on those sites.

## What changed

The 12.10.3 installer could report success while leaving an older login form in place if its attributes had been reformatted. On a site with a commented example before the actual setting, it could enable the example and leave Trusted Device switched off.

The installer now checks the form action together with its navigation script, preserves local attributes and edits the value of the PHP setting. It leaves existing browser allowances intact. An unfamiliar form, duplicate switch or unsupported setting stops the preflight before the installer writes to any site.

## Verify a feature-only installation

1. Run the installer in dry-run mode against the intended installation roots. Review the reported count and any message naming a local file.
2. After applying the update with the normal backup procedure, repeat the dry-run. It should report zero changed files.
3. In a signed-out test profile, open the browser management page and sign in with an eligible account. Check that the page returns to management with JavaScript both enabled and disabled and that the expected browser allowance is displayed.

The [configuration guide](trusted-device/configuration.md#deployment-reference) covers local exceptions and the manual procedure.
