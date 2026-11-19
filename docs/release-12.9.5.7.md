# u5CMS 12.9.5.7 — nonblocking saves and fast Rename

Ordinary editor saves no longer take the global index/backup mutation mutex.
Only a process that actually owns the Rename gate may briefly exclude source
writes. There is no browser save queue, timed save retry or persistent global
blocker from an idle recovery plan. Existing authentication, permissions and
configured revision-conflict policies remain in force.

## Behaviour and concrete acceptance

| Area | Result | Evidence |
| --- | --- | --- |
| Normal saves | Concurrent resource writes, shared Rename gate and independent process-owned writer records. Ancillary dirty/generation-marker errors are logged without rejecting source edits. | 52 activity assertions through actual authenticated page, code and metadata endpoints, including concurrent HTTP writers, background mutexes, strict and configured overwrite policies. |
| Indexing | Yields to active source writers and live Rename, retains its own stable kernel mutex, bounded batches and retry/backoff rules. Old idle plans do not stop indexing. Source-digest guards prevent stale cache publication. | 33 save/index assertions; two independent HTTP servers save all five source languages while the actual indexer is paused both before and after its source read; real SQL waits and killed-indexer recovery. |
| Fast Rename | Streamed candidates, changed-row publication and file pass retained. Five languages, resources/history, recognised links, filenames, heading protection and existing switches remain covered. | 893 Rename/database assertions, 115 HTTP and 92 Chromium assertions, plus 83 real database-lock/recovery assertions. |
| Helpful Rename status | Actual old → new names, phase, changed-row progress and elapsed time. ETA is shown only after measured row-processing progress; otherwise its uncertainty is explicit. | Live process status, exact names/phases, killed-owner release and guarded recovery tested. |
| Large Rename values | Bounded byte guards and connection-local binary staging keep ordinary multilingual Rename rows within a 4 MiB SQL packet. One conditional UPDATE still changes each row; packet staging never adds an editor queue or global lock. | 44 actual HTTP/current/history/round-trip/conflict/recovery assertions on the stable line; 58 also exercise typed-code Rename on applicable future lines. |
| Backup versus source edits | Editing wins. A mixed-generation DB/ZIP attempt returns an actionable conflict before publication, preserving previous valid backups byte for byte. ZIP replacement is staged privately and renamed only after validation. | 19 real DB/ZIP assertions, concurrent authenticated save, previous-file hash comparison and quiescent retry. |
| Crash and recovery | A killed owner cannot retain the kernel gate. Recovery plans and immutable row/file data survive; ordinary saving resumes. A later source conflict is reported instead of overwritten. | Actual PHP Rename worker killed with SIGKILL, immediate save, retained journal bytes and exact resume. |
| Save integrity and encoding | Legacy save endpoints use bounded server-side source digests, conditional updates and increasing revisions. Large pages do not duplicate old content into the SQL packet. Charset-aware names and existing Windows-1252/UTF-8 boundaries remain intact. | Deterministic simultaneous saves, both submissions retained in history, 4 MiB packet case, mixed column charsets, non-ASCII names, page/code/metadata text and BIGINT revision test. |

