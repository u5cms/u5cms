# u5CMS 12.10.0

This stable release consolidates the changes from 12.9.5 through 12.9.5.15. These include search index recovery during concurrent saves, account switching for trusted browsers, form ownership, international mail and the optional sender evidence feature.

## Form compatibility

With sender evidence enabled, a form using the per-user `ifrmonofill` version check now stops before the submission is written or mail is sent. The response is HTTP 409. The archived evidence path accepts append-only submissions. Installations using version-checked forms should keep the ordinary handler for those forms.

Ordinary installations can leave `$formSenderEvidenceRequired` unset or set it to `'no'`. The new check is inside the enabled evidence path. Existing site writers, SELF rights, standard form versioning, admin views and CSV keep their respective code paths.

## Upgrade and rollback

Use the usual application upgrade procedure. Preserve private configuration, uploads and site data. Back up the application and configuration together before installing. No additional database change is required for this release. Sites enabling evidence for the first time should follow the [setup and operational acceptance procedure](release-12.9.5.15.md). To roll back, restore the previous application and its matching private configuration.

Development branches V13 through V17 retain their own version identifiers and carry the same form compatibility correction.
