# Email delivery and international addresses

[Documentation home](README.md)

## Summary

Separate the address displayed to a person, the CMS's historical account identifier and the technical delivery address. They can represent different contracts. A delivery correction must not silently remap an existing login or its permissions.

**Audience:** installation administrators and mail operators. **Version:** delivery paths exercised on 12.10.0. **Scope:** the configured installation and transport.

## Start with the address, not the alphabet

| Example | Technical requirement |
| --- | --- |
| `Anna+Private@example.org` | Ordinary ASCII delivery; preserve the local part's spelling. |
| `info@straße.ch` | Convert the domain to `xn--strae-oqa.ch`; this address alone does not require SMTPUTF8. |
| `Müller@straße.ch` | Convert the domain and retain the international local part; SMTPUTF8 is required. |
| `漢字@例子.中国` | Both parts need appropriate handling; the local part requires SMTPUTF8. |

The decisive difference is often the part **before** `@`. A non-ASCII domain can be represented by an ASCII DNS name, whereas an international local part cannot simply be replaced with another spelling.

## Configure the existing transport

Use the installation's real system sender in `$mymail`. Set `$usesmtp = 'yes';` and configure `$mysmtpoptions` when SMTP is required. The configuration sample shows host, port, connection class, TLS and credentials; retain the provider's actual requirements rather than guessing them from another site's settings.

Keep credentials in protected installation configuration or its secret-loading mechanism. Do not put working SMTP credentials into a repository or a public support example.

Ordinary addresses continue to use the existing transport. International local parts require an SMTPUTF8-capable SMTP channel. u5CMS checks the advertised capability after EHLO and again after STARTTLS where applicable. If it is absent, that message fails rather than transliterating the recipient or silently switching to unchecked PHP mail.

## A useful transport exercise

In a local SMTP fixture, send one ordinary ASCII address, one IDN-domain address and one international-local-part address. Repeat with SMTPUTF8 unavailable, then with TLS and the relevant authentication method enabled.

The executed 100-case SMTP suite covered these paths, recipient rejection, Cc/Bcc, international senders and preserved attachments. A further 15 native-mail/backup/fallback checks passed. All counterpart servers were local fixtures; a real provider still needs a controlled delivery test on the intended installation.

The lesson is that successful address validation and successful message transport are separate results. SMTP acceptance is also different from confirmed arrival in the recipient's inbox.

## Invitations and recovery

Existing CMS account keys retain their historical normalisation. Where an invitation needs a different original delivery spelling, u5CMS can store it separately in optional `accounts.email_delivery`. Its representation preserves characters which do not fit the older database character boundary.

The field is added when needed, provided the installation has the necessary schema permission. If that required change cannot be made, the invitation fails before creating the new account or sending the message. An administrator can provision the field using the documented schema statement.

An invitation normally creates its account before attempting delivery; a subsequent delivery failure can therefore leave an account which needs follow-up. Password recovery changes its password and reset token only after a suitable channel accepts the message. These are different failure-handling rules; do not infer one from the other.

Do not guess a lost original address for a historical account. If its stored identifier is ambiguous and no verified delivery metadata exists, resolve the address through the administrator's account process.

## Serial mail, form mail and backups

Check the actual message type: plain text/HTML alternatives, recipient names, Cc/Bcc, attachments and delivery logs can all matter. Bcc belongs in the SMTP envelope and must not become a disclosed recipient header. The local protocol checks inspected these details instead of treating a successful function return as complete evidence.

Use a clearly identified test recipient and inspect the received message before accepting a production configuration. For batch mail, preserve the site's existing `$serialmailmethod` and waiting policy; a transport correction does not change its intended sending cadence.

## Deployment and troubleshooting

Transfer the complete matching `mail/` directory with the release. It contains the privately encapsulated delivery libraries and their licences. Existing account identifiers and other dependency trees are not migrated by copying that directory.

For a failed message, distinguish invalid address, absent SMTPUTF8, TLS/authentication failure, recipient rejection, transport acceptance and inbox delivery. Retain the timestamp and relevant error; omit credentials and full private message content from a public issue.

The [international-address technical report](international-email-2026-11-08.md) documents exact preparation rules, optional schema, compatibility profiles and rollback behaviour.
