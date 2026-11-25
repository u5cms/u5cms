<a id="internationale-e-mail-adressen-idn-und-smtputf8"></a>
# International email addresses: IDN and SMTPUTF8

8 November 2026. Authorised scope: correct domain handling and support international local parts on all 19 existing branches, followed by a further in-depth review and correction of collateral damage. These changes concern the source on GitHub. No production CMS installation was changed.

<a id="ergebnis"></a>
## Result

Delivery preparation decodes the CMS representation from Windows-1252 and HTML character references exactly once. Only the domain is converted to ASCII using UTS46 with explicitly non-transitional processing. This keeps `straße.ch` and `strasse.ch` as different delivery destinations. Capital `ẞ` is treated as `ß` according to the current IDNA mapping, even where an older fallback library still maps it to `ss`.

After decoding, the local part retains its spelling and case. Umlauts, kanji and characters outside the BMP are supported; plus tags, permitted special characters and quoted local parts are preserved. Valid named, decimal and hexadecimal entities are supported. `&Szlig;` is not a defined HTML entity for capital ẞ and is rejected by strict delivery validation. Capital ẞ can be supplied as a character or as `&#7838;` / `&#x1E9E;`.

| Input | Technical recipient address | SMTPUTF8 required |
| --- | --- | --- |
| `Anna+Privat@example.ch` | `Anna+Privat@example.ch` | No |
| `info@straße.ch` | `info@xn--strae-oqa.ch` | No |
| `info@STRAẞE.CH` | `info@xn--strae-oqa.ch` | No |
| `Müller@straße.ch` | `Müller@xn--strae-oqa.ch` | Yes |
| `漢字@例子.中国` | `漢字@xn--fsqu00a.xn--fiqs8s` | Yes |
| `𠮷@example.ch` | `𠮷@example.ch` | Yes |

The public delivery functions `u5toidn()` and `u5TOidnREMOTEPART()` also use the corrected preparation. The historical key and comparison functions `u5flatidn()` and `u5flatidnlower()` retain their separate previous implementation. Existing login keys, permissions, passwords and sessions are not rewritten. Historical key equality is checked against each starting branch within the same runtime profile. This does not migrate historical identifiers between different PHP/ICU configurations.

<a id="einladungen-und-kontoadressen"></a>
## Invitations and account addresses

Old IDNA versions can change an entered address from `straße` to `strasse` when constructing the login key; the existing account logic also lowercases local parts. Where necessary, new invitations therefore retain a separate delivery address with its original spelling. It is stored as numeric HTML character references in the optional `accounts.email_delivery TEXT NULL` field. This also allows characters outside the BMP to pass through the existing Latin1/UTF8mb3 database boundary. The login key is retained and explicitly stated in the invitation email. Only where the old IDNA2003 default rejects a domain containing capital ẞ altogether is account lookup retried with the corresponding lowercase domain. Successfully constructed historical keys remain unchanged; the delivery address remains the separately stored original address.

The field is added automatically on the first invitation that needs a different delivery address. Ordinary lowercase ASCII addresses work without a schema change. When the field exists, new invitations store their delivery address in it. No new tables are created. If permission to add the required field is missing, the invitation stops with an error before account creation and delivery. An administrator can provision the field with `ALTER TABLE accounts ADD COLUMN email_delivery TEXT NULL`.

Repeated password recovery, account notifications, intranet notices for backend accounts and backup delivery use this delivery address. Additional HTML escaping of GET input and the old invitation form's `%u` representation are handled at their respective input boundaries; UTF-16 surrogate pairs remain together. Unpaired surrogates are rejected.

Occupied historical login keys continue to be rejected, including where two domains or local-part spellings that are distinct under modern rules produce the same historical key. Neither ownership nor the delivery address is overwritten. An original spelling already lost historically is not guessed: if a recovery request's domain differs from the old stored account address and no delivery metadata exists, no password message is sent. Such legacy accounts need a delivery address verified by an administrator.

An invitation still creates the account before attempting delivery. If delivery then fails, the account remains and the interface reports the failure. Password recovery, by contrast, changes the password and reset token only after a suitable delivery channel has accepted the message.

<a id="versand-und-vorhandene-konfiguration"></a>
## Delivery and existing configuration

Ordinary addresses continue to use the existing Laminas transport. International local parts use a privately encapsulated PHPMailer 7.2.0 component. It adopts the existing SMTP settings for host, port, EHLO name, LOGIN/PLAIN/CRAM-MD5 or unauthenticated SMTP, and SSL/STARTTLS, including existing certificate options. Ordinary use of custom Laminas connection adapters is preserved; any additional custom authentication adapter needs a suitable integration before SMTPUTF8 is used.

International local parts require `$usesmtp='yes'` and an SMTPUTF8-capable server. The extension is checked after EHLO and, where applicable, again after STARTTLS. If it is absent, no MAIL/DATA transmission using that address occurs, no transliteration is applied and no alternative recipient is silently substituted. The interface reports failure; technical details go to the server log. An unchecked PHP `mail()` channel cannot confirm this capability and is therefore not used for international local parts. It remains available for ordinary addresses.

