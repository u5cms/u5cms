# Erneute Evolutionsprüfung vom 2. November 2026

Die Hauptprüfung und die drei zusätzlich beauftragten Untersuchungsläufe sind
abgeschlossen. Drei Fehlergruppen sind reproduziert, in allen betroffenen Branches
korrigiert, veröffentlicht und nach der letzten Anwendungskorrektur geprüft.

| Bestätigter Fehler | Korrigierte Branches |
| --- | --- |
| Formular-Datenansichten brechen ohne Suchfilter ab | 12.12.3 und 12.12.4 |
| Vorübergehend blockierte Änderungen liefern unbehandelte Serverfehler | Zehn Branches ab 12.11.1, einschliesslich Such-Arbeitsbranch |
| Die Fehlermeldung verhindert den nächsten Klick auf Speichern | Alle 14 Branches ab 12.10.0, einschliesslich nosniff- und Such-Arbeitsbranch |

Main und produktive Installationen wurden nicht verändert. Es bleibt im
geprüften Umfang kein bestätigter Fehler offen.

## Gegenstand und Referenzen

Geprüft wurde die ganze heutige Reihe: main und 15 Entwicklungs-/Arbeitsbranches,
bis `12.12.4-Default-Design`. Originalreferenz ist
`9d366253417822c279692767b89dcf1ff0388c5c`; aktuelles main ist
`78cadbf87b7307203b8bd4ff96929b306af7d982`. Die einzelnen Ausgangs- und geprüften
Anwendungsstände stehen im [maschinellen Nachweis](../tests/evolution-review-2026-11-02-evidence.json).

Die [Quelleninventur](2026-11-02-evolution-paths.tsv) erfasst 909 unterschiedliche
geänderte Pfade der Branch-Reihe gegenüber main, ohne Umbenennungen als einen
einzigen Pfad zusammenzufassen. Sie ordnet die Änderungen den Prüfrouten zu.
Eine Inventur ist kein Nachweis, dass jede denkbare kundenspezifische Verwendung
ausgeführt wurde. Die Anwendungssnapshots der tatsächlich geprüften Branches
wurden zusätzlich gegen die veröffentlichten Git-Blobs verglichen.

## Bestätigter und behobener Fehler

Die normalen Backend-Datenansichten `formdata2.php` und `formdatamail.php`
brachen ohne nichtleeren Suchfilter ab. `formdata.query.php` legte `$keywords`
nur für einen gesetzten Filter an. Nach dem Rückbau von `tnuoc` war daraus beim
anschliessenden `count($keywords)` ein TypeError geworden. Betroffen waren
`12.12.3-PHP9-Compatibility` und `12.12.4-Default-Design`.

Die Antwort konnte bereits HTTP 200 und einen Teil der Bedienoberfläche enthalten.
Die eigentliche Tabelle, nachfolgende eigene Erweiterungen und der Abschluss der
Seite fehlten trotzdem. Ein vorhandener OIDC-Test hatte nur den Status und das
Ausbleiben einer Loginfehlermeldung geprüft und diesen Abbruch deshalb übersehen.

Die gemeinsame Abfragequelle definiert jetzt immer eine leere Stichwortliste,
bevor sie einen vorhandenen Filter verarbeitet. Die Korrektur verändert zwei
ASCII-Zeilen; alle vorhandenen Nicht-ASCII-Bytes bleiben erhalten. Es wurden keine
Datenbankschemas oder gespeicherten Formulareingaben geändert.

Die [neue Vergleichsprüfung](../tests/formdata-view-reference.py) benutzt echte
HTTP-Einstiege und frische Datenbanken aus der ursprünglichen SQL-Vorlage.
Sie prüft vollständige Ausgabe, tatsächlich ausgewählte Datensätze, die eigene
Erweiterung `modify!formdata2!php`, Status, Sortierung, Filter und verweigerten
Zugriff. Die Mailingansicht wird nur angezeigt; es wird keine Mail verschickt.

