# u5CMS development checkpoint

## 12.9.5.14 maintenance on this development line

Starting commit: `5646f65b9fb23e055f650fc6e7521b9169835a2b`. Carry forward the stable index freshness and clock
corrections and per-form Turnstile lifecycle. The branch keeps its own CMS
version and search implementation. Changes cover the worker and launcher,
form widgets, submission handlers and validator, with regression cases in
`tests/indexer_recovery.py` and `tests/turnstile-browser.cjs`.
The stable [release notes](https://github.com/u5cms/u5cms/blob/v12.9.5.14/docs/release-12.9.5.14.md)
describe operation and rollback. Verify acceptance and regression runs on the
published branch commit before deploying. No live installation is part of this
source change.

## Source acceptance for 12.9.5.7, 19 November 2026

Authorised scope: finish .6 on GitHub without rollout or mail; remove the urgent
production save blocker separately; implement and deeply accept .7, carry all
maintained future branches forward, publish .7, and request its rollout ticket
only at the end. Source acceptance does not deploy a production installation.
Starting release: ac6e3bf059a365b2912a70429596dbdb6f08604d (v12.9.5.6).
Cumulative comparison: 9d366253417822c279692767b89dcf1ff0388c5c (v12.9.5).

Accepted application source: 8da363d84140adee59aab294f5f8d5de50101098.
Candidate e069a3bd74dd27ccc00a25fc8f6580312c10f005 retains those exact application
bytes and adds the English branch guide, complete packet regression and browser
setup. Full stable PHP 8.3/8.4, MariaDB, native ZIP and actual Chromium acceptance
passed in Actions 44522772559 and 44522763360.

Ordinary saves use only the live Rename gate; index/backup work and idle recovery
plans do not reserve source writes. Source updates retain permission/conflict
policy, byte digests, atomic monotonic revisions and both submitted histories.
Rename retains its streamed dependency/history/file pass and actual progress.
Large rows use bounded byte guards and connection-local values before one
conditional UPDATE. A killed owner releases its kernel gate; recovery data is
retained and later source conflicts are never silently overwritten.

The [release report](release-12.9.5.7.md) records the actual concurrent save,
index, backup, large-packet, crash/recovery, SQL contention, encoding, ownership,
login, mail, search and browser results. Final isolated Rename measurements:
0.5246 s sparse and 5.2886 s dense, with all text/file conservation.
The [branch receipt](release-12.9.5.7-backports.json) identifies all 21 maintained
heads, exact application/test evidence and unchanged version/render/search blobs.
All required branch workflows passed; lines without workflows have explicit
current-tree local evidence. Future OIDC, responsive and migration APIs remain.

This is an acceptance record. Verify publication using the actual GitHub Main,
tag and Release objects rather than treating this checkpoint as a live process
status. Operational next action: deploy the published complete .7 package under
the separate rollout ticket, preserve configuration/data/local exceptions, and
accept each installation. Keep matching recovery data for interrupted Rename.
No .7 production deployment was performed by this source acceptance.

## 12.9.5.6 save/index acceptance, 19 November 2026

Authorised: finish .6 after published .5, cumulative review from unchanged
v12.9.5, all maintained future-branch corrections, verified Main/tag/release,
then one authorised deployment-ticket email to Markus with Michael in Cc.
No production deployment by this task. Branch: 12.9.5.6-Index-Save-Hardening;
PR 199. Base: 8bcb09937999d4ebb51da293b06a113bda0940d8.

The actual save/closed-confirmation marker failure was reproduced on .5.
The implementation and 26 new HTTP/save/index checks pass. The final application
candidate 88dab36c2ff3c422f4c6421df4652e9656a6622d passed full stable PHP 8.3/8.4
with Chromium in Actions 44483223585. All 21 maintained target heads and source/
test blobs have been read back and match the individually tested preparations;
version/renderer/search/migration sources are preserved. Some comprehensive
branch pipelines exposed isolated fixture dependencies; test-only follow-ups
supply the actual helper without weakening expectations. All twelve local
code-asset suites passed after those additions. All 17 comprehensive workflows declared on the final target heads completed
successfully. Four older maintenance sources have no declared workflow; their
actual targeted local checks are recorded separately in the inventory.

Next action: merge PR 199 with unchanged accepted application/test blobs,
publish .6 and verify tag/Main/archive, then send the authorised deployment
mail and pause the existing task. Never send the mail before verified release.


## Rename lock-wait guard, 19 November 2026

Authorised follow-up: protect Rename from database lock waits, preserve recovery
and existing wait-window behaviour, and review consistency after interruption.
Base main: c7062c680deb29fcac70815eea929ad57ee3a5d2 (v12.9.5.4).
Follow-up patch version: 12.9.5.5. No production deployment in this task.

Implemented: Rename-only session lock-wait limits, preserved stricter limits,
and immediate failure of an optional history-index operation on lock timeout,
deadlock or cancellation. A partial Rename keeps its immutable recovery plan. Blocked writers link to an
authenticated recovery overview, allowing exact retry without the original window.
Local PHP 8.4.24/MariaDB 11.8.6: 83 new real HTTP/SQL/mutex/retry checks,
893 Rename checks, 114 existing HTTP checks and both existing full-index HTTP
and database-lock regressions passed. Stable PHP 8.3/8.4 with Chromium passed in Actions run 44471208093 at
`a2e201ea4798d2708b62b1bbd17cad7d87a2c6b2`. All 18 maintained Rename branches
passed their own existing Rename and HTTP suites plus 83 new SQL/recovery
assertions. Their expected-head publications were read back and verified; the
inventory is in docs/release-12.9.5.5-backports.json. Initial CI exposed cached
fixture configuration; only the disposable HTTP server's opcode-cache policy
was adjusted, without weakening expectations or changing production caching.
The closing commit adds this evidence and inventory without application changes.
Next action: publish the accepted source via PR 198, verify its tag/Main/source
archive, finish observing branch pipelines, and send the BK acceptance addendum.


## 12.9.5.4 acceptance, 18 November 2026

Authorised: finish and publish the patch release, review the full difference
against v12.9.5, preserve cumulative development work, and send the requested
change/evidence/confidence table to the user's BK address.
Base: main cbadbd009d42c129e53111218ba3401a12984a3f.
Candidate branch: 12.9.5.4-Indexer-Final, pull request 197.

Implementation and cumulative acceptance are complete. The application code
passed the stable GitHub acceptance workflow on PHP 8.3 and 8.4, including the
92-check Chromium suite in each profile. The final test/documentation additions
include a real SIGKILL regression and the published backport inventory; they do
not alter the accepted application sources. Exact evidence and scope are in
[the release report](release-12.9.5.4.md).

The five-path backports to 20 development branches were tested. In the seven
modern-search branches, the existing format/pending/running-status contract in
the notice was restored. All 96 original index checks plus 15 notice checks
pass on the corrected heads. The published final heads and their
published heads and unchanged version/rendering sources were independently
verified. Historical audit branches remain comparison references.
The Policies-only November condition is excluded from all release sources.
Publication uses the reviewed candidate through PR 197; a deployment to a
particular production installation requires its own recorded verification.

## Freigabeprüfung 12.9.5.2 vom 18. November 2026

Der Release-Kandidat bestand 887 Rename-, 114 HTTP- und 92 Chromium-Prüfungen.
Die 16 betroffenen Entwicklungsbranches sind im [Freigabebericht](rename-backport-12.9.5.2.md#release-verification) aufgeführt. Er dokumentiert auch die Testanpassung für sieben Branches mit HTML-Export.

## Stand bei Eröffnung von Pull Request 190

Autorisierter Auftrag: 12.9.5.2 mit besserem Rename und konsistente Nachführung
aller betroffenen Entwicklungsbranches. Ziel: `12.9.5.2-Faster-Rename`.
Anwendungsbasis: 12.9.5.1 `4d6ffd209d13b1068ac5e3532685c32dcefb5d88`;
vollständige Release-Nachweise von `e26dfb1` als zusätzlicher Merge-Elterncommit.

Implementiert und geprüft: schnelle Objekt-/Referenz-/Historien-/Dateiumbenennung,
Überschriftenschutz, Konfigurationsvarianten, Wiederaufnahme, Rechtekontrollen,
Sicherung der Wiederaufnahmedaten und koordinierte Wartungsschreibfunktionen.
Legacy-Codeobjekte, Exporte und Suchfunktionen bleiben erhalten.

[Bericht und Messwerte](rename-backport-12.9.5.2.md),
[Matrix aller 16 Entwicklungsbranches](rename-backport-12.9.5.2-branches.json).
887 Rename- und 114 HTTP-Prüfungen auf der Patchlinie; 14 145 Rename- und
1 824 HTTP-Prüfungen auf den 16 Entwicklungsbranches. Zusätzlich geerbte
Suchspeicher-/Textauszugstests. Browsergrenze und genaue Umgebung sind im Bericht
festgehalten. Keine produktive Installation und kein Main-Merge.

Abgeschlossen: alle 17 Remote-Heads und gespeicherten Git-Bäume stimmen mit den
geprüften Ständen überein; [Commit-/Baumnachweis](rename-backport-12.9.5.2-publication.json).
Patch-Anwendungsstand: `a66b274b59240ab26c04a6e0c00ac9e1c189367b`.
Dieser Abschlussnachweis ändert nur Dokumentation. Keine weitere Implementierung
für diesen Auftrag vorgesehen. Main bleibt separat bei 12.9.5.1; die Übernahme
des Patch-Branches wird als Pull Request bereitgestellt.
