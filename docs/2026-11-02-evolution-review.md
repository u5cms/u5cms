<a id="erneute-evolutionsprüfung-vom-2-november-2026"></a>
# Repeated evolution review of 2 November 2026

The main review and three additionally requested investigations are complete. Three failure groups were reproduced, corrected on every affected branch, published and checked after the last application correction.

| Confirmed failure | Corrected branches |
| --- | --- |
| Form-data views abort without a search filter | 12.12.3 and 12.12.4 |
| Temporarily blocked changes return unhandled server errors | Ten branches from 12.11.1, including the search working branch |
| An error message prevents the next Save click | All 14 branches from 12.10.0, including the nosniff and search working branches |

Main and production installations were not changed. No confirmed failure remains within the checked scope.

<a id="gegenstand-und-referenzen"></a>
## Subject and references

The complete series at the time was checked: main and 15 development/working branches, through `12.12.4-Default-Design`. Original reference: `9d366253417822c279692767b89dcf1ff0388c5c`; current main at the time: `78cadbf87b7307203b8bd4ff96929b306af7d982`. Individual starting and checked application sources are in the [machine-readable evidence](../tests/evolution-review-2026-11-02-evidence.json).

The [source inventory](2026-11-02-evolution-paths.tsv) covers 909 distinct changed paths across the branch series against main, without collapsing renames into a single path. It assigns changes to verification routes. An inventory does not establish that every conceivable custom use was executed. Application snapshots of the actually tested branches were also compared with their published Git blobs.

<a id="bestätigter-und-behobener-fehler"></a>
## Confirmed and corrected failure

Ordinary backend data views `formdata2.php` and `formdatamail.php` aborted without a non-empty search filter. `formdata.query.php` defined `$keywords` only when a filter was supplied. After removing `tnuoc`, the subsequent `count($keywords)` caused a TypeError. `12.12.3-PHP9-Compatibility` and `12.12.4-Default-Design` were affected.

The response could already contain HTTP 200 and part of the interface, while the actual table, subsequent custom extensions and page completion were absent. An existing OIDC test checked only status and the absence of a login error and therefore missed the abort.

The shared query source now always defines an empty keyword list before processing an optional filter. The correction changes two ASCII lines; all existing non-ASCII bytes are retained. No database schemas or stored form submissions changed.

The [new comparison](../tests/formdata-view-reference.py) uses real HTTP entry points and fresh databases from the original SQL seed. It checks complete output, actual selected records, the custom `modify!formdata2!php` extension, status, sorting, filtering and denied access. The mailing view is displayed only; no mail is sent.

Before correction, each affected branch shows 28 differences in 42 cases; current main matches the original. [Saved reproduction](../tests/formdata-view-before-fix.json). After correction, the original and all 16 branches agree across 714 complete-view cases. The OIDC test now also requires actual completion of the data view.

Correction commits:

- [12.12.3: 8931d31](https://github.com/u5cms/u5cms/commit/8931d314c2a5db4ae43ada86721a07afe899bba3)
- [12.12.4: 0b1ab1c](https://github.com/u5cms/u5cms/commit/0b1ab1c19dea9e7eec533d488a7ddd9f4e3e49be)

The other 14 branches do not contain the causing count conversion. No application correction is needed there for this failure. Main and production installations were not changed by this task.

<a id="neue-tatsächliche-prüfausführungen"></a>
## Newly executed checks

PHP 8.3.6, MariaDB 10.11.14 and Chrome Headless Shell 156.0.8078.80; exclusively disposable installations and test data. Earlier reports are not counted as new runs. The complete integration matrix ran on the latest design line; original comparison and new data-view cases ran across every branch.

| Area | New result |
| --- | --- |
| Original against all 16 branches, with real browser form submissions | 612 checks, no comparison differences |
| New complete data views, original plus every branch | 714 cases, no comparison differences |
| Additional contextual request/CSV contracts | 142 checks, no comparison differences |
| Code-object migration and database | 1,671 passed |
| Rename, including resumption | 886 passed |
| Mobile migration | 69 passed |
| Textarea transformations | 748 passed |
| Textarea/frontend/backend/Rename in a real browser | 159 / 51 / 98 / 92 passed |
| Main differences, SQL/ZIP restore and editor | 504 passed, including a large single row under 64 MiB PHP |
| Search core/memory/service/suggestions/distance/index | 7,767 / 46 / 350 / 6,491 / 20,907 / 96 passed |
| PHP/JavaScript normalisation | 16 fixtures and all 7,623 folds agree |
| Search HTTP/browser/standard design | 33 / 222 / 77 passed |
| OIDC core/concurrent bindings | 162 / 15 passed |
| OIDC with a local TLS provider, HTTP/browser | 249 / 73 passed |
| Image upload, archiving and failures | 96 passed; 33 published images inspected |
| Design, both SQL seeds and their exports/reset sources | 16 source checks each; 36 / 30 browser checks |
| PHP core/request contracts/IDN/login hosts | 135 / 22 / 40 / 40 passed |
| PHP syntax and source inspection | 878 files each without errors within the respective scope |
| Mail construction | 20 semantic checks from 11 actual constructor cases; no transport invocation |

Package trees, locks and declared patches for all 13 locked dependencies are unchanged from application source `4159972464e798eb3371cc2d15625fcaa1e9f35d`, previously checked against upstream. Installed return-type patch hashes were checked again. This does not claim a new clean installation from freshly downloaded upstream archives.

Initially, older-branch test setup, child-process PHP configuration and the local browser path needed correction. These failed setup attempts are not successful application tests. The table counts only the subsequent genuinely passing executions.

<a id="bewahrte-konzepte-und-grenzen"></a>
## Preserved concepts and limits

Source and execution review covers custom P/PHP applications and request collections, five languages/fallbacks, configurable PHP execution, public/protected/archived content, local login/optional federation, HIADRI, owner rights, form values and CSV contracts, page/code/metadata handling, conflict detection, histories, resource references, migration, Rename, publication, file protection and backups. All four website representations remain considered together: both SQL seeds, CSS exports and TPL reset templates. The separate mobile view and existing encoding boundary are retained.

After all four investigations and subsequent checks, no confirmed failure remains within the executed scope. This does not certify arbitrary custom extensions, external mail delivery, production data migration, a real external identity provider or an actual PHP 9 runtime. Results are from local runs, rather than a GitHub CI status. Nothing was merged or installed.

<a id="drei-zusätzlich-beauftragte-untersuchungsläufe"></a>
## Three additionally requested investigations

1. Unusual configuration and custom extensions: complete; 2,210 comparison cases without differences.
2. Interruptions, failure sequences and conflicting changes: complete; an additional locking error corrected on all ten affected branches.
3. Complete browser editorial workflows: complete; an overlapping error notice corrected on all 14 affected branches.

Each additional investigation has its own questions, actual executions, findings and completion. Newly confirmed failures are corrected on every affected branch and checked after the final correction.

<a id="zusatzlauf-1-konfiguration-und-eigene-erweiterungen"></a>
### Additional investigation 1: configuration and custom extensions

Complete. The [independent configuration comparison](../tests/configuration-reference.py) executes the original first, then all 16 branches. Ten profiles combine Basic/cookie login, FastCGI, ordinary/higher editorial permissions, explicitly permitted backend access, form checking, quote handling and PHP execution everywhere/archive only/nowhere. Each profile checks five languages, custom request/cookie collections, both complete data views, CSV and existing editing entry points.

Result: 130 functional observations per installation, 2,210 in total, without comparison differences. The existing refinement of denied access from HTTP 200 to HTTP 403 is explicitly checked as continued rejection. Data uses identical fixed times; differences in fixture creation times are not treated as application failures. No further collateral damage was confirmed and no application correction was needed from this investigation. [Saved results](../tests/evolution-configuration-2026-11-02.json).

<a id="zusatzlauf-2-echte-sperren-und-unterbrochene-umbenennung"></a>
### Additional investigation 2: real locks and interrupted Rename

Complete. A further failure was confirmed: active code updates and interrupted renames blocked conflicting writes, but several write actions ended with an unhandled HTTP 500. Code save, New, Copy, Archive, Delete and Rename were affected. The two already controlled page/metadata paths demonstrated the required interface conflict response. The first four cumulative sources produced 48 such server errors with unchanged data.

The ten affected branches from `12.11.1-Migration-Hardening`, including the search working branch, receive the shared conflict response. A dedicated busy exception separates expected contention from other operational failures. Affected entry points return HTTP 409, `Retry-After: 2` and an explanation. Normal revision, permission and CSRF checks remain in the write workflow.

[HTTP/resumption check](../tests/mutation-recovery-http.py): a separate process holds the actual file lock. Another run interrupts a real Rename after moving the directory and before database changes. All affected write attempts use the real HTTP entry points. Native SQL queries and file hashes compare resources, histories and bytes before and after every rejected attempt.

Result: 100 blocked writes return the controlled conflict response and change none of the checked state. Four genuinely interrupted renames complete through the original retry target; subsequent repetition and stale editors do not change unrelated data. Normal writing succeeds again on all ten branches. Earlier sources are checked only against their existing lock consumers; comprehensive ordinary resource coordination arrived with 12.12.1.

Additionally, **every** corrected branch again passes 51 frontend and 98 backend browser checks, including Basic/cookie login, Unicode, revisions, CSRF, New/Copy/Rename/Archive/Delete/Reactivate. [Before-correction reproduction](../tests/evolution-recovery-before-fix.json) and [new results](../tests/evolution-recovery-2026-11-02.json).

<a id="zusatzlauf-3-tatsächliche-redaktionsabläufe"></a>
### Additional investigation 3: actual editorial workflows

Complete. After a failed resource save, its error message covered the Save button and intercepted the next click. The third investigation reproduced this in a real browser at all three examined development stages. Input remained, but the normal retry failed because of the overlaid notice. The shared file is byte-identical on all 14 affected branches; the correction is applied identically throughout.

The notice now sits at the lower right and intercepts no pointer events. Its width stays within the window and it is announced as an alert. The new check uses ordinary clicks and would detect a still-obscured button.

[Browser workflow](../tests/editorial-journey-browser.cjs), [disposable web runner](../tests/editorial-journey-web.py) and [saved results](../tests/evolution-editorial-2026-11-02.json): Basic and cookie login on 12.11.1, 12.12.3 and 12.12.4; real lock and rejected request, retained input and dirty marker, successful retry, a competing change in a separate source window, unchanged newer publication, both histories, exact Unicode/entity values and UTF-8 file bytes, two different pages in backend windows and reopening. Existing synchronisation between two views of the same page remains.

Result: 63 / 73 / 73 checks, 209 total, passed. On reopening, the test waits for the actual object editor after its cookie redirect. Earlier sources are checked against their existing lock consumers, rather than coordination added later. Shared final acceptance after the last application correction is complete too; it is not a fourth additional investigation.

<a id="verifizierter-abschluss-nach-der-letzten-korrektur"></a>
## Verified completion after the last correction

The [final machine-readable evidence](../tests/evolution-final-2026-11-02-evidence.json) contains the actually published application commits for all 16 branches, source-file equality in checked installations, final group runs and their output. In total, 18,237 application files in checked snapshots match their published Git blobs. Branch names and version comments are checked. Main and 12.9.6 are unchanged from task start; no extra development branch was introduced.

After the last application correction, the original comparison again passes 612 checks, complete data views 714 cases, contextual checks 142, migration 1,671, Rename 886 and mobile migration 69. The entire HTTP/browser group passes with 159 textarea, 51 frontend, 98 backend, 92 Rename and 504 main-difference/restore checks, as do search, OIDC, uploads and both design seeds. PHP core/request/IDN/host suites pass again with 135 / 22 / 40 / 40 cases; syntax and source inspection each cover 878 PHP files.

The first closing run failed exclusively during subsequent cleanup of a disposable database because an InnoDB fixture file remained. All 904 application checks in that HTTP/browser group had already passed. The complete group was rerun and passed including cleanup. The evidence retains the first failed process status and successful repetition separately. No application test was weakened.

All three additional investigations are complete. No running test or automatic follow-up remains from this task. Merging, release replacement and production installation are outside the executed steps.

<a id="veröffentlichte-anwendungsstände"></a>
### Published application sources

Later closing commits may update evidence and documentation only. They do not change the application bytes checked here.

| Branch | Checked published application commit |
| --- | --- |
| `12.10.0-Multi-Optimizations-by-AI` | [5072cb3](https://github.com/u5cms/u5cms/commit/5072cb3cb2308c5d96dc3d10a215b4c946f84721) |
| `12.10.1-Support-More-Image-Formats` | [3c25b44](https://github.com/u5cms/u5cms/commit/3c25b440bc7186c229d16f7f742915539c742823) |
| `12.11.0-Code-Resource-Migration` | [80b5f44](https://github.com/u5cms/u5cms/commit/80b5f4426071b9c0ef5c362541021d51c5439236) |
| `12.11.1-Migration-Hardening` | [40fdefc](https://github.com/u5cms/u5cms/commit/40fdefcc5349dff0884294fc2cea291f535a4fa1) |
| `12.11.2-Backup-Restore-Corrections` | [15f54c6](https://github.com/u5cms/u5cms/commit/15f54c6ac6306ff2c8997e259fec7f2aaea3d360) |
| `12.11.3-Private-File-Cache-Corrections` | [f078ebc](https://github.com/u5cms/u5cms/commit/f078ebc44942518c555209067103c34a70b20724) |
| `12.11.4-Intranet-Login-Corrections` | [a73acde](https://github.com/u5cms/u5cms/commit/a73acde7a15f29a27fb521a0006f06185b2ab51c) |
| `12.12.0-OIDC` | [dfab7b8](https://github.com/u5cms/u5cms/commit/dfab7b849945b6ebd286365f5dc3a71074b8e6f0) |
| `12.12.1-General-Corrections` | [ab47e37](https://github.com/u5cms/u5cms/commit/ab47e37820fce7f428b63683e8eb2bec861b9140) |
| `12.12.2-Textarea-Interactions` | [24b9863](https://github.com/u5cms/u5cms/commit/24b9863ff23c037433426a5eac0da99be7376aae) |
| `12.12.3-PHP9-Compatibility` | [83dc1db](https://github.com/u5cms/u5cms/commit/83dc1dbfd160e22de7ce70ac1f854ca59c9810eb) |
| `12.12.4-Default-Design` | [3cac2f2](https://github.com/u5cms/u5cms/commit/3cac2f23eed4fb2ff282424ce0f90c445c80ece0) |
| `12.9.6-Misc-Backend-Optimizations` | [032970e](https://github.com/u5cms/u5cms/commit/032970e4a2f00595f78c371f0a97e146a118f834) |
| `feature/search-phrases-quotes-highlighting` | [6301cb4](https://github.com/u5cms/u5cms/commit/6301cb4452f5b15b0477d602c6b74c78bcd14b20) |
| `fix/nosniff-script-mime-types` | [a2c4db1](https://github.com/u5cms/u5cms/commit/a2c4db1f2c0ed5afef04322ce9d1fe3014191e45) |
| `main` | [78cadbf](https://github.com/u5cms/u5cms/commit/78cadbf87b7307203b8bd4ff96929b306af7d982) |