The existing additional PHP-mail delivery of password messages is retained for ordinary addresses. A genuinely accepted second message with matching recipient, subject and content still counts as success if primary delivery fails. This unchecked second channel is omitted for international local parts. If no suitable channel accepts the password message, the password is not changed and successful delivery is not claimed.

Serial mail retains plain-text/HTML alternatives and Bcc in the SMTP envelope. Bcc does not appear in the transmitted headers. A rejected recipient aborts the new transport before DATA, matching existing Laminas behaviour. Distinct local-part spellings are not merged into one recipient in the new transport. Backups retain their attachments; their decoded payloads were checked through both transports.

<a id="kompatibilität-und-abhängigkeiten"></a>
## Compatibility and dependencies

Existing branch minimum versions are retained. The five branches whose Composer minimum is PHP 8.2 are not retrospectively described as PHP 7.4 applications. The delivery component was checked on PHP 7.3.33, 7.4.33 and 8.4.23, additionally on PHP 7.4 without intl and separately without both intl and mbstring. Complete HTTP account flows on the oldest PHP 7.3 profile explicitly use `mbstring.internal_encoding=Windows-1252`. With UTF-8 as the mbstring default, even the unchanged PHP 7.3 original processes historical umlaut login keys inconsistently; globally changing existing keys or server profiles is outside this delivery correction. This is not certification of an actual PHP 9 runtime.

The complete `mail/` directory is supplied and must be transferred during an update. Existing dependencies require no Composer update. The IDNA and normalisation fallbacks are privately encapsulated; they install no global IDN functions or constants and therefore do not change old identity profiles. Provenance, starting commits and file hashes are recorded in `mail/DEPENDENCIES.json`; licences are included. Libraries are available locally without a CDN download.

Private library changes comprise namespace encapsulation, local Normalizer constants and replacement of two conditionally used obsolete OpenSSL release calls with release of the local reference. Application adapters add exact recipient identity, abort-before-DATA behaviour and correct display of named To recipients. Existing application files preserve their original bytes and Windows-1252/Latin1 boundaries; new independent library files use their own UTF-8 source.

<a id="tatsächlich-ausgeführte-prüfung"></a>
## Checks actually executed

- All 19 starting branches were carried forward individually, retaining their existing changes, including OIDC exclusions, PHP corrections and design/search variants.
- 61 runtime profiles: PHP 8.4 on all 19 branches; PHP 7.3 and PHP 7.4 with and without intl on the 14 suitable branches.
- Per profile: 156 input/entity/IDN/API checks, 100 real loopback SMTP checks, 15 native-mail/backup/fallback checks and 20 before/after observations of original identities.
- Additionally, 2,257 syntax checks, 19 executed login-host suites, MIME/header/content comparisons and the existing PHP/request/source checks on the five newer branches. After the final changes, address/account functions, further original-key observations and the four modified PHP files were checked again in all 61 profiles. Existing source/request checks on the five newer branches were also rerun.
- 680 checks using real disposable CMS installations and MariaDB 11.8.6: the independent original first, then all 19 candidates. No difference in the checked existing page, form, language, account and permission flows.
- 47 additional HTTP checks of real mail endpoints on main and the cumulative development line: form confirmation and error display, invitation, successful and rejected password reset, unchanged password after rejection, serial mail, Bcc and stored delivery logs.
- A further 896 account-flow checks: complete cycles on PHP 7.3/7.4/8.4, plus invitation and recovery on each of the 19 branches. These include repeated recovery, real logins using the emailed passwords, rejection of the old password, unchanged existing administrator/editor accounts, historical key collisions, missing ALTER rights, intranet notices and the actual backup endpoint with retained delivery metadata in its attachments.
- Success and failure cases include unauthenticated SMTP, LOGIN, PLAIN, CRAM-MD5, STARTTLS, absent SMTPUTF8, rejected and partly rejected recipients, ordinary servers without SMTPUTF8, international senders and Cc, and preserved attachment bytes.

Machine-readable evidence is in `tests/international-email-evidence-2026-11-08.json`. These are locally executed checks, not claimed GitHub CI results. SMTP peers were exclusively local test servers; no messages were sent to real recipients and no production providers were certified. The remaining operational prerequisite for international local parts is an appropriately configured SMTPUTF8-capable mail server.

<a id="update-und-rückfall"></a>
## Update and rollback

Transfer the matching modified files and the complete `mail/` directory from the same branch. Retain existing site configuration. If necessary, revert the correction commit for that branch. The optional `accounts.email_delivery` field can remain; old code ignores it. Preserve its contents for a later upgrade. Rolling back to old delivery code restores its original IDN/local-part restrictions. Branch versions and their functional differences are retained.

Implementation and the additional in-depth review are complete. Adopted commit references can be verified in each branch's history; production installation is a separate step.
