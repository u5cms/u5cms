# u5CMS 12.9.5.15

## Ordinary installations

Install the complete release over the application with the site's normal preservation rules for private configuration, assets and data. The form option defaults to off. An unset option or the string `no` uses the existing validation, site writer, form versioning, mail and table/CSV code. There is no automatic database migration. The two form list entry points, SELF permissions, SAML login and Turnstile keep their existing implementations.

## Enable evidence on one installation

This mode covers standard, append-only frontend submissions to formsave.php. It requires the site's existing SimpleSAMLphp login and a protected archive of authenticated responses. Incompatible site writers, payment submissions and in-place submitter replacement are rejected on that instance. Evaluate actual forms before enabling it. A regular site with a custom writer does not need these steps.

1. Stage a complete release with a copy of the site's private configuration and its SAML integration. Preserve uploads, data and archive. Take a recoverable backup of the previous application and configuration together. Prevent submissions during cutover.
2. Inspect the schema first. The existing archive must provide the response ID, IdP entity ID, authsource, original Base64 response and its SHA-256, captured attributes and archive ID. The archive belongs to the identity integration. u5CMS uses it read-only. Provision the nullable evidence column and mail journal with the applicable statements in [form-evidence-schema.sql](form-evidence-schema.sql). Skip an already present compatible column. The storage engine of formdata stays as installed.
3. In private config.php set the mode explicitly, load the key from the site's protected configuration and allow the form owners' internal addresses. Keep the configured CMS operator address current. Replace the previous instance-local hook/guard in the same maintenance window.

```php
$formSenderEvidenceRequired = 'saml-archive-v1';
$formSenderEvidenceSigningKey = getenv('U5_FORM_EVIDENCE_KEY');
$formEvidenceInternalRecipients = array($mymail); // add approved form-owner addresses here
require_once __DIR__.'/form-evidence.inc.php';
u5EvidenceGuardEntry();
```

Use a dedicated high-entropy key of at least 32 bytes. Preserve verification keys securely throughout the retention period. When rotating, configure previous keys in `$formSenderEvidenceVerificationKeys`. The guard checks the submitting/viewing entry point during configuration loading and stops incompatible code after a downgrade. The former literal `yes` requires an explicit upgrade. On rollback restore the matching private configuration, guard and application as one unit.

4. With fresh authentication, submit fixtures with matching and different contact addresses and without an optional contact. Check the original submission, comparison, archive ID and digest, recipient roles, journal and actual receiving inbox. Exercise edit, delete-version, table, CSV, failed delivery and clean redeployment. Reconcile the archive and verify the restore independently before operational acceptance.

## Stored and delivered evidence

The module matches the live SP session to exactly one archived response by response ID, issuer and authsource. It checks the original XML digest, compares the archived and current email attributes with the CMS login and records the submitted contact separately. One formdata INSERT stores the submission with a signed snapshot of the original CSV, verified identity metadata, time, destinations and archive reference. The immutable snapshot describes the original received submission. The normal editable row is a working copy. Older rows without this snapshot remain distinguishable. Other evidence formats are marked unverified and require their original verifier.

Admin and form-owner mail contain the reference and comparison. The form-owner route also receives the configured form message. The contact receipt contains only receipt and delivery references. The original assertion remains in the protected identity archive. The restricted table views and CSV expose a verified summary. Direct archive access is governed separately.

The journal records each attempted transport before sending, then accepted or unconfirmed. An interrupted attempt can retain only the start event. Expected destinations are part of the signed submission, including when sending stops early. Check outstanding attempts against the receiving inbox before any manual resend. A second independent browser submission creates a new submission ID. Mail transport acceptance alone does not acknowledge inbox delivery.

The evidence module does not schedule deletion. The standard form-upload cleanup exits on this instance. Recycle-bin filters can hide older soft-deleted rows. External schedulers, retention, separate archive transfers and attachment preservation are operational responsibilities. The CMS signs the submitted attachment reference, not the binary file contents. Export attachment bytes and verify them separately if forms accept files.
