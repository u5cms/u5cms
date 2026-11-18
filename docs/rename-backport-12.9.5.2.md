# u5CMS 12.9.5.2: resumable rename backport

## Release verification

The application in `v12.9.5.2` was validated with PHP 8.4.27, MariaDB 11.8.6
and Chromium Headless Shell. The release candidate passed 887 Rename checks,
114 HTTP checks and 92 browser checks through the real backend. The browser
suite covers both login modes, all five languages, rendered headings and links,
history, file ownership, failed publication and retry. The inherited search
suites passed 45 memory checks and 55,990 excerpt comparisons.

The published application trees match the validated snapshots for all 14
numbered successor branches and both maintained feature/fix branches in
the [branch matrix](rename-backport-12.9.5.2-branches.json). Their branch
versions and search code were checked against their own pre-backport states.

All 16 branches passed the 92 Chromium checks, totalling 1,472 assertions.
On the five 12.11.x branches, 12.12.0-OIDC and the search feature branch,
the browser fixture now takes its template export filename from the test
driver. This lets it exercise a failed publication against the actual HTML
export. Each of these seven one-line changes affects only the browser test.
The complete published trees were compared with the tested working trees.

| Development branch | Verified commit | Browser assertions passed |
| --- | --- | ---: |
| `12.10.0-Multi-Optimizations-by-AI` | [`d88a53d24`](https://github.com/u5cms/u5cms/commit/d88a53d24e1bbe078d808e0bd7b3328f3460359c) | 92 |
| `12.10.1-Support-More-Image-Formats` | [`27d5ab178`](https://github.com/u5cms/u5cms/commit/27d5ab178d36c405ca9f8be5564027eed6bc5e47) | 92 |
| `12.11.0-Code-Resource-Migration` | [`1046798be`](https://github.com/u5cms/u5cms/commit/1046798be96f4ef0146c5f0b56cc5119241c673b) | 92 |
| `12.11.1-Migration-Hardening` | [`a843c6854`](https://github.com/u5cms/u5cms/commit/a843c6854311267a849fec55fe94d22f5d40213b) | 92 |
| `12.11.2-Backup-Restore-Corrections` | [`eb95d01f4`](https://github.com/u5cms/u5cms/commit/eb95d01f4e491120ccbdedbbb6929eff29ae4bf2) | 92 |
| `12.11.3-Private-File-Cache-Corrections` | [`985a898df`](https://github.com/u5cms/u5cms/commit/985a898df58fc1a31766edf4107f2a846403a63d) | 92 |
| `12.11.4-Intranet-Login-Corrections` | [`3184a8266`](https://github.com/u5cms/u5cms/commit/3184a826690344806f09f5654ea9b36f5df45da7) | 92 |
| `12.12.0-OIDC` | [`60f29f518`](https://github.com/u5cms/u5cms/commit/60f29f5184fb71d78ecf39a50ce2ad481ff5980c) | 92 |
| `12.12.1-General-Corrections` | [`fd3bc9b24`](https://github.com/u5cms/u5cms/commit/fd3bc9b240659c6f41304b37fff1402a64ddca2a) | 92 |
| `12.12.2-Textarea-Interactions` | [`d02e46a71`](https://github.com/u5cms/u5cms/commit/d02e46a71a0348d917c31ea75aac9fe6c5da73c2) | 92 |
| `12.12.3-PHP9-Compatibility` | [`a729857fc`](https://github.com/u5cms/u5cms/commit/a729857fc13c94457f2bdd072c63052e17eee10a) | 92 |
| `12.12.4-Default-Design` | [`63728d31d`](https://github.com/u5cms/u5cms/commit/63728d31d7db837fb79d74c7eded87ed6a398a26) | 92 |
| `12.13.0-Responsive-Design` | [`45d8d1aa4`](https://github.com/u5cms/u5cms/commit/45d8d1aa4007d7664d2c9d80dac8792137e846bc) | 92 |
| `12.9.6-Misc-Backend-Optimizations` | [`64b22fe7d`](https://github.com/u5cms/u5cms/commit/64b22fe7d858c800a83a7efd62cbd94c82c869a2) | 92 |
| `feature/search-phrases-quotes-highlighting` | [`a7563fe1d`](https://github.com/u5cms/u5cms/commit/a7563fe1dc0002a05b677c252a698b122edf373b) | 92 |
| `fix/nosniff-script-mime-types` | [`b8d9166c6`](https://github.com/u5cms/u5cms/commit/b8d9166c613f118c4d557f2a59384600cadc4b27) | 92 |

This patch applies to the 12.9.5.x line. Installations already on a newer
development branch can use their corresponding updated branch. For a pending
Rename, complete or recover it on the installed version before an update.
The [operator guide](rename.md#retry-and-recovery) covers retry and rollback.

## Backport history before the release

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

Implementation checkpoint: the 12.9.5.2 application backport is complete; complete: implementation, branch propagation, guarded publication and remote verification have passed. The modern Rename depends on typed-code migration,
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
Published and verified: all seventeen remote heads and complete Git trees match the tested snapshots. [Commit/tree proof](rename-backport-12.9.5.2-publication.json). No further implementation is pending for this scope.
Installed CMSs remain unchanged.

The completed 12.9.5.1 release documentation and evidence at `e26dfb1` are retained as an additional merge parent. No inherited search application file changes.

Inherited search validation on the patch: 45 streamed-memory checks (75,000,000 bytes under 16 MiB) and 55,990 legacy HTML excerpt comparisons (24 MiB input, 27,267,072-byte peak) pass.

The patch application snapshot is `a66b274b59240ab26c04a6e0c00ac9e1c189367b`; subsequent completion/proof changes contain documentation only. Main is still the separate 12.9.5.1 release. No production CMS was changed.
