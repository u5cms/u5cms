# u5CMS development checkpoint

## Zweite Evolutionsprüfung: Korrekturkandidat (1. November 2026)

Der Originalvergleich hat zwei weitere Regressionen bestätigt: die verbleibende globale Reservierung von Request-/Cookie-Namen in eigenen Anwendungen und still veränderte ältere CSV-Eingaben. Beide sind auf 12.12.3/12.12.4 korrigiert; der erneute Original-/Zielvergleich samt echten Browserformularen, Eigentumsrechten und zusätzlichen Typ-/CSV-Grenzen besteht mit 142 Assertions ohne Abweichung. [Quellen, Reproduktionen, konkrete Korrekturen und noch offene Abschlussmatrix](12.12.3-reference-evolution-audit.md). Jetzt folgt der Durchgang über alle 16 Branches und die Erweiterungsprofile; der frühere Abschluss unten bleibt historische Evidenz.

## Neue Evolutionsprüfung abgeschlossen (31. Oktober 2026, UTC)

Auftrag: originales main `9d366253417822c279692767b89dcf1ff0388c5c` bis 12.12.3 intensiv auf Funktions- und Konzeptverluste prüfen; **alle betroffenen Branches** korrigieren. [Befunde, Quelleninventur, genaue neue Ausführungen und Grenzen](12.12.3-main-evolution-audit.md). Der separate 12.12.4-Designstand wurde ebenfalls durch die reale HTTP-/Browsermatrix geprüft.

