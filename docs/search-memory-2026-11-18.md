# Search memory correction — 18 November 2026

Authorized scope: patch release **12.9.5.1**, based exactly on main
`bf6863337cd6dd64284cf429f65da986d821d741`, and branch-local backports to every
existing branch after inspecting both search generations. The user additionally
authorized a backed-up practical test in policies.eiam.admin.ch through Plesk.

Implementation checkpoint: correction and local regressions complete; practical deployment and release publication pending.

Findings: main and the numbered 12.9.6–12.11.4 branches use the old recursive,
buffered full-site vocabulary scan. The feature search branch and 12.12.0 onward
use the newer streamed search with bounded suggestion budgets, per-resource
witnesses, visibility checks, and phrase/multiword support. Those contracts must
be preserved; the newer engine must not be replaced with the old one. The unused
legacy include still contains the old unbounded scan on every branch.

Planned paths: legacy suggestion helper and include; both legacy result entry
points where present; focused regression tests; documentation. Only main changes
its public version marker. No database schema or content changes are required.

Completed: all remote heads inventoried; both engines and configuration,
authentication, indexing, rendering, history and backup boundaries examined.
Next: run memory/failure/real-entry-point regressions, inspect branch-specific
differences, publish the verified correction with expected-head guards, and
perform the authorized Plesk practical test. No test or deployment claimed yet.

## Correction and first validation

The legacy result entry points now select only the active language search field
and scan it through `mysql_unbuffered_query()`. Tokenization retains only the
current token and the best candidate per term; no full-site string, word array
or unbounded deduplication cache is constructed. All query terms share one scan.
The original AND lookup and corrected-query OR fallback are retained, with one
explicit correction attempt rather than mutual recursion. Empty vocabularies,
failed scans and unchanged candidates terminate. A missing no-result DOM node
no longer causes the existing highlighter callback to throw.

Legacy matching retains byte-based Windows-1252 behavior, selected-language
fields, resource/intranet/protection/type/administrator SQL restrictions, the
last equal-distance candidate rule and original indexed spelling. Correction
inputs are limited to 16 terms of at most 255 bytes, and oversized tokens are
skipped without allocating them. Ordinary exact search is not subject to those
correction-only bounds. Adjacent resource texts are separated instead of
producing fictitious words at page boundaries.

The standalone historical `levenshtein.inc.php` also uses the streamed helper,
keeps its existing query source, and encodes both the link and visible label.
Its old full-site algorithm remains unused by the modern engine and is removed
so custom callers cannot accidentally reintroduce the memory failure.

Executed with PHP 8.4.27 / MariaDB 11.8.6 in disposable fixtures: 17 real legacy
function/SQL checks per entry point (34 total); 45 helper checks including a
75,000,000-byte lazy corpus under a 16-MiB PHP memory limit (4,198,400-byte peak).
Modern unchanged engine: core 7,767; alloc 46; index 96; service 350; suggestions
6,491; edit distance 20,907. Browser/HTTP verification remains in progress.
These are executed local checks, not GitHub CI.

Policies audit: 234 non-deleted/non-hidden resources, 8,327,944 German indexed
bytes; the installed legacy files match the original main bytes. The old
no-result search reproduces a hard 134,217,728-byte failure inside the buffered
database wrapper. On explicit instruction, only the automatic indexer iframe
trigger was paused until 2026-12-01 00:00 Europe/Zurich (Unix 1796079600). The
htaccess trigger and manual indexer remain available. This site-specific pause
is not part of the release or branch backports.