The accepted application source is `8da363d84140adee59aab294f5f8d5de50101098`.
Candidate `e069a3bd74dd27ccc00a25fc8f6580312c10f005` retains those exact application
bytes and includes the English branch guide, the complete packet test and the
browser setup described below.
Full stable acceptance passed with PHP 8.3.36 and PHP 8.4.27, MariaDB 11.4.13,
native ZIP and actual Chromium in
[Actions 44522772559](https://github.com/u5cms/u5cms/actions/runs/44522772559)
and [44522763360](https://github.com/u5cms/u5cms/actions/runs/44522763360).
The browser setup installs the pinned Playwright Chromium against the system
libraries already supplied by the [Ubuntu 24.04 runner image](https://github.com/actions/runner-images/blob/main/images/ubuntu/Ubuntu2404-Readme.md).
This avoids a reproduced Ubuntu package-mirror outage. All native browser,
backup and application tests remain mandatory; none is skipped by this change.

Local targeted tests used PHP 8.4.24 / MariaDB 11.8.6. Local Chromium cannot
start in the execution sandbox and is not counted as a browser pass; local ZIP
absence was explicit, with native ZIP required and passed on GitHub.

## Rename performance

Measured complete operations at application source `8da363d8`, including
publication and conservation of all 20 resource/history text columns and every
file. The final receipt distinguishes accepted application sources from documentation and test-only follow-ups.

| Synthetic site | Changed rows | Files | Complete Rename | Previous SQL + file pass | PHP peak |
| --- | ---: | ---: | ---: | ---: | ---: |
| 5000 resources, 25000 history rows, sparse references | 300 | 955 | 0.5246 s | 29.3572 s | 4 MiB |
| 2000 resources, 5000 history rows, dense references | 7000 | 955 | 5.2886 s | 10.6665 s | 6 MiB |

These are fixture measurements, not a production duration guarantee. Existing
SQL wait budgets, recovery safeguards and optional history lookup remain.

## Cumulative review from v12.9.5

The unchanged baseline is `9d366253417822c279692767b89dcf1ff0388c5c`
(v12.9.5 without a patch suffix). The review covers the callers and ordinary
successful operations as well as each new guard or failure path.

| Cumulative change | Behaviour checked |
| --- | --- |
| International addresses and login-host policy | Ordinary and international mail, MIME/header parity, CRLF rejection, native/fallback paths, actual SMTP harness, host allow/deny and existing authentication. |
| Search memory, text and HTML | Large-source memory budget, legacy rendered text, all indexed languages, HTML conservation, quoted/phrase-search contracts where present. |
| SELF/ONLYMINE | Exact configured operator/domain rules, admin-domain exceptions, existing account permission denials and ordinary editor operations. Historical .2 maintenance lines retain their original feature level. |
| Rename .2–.6 | Dependency grammar, heading/literal preservation, files, all languages, current/history data, policy switches, recovery, SQL contention and process failure. |
| Bounded indexing and .6 save/index integration | Batch/time limits, final watermark only on completion, dirty-before/after-source writes, SQL budgets, stale-source guards, interrupted retry and no live lock left by a killed process. |
| New .7 coordination | Index/backup/recovery-plan work never reserves ordinary source saves. Real Rename alone owns the exclusion gate; crash, actual UI retry, two-editor conflict and nonblocking background-save paths are tested. |

## Forward branches

All 16 active future/development lines and five compatible maintenance branches
were adapted in their own source context. Existing future resource-update,
code-migration, OIDC, responsive, schema and search APIs were retained. Version,
renderer and branch-specific search blobs were checked unchanged against each
starting head. Stable .7 does not import future layout or migration work.

The [branch receipt](release-12.9.5.7-backports.json) records exact remote heads,
tested application trees, preserved blob IDs, applicable cumulative contracts,
local results and full GitHub workflow results. Publication alone is not used as
test evidence. Historical audit/comparison branches are retained.

Partial test installations now include the new real helper dependency. The editor journey records exact CRLF storage/file bytes and LF textarea values at the HTML form boundary. Future
editor journeys exercise successful page/code saves under the old background
publication mutex, and preserve exact unsaved input during a real Rename gate.
Existing denial, input-preservation and history assertions remain; successful
background-save cases were added.

## Installation, recovery and rollback

Install the complete package, including `resource-activity.inc.php` and
`u5admin/resource-save.inc.php`, before activating updated entry points. Preserve
configuration, resources, uploads and installation-specific exceptions. This
release replaces the temporary production no-global-lock workaround; .6 is not
a rollout target. Never remove a lock pathname to unlock a live process.

Finish an active Rename before replacing its implementation, or retain its
matching guarded recovery data and recover it with the compatible package.
Filesystem changes and legacy MyISAM rows cannot form one database transaction:
an interruption may leave an explicitly incomplete Rename. Recovery refuses to
silently overwrite later edits. Keep the plan and row/file data and reconcile a
reported source conflict before resuming. An idle plan is not a live lock.

For rollback, stop new Rename operations, retain the matching database/files and
recovery journals, and restore a coherent full package and snapshot through the
normal operational process. Do not restore only one half of a partial Rename.
External bookmarks and arbitrary application identifiers are outside the CMS's
recognised dependency grammar.

GitHub code acceptance and release publication do not deploy the production
installations. Rollout and installation-specific acceptance are separate.
