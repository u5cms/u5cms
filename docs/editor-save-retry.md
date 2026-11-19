# Erneut speichern nach einer Fehlermeldung

Korrektur vom 2. November 2026, in allen 14 betroffenen Entwicklungsbranches ab
12.10.0-Multi-Optimizations-by-AI, einschliesslich nosniff- und Such-Arbeitsbranch.

Die gemeinsame Ressourcen-Speichersteuerung bewahrte nach einer fehlgeschlagenen
Anfrage die Eingaben. Ihre Meldung lag jedoch oben links über dem Speichern-Knopf
und fing Mausklicks ab. Der tatsächliche erneute Klick lief im Browser in einen
Timeout. Ein erzwungener Testklick würde diesen Bedienfehler verdecken.

Die Meldung steht jetzt unten rechts, ihre Breite passt ins Fenster und sie
fängt keine Zeigerereignisse ab. Sie wird als Alert ausgezeichnet. Text, Dirty-
Status, Revisionen, Konflikterkennung und Quittierungsprotokoll werden nicht
umgestellt. Geänderter Anwendungspfad ist ausschliesslich
`u5admin/resourcesave.js`. Alle 14 Ausgangsdateien sind identisch; alle erhalten
dieselben korrigierten Bytes.

Neue vollständige Browserabläufe auf 12.11.1, 12.12.3 und 12.12.4 bestehen mit
Basic- und echter Cookie-Anmeldung: tatsächliche Dateisperre, reale abgewiesene
Speicheranfrage, unveränderte Eingaben, erneuter gewöhnlicher Mausklick,
erfolgreiches Speichern, konkurrierendes Quellfenster, erhaltene Historien,
bytegenauer UTF-8-Export und erneutes Öffnen. Zwei unterschiedliche Seiten werden
in den beiden Backendfenstern bearbeitet; die bestehende Synchronisierung zweier
Ansichten derselben Seite wird nicht als unabhängige Bearbeitung missverstanden.

Ergebnis: 63 / 73 / 73 Prüfungen, zusammen 209. Die frühere Linie besitzt noch
nicht die mit 12.12.1 eingeführte Koordination gewöhnlicher Seitenschreibzugriffe;
sie wird auf ihre tatsächlich vorhandenen Abläufe geprüft.

Testumgebung: PHP 8.3.6, MariaDB 10.11.14, Chrome Headless Shell 156.0.8078.80,
ausschliesslich Einweginstallationen. Vollständige Werkzeuge und Nachweise:
[Evolutionsbericht](https://github.com/u5cms/u5cms/blob/12.12.4-Default-Design/docs/2026-11-02-evolution-review.md).
Keine produktive Installation und kein Merge wurden vorgenommen.
