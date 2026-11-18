# Ownership of SELF form records

Existing installations compare the complete account identifier. To share SELF records between verified accounts of one organisation, set the following values in the installation's own `config.php`.

```php
$u5onlyminescope = 'domain';
$u5onlymineadmindomains = array();
```

The default scope is `'user'`. An absent or unrecognised value selects this default. In domain mode the CMS compares the complete normalised domain after the @ sign. Each subdomain is independent. For internationalised domains the comparison uses the DNS A-label according to UTS #46. The original record owner and the personal login are retained. Existing rows require no migration.

Domain mode applies to the DAT `SELF` filter and SELF insert, edit and delete controls. Record actions apply to active rows. A template may use `$rh` for the SHA-256 hash of the owner's normalised domain. Hashes are presentation values. The server checks the logged-in identity and target form on every SELF action.

Only enable domain scope for sites whose admission process verifies the user's email address. On a domain-scoped installation an optional list of lowercase ASCII domains, for example `array('office.example')`, gives authenticated members of those exact domains cross-organisation SELF access. This list is empty by default. CMS backend privileges use their existing checks.

## Form-specific storage

For a form with its own persistence routine, store the literal marker `<!-- u5cms-form-writer -->` in the form resource and supply `u5FormWriteRecord($formName)` in trusted site PHP. One declaration covers every language. The CMS checks the stored source, requires the canonical resource name and calls the fixed function after validation. The routine must handle the named form, complete its write and return `true`. A missing or unsuccessful writer aborts the request. Forms without the marker use the built-in writer. No additional configuration value is required.

## Verification

Run `tests/onlymine-domain.php` with a local MariaDB fixture. Set `U5CMS_TEST_PORT`, and optionally the database, user and password through `U5CMS_TEST_DB`, `U5CMS_TEST_USER` and `U5CMS_TEST_PASSWORD`. The test uses temporary tables. Site acceptance should also cover creating, editing, deleting and any site-specific owner transfer.
