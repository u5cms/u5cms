# Authenticated messaging: technical reference

[Topic overview](../authenticated-messaging.md) · [Setup](setup.md) · [Operations](operations.md)

## Summary

The module links a submission to an authenticated, archived SAML response and protects an original snapshot with HMAC-SHA-256. Working records remain editable. Verification of the snapshot, current form contents, identity-provider authentication and actual inbox delivery are distinct checks.

## Original and working record

One `formdata` insert stores the ordinary columns and `sender_evidence_json`. The JSON envelope uses format `u5-form-1`, containing a record and an HMAC. The record includes:

- Random submission ID, server UTC time, form and normalised account identifier.
- Verified identity metadata, contact comparison, archive ID and decoded-XML digest.
- `head_b64` and `data_b64`: Base64-encoded original CMS CSV header and data strings.
- Expected destinations, identified by the `admin`, `owner` and `receipt` roles.

The original header and data use the CMS's encoding and field serialisation. They are a snapshot of the stored submission representation, rather than the raw HTTP request. Editing or creating a delete-version preserves the original envelope while the working values can change.

The table's `verified` summary means that a configured key verifies the envelope and its form/account association matches the row. **It does not compare the current editable `headcsv` and `datacsv` with the original snapshot.** For that comparison, verify the envelope, decode `head_b64` and `data_b64`, then compare the original representation with the working version. Do not infer that an edited message is unchanged merely because its sender evidence remains `verified`.

## Archive and identity checks

The reader selects one archive row using the active service-provider session's response ID, issuer and configured authsource. It checks the hash of the archived Base64 string, decodes it, rejects document types/entities, checks the SAML response root and ID, and requires one readable assertion. It compares the email extracted from that XML with the archived attributes, live attributes and CMS login.

The archive's `saml_response_sha256` hashes the **Base64 string**. The displayed **SHA-256 (XML)** hashes the **decoded XML bytes**. These are different values by design.

The reader relies on the trusted SAML integration for authentication and SAML signature validation; it does not re-run that signature validation itself. The CMS envelope then binds its own original submission snapshot to the archived-response reference. This relationship is not an identity-provider signature over the message text.

## Keys and verification

`$formSenderEvidenceSigningKey` must be a string of at least 32 bytes with suitable randomness. This is a symmetric secret, despite the configuration name's use of “SigningKey”. Anyone able to use that secret can generate another valid HMAC. Database, archive and key access therefore remain material to the trust model.

To rotate the key, deploy a new active secret and retain the previous secret in the protected verification list:

```php
$formSenderEvidenceSigningKey = getenv('U5_FORM_EVIDENCE_KEY');
$formSenderEvidenceVerificationKeys = array(
    getenv('U5_FORM_EVIDENCE_PREVIOUS_KEY'),
);
```

Use this only when the previous variable supplies a real retained key. Keep the setup's active-key validation. Verify an older record and a new record after rotation. Preserve required keys securely through the retention period; losing the original key prevents verification of its envelopes. Public documentation contains variable names and synthetic examples only.

## Notification roles

| Role | Destination | Contents |
| --- | --- | --- |
| `admin` | `$mymail` | Submission/delivery references, form, authenticated and contact addresses, comparison, archive ID and XML digest. |
| `owner` | Approved form `ouremail`, or `$mymail` | The same metadata plus the form message assembled under its server-side display controls. |
| `receipt` | Supplied contact address | Submission and delivery references only. |

Roles are attempted separately, even when two destinations happen to be the same mailbox. The original SAML response is not included in these messages. The transport has its own confidentiality properties; an owner notification can contain message text.

Journal events have their own HMAC over submission, attempt, role, state and UTC time. They describe transport attempts, not recipient actions. The evidence header in HTML/CSV is a summary, rather than an export of the envelope and journal.

## Source and verification

| Source | Responsibility |
| --- | --- |
| [`form-evidence.inc.php`](../../form-evidence.inc.php) | Archive matching, original envelopes, routes, journal and summaries. |
| [`formsave.php`](../../formsave.php) | Compatibility, validation and creation of submissions. |
| [`editformsave.php`](../../editformsave.php), [`delformsave.php`](../../delformsave.php) | Preservation of original evidence across staff versions. |
| [`form-evidence-schema.sql`](../form-evidence-schema.sql) | Explicit CMS schema changes. |
| [`form-evidence-regression.py`](../../tests/form-evidence-regression.py) | Isolated handler regression cases with synthetic SAML and mail fixtures. |

This guide was checked against u5CMS 12.10.0, source commit `42215f7521cf99b9440d705993e2e7a9d7a5da1a`. On 25 November 2026, all 27 cases in the repository's sender-evidence regression suite passed with PHP 8.4 and MariaDB 11.8. The PHP configuration examples also passed syntax checks; separate synthetic checks confirmed HMAC tamper detection, previous-key verification and the distinction between the original snapshot and editable working data.

The regression suite exercises the actual handlers with temporary MariaDB tables; its SAML session and transport are fixtures. These checks support the documented code behaviour and do not constitute acceptance of a live identity provider, protected archive, receiving mailbox or records system. Follow the [installation acceptance checks](operations.md#check-before-going-live) for those integrations.
