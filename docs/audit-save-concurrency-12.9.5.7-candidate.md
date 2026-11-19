# Unabhängige Gegenprüfung des .7-Kandidaten am 19. November 2026

Die zwei HTTP-Server und die Datenbank verwendeten eine isolierte Installation. Auf dem stabilen Kandidaten e7aa452 erfolgte ein berechtigter Editor-POST zuerst während eines benannten Indexlaufs nach Sperrerwerb und dann in einer zweiten Ausführung nach Lesen des zu indexierenden Datensatzes. In beiden Fällen antwortete der Seitenspeicher mit HTTP 200 (je 0,013 s in dieser Installation). Alle fünf Quelltexte und die nachfolgende Einzelindexierung wurden aus der Datenbank zurückgelesen. Ein weiterer Test belegte, dass zwei getrennte Bearbeitungen in derselben Sekunde verschiedene Revisionen erhalten. Beim veralteten Editorstand war die Konfliktmeldung sichtbar, die erste Änderung blieb aktiv. Die .6-Vorherbefunde sind in [diesem Bericht](audit-save-concurrency-12.9.5.6.md) dokumentiert.

## Vor Veröffentlichung zusätzlich entdeckter Fehler

Mit einem Datenbank-Paketlimit von 4 MiB wurde eine synthetische Seite mit je 320 000 Zeichen in fünf Sprachfassungen zweimal über den normalen Editor gespeichert. Auf .6 waren beide Saves erfolgreich. Auf dem .7-Kandidaten e7aa452 blieb beim zweiten Save der erste Inhalt in der Datenbank. Selbst der fehlgeschlagene Request hatte HTTP-Status 200. Datenbankrücklesung und sichtbare Speicherbestätigung waren deshalb entscheidend.

Der SQL-Vergleich sandte die bisherigen fünf Inhalte nochmals hexkodiert an die Datenbank. Die Korrektur berechnet den Vergleichswert aus den ursprünglichen Spaltenbytes auf dem Datenbankserver und grenzt das Update anhand des Ressourcenschlüssels ein. NULL und Leerstring bleiben unterscheidbar. Mit demselben Datensatz konnten danach beide Bearbeitungen gespeichert werden. Die sechs vorbereiteten Wartungs-/Entwicklungsstände mit diesem Schreibpfad erhielten dieselbe Änderung. Die anderen 15 besitzen eine eigene Update-Implementierung. Die Datenbankprüfung mit beiden Speicherungen war auf allen 21 Kandidaten erfolgreich. Die Berechtigungsprüfung und das bedingte Update der drei älteren Editoreinstiege verwenden im ergänzten Code denselben Zustandsstand für Typ und Archivstatus.

## Kandidatenprüfung je Branch

Stand der isolierten Kopien während der Vorbereitung. Die Tabelle belegt diese drei Prüffälle, kein bereits veröffentlichtes Release. Das Paketlimit bezieht sich auf den Datenbankserver. Die beiden 200-Spalten bezeichnen die zwei Zeitpunkte desselben Konkurrenzfalls.

| Branch | Editor-HTTP während Einzelindex | Veraltete Sekundenrevision | Zwei Saves bei 4 MiB |
|---|---|---|---|
| 12.10.0-Multi-Optimizations-by-AI | 200 / 200 | erkannt | beide gespeichert |
| 12.10.1-Support-More-Image-Formats | 200 / 200 | erkannt | beide gespeichert |
| 12.11.0-Code-Resource-Migration | 200 / 200 | erkannt | beide gespeichert |
| 12.11.1-Migration-Hardening | 200 / 200 | erkannt | beide gespeichert |
| 12.11.2-Backup-Restore-Corrections | 200 / 200 | erkannt | beide gespeichert |
| 12.11.3-Private-File-Cache-Corrections | 200 / 200 | erkannt | beide gespeichert |
| 12.11.4-Intranet-Login-Corrections | 200 / 200 | erkannt | beide gespeichert |
| 12.12.0-OIDC | 200 / 200 | erkannt | beide gespeichert |
| 12.12.1-General-Corrections | 200 / 200 | erkannt | beide gespeichert |
| 12.12.2-Textarea-Interactions | 200 / 200 | erkannt | beide gespeichert |
| 12.12.3-PHP9-Compatibility | 200 / 200 | erkannt | beide gespeichert |
| 12.12.4-Default-Design | 200 / 200 | erkannt | beide gespeichert |
| 12.13.0-Responsive-Design | 200 / 200 | erkannt | beide gespeichert |
| 12.9.5.2-Faster-Rename | 200 / 200 | erkannt | beide gespeichert |
| 12.9.5.3-Onlymine | 200 / 200 | erkannt | beide gespeichert |
| 12.9.5.4-Indexer-Final | 200 / 200 | erkannt | beide gespeichert |
| 12.9.6-Misc-Backend-Optimizations | 200 / 200 | erkannt | beide gespeichert |
| feature/search-phrases-quotes-highlighting | 200 / 200 | erkannt | beide gespeichert |
| fix/indexer-db-lock-wait-12952 | 200 / 200 | erkannt | beide gespeichert |
| fix/indexer-db-lock-wait-12953 | 200 / 200 | erkannt | beide gespeichert |
| fix/nosniff-script-mime-types | 200 / 200 | erkannt | beide gespeichert |

Die Tests verwendeten PHP 8.4.24 und MariaDB 11.8.6. Die Veröffentlichung der finalen Remote-Commits, weitere Tests, Browserabnahme und das heruntergeladene .7-Paket sind gesondert zu verifizieren.
