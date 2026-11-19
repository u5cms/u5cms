# 12.9.5.6 implementation checkpoint

Authorised by Stefan on 19 November 2026: implement .6 after published .5, perform independent cumulative review from v12.9.5, preserve and verify all maintained future branches, publish tested .6/Main/tag/release, then email Markus Gaschen with Michael Rolli in Cc to request the deployment ticket. No production deployment by this task.

Owner: foreground implementation in the original .6 conversation, workspace /workspace/scratch/68435f713f4a/u5cms-review. Do not duplicate implementation while a newer active checkpoint exists. Re-read current branch and open PRs before mutation. This initial checkpoint is not an implementation or acceptance result.

Verified source refs obtained from GitHub and a fresh clone:
- v12.9.5: commit 9d366253417822c279692767b89dcf1ff0388c5c;
  annotated tag object fa6e8982e85d7ca9ec33683ab33d31eb86a31eb9.
- v12.9.5.4: c7062c680deb29fcac70815eea929ad57ee3a5d2
- v12.9.5.5/Main: 8bcb09937999d4ebb51da293b06a113bda0940d8
- .5 release published 19 November 2026 06:54:55 UTC.
- No open PR or .6 release when starting.

Initial source findings, NOT newly executed runtime tests:
- Actual page saves use u5admin/savepage.php, not only done.php.
- savepage.php writes source/history, then launches indexer.php?n=NAME and lastsave.php in separate browser iframes. done.php does likewise for other actions.
- lastsave.php alone writes lastsave.txt. Thus a completed source write can outlive a closed/failed confirmation-frame request without the expected marker.
- Named indexing renders five language versions without U5IndexSlice's mutex and without the full-index database wait guard.
- Source-byte digests protect index writes from concurrent direct source changes, but coordination with partial Rename and dependency changes needs explicit verification.
- Preserved rules: intentional no-autoindex search text, separate active/archive row identity, existing source encodings, domain/user defaults, branch-specific modern search format/pending handling, typed-code migration/journal namespaces and renderer.

Plan: map entrypoints/state/locks/dirty markers; move source invalidation to coordinated server mutations; guard named/full indexing consistently against SQL waits and simultaneous Rename/writers without invalidating source; meaningful real save/closed-frame/parallel/blocker/failure tests; rerun stable cumulative suites; architecture-aware forward ports and exact head/test inventory; release and mail only after passing acceptance.

Current step: source analysis, disposable runtime preparation. Next: implement and reproduce the source findings, recording actual outcomes and limitations before publication.


## First implementation and actual local verification

Implemented on the dedicated .6 branch (not released): shared named/full index
mutex, nonblocking source/Rename coordination using each architecture's existing
mutation lock, bounded named SQL waits/socket reads, a maximum of three browser
retries for busy named work, and atomic server-side dirty markers before writes
and at shutdown before the mutation mutex is released. The legacy lastsave
endpoint also writes the marker atomically. The common writer marks
conservatively even if later validation rejects a request; this can schedule
extra full work but cannot silently drop a source update.

Actual local profile: PHP 8.4.24, native intl/ICU, MariaDB 11.8.6, isolated copied
CMS and private database. First 22 save/index assertions passed, including a
real editor POST without loading any child iframe, five-language index values,
held index/source kernel locks, pending Rename, a real blocked named SQL call
returning after about five seconds, zero waiting SQL afterwards, and retry.
The expanded suite adds rapid saves and full/source contention; its newest
result must be checked before quoting the final assertion count.

Also freshly passed: full HTTP index completing in six requests; full DB-lock
termination; SIGKILL/competitor/watermark test; 83 Rename DB-lock/recovery,
114 authenticated Rename HTTP, 893 Rename core/DB, 12 index batch,
13 index-notice, ownership/form-dispatch, 45 search-memory,
55,990 HTML excerpt, 17 legacy SQL search, 158 address/mail and 40 login-host
checks. These are local checks, not browser/CI or production claims.

The first login-host run exposed the local fixture runtime setup: subprocesses
using PHP_BINARY did not inherit the explicit -c ini. Setting PHPRC to that
same runtime configuration (including native intl) made all 40 checks pass.
No application or expectation was changed to hide that failure.

Outstanding before .6 release: baseline reproduction of the closed-frame
marker failure, broader cumulative review against .nix, new real concurrency
cases and failure paths, PHP 8.3/8.4 CI with Chromium, modern-search and
branch-specific forward ports with actual per-branch evidence, source/tag/Main
archive verification, final cumulative report and authorised deployment mail.
Never present this checkpoint as completed .6 acceptance. Next action: run the
expanded save/index regression, reproduce its baseline failure, then review and
forward-port while preserving modern search/migration/renderer sources.


## Expanded save regression result

All 26 assertions of the expanded save/index suite passed on the current .6
sources. The suite found and corrected a real response-order error during
implementation: the busy full slice initially emitted its continuation before
setting HTTP 409. Status/Retry-After are now sent before the body; the test
expectation remains unchanged. This was not a released-source regression.

The identical real-save regression was run against an unchanged archive of
v12.9.5.5 and failed exactly at the missing server-side dirty marker after
a successful save without browser children. This reproduces the baseline
defect; it is an expected failing test, not a passing acceptance run.


## CI and branch architecture follow-up

GitHub Actions 44481530011 passed complete stable acceptance on both PHP 8.3
and PHP 8.4 at ac3e5821a37ae9ce8639e89f861c8202f9691676, including Chromium.
The separate attempted local Chromium launch was blocked by the local socket
sandbox before the browser started; it supplies no browser acceptance claim.

Forward-port testing found an API distinction in 12.11.0: codeassets.inc.php
exists there but does not yet declare u5CodeMigrationLock. The shared helper
now checks the function and falls back to that branch's generic Rename lock.
All branch tests are being rerun with this correction. Versions, renderer,
modern search, and each branch's migration/journal sources remain unchanged.
No future-branch changes have been published yet.


## Final prepublication acceptance, 19 November 2026

All 21 target heads have been published and independently read back. All 17
declared comprehensive workflows at their final heads passed. The four
remaining maintenance sources have no workflow, but passed their targeted
local save/index, batch and Rename checks. Exact commits, preserved blobs,
checks and workflow URLs are in release-12.9.5.6-backports.json.
The final stable application candidate 88dab36c2ff3c422f4c6421df4652e9656a6622d
passed PHP 8.3/8.4 with Chromium (Actions 44483223585). Subsequent source-branch
changes contain documentation only. The initial v12.9.5 SHA is an annotated
tag object; its actual commit is explicitly corrected above and in the report.
Git source comparisons resolve to the same original tree.

Next: expected-head merge of PR 199, create and publish v12.9.5.6, verify
Main/tag/source archive and target heads, then the authorised single deployment
email and private delivery proof. Release and email must not be inferred as
already completed from this prepublication checkpoint.
