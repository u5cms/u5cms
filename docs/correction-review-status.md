> A subsequent authorized installation-acceptance pass found and corrected two further parser/reference defects. [Read the newer completed pass and its new CI evidence](2026-11-03-installation-acceptance.md). The results below retain their exact earlier scope and tested commits.

# u5CMS correction and anti-regression review — completed 3 November 2026

All necessary corrections are published on the 15 development branches and the audit branch. The final 48-job GitHub matrix passed under PHP 8.3, 8.4 and 8.5. Every job log was checked for completion of both groups and execution of the newly required seed, mobile and backup tests. Main and production are unchanged.

## What the additional pass found

The streaming hexadecimal backup format introduced a real packet-budget regression. Original Main restored a 24 MiB row under a 32 MiB packet limit; the newer exporter emitted a 50,331,731-byte INSERT which failed under that same valid limit. The new automated regression also fails on the old published candidate, proving that it detects this defect.

The correction preserves lossless hexadecimal bytes and the ordinary small-row format. Oversized rows use bounded fragments in a restore-target staging table. Staging survives connection changes, incomplete assembly fails before the real row is inserted, and successful completion or retry cleans the intermediate state. Engine selection uses actual metadata. The correction is present on all nine affected development branches and the audit branch. Source data, application connection encoding and backup publication remain unchanged. Full restore operation, rights, session requirements and staging lifecycle are documented in [regression-ci.md](regression-ci.md).

## Verification

| Check | New executed result |
| --- | --- |
| Published application state | 17,816 blobs matched; 629 required/changed paths matched; no mismatch |
| Current Main against all 15 branches | 576 behavior checks; 2,080 configuration and 672 complete-view observations; no differences |
| Pre-correction cumulative state against the two latest profiles | 108 behavior checks; 390 configuration and 126 complete-view observations; no differences |
| Actual conference seed | 9,120 page/language/role cases; no lost access or business-form contracts |
| Mobile controls | 84 real-browser checks on each of ten local branch profiles; now mandatory in supported CI profiles |
| Rename conservation/performance | Four sparse/dense comparisons with actual legacy SQL functions; all 20 text columns and every file conserved |
| Backup packet regression | 28 checks on both local helper profiles; original control, constrained plain/gzip, Unicode/Latin-1/binary/types/NULL/empty, fresh sessions, fail-before-write, retry and cleanup |
| Strict editor and SQL/ZIP restore | 507 browser checks passed, including 24 MiB export with a 64 MiB PHP limit |
| Actual final GitHub CI | All 48 jobs passed; all 48 logs attested; PHP 8.3/8.4/8.5 and MariaDB 11.4 |

The source inventory covers original Main, current Main and previous branch states. The remaining forms, permissions, conference submission/review/publication/registration, Dat options, legacy versus explicit RFC import, search, OIDC, uploads, migration, history, recovery, dependency patches and designs are exercised by the branch capability matrix. The additional seed comparator requires real abstract/registration controls and rejects nine destructive oracle mutations. The deliberately evolved navigation search has its own browser suite.

Local runtime: PHP 8.4.27, MariaDB 11.8.6, Chromium 156. Tests use disposable installations/databases and per-run synthetic credentials; mail transport is disabled. These results do not certify real external mail/payment/federation acceptance or an actual PHP 9 runtime. No merge, release replacement or production installation is performed. The separate earlier restricted detailed payload is excluded.

## Exact tested code and actual CI

| Branch | Tested commit | PHP matrix |
| --- | --- | --- |
| `audit/correction-contracts-20261103` | [c16f04f41d80](https://github.com/u5cms/u5cms/commit/c16f04f41d806598331bbc1ebf62a29c8786bc46) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41658353904) |
| `12.10.0-Multi-Optimizations-by-AI` | [5c094930e21e](https://github.com/u5cms/u5cms/commit/5c094930e21e16eaef32bc725b813ec6ea6ddec7) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41658374381) |
| `12.10.1-Support-More-Image-Formats` | [bc68a7596da2](https://github.com/u5cms/u5cms/commit/bc68a7596da29032812fa86eddcf6626760ed4fd) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41658395796) |
| `12.11.0-Code-Resource-Migration` | [f8febcc60891](https://github.com/u5cms/u5cms/commit/f8febcc608918a802f28cd03270d1854b3082ee1) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41658415842) |
| `12.11.1-Migration-Hardening` | [2596303ccce3](https://github.com/u5cms/u5cms/commit/2596303ccce36486f6811cdc9eeb19112a06424c) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41658555191) |
| `12.11.2-Backup-Restore-Corrections` | [92f090c9e8d0](https://github.com/u5cms/u5cms/commit/92f090c9e8d06581f1df6ccc1dc31b1dc42f568c) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41658581547) |
| `12.11.3-Private-File-Cache-Corrections` | [ad6d5ef9d123](https://github.com/u5cms/u5cms/commit/ad6d5ef9d12387799edd54e7e166bd1539ffc953) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41658632590) |
| `12.11.4-Intranet-Login-Corrections` | [6ea8d26640d2](https://github.com/u5cms/u5cms/commit/6ea8d26640d269cb78082299fd6c0321b17dc28c) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41658655119) |
| `12.12.0-OIDC` | [ccc88f9b384f](https://github.com/u5cms/u5cms/commit/ccc88f9b384fd518d44da6d37264b50d69fb9821) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41658946671) |
| `12.12.1-General-Corrections` | [4386f8a911bb](https://github.com/u5cms/u5cms/commit/4386f8a911bb9fa0a3f71809f25478d0e0e76540) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41658966546) |
| `12.12.2-Textarea-Interactions` | [d9308be7691a](https://github.com/u5cms/u5cms/commit/d9308be7691a822cadf2de5f995f3403a3c9e426) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41659017409) |
| `12.12.3-PHP9-Compatibility` | [ba09d7c53df3](https://github.com/u5cms/u5cms/commit/ba09d7c53df3c50149d49d4bb5c0f778dfed703f) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41659040689) |
| `12.12.4-Default-Design` | [68a9500a0e2a](https://github.com/u5cms/u5cms/commit/68a9500a0e2a8aa1527075414858b40a699c8945) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41659062775) |
| `12.9.6-Misc-Backend-Optimizations` | [256d4e86c9cd](https://github.com/u5cms/u5cms/commit/256d4e86c9cdae25c55b2e847bdbb42d10550c90) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41659084925) |
| `feature/search-phrases-quotes-highlighting` | [7fb5fa789601](https://github.com/u5cms/u5cms/commit/7fb5fa789601d13f9cafabd4f94865fb4f55c722) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41659294550) |
| `fix/nosniff-script-mime-types` | [1d3501d3e458](https://github.com/u5cms/u5cms/commit/1d3501d3e458048f1e66a4904ad628473a7eb42d) | [3/3 passed](https://github.com/u5cms/u5cms/actions/runs/41659321398) |

[Machine-readable evidence](../tests/anti-regression-final-2026-11-03.json) contains the exact job IDs, source commits and attested checks. The final documentation-only checkpoint preserves the tested application/test bytes; it does not represent an extra application run.

## Completion and rollback

No confirmed regression remains open in the tested scope of this pass. Main remains `78cadbf87b7307203b8bd4ff96929b306af7d982`. Rollback uses the listed parent of the relevant guarded correction commit; repository publication did not rewrite installed data or templates. There is no scheduled follow-up or continuing background process. The authorized review is complete.
