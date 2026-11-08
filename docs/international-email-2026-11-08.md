# International email correction

Authorised 8 November 2026: correct IDN domain delivery and add SMTPUTF8 local-part support in all 19 existing branches, on old and new supported servers. No production installation.

Starting refs: see the branch evidence to be added after validation. Main starts at 78cadbf87b7307203b8bd4ff96929b306af7d982. Every branch retains its own application changes.

Implementation boundary: preserve historical account-key normalisation and stored data; introduce an independent delivery-address boundary. Decode CMS Windows-1252/entities before IDNA. Request nontransitional UTS46 explicitly. Preserve local-part bytes/case after decoding. Add an isolated maintained SMTPUTF8 sender only for messages requiring it; preserve ordinary Laminas delivery and MIME. Check SMTP capability and retain authentication/TLS options. Address native mail duplicates and backup attachments explicitly. No global IDN polyfill injection.

Last completed: current branch/source inventory, two helper generations and native PHP 7.4/8.4 defect reproduction.
Next: implement, then compare ordinary mail and legacy identities against each starting branch; exercise real disposable SMTP peers with/without SMTPUTF8, failures and TLS/auth, CMS plain/serial/native/backup routes, malformed addresses and entity encodings. Publish each branch with an expected-head guard only after the required checks pass.

Status: implementation in progress; no passing implementation tests or deployment are claimed by this checkpoint.

## Implementation checkpoint

Independent delivery parser and private namespaced IDNA/normalization fallback are implemented. SMTPUTF8 messages use namespaced PHPMailer 7.2.0; ordinary messages retain Laminas. Global identity APIs and account data are unchanged. Plain/serial, native duplicate and backup entry points are connected. Initial address checks: 96 on PHP 7.4, 8.4 and PHP 7.4 without intl. Actual loopback SMTP checks: 77 on each native runtime, including LOGIN/PLAIN/CRAM-MD5, STARTTLS, UTF8 refusal, recipient refusal, alternatives and Bcc. These are preliminary checks, not final branch acceptance.

Next: native/MIME parity, actual CMS endpoint and identity comparisons, all 19 branch candidates and the additional collateral-damage pass.

## Additional collateral-damage pass

All 19 branch candidates passed 47 runtime profiles (native PHP 8.4 everywhere; PHP 7.4 and PHP 7.4 without intl on the 14 compatible branches). Each profile checks 130 address/API/encoding expectations, 99 loopback SMTP expectations, 15 native/backup/fallback expectations, original-vs-target identity observations and changed-file syntax. Ordinary native MIME/header/body semantics are compared separately. The five >=PHP-8.2 branches keep their existing floor and additionally pass their PHP readiness/source/request contracts.

Actual HTTP routes with MariaDB 11.8.6 pass on main and the cumulative branch: form save and unsupported-server notification, backend invitation, password reset success and refusal (no password mutation on failure), serial MIME, Bcc and database status/log. A broader original-vs-candidate CMS reference pass is being repeated over all 19 branches before publication.

Follow-up corrections include explicit U+1E9E handling in the private IDN fallback, retained NULL/number text inputs, a guard against partial recipient delivery, exact-case EAI recipient identity, native duplicate success receipts, To display-name handling and preservation of the included message object. These are covered by focused repeat checks. Publication to the 19 starting branches remains pending final evidence.
