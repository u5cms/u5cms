# u5CMS 12.9.5.6 – Speichern und Indexierung zusammen absichern

## Befund und Änderung

Ein erfolgreich gespeicherter Seiteninhalt konnte ohne die für den späteren
Vollindex nötige Änderungsmarkierung bleiben: `savepage.php` und andere
Bestätigungsseiten luden `lastsave.php` erst in einem separaten Browser-Iframe.
Die Einzelindexierung umging ausserdem den Vollindex-Mutex und dessen
Datenbankwartebegrenzung. Ein geschlossener Bestätigungsframe, gleichzeitige
Indexaufrufe und Datenbanksperren waren deshalb eigenständige Fehlerfälle.

Die neue echte Editor-POST-Prüfung reproduziert die fehlende Markierung auf
dem unveränderten v12.9.5.5. Sie lädt bewusst keine Browser-Unteranfragen.
Mit der Korrektur bestehen alle 26 Save-/Indexprüfungen.

Die gemeinsame serverseitige Ressourcenschreibsperre schreibt den
Änderungsmarker atomar vor der Mutation und nochmals beim Requestende vor
Freigabe der Sperre. Die Markierung hängt damit nicht mehr vom Nachladen eines
Browserframes ab. Sie ist konservativ: auch ein später abgewiesener Vorgang
kann einen zusätzlichen Vollaufbau fällig machen. Das verhindert keinen Save
und erklärt keinen Vollindex vorzeitig für abgeschlossen.

Einzel- und Vollindex verwenden dieselbe nicht blockierende Indexsperre.
Vor dem Datenbankzugriff nehmen sie zusätzlich die bestehende
Ressourcen-/Rename-Sperre, immer in dieser Reihenfolge. Ein aktiver oder
unterbrochener Rename wird nicht als fertiger Seitenstand indexiert. Bei
belegter Quellsperre wartet ein Vollindex in seiner normalen Pause weiter;
ein Einzelindex liefert 409 mit höchstens drei Browser-Wiederholungen nach
je zehn Sekunden. Bei geschlossenem Frame bleibt der Vollaufbau fällig.

Auch die Einzelindexverbindung begrenzt Metadaten-/Tabellen- und
InnoDB-Zeilensperrwartezeiten auf höchstens fünf Sekunden. Strengere
Serverwerte gelten weiter. Socket-Lesegrenzen werden vor der Verbindung
gesetzt. Die normalen Backend-Verbindungen werden dadurch nicht umgestellt.
Der bestehende Vergleich der Quellbytes bleibt zusätzlich erhalten.

## Einstieg, Zustand und Koordination

| Einstieg | Authentisierung / Wirkung | Koordination und Abschluss |
| --- | --- | --- |
| `savepage.php`, Metadaten, Dateien, Kopieren, Archivieren, Löschen und weitere koordinierte Backend-Schreiber | Bestehende Backend-/CSRF-/Objektrechte; Quell- und Historienänderung | Bestehende Quellsperre; serverseitige Markierung vorher/nachher. Bei belegter Sperre bleibt die bisherige 409-Antwort samt Editorinhalt erhalten. |
| `done.php` und `lastsave.php` | Bestehende Backend-Anmeldung; Bestätigung und kompatible Markierung | Der alte Iframe bleibt kompatibel, ist für koordinierte Saves aber nicht mehr Voraussetzung. `lastsave.php` veröffentlicht atomar. |
| `indexer.php?n=…` | Bestehender Indexeinstieg; nur genannte Ressource, fünf Sprachfassungen | Gemeinsamer Indexmutex, dann Quellmutex; fünf Sekunden SQL-Sperrwartezeit. Kein Vollindex-Abschlusszeitstempel und kein Löschen des Vollindex-Fortschritts. |
| Automatischer/manueller Vollindex und Fortsetzung | Bestehende Trigger/Optionen und Wiederaufnahme | Gemeinsame Sperren; höchstens acht Ressourcen pro Teilaufruf, Zeitbudget zwischen Ressourcen, Pause/Fortschritt. Gesamtdatum erst nach Erfolg. |
| Rename | Bestehende Anmeldung, CSRF und Archiv-/Coderechte | Eigene begrenzte SQL-Wartezeiten aus .5; unveränderter Wiederaufnahmeplan, Quellsperre und erreichbare Recovery-Übersicht. |
| Frontend-Formularspeicher und Statusspeicher | Bestehende Formular-/SELF-/Versionsprüfungen | Deren bereits vorhandene serverseitige Änderungsmarkierung bleibt erhalten. Domainmodus und Site-Writer werden durch .6 nicht umdefiniert. |

