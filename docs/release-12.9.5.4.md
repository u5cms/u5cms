<a id="u5cms-12954--kumulative-abnahme"></a>
# u5CMS 12.9.5.4 – cumulative acceptance

The comparison base is the unchanged release **v12.9.5** (`fa6e8982e85d7ca9ec33683ab33d31eb86a31eb9`). The table covers the complete change line through 12.9.5.4, including earlier mail/login corrections and patches .1, .2 and .3.

**Assessment:** No new regression was found within the stated verification scope. The application is accepted for publication. “High” denotes concrete source, original-comparison and runtime evidence; it is neither a statistically measured percentage nor a guarantee for every site-specific extension.

The application code at candidate `3f53ddd00e5c1cd3c0d5a1c16750e5436e463945` passed [GitHub acceptance on PHP 8.3 and 8.4](https://github.com/u5cms/u5cms/actions/runs/44407572855), including **92 real Chromium checks per profile**. The closing commit adds reports and the additional SIGKILL test without changing those application sources. Final checks are visible on [PR 197](https://github.com/u5cms/u5cms/pull/197).

<a id="änderungen-nachweise-und-einschätzung"></a>
## Changes, evidence and assessment

| No. | Change | Behaviour compared with 12.9.5 | Evidence | Assessment |
| --- | --- | --- | --- | --- |
| S1 | Search results and visibility | Both legacy search entry points load only metadata, five fallback titles and the selected language's text. Protection, intranet, hidden, type and additional filters are retained. | 34 real SQL checks across both entry points; PHP 8.3/8.4 in CI. | High |
| S2 | Search suggestions and memory | A streaming pass replaces the complete vocabulary in memory and recursive correction passes. Limits apply only to suggestions: 16 terms, at most 255 bytes each. | 45 error/boundary/language checks; 75 MB of source text at approximately 4.2 MB peak PHP memory. | High |
| S3 | HTML excerpts and failures | Entity boundaries are streamed. Length, positive/negative ranges and existing byte semantics are retained; missing result nodes do not cause callback errors. | 55,990 comparisons against the original HTML procedure; 25.2 MB stress case. | High |
| I1 | Full and single-resource indexing | Full rebuilding handles at most eight resources per HTTP request, with a two-second budget between resources followed by a pause. Single-resource indexing remains separate. | 12 batch checks; the real renderer completes a full rebuild in six requests; subsequent idle and single-resource behaviour checked. | High |
| I2 | Concurrency and resumption | A kernel file lock prevents competing full indexers; atomic progress and delayed retries retain state after interruption. SQL comparisons protect concurrent source changes. | Batch checks; real competing process and SIGKILL test; unchanged checked SQL source comparison from the .3 correction. | High within the checked scope |
| I3 | Database locks | Only the full-index connection caps metadata and InnoDB lock waits at five seconds. Stricter server values are retained through LEAST. | Real HTTP request against a locked MyISAM table: termination after approximately 5.03 s; no waiting SQL statements and a released file lock. MariaDB 11.4 and 11.8. | High |
| I4 | Index date and notice | The watermark is published only after a successful complete pass; changes made during that pass remain due. The notice identifies that state without promising an imminent run. Missing/invalid markers do not produce an epoch date. | 13 actual notice checks covering start, partial pass, resumption, completion, further changes and invalid markers. | High |
| R1 | Object renaming | Resources, references, metadata, five languages and form ownership are updated together. Heading brackets and literal text remain protected; rewriting form payloads remains optional. | 893 Rename checks; 114 real HTTP checks and 92 Chromium checks on each PHP 8.3/8.4 profile. | High |
| R2 | Files, exports and histories | Language, album and companion files, controlled symbolic links, access protection, templates and histories are renamed consistently. Existing search fields are not cleared. | Rename, HTTP and Chromium suites; large original comparison confirms all 20 text columns and each of 955 files per fixture. | High |
| R3 | Failures, permissions and retries | Protected journals permit the same rename to resume after failure. CSRF, administrator/archive permissions, name conflicts and idempotent retries are checked. The former separate history-writing entry returns 410. | Success, rejection, interruption and resumption in the 893/114/92 suites. | High |
| R4 | Other backend writers and backups | Maintenance, page, metadata and file writers coordinate with Rename. Conflicting changes are rejected; existing recovery backups accompany matching backups. | HTTP checks of affected writer entry points and data preservation on 409; Rename/recovery suite; mail attachment bytes checked. | High within the checked scope |
| B1 | Personal SELF permissions | The default remains user: the complete username and existing personal ownership. Missing or invalid scope settings do not enable domain access. | Ownership suite covering default mode, invalid scope, record status, form boundaries and unrelated accounts. | High |
| B2 | Optional domain mode and central administration | Exact normalised domains share SELF records; subdomains remain independent. Cross-organisation access is restricted to the explicit configuration list and verified identities. IDN aliases are not merged. | Ownership/form suite with positive and negative cases; verified login and domain matching; PHP 8.3/8.4. | High; site admission must verify email addresses |
| F1 | Form storage and conflicts | A fixed trusted site writer can handle persistence. Form name and version conflicts are checked first; ordinary forms retain the existing writer. | Dispatch/ownership checks, exactly one writer invocation; source checks of both storage paths and the disabled-by-default extension. | High within the checked scope |
| L1 | Login target domain and backend include | Four login entry points compare IDNs non-transitionally. Distinct ß/ss domains remain separate; Unicode/Punycode pairs work. The direct backend include is corrected. | 40 permitted/rejected host/path/port cases per runtime; Basic/session authentication through real HTTP and browser paths. | High |
| M1 | Email addresses and encoding | Delivery normalisation preserves local-part spelling and uses explicit UTS46 for domains; entities and historical browser escapes are decoded at controlled boundaries. Historical login keys retain their own previous path. | 158 address/IDN/API checks; 31 original/candidate MIME comparisons; historical key implementation separately preserved. | High in the checked runtime profile |
| M2 | Delivery, SMTPUTF8 and failures | Ordinary addresses retain the previous channel. International local parts use the existing SMTP setup with negotiated SMTPUTF8; no silent address rewriting or success after rejection. | 100 real loopback SMTP checks and 15 native/backup/fallback cases, including TLS, authentication, rejection, Cc/Bcc and retained payloads. | High for checked protocol cases; production providers assessed separately |
| M3 | Invitations, recovery and notifications | Separate original delivery address in optional accounts.email_delivery where needed. Historical account keys retained. Password changes only after message acceptance; invitation failures displayed. | Address/account functions, original mail comparison and failure suite; optional schema/recovery contracts checked against supplied mail evidence. | High within the evidenced scope; not recertification of every production account |
| M4 | Serial, form and backup mail | Plain text/HTML, recipient names, Cc/Bcc, logs and attachments retained. Confirmed native duplicate delivery still counts as success after primary failure. | 31 semantic before/after comparisons, 100 SMTP and 15 native/attachment checks; no real test mail to third parties. | High |
| M5 | Local mail libraries | IDN/Normalizer and SMTPUTF8 libraries are privately encapsulated and supplied locally with licences. Existing vendor/ is not replaced. | All 24 file hashes match mail/DEPENDENCIES.json; syntax and delivery tests. | High for integrity and checked use |
| D1 | Version, documentation and acceptance | 12.9.5.4 in index.php; complete comparison table, backport commits and repeatable stable GitHub acceptance. .4 contains no fixed November pause. | Tree/diff review against v12.9.5; PHP 8.3/8.4 with Chromium; 20 updated branches with head and version checks. | High; acceptance applies to the checked source |

<a id="grosssite-vergleich-mit-dem-ursprünglichen-rename-code"></a>
## Large-site comparison with the original Rename code

Two separate disposable databases compared the actual original SQL/file functions with the complete new Rename. All 20 resource/history text columns and every file's payload were preserved.

| Fixture | Resources | History entries | Files | Old SQL/file portion | Complete new Rename |
| --- | ---: | ---: | ---: | ---: | ---: |
| Sparse dependencies | 5,000 | 25,000 | 955 | 20.43 s | 0.41 s |
| Dense dependencies | 2,000 | 5,000 | 955 | 7.36 s | 3.99 s |

These are reproducible local measurements using PHP 8.4.27/MariaDB 11.8.6, rather than a production latency guarantee. All 108 PHP files changed against the base also passed syntax checks, and all 24 supplied mail-library files passed their integrity comparison.

<a id="backports-und-umfang"></a>
## Backports and scope

Database wait limits and the date notice were carried forward to **20 other development branches**. Every branch passed twelve batch checks and syntax checks of the three changed application files. Older search sources passed 13 notice checks. The seven modern-search branches passed 15 notice checks and all 96 existing index checks with unchanged expectations. Published heads, the five-path scope and unchanged versions/renderers were read back from GitHub. The [complete starting and result commits](release-12.9.5.4-backports.json) are supplied. Historical audit/ branches remain comparison sources. Existing comprehensive development-branch pipelines are distinct from these targeted checks.

Extended verification found a regression caused by the initial backport on the seven modern-search branches: an outstanding index-format upgrade was no longer visible when timestamps were already current. Existing format/pending and running-state checks were restored. The final heads listed here contain that correction; additional tests explicitly cover the missing format and pending marker. The stable 12.9.5.4 application source is unchanged by this correction.

The local Policies November condition is not part of the release. No site credentials or production config.php values are included. SQL seeds and the existing vendor/ tree are unchanged from 12.9.5. This release evidence does not claim to deliver or change individually maintained r/ resources.

<a id="installation-und-rückfall"></a>
## Installation and rollback

Transfer the complete matching release, including mail/ and the new helpers; preserve existing config.php, databases, media and site resources. Personal SELF ownership remains the default without a configuration change. Explicit settings in [onlymine.md](onlymine.md) govern domain mode and central administration. The new .4 database wait limit requires no schema change. The mail extension included since the base can add optional accounts.email_delivery where needed; older code ignores it.

For rollback, restore the previous complete source together. Preserve an existing Rename journal and its matching backup, and resume an interrupted rename consistently. Do not overwrite an installation with a database or customised sources from another site's backup.

<a id="grenzen-dieses-nachweises"></a>
## Limits of this evidence

Fresh executions used PHP 8.3/8.4 with MariaDB 11.4 on GitHub and PHP 8.4.27/MariaDB 11.8.6 locally. Historical PHP 7/MySQL installations, production SMTP providers, arbitrary site PHP resources and a new deployment to every Plesk installation are not claimed acceptance results. Complete earlier mail evidence remains in [international-email-2026-11-08.md](international-email-2026-11-08.md). A process-termination test and SQL-lock test substantiate the stated resumption/locking properties; the resource budget is checked between pages, so one expensive site PHP resource may exceed two seconds.

<a id="vollständiges-dateiverzeichnis-gegenüber-v1295"></a>
## Complete file inventory against v12.9.5

A = added, M = modified. Groups refer to the effects, evidence and assessments above. The list includes application files, local dependencies, tests and documentation.

| Status | File | Change group |
| --- | --- | --- |
| A | `.github/workflows/stable-release.yml` | D1 |
| M | `.gitignore` | D1 |
| A | `BRANCHES.md` | D1 |
| M | `README.md` | D1 |
| M | `addreadrights.inc.php` | B1–B2 |
| M | `alloc.inc.php` | S1–S3 |
| M | `attempt.php` | M1–M5 |
| M | `checkaddself.inc.php` | B1–B2 |
| M | `checkdelself.inc.php` | B1–B2 |
| M | `checkeditself.inc.php` | B1–B2 |
| M | `config.php.sample` | B1–B2 |
| M | `connect.inc.php` | I1–I4 |
| M | `delreadrights.inc.php` | B1–B2 |
| A | `docs/development-status.md` | D1 |
| A | `docs/international-email-2026-11-08.md` | D1 |
| A | `docs/login-host-policy.md` | D1 |
| A | `docs/onlymine.md` | D1 |
| A | `docs/release-12.9.5.4-backports.json` | D1 |
| A | `docs/release-12.9.5.4.md` | D1 |
| A | `docs/rename-backport-12.9.5.2-benchmark.json` | D1 |
| A | `docs/rename-backport-12.9.5.2-branches.json` | D1 |
| A | `docs/rename-backport-12.9.5.2-publication.json` | D1 |
| A | `docs/rename-backport-12.9.5.2.md` | D1 |
| A | `docs/rename.md` | D1 |
| A | `docs/search-memory-2026-11-18.md` | D1 |
| A | `docs/search-memory-backports.json` | D1 |
| A | `docs/search-memory-starting-heads.json` | D1 |
| M | `editformsave.php` | F1 |
| M | `editreadrights.inc.php` | B1–B2 |
| M | `formsave.php` | F1 |
| M | `htaccessandindexer.inc.php` | I1–I4 |
| M | `index.php` | D1 |
| M | `indexer.php` | I1–I4 |
| M | `indexupdate.inc.php` | I1–I4 |
| M | `levenshtein.inc.php` | S1–S3 |
| M | `login.idn.inc.php` | B1–B2 |
| M | `login.inc.php` | B1–B2 |
| M | `loginformsave.inc.php` | B1–B2 |
| M | `loginsave.php` | L1 |
| M | `loginsi.inc.php` | B1–B2 |
| A | `mail/DEPENDENCIES.json` | M1–M5 |
| A | `mail/accounts.php` | M1–M5 |
| A | `mail/addresses.php` | M1–M5 |
| A | `mail/delivery.php` | M1–M5 |
| A | `mail/header.php` | M1–M5 |
| A | `mail/idn/Idn.php` | M1–M5 |
| A | `mail/idn/Info.php` | M1–M5 |
| A | `mail/idn/LICENSE` | M1–M5 |
| A | `mail/idn/Resources/unidata/DisallowedRanges.php` | M1–M5 |
| A | `mail/idn/Resources/unidata/Regex.php` | M1–M5 |
| A | `mail/idn/Resources/unidata/deviation.php` | M1–M5 |
| A | `mail/idn/Resources/unidata/disallowed.php` | M1–M5 |
| A | `mail/idn/Resources/unidata/disallowed_STD3_mapped.php` | M1–M5 |
| A | `mail/idn/Resources/unidata/disallowed_STD3_valid.php` | M1–M5 |
| A | `mail/idn/Resources/unidata/ignored.php` | M1–M5 |
| A | `mail/idn/Resources/unidata/mapped.php` | M1–M5 |
| A | `mail/idn/Resources/unidata/virama.php` | M1–M5 |
| A | `mail/message.php` | M1–M5 |
| A | `mail/normalizer/LICENSE` | M1–M5 |
| A | `mail/normalizer/Normalizer.php` | M1–M5 |
| A | `mail/normalizer/Resources/unidata/canonicalComposition.php` | M1–M5 |
| A | `mail/normalizer/Resources/unidata/canonicalDecomposition.php` | M1–M5 |
| A | `mail/normalizer/Resources/unidata/combiningClass.php` | M1–M5 |
| A | `mail/normalizer/Resources/unidata/compatibilityDecomposition.php` | M1–M5 |
| A | `mail/phpmailer/LICENSE` | M1–M5 |
| A | `mail/phpmailer/SMTPUTF8.md` | M1–M5 |
| A | `mail/phpmailer/src/Exception.php` | M1–M5 |
| A | `mail/phpmailer/src/OAuthTokenProvider.php` | M1–M5 |
| A | `mail/phpmailer/src/PHPMailer.php` | M1–M5 |
| A | `mail/phpmailer/src/SMTP.php` | M1–M5 |
| A | `mail/smtp.php` | M1–M5 |
| M | `myfunctions.inc.php` | M1–M5 |
| A | `onlymine.inc.php` | B1–B2 |
| M | `render.inc.php` | B1–B2 |
| M | `reset2.php` | M1–M5 |
| M | `saml/login.php` | L1 |
| A | `search-legacy-suggestions.inc.php` | S1–S3 |
| M | `sendintranetlogin.php` | M1–M5 |
| A | `tests/browser-runtime.cjs` | D1 |
| A | `tests/fixture-environment.cjs` | D1 |
| A | `tests/fixture_environment.py` | D1 |
| A | `tests/index-update-notice.php` | D1 |
| A | `tests/indexer-batch-regression.php` | D1 |
| A | `tests/indexer-crash-regression.py` | D1 |
| A | `tests/indexer-database-lock-regression.py` | D1 |
| A | `tests/indexer-http-regression.py` | D1 |
| A | `tests/international-email-evidence-2026-11-08.json` | D1 |
| A | `tests/international-email-native.py` | D1 |
| A | `tests/international-email-parity.py` | D1 |
| A | `tests/international-email-smtp.py` | D1 |
| A | `tests/international-email-worker.php` | D1 |
| A | `tests/international-email.php` | D1 |
| A | `tests/login-host-regression.php` | D1 |
| A | `tests/onlymine-domain.php` | D1 |
| A | `tests/rename-benchmark.php` | D1 |
| A | `tests/rename-browser.cjs` | D1 |
| A | `tests/rename-crash-worker.php` | D1 |
| A | `tests/rename-http.py` | D1 |
| A | `tests/rename-regression.php` | D1 |
| A | `tests/rename-web-fixture.php` | D1 |
| A | `tests/rename-web-regression.py` | D1 |
| A | `tests/rename-web-support.php` | D1 |
| A | `tests/search-legacy-database-regression.php` | D1 |
| A | `tests/search-legacy-html-regression.php` | D1 |
| A | `tests/search-legacy-memory-regression.php` | D1 |
| A | `tests/search-memory-branch-verification.json` | D1 |
| M | `u5admin/albumsort.php` | R1–R4 |
| M | `u5admin/archiveunarchive2.php` | R1–R4 |
| M | `u5admin/backupdb.php` | R1–R4 |
| M | `u5admin/backupfiles.php` | R1–R4 |
| M | `u5admin/checkco.php` | R1–R4 |
| M | `u5admin/cleanlog.php` | R1–R4 |
| M | `u5admin/coding2.php` | R1–R4 |
| M | `u5admin/copy2.php` | R1–R4 |
| M | `u5admin/definelanguages2.php` | R1–R4 |
| M | `u5admin/defineloginglobals2.php` | R1–R4 |
| M | `u5admin/delete2.php` | R1–R4 |
| M | `u5admin/deletefile.php` | R1–R4 |
| M | `u5admin/deletefilea.php` | R1–R4 |
| M | `u5admin/invite.php` | M1–M5 |
| M | `u5admin/ishomepage2.php` | R1–R4 |
| M | `u5admin/kill2.php` | R1–R4 |
| M | `u5admin/loginsave.php` | L1 |
| M | `u5admin/mailrenderfunctions.inc.php` | M1–M5 |
| M | `u5admin/mailsend.php` | M1–M5 |
| M | `u5admin/meta2.php` | R1–R4 |
| M | `u5admin/new2.php` | R1–R4 |
| M | `u5admin/phperror.php` | R1–R4 |
| M | `u5admin/reactivateinitialu5cmslayout.php` | R1–R4 |
| M | `u5admin/rename.php` | R1–R4 |
| M | `u5admin/rename2.php` | R1–R4 |
| M | `u5admin/rename3.php` | R1–R4 |
| M | `u5admin/renaming.php` | R1–R4 |
| A | `u5admin/resource-mutation.inc.php` | R1–R4 |
| A | `u5admin/resource-rename-support.inc.php` | R1–R4 |
| A | `u5admin/resource-rename.inc.php` | R1–R4 |
| M | `u5admin/savepage.php` | R1–R4 |
| M | `u5admin/sendfile2.php` | R1–R4 |
| M | `u5admin/sendfilea2.php` | R1–R4 |
| M | `u5admin/u5idn.inc.php` | M1–M5 |
| M | `u5admin/uniquenames.php` | M1–M5 |
| M | `u5admin/updateintranet.php` | R1–R4 |
| M | `u5sys._login.php` | L1 |
| M | `u5sys._search.php` | S1–S3 |
| M | `u5sys._searchsi.php` | S1–S3 |
| M | `zendmail.php` | M1–M5 |
