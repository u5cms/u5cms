# 12.9.5.7 acceptance in progress

Ordinary editor saves must remain available while indexing, backup work or an
idle Rename recovery plan exists. Versions .2–.6 coupled those operations through
one exclusive mutation mutex; .7 removes that coupling. Only a live Rename can
briefly exclude coordinated source writes. No queued or timed browser save retry
is introduced. The original permission, authentication and conflict policies apply.

## Changed behaviour and preservation

| Area | .7 behaviour | Actual evidence so far |
| --- | --- | --- |
| Normal saves | Shared Rename gate, separate activity records; no index/backup mutex acquisition. Ancillary search-marker errors are logged without refusing a source save. | Actual authenticated save under both old mutexes; two concurrent writers. |
| Indexing | Yields to a live Rename or existing writer, retains its own single-run mutex and bounded batches; source-digest guards reject stale cache writes. Idle plans never hold indexing hostage. | 33 HTTP/save/index checks; independent two-server reproducer saves all five languages while the real indexer is paused after reading the source. |
| Live Rename | Retains streamed candidate discovery, guarded changed rows, file pass, headings, five languages, dependencies, histories and policy switches. Reports actual names, phase, elapsed time and measured progress. | 893 core/database checks; 115 HTTP checks; 83 real SQL-lock/recovery assertions. |
| Backups | Source generations detect concurrent coordinated mutations. The backup yields and removes an invalid result; editor saves remain available. | Actual database backup paused after producing output, concurrent authenticated save, refusal/removal, quiescent retry. Native ZIP acceptance is required on the CI runner. |
| Process failure | Kernel-owned gate ends with the process. Immutable recovery data survives; ordinary editing resumes immediately. | Actual PHP Rename worker killed with SIGKILL, valid save succeeds, exact Rename retry finishes. |
| Same-second saves | Legacy page/code/metadata writes use conditional resource updates and increasing revisions, retaining submitted conflict versions in history. Existing future-branch update APIs are preserved. | Two actual HTTP writers forced to read the same revision; exactly one active version, both submissions in history. Configured overwrite policy retained. |
| Encodings | Existing Windows-1252 forms and UTF-8/Latin-1 database boundaries preserved. No source-wide re-encoding. | Actual page, metadata and code POSTs with accents, euro and entities; mixed column charsets. |

The new activity/concurrency suite passes 52 assertions locally on
PHP 8.4.24 / MariaDB 11.8.6. The stable candidate `9d8ca99e` has full PHP 8.3
GitHub acceptance including Chromium; PHP 8.4 also passed including Chromium. Final versioned candidate checks
are still in progress. Local Chromium cannot start because this execution
sandbox rejects its Unix socket; that is not counted as a browser pass.

## Rename performance

Measured complete operations, including publication and byte/text conservation:

| Synthetic site | Changed rows | Files | Complete Rename | Old SQL/file implementation |
| --- | ---: | ---: | ---: | ---: |
| 5,000 resources, 25,000 history rows, sparse references | 300 | 955 | 0.576 s | 24.959 s |
| 2,000 resources, 5,000 history rows, dense references | 7,000 | 955 | 5.433 s | 10.287 s |

All 20 resource/history text columns and every file were checked. These are
fixture measurements, not a duration guarantee for production or SQL contention.
The optional history lookup and the .5 database wait budgets remain effective.

## Cumulative acceptance and operational boundary

The comparison baseline is unchanged v12.9.5, resolved commit
`9d366253417822c279692767b89dcf1ff0388c5c`. Inherited login-host, international-mail,
SELF/ONLYMINE, search memory/text/HTML, Rename and index contracts remain part of
the stable acceptance. This release does not import future OIDC, typed-code,
responsive, schema or layout work into the stable line. Each of the 21 maintained
future branches is adapted and tested in its own source context before guarded
publication. Branch acceptance/publication is still pending in this report.

Filesystem and legacy MyISAM changes cannot form one atomic transaction. An
interruption can leave a partly applied Rename, explicitly reported as incomplete.
Recovery never silently overwrites a later source edit. Resolve such a conflict
using its saved data before retrying; retain the plan and row/file recovery data.
External bookmarks and arbitrary application identifiers remain outside the
recognised CMS dependency grammar.

The complete release replaces the temporary no-global-lock workaround. Install
all newly added system helpers with the release before activating updated entry
points; preserve configuration, data and installation-specific exceptions.
Production rollout remains a separate operation from this code acceptance.