## Kumulative Geschichte ab 12.9.5 ohne Suffix

Die unveränderten Vergleichsstände wurden aus GitHub und einer frischen Kopie
aufgelöst: v12.9.5 führt als annotierter Tag vom Tagobjekt
`fa6e8982e85d7ca9ec33683ab33d31eb86a31eb9` auf den tatsächlichen
Vergleichscommit `9d366253417822c279692767b89dcf1ff0388c5c`
(Baum `df8329eef819a7f8a2402df5f4fcfb7e606ec087`). Die Git-Vergleiche
lösen diesen Tag auf denselben Commit/Baum auf. Die neueren Vergleichscommits sind
v12.9.5.4 `c7062c680deb29fcac70815eea929ad57ee3a5d2`,
v12.9.5.5 `8bcb09937999d4ebb51da293b06a113bda0940d8`.

| Änderungslinie | Enthaltenes Verhalten und Nachweis |
| --- | --- |
| Vor .1, innerhalb der Änderungslinie ab .nix | Login-Zielhostprüfung, internationale Versandadressen und separat erhaltene historische Kontoschlüssel, lokale Mailbibliotheken und Fehlerbehandlung. Adress-, Host-, MIME-/SMTP-/Native-Mailprüfungen in der stabilen Abnahme; alle 24 Dependency-Dateihashes gesondert verglichen. |
| .1 | Speicherschonende historische Suche und Vorschläge; Sichtbarkeitsfilter und Textauszüge erhalten. 45 Speicher-/Grenzprüfungen, 55'990 Originalvergleiche der Auszüge und SQL-Prüfungen beider Sucheinstiege. |
| .2 samt Reparaturen | Schnelleres Rename mit Überschriftenschutz, fünf Sprachen, Referenzen, Dateien, Historien, Rechten und Wiederaufnahme. Suchtexte ausgenommener Seiten werden erhalten; Backend-Schreiber und Backups koordinieren sich. 893 Kern-/Datenbank-, 114 HTTP- und 92 Browserprüfungen auf der stabilen Linie. |
| .3 samt Reparaturen | Optionaler exakter Domain-SELF-Modus und ausdrücklich konfigurierte zentrale Domains; reguläre Status 1–4; Site-Writer hinter Formular-/Konfliktprüfung. Persönliche Voreinstellung, leere Adminliste und bestehende Kontoschlüssel bleiben erhalten. Ownership-/Dispatch-Prüfungen und Loginprüfungen in CI. |
| .4 | Begrenzte und wiederaufnehmbare Vollindexierung, Datenbankwartebudget, Abschlussdatum und korrekte Anzeige. Echte HTTP-, SQL-Sperr-, Konkurrenz- und Prozessabbruchprüfungen. |
| .5 | Rename-SQL-Wartebegrenzung, Abbruch statt doppelter Sperrwartezeit sowie authentisierte Wiederaufnahme ohne ursprüngliches Wartefenster. 83 zusätzliche echte SQL-/HTTP-/Recoveryprüfungen pro stabilem Profil. |
| .6 | Zuverlässige serverseitige Markierung koordinierter Saves, gemeinsamer Einzel-/Vollindexmutex, Ausschluss unfertiger Rename-Zustände und begrenzte Einzelindexwartezeiten/Wiederholungen. Gegen .5 reproduzierter Fehler und 26 neue tatsächliche Save-/Indexprüfungen. |