Vor der Korrektur entstehen je betroffenem Branch 28 Abweichungen in 42 Fällen;
aktuelles main stimmt mit dem Original überein.
[Gesicherte Reproduktion](../tests/formdata-view-before-fix.json).
Nach der Korrektur stimmen Original und alle 16 heutigen Branches in insgesamt
714 vollständigen Ansichtsfällen überein. Der OIDC-Test verlangt jetzt ebenfalls
den tatsächlichen Abschluss der Datenansicht.

Korrekturcommits:

- [12.12.3: 8931d31](https://github.com/u5cms/u5cms/commit/8931d314c2a5db4ae43ada86721a07afe899bba3)
- [12.12.4: 0b1ab1c](https://github.com/u5cms/u5cms/commit/0b1ab1c19dea9e7eec533d488a7ddd9f4e3e49be)

Die anderen 14 Branches enthalten den verursachenden Count-Rückbau nicht. Dort
ist für diesen Fehler keine Anwendungskorrektur erforderlich. Main und produktive
Installationen wurden in diesem Auftrag nicht verändert.

## Neue tatsächliche Prüfausführungen

PHP 8.3.6, MariaDB 10.11.14 und Chrome Headless Shell 156.0.8078.80; ausschliesslich
Einweginstallationen und Testdaten. Frühere Berichte werden nicht als neue Läufe
gezählt. Die vollständige Integrationsmatrix lief auf der neuesten Designlinie;
der Originalvergleich und die neuen Datenansichtsfälle liefen über alle Branches.

| Bereich | Neues Ergebnis |
| --- | --- |
| Original gegen alle 16 Branches, mit echten Browser-Formularübermittlungen | 612 Prüfungen, keine Vergleichsabweichung |
| Neue vollständige Datenansichten, Original plus alle Branches | 714 Fälle, keine Vergleichsabweichung |
| Zusätzliche kontextbezogene Request-/CSV-Verträge | 142 Prüfungen, keine Vergleichsabweichung |
| Codeobjekt-Migration und Datenbank | 1.671 bestanden |
| Umbenennung, einschliesslich Wiederaufnahme | 886 bestanden |
| Mobile Migration | 69 bestanden |
| Textarea-Transformationen | 748 bestanden |
| Textarea/Frontend/Backend/Umbenennen im echten Browser | 159 / 51 / 98 / 92 bestanden |
| Main-Differenzen, SQL-/ZIP-Restore und Editor | 504 bestanden, einschliesslich grosser Einzelzeile unter 64 MiB PHP |
| Suchkern/Speicher/Service/Vorschläge/Distanz/Index | 7.767 / 46 / 350 / 6.491 / 20.907 / 96 bestanden |
| PHP-/JavaScript-Normalisierung | 16 Fixtures und alle 7.623 Faltungen stimmen überein |
| Suche HTTP/Browser/Standarddesign | 33 / 222 / 77 bestanden |
| OIDC-Kern/konkurrierende Bindungen | 162 / 15 bestanden |
| OIDC mit lokalem TLS-Provider, HTTP/Browser | 249 / 73 bestanden |
| Bild-Upload, Archivierung und Fehlerfälle | 96 bestanden; 33 veröffentlichte Bilder inhaltlich geprüft |
| Design, beide SQL-Seeds und ihre Exporte/Resetquellen | Je 16 Quellenprüfungen; 36 / 30 Browserprüfungen |
| PHP-Kern/Request-Verträge/IDN/Login-Hosts | 135 / 22 / 40 / 40 bestanden |
| PHP-Syntax und Quellprüfung | Je 878 Dateien ohne Fehler im jeweiligen Prüfumfang |
| Mail-Konstruktion | 20 semantische Prüfungen aus 11 tatsächlichen Konstruktorfällen; kein Transportaufruf |

Die Paketbäume, Locks und deklarierten Patches aller 13 gesperrten Abhängigkeiten
sind unverändert gegenüber dem zuvor gegen Upstream geprüften Anwendungsstand
`4159972464e798eb3371cc2d15625fcaa1e9f35d`. Die installierten Hashes der
Rückgabetyp-Patches wurden neu geprüft. Eine erneute kalte Installation aus neu
heruntergeladenen Upstream-Archiven wird damit nicht behauptet.

Zu Beginn mussten der Testaufbau für ältere Branches, die PHP-Konfiguration der
Unterprozesse und der lokale Browserpfad berichtigt werden. Diese fehlgeschlagenen
Aufbauversuche sind keine erfolgreichen Anwendungstests. Die Tabelle zählt nur
die anschliessend tatsächlich bestandenen Ausführungen.

## Bewahrte Konzepte und Grenzen

Die Quellen- und Ausführungsprüfung umfasst eigene P-/PHP-Anwendungen und
Request-Collections, fünf Sprachen und Fallbacks, konfigurierbare PHP-Ausführung,
öffentlich/geschützt/archiviert, lokale Anmeldung und optionale Föderation,
HIADRI, Eigentümerrechte, Formularwerte und CSV-Verträge, Seiten-/Code-/Metadaten,
Konflikterkennung, Historien, Ressourcenreferenzen, Migration, Umbenennen,
Veröffentlichung, Dateischutz und Sicherungen. Die vier Website-Fassungen bleiben
zusammen betrachtet: beide SQL-Seeds, CSS-Exporte und TPL-Resetvorlagen. Die
getrennte Mobilansicht und die bestehende Encoding-Grenze bleiben erhalten.

Nach allen vier Untersuchungsläufen und den anschliessenden Prüfungen bleibt im
ausgeführten Umfang kein bestätigter Fehler offen. Das ist keine Zertifizierung beliebiger eigener Erweiterungen, externer
Mailzustellung, produktiver Datenübernahme, eines echten externen Identity-Providers
oder einer tatsächlichen PHP-9-Laufzeit. Die Ergebnisse stammen aus lokalen
Prüfläufen; sie sind kein GitHub-CI-Status. Es wurde nichts gemergt oder installiert.

## Drei zusätzlich beauftragte Untersuchungsläufe

1. Ungewöhnliche Konfigurationen und eigene Erweiterungen: abgeschlossen; 2.210 Vergleichsfälle ohne Abweichung.
2. Abbrüche, Fehlerfolgen und konkurrierende Änderungen: abgeschlossen; zusätzlicher Sperrfehler auf allen zehn betroffenen Branches korrigiert.
3. Vollständige Redaktionsabläufe im Browser: abgeschlossen; überdeckender Fehlerhinweis auf allen 14 betroffenen Branches korrigiert.

Jeder Zusatzlauf hat eigene Fragestellungen, tatsächliche Ausführungen, Befunde
und einen Abschluss. Die neu bestätigten Fehler sind in allen betroffenen Branches
behoben und nach der letzten Korrektur geprüft.

### Zusatzlauf 1: Konfiguration und eigene Erweiterungen

Abgeschlossen. Die [unabhängige Konfigurationsprüfung](../tests/configuration-reference.py)
führt zuerst das Original aus, dann alle 16 Branches. Zehn Profile kombinieren
Basic-/Cookie-Anmeldung, FastCGI, gewöhnliche und höhere Redaktionsrechte,
ausdrücklich freigegebenen Backendzugang, Formularprüfung, Quotehandling sowie
PHP-Ausführung überall, nur im Archiv und nirgends. Jedes Profil prüft fünf
Sprachen, eigene Request-/Cookie-Collections, beide vollständigen Datenansichten,
CSV und die vorhandenen Bearbeitungseinstiege.

Ergebnis: 130 fachliche Beobachtungen je Installation, zusammen 2.210, ohne
Vergleichsabweichung. Die vorhandene Präzisierung verweigerten Zugriffs von HTTP
200 zu HTTP 403 wird ausdrücklich als erhaltene Ablehnung geprüft. Die Daten
haben dieselben festgesetzten Zeitwerte; Zeitunterschiede zwischen Fixture-
Erstellungen werden nicht als Anwendungsfehler gewertet. Es wurde kein weiterer
Kollateralschaden bestätigt und keine Anwendungskorrektur aus diesem Lauf nötig.
[Gespeicherte Ergebnisse](../tests/evolution-configuration-2026-11-02.json).

### Zusatzlauf 2: Echte Sperren und unterbrochene Umbenennung

Abgeschlossen. Ein weiterer Fehler wurde bestätigt: Laufende Codeaktualisierungen
und unterbrochene Umbenennungen verhinderten konkurrierende Änderungen, mehrere
Schreibaktionen endeten dabei jedoch mit einem unbehandelten HTTP 500. Dies betraf
Code speichern, Neu, Kopieren, Archivieren, Löschen und Umbenennen. Die beiden
bereits kontrollierten Seiten-/Metadatenpfade zeigten, wie der Konflikt der
Oberfläche mitgeteilt werden muss. Die ersten vier kumulativen Stände ergaben
48 solcher Serverfehler bei unveränderten Daten.

Die zehn betroffenen Branches ab `12.11.1-Migration-Hardening`, einschliesslich
des Such-Arbeitsbranches, erhalten die gemeinsame Konfliktantwort. Eine eigene
Busy-Exception trennt erwartete Konkurrenz von anderen Betriebsfehlern. Die
betroffenen Einstiege liefern HTTP 409, `Retry-After: 2` und eine Erklärung.
Die normale Revision-, Rechte- und CSRF-Prüfung bleibt Teil des Schreibablaufs.

[HTTP-/Wiederaufnahmeprüfung](../tests/mutation-recovery-http.py): Ein getrennter
Prozess hält die tatsächliche Dateisperre. Ein weiterer Lauf unterbricht eine
echte Umbenennung nach dem Verzeichniswechsel und vor den Datenbankänderungen.
Alle betroffenen Schreibversuche werden über die echten HTTP-Einstiege ausgeführt.
Native SQL-Abfragen und Dateihashes vergleichen Ressourcen, Historien und Bytes
vor und nach jedem abgewiesenen Versuch.

Ergebnis: 100 blockierte Schreibaktionen liefern die kontrollierte Konfliktantwort
und verändern keinen der geprüften Zustände. Vier tatsächlich unterbrochene
Umbenennungen werden über das originale Retry-Ziel abgeschlossen; Wiederholung
und veraltete Editoren verändern danach keine falschen Daten. Auf allen zehn
Branches gelingt anschliessend wieder ein normaler Schreibzugriff. Die früheren
Stände werden nur auf die dort bereits vorhandenen Sperrverbraucher geprüft;
die umfassendere gewöhnliche Ressourcenkoordination kam erst mit 12.12.1 hinzu.

Zusätzlich bestehen auf **jedem** der zehn korrigierten Branches erneut 51
Frontend- und 98 Backend-Browserprüfungen, einschliesslich Basic-/Cookie-Anmeldung,
Unicode, Revisionen, CSRF, Neu/Kopieren/Umbenennen/Archivieren/Löschen/Reaktivieren.
[Reproduktion vorher](../tests/evolution-recovery-before-fix.json) und
[neue Ergebnisse](../tests/evolution-recovery-2026-11-02.json).

### Zusatzlauf 3: Tatsächliche Redaktionsabläufe

Abgeschlossen. Die Fehlermeldung nach einem gescheiterten Ressourcen-Speichervorgang
lag über dem Speichern-Knopf und fing den nächsten Mausklick ab. Der dritte Lauf
reproduzierte dies im realen Browser auf allen drei untersuchten Entwicklungsstufen.
Die Eingaben blieben erhalten, der normale Wiederholungsversuch scheiterte jedoch
an der darüberliegenden Meldung. Die gemeinsame Datei ist auf allen 14 betroffenen
Branches bytegleich; die Korrektur wird überall identisch nachgeführt.

Die Meldung sitzt jetzt unten rechts und fängt keine Zeigerereignisse ab. Ihre
Breite bleibt im Fenster; sie wird als Alert angekündigt. Die neue Prüfung benutzt
gewöhnliche Klicks und würde einen weiterhin verdeckten Knopf erkennen.

[Browserablauf](../tests/editorial-journey-browser.cjs),
[Einweg-Webrunner](../tests/editorial-journey-web.py) und
[gesicherte Ergebnisse](../tests/evolution-editorial-2026-11-02.json): Je Basic- und
Cookie-Anmeldung auf 12.11.1, 12.12.3 und 12.12.4; echte Sperre und abgewiesene
Anfrage, erhaltene Eingaben und Dirty-Markierung, erfolgreicher Wiederholungsversuch,
Gegenänderung in einem separaten Quellfenster, unveränderte neuere Veröffentlichung,
beide Historien, exakte Unicode-/Entity-Werte und UTF-8-Dateibytes, zwei verschiedene
Seiten in den Backendfenstern und erneutes Öffnen. Die vorhandene Kopplung zweier
Ansichten derselben Seite bleibt dabei erhalten.

Ergebnis: 63 / 73 / 73 Prüfungen, insgesamt 209, bestanden. Die Prüfung wartet beim
Wiederöffnen auf den tatsächlichen Objekteditor nach dessen Cookie-Weiterleitung.
Frühere Stände werden auf ihre vorhandenen Sperrverbraucher geprüft, nicht auf erst
später ergänzte Koordination. Die gemeinsame Abschlussprüfung nach der letzten Anwendungskorrektur ist ebenfalls
abgeschlossen; sie ist kein vierter zusätzlicher Untersuchungslauf.

## Verifizierter Abschluss nach der letzten Korrektur

Der [abschliessende maschinelle Nachweis](../tests/evolution-final-2026-11-02-evidence.json)
enthält die tatsächlich veröffentlichten Anwendungscommits aller 16 Branches,
die Dateigleichheit der geprüften Installationsquellen, die letzten Gruppenläufe
und ihre Ausgaben. Insgesamt stimmen 18.237 Anwendungsdateien der geprüften
Snapshots mit den zugehörigen veröffentlichten Git-Blobs überein. Branch-Namen
und Versionskommentare sind geprüft. Main und 12.9.6 sind gegenüber Auftragsbeginn
unverändert; es wurde kein zusätzlicher Entwicklungsbranch eingeführt.

Nach der letzten Anwendungskorrektur bestehen erneut der Originalvergleich mit
612 Prüfungen, die vollständigen Datenansichten mit 714 Fällen, die kontextbezogenen
142 Prüfungen, Migration 1.671, Umbenennung 886 und mobile Migration 69. Die gesamte
HTTP-/Browsergruppe mit 159 Textarea-, 51 Frontend-, 98 Backend-, 92 Umbenennungs-
und 504 Main-Differenz-/Restore-Prüfungen besteht, ebenso Suche, OIDC, Uploads
und beide Design-Seeds. Die PHP-Kern-/Request-/IDN-/Hostreihen bestehen erneut
mit 135 / 22 / 40 / 40 Fällen; Syntax und Quellprüfung umfassen je 878 PHP-Dateien.

Beim ersten Abschlusslauf scheiterte ausschliesslich das anschliessende Aufräumen
einer Einwegdatenbank an einer zurückgebliebenen InnoDB-Fixture-Datei. Alle 904
Anwendungsprüfungen dieser HTTP-/Browsergruppe waren bereits erfolgreich. Die
komplette Gruppe wurde erneut ausgeführt und bestand einschliesslich Aufräumen.
Der Nachweis bewahrt den ersten fehlgeschlagenen Prozessstatus und die erfolgreiche
Wiederholung getrennt. Kein Anwendungstest wurde dafür abgeschwächt.

Alle drei zusätzlichen Untersuchungsaufträge sind abgeschlossen. Es bleibt aus
diesem Auftrag kein laufender Test oder automatischer Folgeauftrag. Merging,
Release-Austausch und produktive Installation gehören nicht zu den ausgeführten
Schritten.

### Veröffentlichte Anwendungsstände

Spätere Abschlusscommits können ausschliesslich Nachweise und Dokumentation
fortschreiben. Die hier geprüften Anwendungsbytes sind dadurch nicht geändert.

| Branch | Geprüfter veröffentlichter Anwendungscommit |
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
