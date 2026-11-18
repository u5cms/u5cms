# Search memory hotfix — 12.10.0-Multi-Optimizations-by-AI

Date: 18 November 2026. Authorized branch-local backport, based exactly on
`2bc182cf8302ba8d8fd3a83cef84ec84dd42360e`.

This branch uses the legacy search. The backport streams typo candidates, selects only displayed result columns, prevents repeated correction recursion and streams HTML excerpt offsets. Its original language, visibility, intranet, SQL restrictions and excerpt output conventions are retained.

Changed paths: `search-legacy-suggestions.inc.php`, `levenshtein.inc.php`, `tests/search-legacy-memory-regression.php`, `u5sys._search.php`, `u5sys._searchsi.php`, `alloc.inc.php`, `tests/search-legacy-database-regression.php`, `tests/search-legacy-html-regression.php`.
The branch's own version in index.php and cumulative development work are preserved.

Validation: 34 real legacy entry-point/SQL checks, 45 streaming-helper checks, 55,990 original HTML substring comparisons, a 75-MB lazy corpus under 16 MiB and 24-MiB entity-rich HTML under 64 MiB. This branch uses the same verified application blobs as the main-based patch and the installed Policies test.
No database/index-format/configuration migration is required. Restore this branch's
previous source commit to roll back.

[Full main-based report, exact runtime, practical measurements and limits](https://github.com/u5cms/u5cms/blob/main/docs/search-memory-2026-11-18.md).
The temporary Policies indexer pause is site-local and is not included here.
