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

Implementation in progress. The modern Rename depends on typed-code migration,
row journals and coordinated writers. The backport must preserve the existing
12.9.5 code-object format and templates, all five languages and configured
reference policies. No automatic code-resource migration is authorised by this
release. Runtime PHP 8.4.27 is available; an isolated disposable database and
actual request/retry validation will be prepared before release.

Last completed: read remote refs, source Rename, regression fixtures and project
requirements; created an isolated checkout from the search-fix branch.
Next: isolate the generic Rename/journal helpers from typed migration and map
all legacy writer, export, backup and restore entry points.

No new application tests have run at this checkpoint. No production CMS changed.
