# u5CMS development checkpoint

## Save coordination and large Rename rows, 19 November 2026

Authorised: carry the .7 correction into this branch while retaining its version,
rendering, search, configuration and migration APIs. Previous branch head: `4caec99561b307f013f08b0c2f57be2ca61c5d05`.
Ordinary saves share only the live Rename gate; no index/backup wait or idle-plan
blocker is introduced. Large Rename guards use byte digests; new large values are
staged in connection-local binary variables before one conditional row UPDATE.
Both resource and code Rename storage APIs are covered where present.
Local PHP 8.4.24 / MariaDB 11.8.6 passed the actual five-language/current/history
packet regression (44 assertions at 4 MiB), plus this line's applicable source,
concurrency, index, Rename and code-migration suites. Tested packet tree: `c0c0a4d8e61e63c5d20cbd4e471adc438ed21941`.
Next: complete the required full branch CI, then consult the final .7 branch
receipt and release report on main. No production deployment by this work.

## .7 save/Rename correction in progress, 19 November 2026

Authorised: preserve this branch's cumulative work and forward-port .7.
Target: 12.9.5.4-Indexer-Final. Exact base: 3cda3d6d8e538565b1cd38ec080dbd39fe5c212e.
Candidate: process-owned Rename gate, concurrent ordinary writes, index yielding,
nonblocking recovery plans, real Rename phase/progress and crash recovery tests.
This branch's renderer, version, search format, migration namespace and encoding
remain distinct. Its existing monotonic resource update API is preserved where
present. Tests and expected-head publication are still pending at this checkpoint.
No production deployment or rollout email. Next: actual local HTTP/concurrency/
Rename tests and this branch's comprehensive pipeline before release acceptance.

## Save/index coordination, 19 November 2026

Base d0939356f17a0e67fa08a64a2a410d99048b9239. Target 12.9.5.4-Indexer-Final.
Bound named/full index work and server-side save invalidation; preserve this
branch's version, renderer, index format and migration/Rename APIs.
Local PHP 8.4.24/MariaDB 11.8.6: 26 real save/index checks and the branch's
existing batch, notice (where present), Rename and ownership checks passed.
Modern-search branches retain all 96 index expectations; only their isolated
fixtures gain the actual new helper dependencies. The new test is registered
in the existing capability manifest/workflow when present. This is targeted
local verification, distinct from the branch's comprehensive Actions pipeline.
No production deployment. Published ref must be checked against the new SHA.


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
