# u5CMS 12.9.5.2: resumable rename backport

Authorised by Stefan Minder on 18 November 2026: release 12.9.5.2 with the
improved Rename and carry the necessary changes through the branch series.

Target: `12.9.5.2-Faster-Rename`.
Starting parent: `fix/search-memory-12.9.5.1` at
`4d6ffd209d13b1068ac5e3532685c32dcefb5d88`.
Rename source inspected: `12.12.1-General-Corrections` at
`493c174ddea42beedd51f1879f42e37ae1b00623`.

Initial remote inventory includes all 22 current branches. Rename is already
present in the numbered branches from 12.12.1 through 12.13.0 and the audit
branches; the earlier cumulative branches retain the legacy implementation.
Main remains the production baseline and is not a deployment target here.
The search work in another workspace is active; its working tree is left alone.
Shared remote heads will be refreshed before every publication.

Implementation checkpoint: the 12.9.5.2 application backport is complete; branch propagation tests have passed; guarded publication and final source verification are in progress. The modern Rename depends on typed-code migration,
row journals and coordinated writers. The backport must preserve the existing
12.9.5 code-object format and templates, all five languages and configured
reference policies. No automatic code-resource migration is authorised by this
release. Runtime PHP 8.4.27 is available; an isolated disposable database and
actual request/retry validation will be prepared before release.

Implemented: isolated generic parsers, byte-preserving streamed row reads,
capacity checks, guarded chunked journals and conflict-checked writes from the
12.12.1 Rename. The existing legacy exporter and `htmltemplate.css` remain in
use. Flat export URLs and relative CSS imports (including reset templates) follow legacy code objects while identifiers and backup paths remain literal. The source Rename dialog waits for complete publication; retries preserve
archive/code HIADRI and configuration after ownership has moved. The retired
history GET endpoint returns 410. Coordinated writers return 409 while pending;
backups can still acquire the recovery lock and include row journals and plans.

Old databases have no history-name index. The first rename attempts to add a
nonunique `(name prefix,lastmut,deleted)` lookup index if none starts with name.
Failure to acquire ALTER privileges leaves the correct, slower guarded path
available; it does not prevent the rename. No code/schema migration entry point
is imported or invoked by the standalone legacy helpers.

Executed locally: PHP 8.4.27 / MariaDB 11.8.6. 887 core/database checks (including
all policy combinations and actual SIGKILL/resume); 114 actual HTTP checks
(Basic/session, CSRF, archive rights before/after ownership moves, renderer,
files, history, forced publication failure, blocked writes and exact retry).
Two benchmark fixtures verify all 20 text columns and every file. Sparse:
5,000 resources/25,000 history rows/955 files, 20.8101s legacy vs 0.3566s complete
new Rename, 58.35x. Dense: 2,000/5,000/955, 7.1521s vs 3.7186s, 1.92x.
Peak PHP allocation 4/6 MiB. The preliminary no-index dense run was slower
(32.04s), establishing the need for the guarded optional history index.
See `rename-backport-12.9.5.2-benchmark.json` for the final indexed measurements.

Chromium was attempted but cannot create its UNIX singleton socket in this
execution environment (EPERM). No new browser execution is claimed; the HTTP
suite uses the actual PHP application, credentials, forms, CSRF and renderer.
JavaScript syntax is checked separately. Recorded earlier modern browser tests
are not represented as new tests of this patch.

Last completed: implementation and complete legacy DB/HTTP/benchmark validation.
The complete 16-branch matrix passes: eleven earlier cumulative branches receive
the ordinary Rename backport; five branches already containing Rename retain
that implementation and gain the missing maintenance-writer guards. The
historical audit snapshots, 12.9.5.1 patch/tag and main are not rewritten.
All branch versions and search application bytes are preserved. The machine
report records exact starting heads and changed-path inventories.
Next: publish with expected-head guards and verify every resulting remote tree.
Installed CMSs remain unchanged.

The completed 12.9.5.1 release documentation and evidence at `e26dfb1` are retained as an additional merge parent. No inherited search application file changes.

Inherited search validation on the patch: 45 streamed-memory checks (75,000,000 bytes under 16 MiB) and 55,990 legacy HTML excerpt comparisons (24 MiB input, 27,267,072-byte peak) pass.
