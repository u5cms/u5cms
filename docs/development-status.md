# u5CMS development checkpoint

## .7 save/Rename correction in progress, 19 November 2026

Authorised: preserve this branch's cumulative work and forward-port .7.
Target: fix/indexer-db-lock-wait-12953. Exact base: 6295b528e5a77fb6b5dad42bce0fbaac947bb8fd.
Candidate: process-owned Rename gate, concurrent ordinary writes, index yielding,
nonblocking recovery plans, real Rename phase/progress and crash recovery tests.
This branch's renderer, version, search format, migration namespace and encoding
remain distinct. Its existing monotonic resource update API is preserved where
present. Tests and expected-head publication are still pending at this checkpoint.
No production deployment or rollout email. Next: actual local HTTP/concurrency/
Rename tests and this branch's comprehensive pipeline before release acceptance.

## Save/index coordination, 19 November 2026

Base 5752177328b570b71761cda1f9e9f9995ff677a8. Target fix/indexer-db-lock-wait-12953.
Bound named/full index work and server-side save invalidation; preserve this
branch's version, renderer, index format and migration/Rename APIs.
Local PHP 8.4.24/MariaDB 11.8.6: 26 real save/index checks and the branch's
existing batch, notice (where present), Rename and ownership checks passed.
Modern-search branches retain all 96 index expectations; only their isolated
fixtures gain the actual new helper dependencies. The new test is registered
in the existing capability manifest/workflow when present. This is targeted
local verification, distinct from the branch's comprehensive Actions pipeline.
No production deployment. Published ref must be checked against the new SHA.


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
