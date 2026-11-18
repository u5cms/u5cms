# u5CMS 12.9.5.1 — search memory correction

Date: 18 November 2026 (Europe/Zurich). The user authorized a patch release from
current main, branch-local backports to all existing branches, an installed test
on policies.eiam.admin.ch, and a deployment request to Markus Gaschen.

Release base: `bf6863337cd6dd64284cf429f65da986d821d741`. The public version in
`index.php` is **12.9.5.1**. The earlier v12.9.5 tag is not replaced. No later
feature branch is imported into main. The [starting-head inventory](search-memory-starting-heads.json)
records every examined branch; the [verified publication map](search-memory-backports.json) records all 20 branch-local backports.

## Final behavior

* Both legacy result entry points select result metadata, all five fallback
  titles and only the selected language's body, rather than every content and
  search column. Finding, language, type, hidden/deleted, intranet, protected
  content and trusted administrator SQL conditions remain intact.
* `search-legacy-suggestions.inc.php` computes the best candidate for every
  query term in one unbuffered database pass. It tokenizes one row at a time,
  retaining no site-wide string, word array or growing vocabulary cache. Each
  database stream is released before a subsequent query. Empty vocabularies,
  database failures and repeated candidates terminate; a corrected query gets
  one AND/OR result attempt rather than mutually recursive scans.
* Legacy byte-based Windows-1252 distances, source spelling and last-equal-cost
  tie selection are retained. Resource boundaries no longer invent joined
  words. Correction-only limits are 16 terms, each at most 255 bytes; oversized
  source tokens are skipped without copying them. Ordinary exact search keeps
  its existing input behavior.
* The historical `levenshtein.inc.php` uses the same stream and escapes the
  suggestion label and URL. Its separate legacy query source is preserved.
* The old `alloc.inc.php` HTML length/substrings now stream entity boundaries
  instead of allocating a PHP array for every source character. Existing byte,
  entity and positive/negative-offset output semantics are preserved. This
  resolves a second large-page failure exposed by French Policies excerpts.
* A missing no-result `terms` node no longer causes the old callback to throw.

There is no database schema/content, index-format, configuration or template
migration. Existing source encodings are preserved byte-for-byte outside the
changed ASCII code. Work still grows with scanned text, and buffered legacy
result bodies grow with matching text in one language; this is not a claim of
constant memory for arbitrarily large individual database fields/results.

## Two search generations

Main, 12.9.6 through 12.11.4 and the older fix branches receive the complete
legacy correction. The search-feature branch, 12.12.0 through 12.13.0 and the
audit branches already use the newer streamed engine. Their active search core,
service, suggestion budgets, request parser, excerpts, highlighting, phrases,
multiword corrections, witnesses, visibility policies and assets are not changed.
Only the retained historical suggestion include and its new helper are repaired
there. Each branch retains its own application version and cumulative work.

## Executed validation

Runtime: PHP 8.4.27, MariaDB 11.8.6, Chromium Headless Shell 156.0.8078.79.
SQL/HTTP fixtures are disposable. These are local executions, not GitHub CI.

| Validation | Result |
| --- | --- |
| Real legacy suchen/leven with real SQL | 17 checks per result entry point; 34 passed |
| Stream helper: failures, interrupted reads, language bytes, ties, empty data, multiword scan | 45 passed |
| Lazy site corpus under a 16-MiB PHP limit | 75,000,000 source bytes; 4,198,400-byte peak |
| HTML length/substrings against the original implementation | 55,990 comparisons passed, including malformed entities and negative offsets |
| Entity-rich HTML stress under a 64-MiB PHP limit | 25,165,814 source bytes; 27,267,072-byte peak |
| Unchanged modern core / alloc / index / service / suggestions / distances | 7,767 / 46 / 96 / 350 / 6,491 / 20,907 passed |
| Modern PHP/JavaScript normalization parity | 16 fixtures and all 7,623 folds passed |
| Modern real browser / default-theme presentation / HTTP+database | 222 / 77 / 33 passed |
| Published backports: exact tree, tested application blobs, own versions | 20 branches verified; unrelated files preserved |
| PHP syntax and changed-line whitespace | Passed |

A separate pre-existing standalone highlighter scroll-margin assertion failed
twice in this local Chromium runtime (`First hit clears the 72px fixed header`).
Its active code and test are unchanged; the complete browser/HTTP suite above
passes. No change to scrolling is included or certified by this memory patch.
Historical PHP/actual future PHP-9 runtimes were not re-certified by this run.

## Actual Policies installation and measurements

The installed old result entry points matched the original main source hashes.
The audit found 234 non-deleted/non-hidden resources and 8,327,944 German indexed
bytes. The old no-result search exhausted a hard 134,217,728-byte memory limit
inside its buffered database query. Restricting suggestion scans alone exposed
a second buffered-result failure; the result projection fixes it. French testing
then exposed the per-character excerpt array; the HTML stream fixes it.

All final cases below ran the actual installed legacy result entry point with
`memory_limit=64M`, against its real database. Every final fatal-error field was
null. Timing is one server sample, not an HTTP latency guarantee.

| Language / input | Hits | Peak MiB | Seconds |
| --- | ---: | ---: | ---: |
| DE: Informationssicherhet | 64 | 8 | 0.549 |
| FR: informatiqe | 104 | 12 | 0.483 |
| IT: sicureza | 70 | 6 | 0.257 |
| RM: segireza | 57 | 4 | 0.211 |
| EN: securty | 61 | 6 | 0.239 |
| DE: Informationssicherheit (exact) | 64 | 8 | 0.144 |
| DE: Informationssicherhet Datenschuz | 42 | 8 | 0.661 |
| DE: und (broad exact result set) | 193 | 18 | 0.194 |

A live browser also verified the corrected German query and its 64 rendered
results. Original files are backed up outside the document root, with source
hash guards and syntax checks before atomic replacements. Installation was
limited to the search correction; no content, account or configuration migration
was performed.

On a separate explicit instruction, only Policies' automatic indexer iframe
trigger was paused through November 2026. It resumes at 2026-12-01 00:00
Europe/Zurich (Unix 1796079600). The htaccess trigger and manual indexing remain
available. This local date condition is not included in the release/backports.
Preserve it if another full deployment is made on Policies before December.

## Installation and rollback

For the main-based release, back up and deploy `index.php`, `alloc.inc.php`,
`u5sys._search.php`, `u5sys._searchsi.php`, `levenshtein.inc.php` and the new
`search-legacy-suggestions.inc.php`. Install the helper before the callers.
No SQL import, index rebuild or config edit is needed for this patch. The full
release also contains the login-host and international-email corrections that
were already present in its requested main base after v12.9.5.

Use the repaired development branch for installations already on that branch's
newer features. Their own version comments are retained. Restore the saved
previous source set to roll back; no database rollback is needed for this
source-only correction. Keep the Policies-only indexer pause separate from the
release rollback.

## Publication checkpoint

All 20 branch-local backports are published and verified against their exact
starting parents. The old-engine application blobs match the tested patch; the
ten modern-engine branches retain every active search file byte-for-byte.
[Machine verification](../tests/search-memory-branch-verification.json).
Main publication uses the authorized pull request and v12.9.5.1 tag;
the release metadata records the final merge SHA. The installed Policies code
was independently hash-verified with all eight final live cases and its
November-only automatic-indexer pause.
