# Search memory hotfix — fix/nosniff-script-mime-types

18 November 2026. Authorized branch-local backport from `77e4f6e5d916e1c559b34fa52c338fbcd21013f7`.

The legacy engine now streams typo candidates in one pass, selects only displayed result fields, terminates correction retries and streams HTML excerpt offsets. Language/fallback titles, visibility, protected/intranet and administrator SQL policies, byte encodings and existing excerpt semantics are retained. The applied application blobs match the verified main-based patch and Policies installation.

Changed paths: `search-legacy-suggestions.inc.php`, `levenshtein.inc.php`, `tests/search-legacy-memory-regression.php`, `u5sys._search.php`, `u5sys._searchsi.php`, `alloc.inc.php`, `tests/search-legacy-database-regression.php`, `tests/search-legacy-html-regression.php`.
The branch's own version comment and cumulative features are preserved.

Executed validation on the identical application blobs: 34 real legacy SQL/entry-point checks, 45 helper checks, 55,990 original HTML substring comparisons, a 75-MB lazy corpus under 16 MiB and 24-MiB entity-rich slicing under 64 MiB.

No database, index-format or configuration migration. Rollback: restore the source
at the starting commit. The Policies-only indexer pause is not part of this backport.
[Full shared report and practical measurements](https://github.com/u5cms/u5cms/blob/main/docs/search-memory-2026-11-18.md).
