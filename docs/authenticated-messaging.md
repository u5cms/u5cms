# Authenticated messaging with sender evidence

## Summary

- Receive a message through a standard u5CMS form and retain an integrity-protected snapshot of its original contents and authenticated sender.
- Use **u5CMS 12.10.0 or later**, a working SimpleSAMLphp login and a protected archive of the authenticated SAML responses. The archive requires a deployment-specific integration.
- An administrator prepares the database, sets `saml-archive-v1` in the private `config.php`, supplies a private key and approves internal recipients.
- Staff read messages in the existing form-data views. A contact address entered in the form can differ from the authenticated account; the comparison is recorded explicitly.
- Complete the [setup](authenticated-messaging/setup.md) and [acceptance checks](authenticated-messaging/operations.md#check-before-going-live) before opening the service. Email transport acceptance does not confirm receipt in an inbox.

**Audience:** installation administrators, form authors and staff handling submissions. **Version:** the feature began in 12.9.5.15; 12.10.0 adds an important compatibility check. Use the complete matching release.

## Choose your task

| I want to… | Read |
| --- | --- |
| Install and configure the feature | [Setup](authenticated-messaging/setup.md) |
| Create a message form | [Form example](authenticated-messaging/setup.md#5-create-the-message-form) |
| Send, read or export a message | [Everyday use](authenticated-messaging/operations.md#send-and-handle-a-message) |
| Test, update or recover the service | [Operations](authenticated-messaging/operations.md) |
| Understand the evidence, archive or delivery journal | [Technical reference](authenticated-messaging/reference.md) |

## What happens to a message

1. The sender signs in through the configured SAML identity provider.
2. At submission, u5CMS matches the active login to exactly one archived SAML response and checks its identity and integrity.
3. u5CMS compares the authenticated account with the form's contact address, then stores the form data and a protected original snapshot together.
4. It attempts the configured internal notifications and optional contact receipt, recording each transport attempt separately.
5. Authorised staff process the working record and retain the evidence and associated files through the organisation's records process.

The feature provides **authenticated submissions and sender evidence**. The original snapshot uses HMAC-SHA-256, a shared-secret integrity mechanism. It is not the sender's electronic signature or end-to-end encryption. Access protection, mail confidentiality and long-term records management need their own configuration.

## Understand the addresses

| Value | Meaning |
| --- | --- |
| Verified SAML email | The email attribute from the authenticated response, checked against the live SAML session and CMS login. Its assurance depends on the identity provider. |
| Submitted email | The optional contact address entered in the form. It is not independently verified by this feature. |
| Comparison `match` | Contact and authenticated addresses match after normalisation. |
| Comparison `changed` | A different contact address was supplied; the submission can still be accepted. |
| Comparison `missing` | No contact address was supplied; the submission can still be accepted if the form allows this. |

For example, a person signed in as `alice@example.org` may request a receipt at `casework@example.net`. The evidence continues to identify `alice@example.org`; the contact address does not replace that identity.

## Compatibility

The mode applies to the **whole CMS installation**, rather than one selected form. It supports standard frontend submissions which create a new record. Site-specific form writers, PayPal submissions, submitter replacement with `ed2cu=on` and per-user version-checked forms using `ifrmonofill` are incompatible. Use a separate installation when ordinary application forms must keep those behaviours.

For ordinary installations, leave `$formSenderEvidenceRequired` unset or set it to `'no'`. [Release notes](https://github.com/u5cms/u5cms/blob/v12.10.0/docs/release-12.10.0.md) explain the version boundary; the [reference](authenticated-messaging/reference.md) explains the evidence's limits.
