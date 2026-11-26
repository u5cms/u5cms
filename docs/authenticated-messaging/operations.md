# Authenticated messaging: use and operations

[Documentation home](../README.md) · [Authenticated messaging](../authenticated-messaging.md) · [Setup](setup.md) · [Reference](reference.md)

## Summary

Sign in, submit once and retain the reference. Staff use the existing **Form data** views, distinguish the authenticated address from the contact address, and check delivery separately. A saved message with unconfirmed mail delivery needs investigation before another send. Preserve the original evidence, archive entries and attachment bytes through updates and records transfer.

**For** staff handling messages and service administrators. **Version** u5CMS 12.10.0 and later for the workflow described here.

## Send and handle a message

For the sender:

1. Open the message page and sign in through the configured identity provider.
2. Enter the message and, if wanted, a contact address. The contact can differ from the signed-in address.
3. Choose **Send message** once and follow the confirmation. A contact receipt contains submission and delivery references, rather than a copy of the message.
4. If the page says **Submission saved. Mail delivery is unconfirmed.**, keep its reference and contact support. A new submission would create another record.

For authorised staff:

1. Open **Form data** in the CMS backend (`/u5admin/formdata.php` when installed at the website root) and choose the relevant form.
2. Use **html** for the table or **xls** for the CSV export. The existing account and form permissions apply; granting a role in the evidence settings does not grant CMS access.
3. Read **Sender evidence**, **Submission ID**, **Verified SAML email**, **Submitted email**, **Comparison**, **Archive ID** and **SHA-256 (XML)**.
4. Treat `changed` as a different contact address and `missing` as an absent contact, according to the business process. Neither changes the authenticated sender.
5. Process the working record. Preserve the protected original snapshot when editing or creating a delete-version. Use the [original snapshot](reference.md#original-and-working-record) for an exact comparison with what arrived.

The **xls** action produces CSV data, not a native Excel workbook. A normal CSV export shows the current working record and evidence summary; it is not a complete evidence archive. Raw SAML responses require separate protected archive access.

## Check before going live

Use synthetic messages and controlled mailboxes on the test installation. Record the tested release and the actual result of each check.

| Check | Expected result |
| --- | --- |
| Fresh SAML login; same contact | One new submission, `verified` evidence and comparison `match`. |
| Different contact | Authenticated identity retained; comparison `changed`; receipt contains only references. |
| No optional contact | Comparison `missing`; internal notification attempted; no contact receipt. |
| Expired login, mismatched account or damaged archive | No submission or mail accepted through the evidence path. |
| Unapproved owner address | Submission rejected before storage and mail. |
| HTML and CSV | Same submission ID, identity comparison, archive reference and XML digest. |
| Interrupted or failed mail transport | Stored submission retained; delivery unconfirmed or an attempt left at `started`; no automatic replay. |
| Each enabled mail route | Actual receiving inbox reconciled with its submission and delivery references. |
| Staff edit and delete-version | The original evidence envelope retained; permissions and working-version behaviour correct. |
| Unauthorised reader | Message, CSV and attachment access denied as required by the installation's access policy. |
| Clean redeployment and restore | New submissions still work; older evidence verifies; archived originals and attachments remain recoverable. |
| Incompatible forms | Site writer, payment, replacement and per-user version-checked paths rejected on the evidence installation. |

Also test the organisation's records transfer and a retrieval of one complete case. Keep the technical references and the acceptance owner in the ticket; keep keys, tokens and message contents in their protected systems.

## Investigate delivery

The journal `form_evidence_mail` contains submission ID, attempt ID, role, UTC time and event state. It is available to authorised operations staff through the database; the feature does not add a mail-journal dashboard.

| State | Interpretation and next action |
| --- | --- |
| `started` only | Sending may have been interrupted. Check transport logs and the receiving inbox. |
| `accepted` | The configured mail transport accepted the hand-off. Confirm inbox receipt separately. |
| `unconfirmed` | The hand-off failed or could not be confirmed. Investigate before arranging a controlled resend. |

Match the signed record's expected destinations with the attempts as well: an early failure can leave a later destination with no attempt at all. There is no automatic retry queue or exactly-once email guarantee. A manual resend should refer to the existing submission and follow the operator's process; asking the sender to submit again creates another submission ID.

## Resolve common problems

| Symptom | What to check |
| --- | --- |
| **Sender evidence unavailable.** / HTTP 403 | Fresh SAML login, one matching archive row, archive digests, matching SAML/CMS account and approved form owner. |
| **Submission unavailable.** / HTTP 503 | PHP web-process key, evidence column, journal table, database permissions and server errors. |
| **Form evidence configuration needs an explicit upgrade.** | Exact mode `saml-archive-v1`; replace an old `'yes'` setting with the supported configuration. |
| Handler-format error after deployment | Install the complete matching release and its entry guard; restore application and private configuration together if rolling back. |
| HTTP 409 on a form | Unsupported writer or version-checked form. Review compatibility; do not remove the check. |
| `unrecorded` | This row has no original evidence envelope, for example an older or ordinary submission. |
| `unverified` | Missing verification key, changed/unsupported envelope, or record association mismatch. Preserve the record and investigate. |
| Old deleted records disappear from lists | Check recycle-bin filters. A hidden row is not proof that retained data was physically deleted. |

## Update, retain and restore

Back up the application and matching private configuration, database, protected SAML archive, key history and uploads. Rehearse the restore on a separate instance. After deployment, send a fresh test message and verify both new and older evidence. Keep earlier-format verification tools when historical evidence needs them.

Set the retention period and records destination in the organisation's operational instructions. The module schedules no deletion and disables the standard form-upload cleanup while enabled; external schedulers and manual deletion still need review. Disabling the mode re-enables ordinary behaviour, including ordinary cleanup, so preserve retained uploads before doing so.

For a complete records transfer, retain the original envelope, the working/version history required by the process, referenced SAML response and verification material, relevant journal events, and **attachment bytes with separately checked hashes**. The CMS protects attachment references; it does not hash the attached file contents. Confirm that a retrieved case is complete and verifiable.
