# u5CMS development checkpoint

## 12.9.5.2 Rename (18. November 2026)

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

Der Abschluss erfolgt mit erwarteten Remote-Heads und Vergleich der gespeicherten
Git-Bäume. Keine weitere Implementierung für diesen Auftrag vorgesehen.