Behoben: globale Blockade eigener Request-Collections, unbeabsichtigte Änderung bestehender IDN-Schlüssel durch Composer-Polyfills, dazu passende unabhängige OIDC-E-Mailprüfung, fehlende Download-Typprüfung am eigentlichen Endpunkt und fehlende Albumauswahl nach dem Count-Rückbau. Diese PHP-spezifischen Korrekturen betreffen 12.12.3/12.12.4. Die gemeinsame Login-Domain- und Include-Korrektur liegt auf allen 16 Branches, main über [PR 187](https://github.com/u5cms/u5cms/pull/187) bei `78cadbf87b7307203b8bd4ff96929b306af7d982`. Alle 13 Branches mit dem Bild-Uploadtest erhalten eine ausdrückliche gemeinsame UTC-Testuhr.

Neue Hauptmatrix auf PHP 8.3.6/MariaDB 10.11.14/Chromium 153.0.8010.0: Migration 1.671, Rename 886, Mobile-Migration 69 und vollständige Such-/OIDC-/Browserläufe. Zuletzt 504 Main-Differenzprüfungen inklusive 24-MiB-Einzelzeile unter 64 MiB PHP; 498 auf der getrennten Designlinie ohne Zusatzstress. Uploads zuletzt je 96, jeweils 33 Bilder geprüft. Auf PHP 8.3 und 8.5: 876 Syntax-/Quellprüfdateien, 135 Kern-, 14 Request-, 40 IDN- und 40 Hostfälle sowie je 52 kalte Dependency-Installationsprüfungen. Mail-Konstruktoren wurden gegen das originale main verglichen; kein Versand. Zwei grosse Rename-Fixtures erhielten alle geprüften Textfelder und Dateien.

Abschluss: Korrekturen, Beschreibungen und dieser Bericht sind über bewachte, branchlokale Commits veröffentlicht; die abschliessende Ref-Kontrolle prüft Namen, Versionskommentare, Beschreibungen und tatsächliche Korrektur-Bytes. Der gemergte temporäre PR-Branch wird bei dieser Kontrolle entfernt. Keine produktive Installation, kein echter externer Provider und keine tatsächliche PHP-9-Laufzeit wurden damit zertifiziert. Die nachfolgenden Abschnitte sind frühere Momentaufnahmen und ersetzen diesen neuen Bericht nicht.

## Branch-Namen und Versionsabstufung (31. Oktober 2026)

Aktuelle Branch-Bezeichnung: `12.12.4-Default-Design`, Versionskommentar 12.12.4. Die vollständige aktuelle Zuordnung steht in [BRANCHES.md](../BRANCHES.md). Die fünf rein numerischen 12.11-Branches haben beschreibende Namenszusätze erhalten; Textarea, PHP-Kompatibilität und Standarddesign werden als 12.12.2, 12.12.3 und 12.12.4 fortgeführt. Prüfberichte behalten ihre historischen Dateinamen und tatsächlichen Validierungsstände. Die nachfolgende frühere Momentaufnahme ist unter Berücksichtigung dieser Umbenennung zu lesen; ihre SHA-Angaben bleiben historische Anwendungsstände.

Autorisierter Umfang dieses Schritts: Branch-Namen, README-Beschreibungen und Übersicht, Versionskommentare in index.php sowie Entfernung des historischen php9_compat-Branches. Ausgangscommit dieses Branches: `ec1524fc6999387e2037b253a7becac46f8a3367`. Fachliche Änderungen und frühere Prüfergebnisse werden beibehalten. Offene Veröffentlichungsschritte und ihr Abschluss werden anhand der Remote-Refs überprüft.

Anschliessend ausdrücklich autorisiert: intensive Prüfung der gesamten Evolution von main (Anwendungsbasis 9d366253417822c279692767b89dcf1ff0388c5c) bis 12.12.3-PHP9-Compatibility auf Kollateralschäden und Erhalt der ursprünglichen Funktionskonzepte, Konfigurationsvarianten und Sonderfälle. Erkannte Regressionen korrigieren, real prüfen und notwendige Korrekturen im darauf aufbauenden 12.12.4-Default-Design nachführen. Diese Prüfung ist ein neuer Auftrag; frühere Testberichte sind keine Erledigung dieses Auftrags.


Snapshot: 31 October 2026, Europe/Zurich.
Repository: https://github.com/u5cms/u5cms

This is the durable handoff for work spread across chats. GitHub refs and
committed evidence establish saved work; a chat promise or a quiet period does
not establish completion, failure, or whether another chat is running.
Refresh the snapshot against remote refs before resuming work.

## Mobile controls and default design (31 October 2026)

The mobile controls backport is complete, validated individually on all nine
12.11.0-12.14.0 branches and verified on the remote refs. The corrected cumulative
12.14.0 head is `cb876c924af9193a243e01edaacdfc773a4637c7`.
See [the mobile report](mobile-controls.md).

The subsequent authorized default-site design refinement is implemented and
locally validated on
`12.15.0-Default-Design`, starting exactly at that corrected head. Its four
representations are both SQL seeds, CSS exports and reset templates, synchronized
in the final source revision. See
[the design checkpoint](12.15.0-default-design.md). Both newly authorized tasks are complete in their respective source branches.
There is no automatic implementation task pending; merging/deployment remain separate.

## Completed authorized sequence (31 October 2026)

1. Implement and validate textarea source actions on `12.13.0-Textarea-Interactions`, based on `f097070d54ac19003bb7b145ee91f6652a29e534`.
2. Immediately afterwards, create `12.14.0-PHP9-Compatibility` from the completed textarea branch. Study `php9_compat` for intent, but correct types and encoding at their real boundaries, remove `tnuoc` call-site workarounds, and do not adopt the historical blanket-wrapper approach.

Both requested implementation branches are complete and saved remotely. The textarea result is published at `c0748dbedb6200c007438555ca76999aa0d97cea` on `12.13.0-Textarea-Interactions`. The cumulative `12.14.0-PHP9-Compatibility` starts exactly there; its first recovery checkpoint is `92259f6136382725331917d004e1f9c60193a593`, followed by the final validation/fix commit containing this report. Fetch the branch to obtain its current final SHA.

The second branch removes `tnuoc`, establishes actual input/encoding contracts, updates dependencies and declares native interface return types without adopting historical blanket wrappers. It also fixes missing download time components and initializes the configured SAML HTTPS decision before issuing cookies. PHP 8.2 is now the dependency platform minimum. Executed checks use PHP 8.3.6 and 8.5.12; no PHP 9 runtime certification is claimed. See [the textarea report](12.13.0-textarea.md) and [the PHP compatibility report](12.14.0-php9.md).

Final evidence includes 131 focused contracts and 870 syntax/source-audit files on both runtimes; 52 clean upstream patch-installation checks on each; 159 textarea, 51 frontend, 98 backend and 475 main-diff checks on each; 1,671 migration/database, 886 rename and the complete inherited search/OIDC matrix on PHP 8.5. Final search and OIDC HTTPS/browser paths also pass on PHP 8.3, including 248 HTTPS checks and Secure/HttpOnly SAML cookies. All tests used disposable local fixtures.

Changed paths are recoverable with `git diff --name-status --no-renames c0748dbedb6200c007438555ca76999aa0d97cea origin/12.14.0-PHP9-Compatibility`. They include request/bootstrap and text-consumer PHP, rendering/IDN, CSV/upload/image/download/federation paths, the Composer lock/vendor tree, pinned return-type patches and their installer, regression runners and reports. The 746 token-migrated text boundaries have a committed per-call-site inventory. Existing application source bytes and database NULL distinctions are preserved.

Last completed step: final strict HTTP/provider/browser, database, source and dependency checks; publish the tested corrections and reports with an expected-head guard. No implementation test is left running as part of this checkpoint. Next action: review the two completed branches. Merging or deployment requires a separate user instruction; no further automatic implementation task is pending.

## Saved implementation

| Item | Evidence | State |
| --- | --- | --- |
| Production source branch | `main` at `9d366253417822c279692767b89dcf1ff0388c5c`, last changed 9 October 2026 | Version 12.9.5; the later work below has not been merged into this branch. Installed production sites were not inspected in this checkpoint review. |
| Current cumulative development branch | `12.15.0-Default-Design` | Contains the completed mobile correction and restrained default design; fetch and inspect its current remote head. |
| Textarea interactions | `12.13.0-Textarea-Interactions` at `c0748dbedb6200c007438555ca76999aa0d97cea` | Implemented, validated and published. |
| PHP compatibility | `12.14.0-PHP9-Compatibility`, based on the completed textarea SHA; [scope and executed validation](12.14.0-php9.md) | Implemented, validated on the documented runtime matrix and published. Actual PHP 9 runtime and deployment-specific acceptance remain separate. |
| Previous application baseline | `f842f5d1fca8d6856e67640e645519e09dfd05cf`, committed 30 October 2026 | 18 commits ahead of the main SHA above, zero behind; 296 changed paths. Later checkpoint-only commits do not imply a new application test run. |
| Search | `2f5ce37c18b017375dfc31dab6086f325bd836a2`; [search guide](search.md) | Included in the current cumulative branch. Multiword and quoted suggestions, partial results, excerpts, normalization, highlighting, container opening and scrolling are implemented. |
| Optional OIDC | `acdd8d6cf362c93803e94897336d347e26d1dba6`; [OIDC validation](12.12.0-oidc-validation.md) | Included; committed implementation and recorded local protocol/database/browser validation. Real provider acceptance remains separate. |
| Rename and complete main comparison | `f842f5d1fca8d6856e67640e645519e09dfd05cf`; [cumulative audit](12.12.1-general-corrections.md) | Included; committed implementation and recorded validation, including interruption/retry and inherited application suites. |
| Pull requests | GitHub open-pull-request collection checked on the snapshot date | No open pull requests observed. |
| Automated GitHub checks | Actions runs and commit statuses checked on the snapshot date | No workflow runs or commit status checks observed. The committed test reports must not be described as passing GitHub CI. |

The current cumulative branch retains the 12.9.6 backend, 12.10 optimizations,
12.10.1 image formats, MIME correction, 12.11.0-12.11.4 code-resource migration
and recovery, search and OIDC development. It is a continuation of that history,
not a fresh copy of main.

There are two separate historical heads:

* `12.9.6-Misc-Backend-Optimizations` has one commit not in the current
  cumulative branch: `c035f25331d9690c5ce890d540c307dc3bb62e1d`.
  Inspection shows that it only changes the version comment in `index.php`
  from 12.9.5 to 12.9.6. Its earlier functional commits are ancestors of the
  cumulative branch; do not merge the old version marker over a newer release.
* `php9_compat` is a separate old branch, observed one commit ahead and
  143 behind main. It has not been accepted into the cumulative release.

## Completion and remaining boundaries

The OIDC and General Corrections implementation is saved remotely and has
committed validation reports. The most recent application audit records, among
other suites, 886 rename checks, 1,671 migration/database checks, 476 extended
main-diff checks, 162 OIDC core checks, 247 HTTPS endpoint checks, 73 OIDC browser
checks and the search and image-upload suites. Read the reports for the exact
scope, environments and limitations; counts are not a universal correctness
guarantee.

The remote branch heads and cumulative ancestry were verified when publishing these results. The reports record actual local execution; they are not GitHub CI status. This task does not inspect the process state or private workspaces of other chats. The saved checkpoints provide a concrete recovery path after an interruption.

| Remaining item | Treatment |
| --- | --- |
| Live eIAM/AGOV registration, exact provider policy and logout acceptance | Deployment-specific acceptance remains open; local TLS/IdP fixtures are not live-provider certification. |
| Real external SAML interoperability | Existing mock-path compatibility is recorded; live federation was not newly certified. |
| Actual PHP 9 runtime and future upstream package constraints | Not executed or certified by this branch; review the published PHP changes and upstream platform support against that runtime. |
| Additional browser/server/site-specific acceptance | Follow the limits in the cumulative report, including the current branch's Firefox/WebKit and server-specific coverage limits. |
| Merge/release/deployment | Not performed by the completed implementation tasks or this checkpoint review. Await the user's explicit instruction for these actions. |
| Textarea interactions and P-object Edit actions | Implemented and validated on the first branch above; see its report. |
| Further search relevance, passage links and match navigation | Deferred discussion; the user said this might be done later. No such remote branch was present in the branch inventory. An earlier proposed branch name is not evidence of delivered work. |
| Site-specific long-page pauses around 21-22 entries | Separate CMS-object work. These core branch commits are not evidence that the site-specific issue is resolved; do not close that assignment through this checkpoint. |

The checkpoint safeguards and both subsequently authorized application branches are complete. Merge and deployment remain separate actions.

## How to resume after an interrupted chat

1. Read this file and the root `AGENTS.md`. Fetch all remote branches and inspect
   their current heads, ancestry, and the local working tree before selecting a
   base. Do not discard another workspace's uncommitted work.
2. Compare the actual user instructions with the saved implementation and
   reports. Keep requests on hold separate from unfinished authorized work.
   If a needed earlier workspace cannot be accessed, record that limit rather
   than asserting that its changes were recovered.
3. Record the task, target branch, exact starting SHA, changed paths, last
   completed step, last test result, unresolved issue and one concrete next
   action. If testing is in progress, record the actual run/process handle when
   available; a proposed command is not a running test.
4. Preserve a useful checkpoint before a long verification phase and after each
   meaningful completed phase. Commit and push safe, reviewable work or an
   explicit incomplete-work checkpoint within the authorized scope. Do not rely
   on chat history or uncommitted scratch files as the only recovery copy.
5. Before publishing, reread the remote head. Use a normal fast-forward push or
   an expected-head comparison. If the branch moved, inspect and reconcile the
   other work; do not overwrite it. One chat must not claim ownership of work in
   another chat without verifying its status.
6. Run the checks required by the actual changes and project rules. Once the
   authorized scope and required checks pass, finish. Repeating or broadening
   tests requires a new change, a failure or a specific unresolved concern.
   Record additional ideas as deferred work instead of extending the task
   indefinitely.
7. Verify the published remote commit. Finish with the branch and commit link,
   actual verification scope, remaining acceptance boundaries and next action.
   Distinguish implemented, validated, pushed, merged and deployed. A missing
   final chat message can then be recovered from these records.

This document is a recovery and completion protocol, not a background monitor.
No cross-chat process inspection, automatic restart, recurring watcher or
scheduled task is established by this documentation.