Die vollständige ältere [Änderungs- und Dateitabelle ab .nix](release-12.9.5.4.md)
und die [.5-Ergänzungen](release-12.9.5.5.md) bleiben nachvollziehbar.
Die .6-Dateiliste und Branch-Commits stehen im zugehörigen Inventar.

## Prüfung und Branch-Erhalt

Frisch lokal ausgeführt: PHP 8.4.24 mit nativer intl-/ICU-Unterstützung und
MariaDB 11.8.6, private kopierte Installationen und isolierte Testdatenbanken.
Neben der neuen Save-Prüfung bestanden Vollindex-Abschluss in sechs Anfragen,
SQL-Abbruch ohne verbleibende wartende Anweisung, SIGKILL/Wiederanlauf,
Rename, SELF/Writer, Suchspeicher/SQL/Auszüge, 158 Adress- und 40 Hostprüfungen.
Die vollständige stabile CI auf PHP 8.3 und 8.4 mit MariaDB 11.4 und Chromium
bestand zunächst bei `ac3e5821a37ae9ce8639e89f861c8202f9691676`
([Actions 44481530011](https://github.com/u5cms/u5cms/actions/runs/44481530011)).
Der abschliessende Anwendungskandidat `88dab36c2ff3c422f4c6421df4652e9656a6622d`
bestand dieselbe vollständige PHP-8.3-/8.4-Abnahme einschliesslich Chromium
in [Actions 44483223585](https://github.com/u5cms/u5cms/actions/runs/44483223585).
Die nachfolgenden Inventar-/Berichtsänderungen ändern diese Anwendungssourcen
und stabilen Testdateien nicht.

Alle 21 veröffentlichten Branch-Heads und sämtliche geänderten Programm-/
Testdateien wurden unabhängig von GitHub zurückgelesen und bytegenau mit den
geprüften lokalen Ständen verglichen. Die Versionen, Renderer sowie vorhandene
Such-/Migrationsdateien wurden zusätzlich gegen die jeweiligen Ausgangs-Blobs
verglichen. Das unveränderte .5-Quellarchiv enthält 1’185 Dateien und stimmt
vollständig mit seinem Git-Tag überein.

Die Nachführung bewahrt die jeweilige Branch-Version, Renderer, moderne Suche
samt Format-/Pending-/Running-Verträgen sowie die vorhandenen Migrations- und
Rename-APIs. Auf modernen Suchbranches wird nur die isolierte Test-Fixture um
die neuen Produktionshilfen ergänzt; alle 96 ursprünglichen Erwartungen
bleiben unverändert. Das Commit-/Pfad-/Testinventar dokumentiert die tatsächliche
gezielte Branch-Abnahme, getrennt von den umfassenden Branch-Pipelines.

## Installation, Rückfall und Grenzen

Das vollständige zusammengehörige Release einschliesslich `index-work.inc.php`
übertragen; Konfiguration, Datenbanken, Medien und Site-Ressourcen erhalten.
Vor dem Versionswechsel einen offenen Rename mit seinem passenden System-
und Wiederaufnahmestand abschliessen. Für den Rückfall den zusammengehörigen
vorigen Code wiederherstellen und vorhandene Wiederaufnahmepläne behalten.
Es gibt keine neue .6-Datenbankschemaänderung oder neue Pflichtkonfiguration.

Counter benötigt weiterhin `$u5onlyminescope = 'domain';` und
`$u5onlymineadmindomains = array('bk.admin.ch');`. Andere Installationen behalten
ihre lokale Einstellung; Standard ist persönliche Zuordnung ohne zentrale
Domains. Die lokale Policies-Novemberbedingung ist nicht Releasebestandteil
und muss beim Deployment separat erhalten werden.

Fünf Sekunden begrenzen eine SQL-Sperrwartezeit, nicht jede SQL-Ausführung,
Netzwerk-/Dateisystemoperation oder komplette dynamische PHP-Ressource.
Eine einzelne schwierige Seite kann das zwischen Seiten geprüfte
Zwei-Sekunden-Budget überschreiten. Ein dauerhaft fehlerhafter Renderer wird
weiter mit Verzögerung wiederholt und muss behoben werden; er wird nicht
stillschweigend als erfolgreich indexiert. Automatischer Vollaufbau braucht
weiter geeignete Seitenaufrufe und wartet standardmässig 30 Minuten seit dem
letzten Save. Nach ausgelaufenen Einzelwiederholungen bleibt dieser Weg fällig.

Datenbank und Dateisystem bleiben keine gemeinsame atomare Transaktion.
Unkoordinierte externe Datenbank-/Dateiänderungen und sämtliche beliebigen
Site-PHP-Erweiterungen sind keine pauschal abgenommenen Abläufe. Die Prüfung
ist keine vollständige neue Zertifizierung alter PHP-/MySQL-Laufzeiten oder
eines produktiven SMTP-Providers. Es wurden keine realen Testnachrichten an
Dritte und keine Änderungen an produktiven Schalterdaten vorgenommen.
Eine Produktionsausrollung oder Produktionsabnahme wird hier nicht behauptet.


### Branch-Umfang und Prüffortsetzung

Das [vollständige Nachführungsinventar](release-12.9.5.6-backports.json)
enthält 21 Ausgangs-/Folgecommits, die geänderten Pfade und die ausdrücklich
unveränderten Versions-/Renderer-/Such-/Migrations-Blobs. Pro Branch bestanden
26 tatsächliche Save-/Indexprüfungen, zwölf Batchprüfungen und die passende
Rename-Suite; vorhandene Ownership- und Anzeigeprüfungen bestanden ebenfalls.
Auf den sieben modernen Suchständen bestanden alle 96 ursprünglichen
Indexprüfungen. Zwei ältere Indexer-Arbeitsstände enthalten die spätere
Anzeige-Suite noch nicht; das Inventar kennzeichnet dies als nicht vorhanden.

Die umfassenden Branch-Pipelines erkannten zusätzlich fehlende neue
Hilfsdateien in isolierten Layout-Reset-, Restore- und Upload-Fixtures. Diese
Fixtures wurden um die echten Produktionsabhängigkeiten ergänzt. Erwartungen
und Anwendungscode wurden dafür nicht gelockert oder geändert. Die vorhandenen
Codeobjekt-/Datenbankprüfungen wurden lokal auf allen zwölf betreffenden
Branchständen erneut ausgeführt und bestanden (1’210 bis 1’673 Prüfungen).
Alle 17 auf diesen finalen Branch-Heads vorhandenen umfassenden GitHub-
Pipelines sind erfolgreich abgeschlossen. Das Inventar nennt je Branch den
exakten getesteten Head, die Actions-URL und das Ergebnis. Die vier übrigen
Wartungsstände besitzen dort keinen Workflow; ihre gezielten lokalen
Save-/Index-/Rename-Nachweise sind ausdrücklich getrennt aufgeführt.

Die drei historischen audit/-Branches bleiben unveränderte Vergleichsstände.
Die bereits integrierten isolierten Arbeitsstände fix/international-email-20261108,
fix/search-memory-12.9.5.1 und fix/rename-database-lock-waits bleiben als
historische Arbeitsreferenzen erhalten; ihre Änderungen sind in der
kumulativen stabilen Linie und den weitergeführten Funktionsbranches enthalten.
Es werden keine älteren Teilstände als neue Auslieferungsgrundlage ausgegeben.
