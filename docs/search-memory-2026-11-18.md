# Search memory hotfix — audit/evolution-20261102-evening

18 November 2026. Authorized branch-local backport from `c43d1df1a3869f07d5f8af7abc83d550a28b4283`.

The active modern search is retained byte-for-byte, including phrases, multiword corrections, witness checks, budgets, normalization, excerpts, styles and highlighting. Only the unused historical suggestion include and its streamed helper are repaired, with the helper regression.

Changed paths: `search-legacy-suggestions.inc.php`, `levenshtein.inc.php`, `tests/search-legacy-memory-regression.php`.
The branch's own version comment and cumulative features are preserved.

The helper passes 45 checks with a 75-MB lazy corpus under 16 MiB. The matching unchanged modern engine passed its core/service/suggestions/distance, normalization parity and complete browser/HTTP suite. A separate unchanged standalone scroll-margin assertion failed in this runtime; the shared report records that limit.

No database, index-format or configuration migration. Rollback: restore the source
at the starting commit. The Policies-only indexer pause is not part of this backport.
[Full shared report and practical measurements](https://github.com/u5cms/u5cms/blob/main/docs/search-memory-2026-11-18.md).
