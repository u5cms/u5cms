# Additional u5CMS installation acceptance — completed 3 November 2026

The additional pass found and corrected two concrete defects. All eleven affected development branches and both audit branches contain their applicable corrections. The 39 new GitHub CI jobs passed under PHP 8.3, 8.4 and 8.5; every job log was checked for actual execution of the required new regressions and completion of both groups. Main and production remain unchanged.

## Corrections

1. **Long HTML attributes blocked upgrades.** The real database copy returned controlled HTTP 500: `Cannot parse code reference tags.` A synthetic 16 KiB unquoted attribute reproduces the old helper's default PCRE JIT-stack exhaustion. Disjoint possessive tokens preserve quoting and reference context while avoiding that stack growth. No global regex limit was raised. This affects ten development branches from 12.11.1 and both audit branches. The new 52-check test fails the old published helper and passes every applicable corrected helper family with JIT on and off.
2. **Escaped examples changed in earlier branches.** Seven earlier development profiles could rewrite `[[cssbase]]`, which is authored literal text. The existing later protection is backported to their matching parsers, including the earlier 12.11.0 parser. The independent 16-check test verifies literal examples, actual includes, headings, query syntax, Windows-1252 bytes and idempotence; it fails the old earlier helper.

Only `codeassets.inc.php` changes in application code. Branch-specific features and source encodings are retained. All new applicable tests are required by each corrected profile's manifest. The acceptance branch also explicitly enables its own push CI filter.

## Additional executed evidence

| Check | Result |
| --- | --- |
| Full real-export backup and restore | 18 tables, 950,272 rows; every schema and every row's bytes match; source unchanged |
| Real-data Main/latest comparison | 883 pages, four languages, 4,824 access/page cases per installation; matching statuses, text, forms and controls; no missing case, no incomplete allowed page and no PHP abort |
| Private-upload capability differences | Twelve cases rechecked through actual endpoints; same 15 file references; 144 successful synthetic-byte downloads; 517 checks including forged/cross-installation token rejection |
| Complete migration suite | 1,671 checks passed after the parser correction |
| Review account switch and logout | 117 real-browser/database checks per installation, on Main and latest, across DE/EN/FR; stale tabs do not save under the wrong identity or after logout; valid recovery retains the right owner and abstract |
| Additional SQL backup schemas | 17 checks; Aria/InnoDB, quoted Unicode identifiers, generated/invisible columns, unsigned/decimal/floating/enum/set/temporal values, NULL/empty, plain/gzip under 1 MiB |
| Source/publication conservation | Every published patch matches its immutable parent and prepared blobs; no unexpected changed path; four unaffected development branches and Main remain unchanged |
| New GitHub CI | 13 matrices, all 39 jobs and all 39 execution logs verified |

The raw page comparison's twelve differing capability values are expected to differ between installations. They were not discarded by a blanket normalization: each was independently bound to its stored filename and tested through the actual `df.php` endpoints using synthetic file bytes. Canonical references and all other page contracts match.

One initial PHP 8.3 job on the textarea branch failed the existing Escape-dialog assertion. Its original failed job is retained in the machine evidence. An instrumented local run on the exact application commit passed all 161 browser/editor checks, with Escape events reaching and closing the menu in both Basic and session modes. The specific failed job was requested for an unchanged retry; the returned latest matrix entries passed. No application change or committed test/assertion adjustment was made for that initial signal. Its cause was not established and is retained as a non-reproduced CI observation.

The first corrected real-data upgrade took 65.5 seconds locally; this is one-time migration work, not normal page latency. Local runtime: PHP 8.4.27, MariaDB 11.8.6 and Chromium 156. CI uses PHP 8.3/8.4/8.5 and MariaDB 11.4, disposable databases and disabled mail transport.

## Exact tested commits and actual CI

| Branch | Tested commit | New matrix |
| --- | --- | --- |
| `12.11.0-Code-Resource-Migration` | [33d3bfd18e9c](https://github.com/u5cms/u5cms/commit/33d3bfd18e9cf051de85b26922ca6df786135e3e) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41708666190) |
| `12.11.1-Migration-Hardening` | [3576f59d078f](https://github.com/u5cms/u5cms/commit/3576f59d078f8978d4b9110d031e5dd45e77eb59) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41708702417) |
| `12.11.2-Backup-Restore-Corrections` | [90cfe5ad09b9](https://github.com/u5cms/u5cms/commit/90cfe5ad09b9832a591f91b5c1068081b9dd6e12) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41708742266) |
| `12.11.3-Private-File-Cache-Corrections` | [8dfece91cc4e](https://github.com/u5cms/u5cms/commit/8dfece91cc4e6f78c3983e3f905f0344050e1b29) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41708836949) |
| `12.11.4-Intranet-Login-Corrections` | [adca1ae9cabb](https://github.com/u5cms/u5cms/commit/adca1ae9cabb6fa2d7d87c884f6ff59cdd151892) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41708872523) |
| `12.12.0-OIDC` | [f4678f8e530c](https://github.com/u5cms/u5cms/commit/f4678f8e530c78ce9b0d81cdf52de284027687c8) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41708909763) |
| `12.12.1-General-Corrections` | [724c15f1f294](https://github.com/u5cms/u5cms/commit/724c15f1f29402d5140c422a0e3c77750c6171bb) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41709058077) |
| `12.12.2-Textarea-Interactions` | [0748368ff64b](https://github.com/u5cms/u5cms/commit/0748368ff64bba70e090945c185bc27179e62a9a) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41709095578) |
| `12.12.3-PHP9-Compatibility` | [3323f9553e0b](https://github.com/u5cms/u5cms/commit/3323f9553e0bdce09f3931733b8ee2a5a67e6af7) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41709133567) |
| `12.12.4-Default-Design` | [ffa5399135ba](https://github.com/u5cms/u5cms/commit/ffa5399135ba5fd685a72c068cb5fec5238d4631) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41709245937) |
| `audit/acceptance-20261103` | [2c86c8e36682](https://github.com/u5cms/u5cms/commit/2c86c8e36682b698c92f0fca3e58c29d36a875f9) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41708286989) |
| `audit/correction-contracts-20261103` | [3ab62bb6e7c1](https://github.com/u5cms/u5cms/commit/3ab62bb6e7c11fec9923672bd8d5c9e546d22ed3) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41709367178) |
| `feature/search-phrases-quotes-highlighting` | [943b77203957](https://github.com/u5cms/u5cms/commit/943b77203957f0724894cea7a2579294440847b6) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41709414226) |

[Machine-readable evidence](../tests/installation-acceptance-final-2026-11-03.json) records every exact commit, job ID and log attestation. A final documentation-only descendant preserves these tested application and test bytes; it is not an extra application run.

## Acceptance boundaries and rollback

The real export did not include the original server configuration or complete site filesystem. Its page comparison uses equal neutral presentation and `executephp=nowhere`; 119 active pages contain site PHP that this comparison does not execute. The original uploaded documents are not certified: download binding uses synthetic bytes under the referenced names. Real-data comparison covers current Main and latest; the other affected profiles are validated through their required synthetic CI matrices.

Live mail/payment/federation providers and an actual PHP 9 runtime remain outside this acceptance. No Main merge, release replacement, production installation or external message was performed. The private export, rows, filenames and capability values are excluded from published evidence, as is the separate earlier restricted payload.

Rollback uses the recorded parent of the applicable correction commit. GitHub publication does not rewrite an installed database or its files. No confirmed defect from this additional pass remains open in the tested scope. The finite authorized pass is complete; no continuing background task or follow-up automation is created.
