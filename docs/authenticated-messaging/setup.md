# Authenticated messaging: setup

[Topic overview](../authenticated-messaging.md) · [Operations](operations.md) · [Reference](reference.md)

## Summary

Prepare the SAML response archive first. Then add the two CMS database structures, configure the private key and recipients, and publish a standard message form. Test the complete route with fresh authentication before enabling public submissions. All examples use reserved addresses; replace them with your own values.

## 1. Prepare the installation

Use a separate test instance and the complete u5CMS 12.10.0 release or a later compatible release. Preserve the existing private configuration, SAML integration, database and uploads together. Keep new submissions closed during the production change.

Confirm these prerequisites:

| Requirement | Successful check |
| --- | --- |
| HTTPS and access rules | The intended users can sign in; unauthorised users cannot read submitted messages or attachments. |
| SAML login | SimpleSAMLphp authenticates through the intended provider. The CMS login corresponds to the response's email attribute. |
| PHP runtime | The installed release works with its normal extensions, including DOM/XML, mbstring and database support. |
| Private key | The PHP web process can read a dedicated, high-entropy secret from the protected deployment configuration. |
| Mail | `$mymail` is the installation's valid operator/sending address; the configured transport can reach approved internal mailboxes. |
| Forms | Every active form is compatible with the installation-wide mode. See [compatibility](../authenticated-messaging.md#compatibility). |

Retain the deployment's existing SAML settings. In particular, `$u5samlsspconfigdir` must point to its real configuration directory, `$u5samlsspauthsourcekey` must select its real service provider, and `$u5samlmockauth` must be `'no'`. A test or simulated login cannot supply production sender evidence.

## 2. Provide the protected SAML archive

**This is a prerequisite supplied by the identity integration. u5CMS does not install an archive writer or create this table.** Give the following contract to the person maintaining the SAML integration.

The integration must archive the original response associated with a successfully authenticated service-provider session. Authentication and signature validation belong to that trusted integration. Capture the original response before it is discarded; do not manufacture a replacement response from session attributes.

The CMS database connection must be able to read a table or compatible view named `saml_response_archive` with these columns:

| Column | Required contents |
| --- | --- |
| `id` | A stable archive reference retained with the original response. |
| `response_id` | The exact response ID recorded in the SP session's `saml:sp:prevAuth` data. |
| `idp_entity_id` | The exact issuer from that same authenticated session. |
| `authsource` | The configured `$u5samlsspauthsourcekey`. |
| `saml_response_b64` | The exact Base64-encoded original SAML response. |
| `saml_response_sha256` | Lower-case hexadecimal SHA-256 of that **Base64 string**, before decoding. |
| `attributes_json` | A UTF-8 JSON object mapping attribute names to value arrays, as captured by the integration. |

The combination of response ID, issuer and authsource must resolve to exactly one row. The current reader expects one readable SAML assertion in a SAML 2.0 `Response`, with one unambiguous email value under `mail` or a supported `emailaddress` attribute name. An encrypted-only archived assertion does not satisfy this reader.

Protect archive access separately from ordinary form access. Keep originals, verification material and backups recoverable for the agreed retention period. Confirm a real login produces a compatible archive entry before continuing. Creating an empty table alone is insufficient.

## 3. Prepare the CMS database

Inspect the intended database before running any changes:

```sql
SHOW COLUMNS FROM formdata LIKE 'sender_evidence_json';
SHOW TABLES LIKE 'form_evidence_mail';
```

Use the applicable statements in [`form-evidence-schema.sql`](../form-evidence-schema.sql):

1. If the evidence column is absent, add the nullable `MEDIUMTEXT` column shown there. Preserve a compatible existing column and its contents.
2. If the mail journal is absent, create it using the supplied `CREATE TABLE` statement. If present, compare its definition with the supplied schema.
3. Preserve the existing storage engine of `formdata`. There is no automatic migration, and existing records are not assigned retrospective evidence.

Do not run the whole file repeatedly: its `ALTER TABLE` and `CREATE TABLE` statements are deliberately explicit, one-time changes.

## 4. Configure the private site settings

Generate a dedicated secret, for example with `openssl rand -hex 32`, and store it as `U5_FORM_EVIDENCE_KEY` through the deployment's secret mechanism. The example produces 256 bits of randomness encoded as 64 ASCII characters. Ensure the **PHP web process**, including PHP-FPM where applicable, receives the value; a variable present only in an administrator's shell is insufficient.

Merge this block into the existing private `config.php`, after `$mymail` has been set. Replace previous evidence assignments or local evidence hooks in the same change, preserving unrelated configuration and the file's encoding.

```php
$formSenderEvidenceRequired = 'saml-archive-v1';
$formSenderEvidenceSigningKey = getenv('U5_FORM_EVIDENCE_KEY');

if (!is_string($formSenderEvidenceSigningKey)
    || strlen($formSenderEvidenceSigningKey) < 32) {
    throw new RuntimeException('The form evidence key is unavailable.');
}

$formEvidenceInternalRecipients = array(
    $mymail,
    'messages@example.org',
);

require_once __DIR__.'/form-evidence.inc.php';
u5EvidenceGuardEntry();
```

The allow-list approves the internal form-owner addresses that forms may select. It does not itself send a copy to every listed address. `$mymail` supplies the operator route and outgoing sender; the form's `ouremail` selects its owner route. The entry guard rejects incompatible handlers after a partial deployment or downgrade. The former value `'yes'` is not a supported mode.

Check PHP syntax using the deployment's PHP executable. Keep the key and real configuration out of source control, messages and tickets. For rotation, retain old keys in the protected `$formSenderEvidenceVerificationKeys` array; see [key management](reference.md#keys-and-verification).

## 5. Create the message form

Create a CMS page protected by the intended login and access rules. Start with the installation's normal form element, which renders as one `<form name="u5form">` posting to `formsave.php`. The following page content uses the standard `[fo:]` and `[h:]` elements supplied with u5CMS:

```text
[fo:]
[h:]
<input type="hidden" name="ouremail" value="messages@example.org">
<input type="hidden" name="in2my" value="off">
<input type="hidden" name="em2me" value="on">
<input type="hidden" name="em2cu" value="on">
<input type="hidden" name="thanks" value="message-received">

<p><label>Contact email (optional)
  <input type="email" name="youremail">
</label></p>
<p><label>Message (required)
  <textarea name="message_mandatory" required></textarea>
</label></p>
<button type="submit">Send message</button>
[:h]
[:fo]
```

Create the `message-received` confirmation page as well. Explain that the message has been saved; do not promise that staff have already received or read the email. Preserve any anti-abuse controls required by the installation. If its standard form elements were customised, check the rendered form and handler rather than replacing those elements globally.

This example sends the form-owner notification and, when a contact is supplied, a reference-only receipt. It switches off the separate operator notification to avoid an unwanted additional route. For both internal routes, set `in2my` to `on`. At least one internal route must remain enabled.

| Control | Effect in evidence mode |
| --- | --- |
| `ouremail` | One approved internal owner address; empty uses `$mymail`. |
| `in2my` | `off` disables the operator notification; other values enable it. |
| `em2me` | `off` disables the owner notification; other values enable it. |
| `em2cu` | `off` disables the contact receipt; otherwise it is attempted when a contact is present. |
| `youremail` / `youremail_mandatory` | Optional / required contact field. Use one; conflicting addresses are rejected. |

Delivery controls are read again from the stored, server-rendered form. A browser changing hidden fields cannot choose a different internal destination. Mail uses the evidence module's subject and reference format; ordinary custom thank-you mail text does not replace that receipt format.

## 6. Accept and release

Run the [acceptance checks](operations.md#check-before-going-live), record the result and reopen submissions only after the complete deployment works. An application-only update does not prove that the private SAML archive, transport and records process work together.
