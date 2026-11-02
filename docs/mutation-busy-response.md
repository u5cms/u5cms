# Vorübergehend blockierte Ressourcenänderungen

Korrektur vom 2. November 2026. Betroffen sind zehn Branches ab
12.11.1-Migration-Hardening, einschliesslich des Such-Arbeitsbranches.

Mehrere bereits durch Dateisperren geschützte Schreibaktionen lieferten bei einer
laufenden Aktualisierung HTTP 500. Die Einstiege verwenden jetzt die gemeinsame
Konfliktantwort: HTTP 409, Retry-After 2 Sekunden und eine Erklärung. Eine eigene
Busy-Exception kennzeichnet diesen erwarteten Konflikt. Andere Fehler werden
dadurch nicht als erfolgreiche Schreiboperation ausgegeben.

Geändert sind codeassets.inc.php, new2.php, copy2.php, delete2.php,
archiveunarchive2.php, coding2.php und rename2.php. Die älteren sechs betroffenen
Branches erhalten zusätzlich resource-mutation.inc.php. Vorhandene Quellbytes
ausserhalb der gezielten ASCII-Änderungen bleiben erhalten. Datenbankschemas
und bestehende gespeicherte Daten werden nicht migriert.

Neue Ausführung: 100 echte blockierte HTTP-Schreibaktionen über alle zehn
betroffenen Branches; unveränderte Ressourcen, Historien und Dateien. Vier echte
unterbrochene Umbenennungen werden wiederaufgenommen, wiederholte Anfragen und
veraltete Editoren geprüft. Auf jedem korrigierten Branch bestehen danach 51
Frontend- und 98 Backend-Browserprüfungen. Testumgebung: PHP 8.3.6, MariaDB
10.11.14, Chrome Headless Shell 156.0.8078.80, ausschliesslich Einweginstallationen.

Der vollständige Bericht, die gemeinsamen Testwerkzeuge und Vorher-/Nachher-
Nachweise liegen auf der kumulativen Linie unter
[2026-11-02-evolution-review.md](https://github.com/u5cms/u5cms/blob/12.12.4-Default-Design/docs/2026-11-02-evolution-review.md).
Dieser Stand ist veröffentlichtes Quellmaterial; eine produktive Installation
oder ein Merge ist nicht erfolgt.
