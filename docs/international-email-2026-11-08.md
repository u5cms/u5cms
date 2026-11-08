# Internationale E-Mail-Adressen: IDN und SMTPUTF8

8. November 2026. Autorisiert: Domainkorrektur und internationale Local-Parts in allen 19 bestehenden Branches; anschliessend zusätzliche Tiefenprüfung und automatische Korrektur von Kollateralschäden. Die Änderungen betreffen den Quellcode auf GitHub. Eine produktive CMS-Installation wurde nicht verändert.

## Ergebnis

Die Versandaufbereitung dekodiert die CMS-Darstellung aus Windows-1252 und HTML-Zeichencodes genau einmal. Nur die Domain wird nach UTS46 mit ausdrücklich nichttransitionaler Verarbeitung in ASCII umgewandelt. Damit bleiben `straße.ch` und `strasse.ch` verschiedene Versandziele. Grosses `ẞ` wird nach der aktuellen IDNA-Zuordnung als `ß` behandelt, auch wenn eine ältere Ersatzbibliothek dafür noch `ss` vorsieht.

Der Local-Part bleibt nach dem Dekodieren in seiner Schreibweise erhalten. Umlaute, Kanji und Zeichen ausserhalb der BMP sind möglich; Plus-Zusätze, zulässige Sonderzeichen und zitierte Local-Parts bleiben erhalten. Gültige benannte, dezimale und hexadezimale Entities werden unterstützt. `&Szlig;` ist keine definierte HTML-Entität für ein grosses ẞ und wird von der strengen Versandvalidierung abgewiesen. Das grosse ẞ kann als Zeichen oder `&#7838;` / `&#x1E9E;` vorliegen.

| Eingabe | Technische Empfängeradresse | SMTPUTF8 erforderlich |
| --- | --- | --- |
| `Anna+Privat@example.ch` | `Anna+Privat@example.ch` | Nein |
| `info@straße.ch` | `info@xn--strae-oqa.ch` | Nein |
| `info@STRAẞE.CH` | `info@xn--strae-oqa.ch` | Nein |
| `Müller@straße.ch` | `Müller@xn--strae-oqa.ch` | Ja |
| `漢字@例子.中国` | `漢字@xn--fsqu00a.xn--fiqs8s` | Ja |
| `𠮷@example.ch` | `𠮷@example.ch` | Ja |

Die öffentlichen Versandfunktionen `u5toidn()` und `u5TOidnREMOTEPART()` verwenden ebenfalls die korrigierte Aufbereitung. Die historischen Schlüssel- und Vergleichsfunktionen `u5flatidn()` und `u5flatidnlower()` benutzen ihre gesondert erhaltene bisherige Implementierung. Bestehende Login-Schlüssel, Rechte, Passwörter und Sitzungen werden nicht umgeschrieben. Die Gleichheit der bisherigen Schlüssel wird gegen jeden Ausgangsbranch innerhalb desselben Laufzeitprofils geprüft. Dies ist keine Migration historischer Kennungen zwischen unterschiedlichen PHP-/ICU-Konfigurationen.

## Einladungen und Kontoadressen

Alte IDNA-Versionen können die eingegebene Adresse bereits bei der Bildung des Login-Schlüssels von `straße` zu `strasse` verändern; die vorhandene Kontologik schreibt ausserdem Local-Parts klein. Deshalb erhalten neue Einladungen bei Bedarf eine separate, unverändert geschriebene Versandadresse. Sie wird als numerische HTML-Zeichencodes im optionalen Feld `accounts.email_delivery TEXT NULL` gespeichert. So passen auch Zeichen ausserhalb der BMP durch die bestehende Latin1-/UTF8mb3-Datenbankgrenze. Der Login-Schlüssel bleibt erhalten und wird in der Einladungsmail ausdrücklich genannt.

Das Feld wird bei der ersten Einladung, die eine abweichende Versandadresse benötigt, automatisch ergänzt. Gewöhnliche kleingeschriebene ASCII-Adressen funktionieren auch ohne Schemaänderung. Ist das Feld vorhanden, speichern neue Einladungen ihre Versandadresse darin. Es entstehen keine neuen Tabellen. Fehlt die Berechtigung zum Ergänzen des benötigten Felds, bricht die Einladung vor Kontoanlage und Versand mit einer Fehlermeldung ab. Der Administrator kann das Feld vorab mit `ALTER TABLE accounts ADD COLUMN email_delivery TEXT NULL` anlegen.

Wiederholte Passwortwiederherstellungen, kontobezogene Benachrichtigungen, Intranet-Hinweise für Backend-Konten und der Backup-Versand verwenden diese Versandadresse. Die zusätzliche HTML-Maskierung von GET-Eingaben und die `%u`-Darstellung des alten Einladungsformulars werden an ihrer jeweiligen Eingabegrenze behandelt; UTF-16-Surrogatpaare bleiben zusammen. Ungepaarte Surrogate werden abgewiesen.

Bereits belegte alte Login-Schlüssel werden weiterhin abgewiesen, auch wenn zwei modern unterschiedliche Domains oder Local-Part-Schreibweisen denselben historischen Schlüssel ergeben. Dabei werden weder Eigentümer noch Versandadresse überschrieben. Eine historisch bereits verlorene Originalschreibweise wird nicht erraten: Weicht die angefragte Domain einer Wiederherstellung von der gespeicherten alten Kontoadresse ohne Versandmetadaten ab, wird keine Passwortnachricht verschickt. Solche Altkonten benötigen eine vom Administrator verifizierte Versandadresse.

Eine Einladung legt das neue Konto wie bisher vor dem Versand an. Bei anschliessendem Versandfehler bleibt dieses Konto bestehen; die Oberfläche meldet den Fehler. Bei Passwortwiederherstellung werden Passwort und Reset-Token dagegen erst nach erfolgreicher Übergabe der Nachricht an einen geeigneten Versandkanal geändert.

## Versand und vorhandene Konfiguration

Normale Adressen verwenden weiterhin den vorhandenen Laminas-Versand. Internationale Local-Parts werden über eine privat gekapselte PHPMailer-7.2.0-Komponente versandt. Sie übernimmt die vorhandenen SMTP-Einstellungen für Host, Port, EHLO-Namen, LOGIN/PLAIN/CRAM-MD5 bzw. SMTP ohne Authentisierung und SSL/STARTTLS einschliesslich der vorhandenen Zertifikatsoptionen. Die normale Nutzung eigener Laminas-Verbindungsadapter bleibt erhalten; ein darüber hinausgehender eigener Authentisierungsadapter benötigt vor SMTPUTF8-Nutzung eine passende Anbindung.

Für internationale Local-Parts ist `$usesmtp='yes'` mit einem SMTPUTF8-fähigen Server erforderlich. Die Erweiterung wird nach EHLO und gegebenenfalls erneut nach STARTTLS geprüft. Fehlt sie, erfolgt keine MAIL-/DATA-Übertragung mit einer solchen Adresse, keine Umschrift und kein stilles Ausweichen auf eine andere Empfängeradresse. Die Oberfläche meldet den Fehler; technische Einzelheiten stehen im Serverlog. Ein ungeprüfter PHP-`mail()`-Kanal kann diese Fähigkeit nicht bestätigen und wird deshalb nicht für internationale Local-Parts benutzt. Für gewöhnliche Adressen bleibt dieser Kanal verfügbar.

Der bestehende zusätzliche PHP-Mail-Versand von Passwortnachrichten bleibt für gewöhnliche Adressen erhalten. Eine tatsächlich angenommene, in Empfänger, Betreff und Inhalt passende Zweitnachricht zählt weiterhin als Erfolg, wenn der primäre Versand scheitert. Für internationale Local-Parts entfällt dieser ungeprüfte Zweitkanal. Wenn kein geeigneter Kanal die Passwortnachricht annimmt, wird das Passwort nicht geändert und keine erfolgreiche Zustellung behauptet.

Serienmails behalten Plaintext-/HTML-Alternativen und Bcc im SMTP-Umschlag. Bcc erscheint nicht im übertragenen Nachrichtenkopf. Eine abgewiesene Empfängeradresse bricht den neuen Versandweg vor DATA ab, entsprechend dem bisherigen Laminas-Verhalten. Unterschiedliche Schreibweisen des Local-Parts werden im neuen Versandweg nicht als identische Empfänger zusammengelegt. Backups behalten ihre Anhänge; deren dekodierte Nutzdaten wurden über beide Versandwege geprüft.

## Kompatibilität und Abhängigkeiten

Die bestehenden Mindestversionen der Branches werden beibehalten. Die fünf Branches mit Composer-Mindestversion PHP 8.2 werden nicht nachträglich als PHP-7.4-Anwendungen ausgegeben. Die Versandkomponente wurde auf PHP 7.4.33 und 8.4.23 geprüft, zusätzlich auf PHP 7.4 ohne intl und gesondert ohne intl und ohne mbstring. Eine tatsächliche PHP-9-Zertifizierung ist damit nicht verbunden.

`mail/` wird vollständig mitgeliefert und muss beim Update mitübertragen werden. Es ist kein Composer-Update vorhandener Abhängigkeiten erforderlich. Die Ersatzimplementierung von IDNA und Normalisierung ist privat gekapselt; sie installiert keine globalen IDN-Funktionen oder Konstanten und verändert dadurch keine alten Identitätsprofile. Herkunft, Ausgangscommits und Datei-Hashes stehen in `mail/DEPENDENCIES.json`; die Lizenzen liegen bei. Die Bibliotheken sind lokal verfügbar, ohne Nachladen von einem CDN.

Die privaten Bibliotheksanpassungen sind Namensraum-Kapselung, lokale Normalizer-Konstanten und der Ersatz zweier bedingt verwendeter veralteter OpenSSL-Freigabeaufrufe durch Freigabe der lokalen Referenz. Die anwendungseigenen Adapter ergänzen genaue Empfängeridentität, Abbruch vor DATA und die korrekte Darstellung benannter To-Empfänger. Die bestehenden Anwendungsdateien behalten ihre ursprünglichen Bytes und ihre Windows-1252-/Latin1-Grenzen; die neuen unabhängigen Bibliotheksdateien verwenden ihren eigenen UTF-8-Quelltext.

## Tatsächlich ausgeführte Prüfung

- Alle 19 Ausgangsbranches wurden einzeln fortgeführt, mit ihren jeweiligen bisherigen Änderungen einschliesslich OIDC-Sperren, PHP-Korrekturen und Design-/Suchvarianten.
- 47 Laufzeitprofile: PHP 8.4 auf allen 19 Branches; PHP 7.4 mit und ohne intl auf den 14 dafür geeigneten Branches.
- Pro Profil: 149 Eingabe-/Entity-/IDN-/API-Prüfungen, 100 echte Loopback-SMTP-Prüfungen, 15 Prüfungen von nativem Mail, Backups und Ausweichversand sowie 20 ursprüngliche Identitätsbeobachtungen im Vorher-/Nachher-Vergleich.
- Zusätzlich 1'739 Syntaxprüfungen, 19 ausgeführte Login-Host-Prüfreihen, MIME-/Header-/Inhaltsvergleiche und die bestehenden PHP-/Request-/Quelltextprüfungen der fünf neueren Branches. Nach den letzten Anpassungen wurden die Kompatibilität des eingebundenen Message-Objekts und die MIME-Vergleiche über alle 19 Branches wiederholt.
- 680 Prüfungen mit echten, wegwerfbaren CMS-Installationen und MariaDB 11.8.6: unabhängiger Originalstand zuerst, danach alle 19 Kandidaten. Keine Abweichung der geprüften bestehenden Seiten-, Formular-, Sprach-, Konto- und Rechteabläufe.
- 47 zusätzliche HTTP-Prüfungen der echten Mail-Endpunkte auf main und der kumulativen Entwicklungslinie: Formularbestätigung und Fehleranzeige, Einladung, Passwortzurücksetzen mit Erfolg und Ablehnung, unverändertes Passwort bei Ablehnung, Serienmail, Bcc und gespeichertes Versandprotokoll.
- Erfolgs- und Fehlerfälle umfassen SMTP ohne Authentisierung, LOGIN, PLAIN, CRAM-MD5, STARTTLS, fehlendes SMTPUTF8, abgewiesene und teilweise abgewiesene Empfänger, gewöhnliche Server ohne SMTPUTF8, internationale Absender und Cc sowie erhaltene Anhangsbytes.

Die maschinellen Nachweise stehen in `tests/international-email-evidence-2026-11-08.json`. Das sind lokal ausgeführte Prüfungen, keine behaupteten GitHub-CI-Ergebnisse. Die SMTP-Gegenstellen waren ausschliesslich lokale Testserver; es wurden keine Nachrichten an reale Empfänger verschickt und keine produktiven Provider zertifiziert. Die verbleibende betriebliche Voraussetzung für internationale Local-Parts ist ein entsprechend konfigurierter SMTPUTF8-fähiger Mailserver.

## Update und Rückfall

Die zusammengehörigen geänderten Dateien und den vollständigen Ordner `mail/` aus demselben Branch übertragen. Vorhandene Standortkonfigurationen bleiben bestehen. Bei Bedarf den Korrekturcommit des jeweiligen Branches rückgängig machen. Das optionale Feld `accounts.email_delivery` kann dabei bestehen bleiben; alter Code ignoriert es. Die Feldinhalte für eine spätere erneute Aktualisierung aufbewahren. Ein Rückfall auf alten Versandcode bringt dessen ursprüngliche IDN-/Local-Part-Einschränkungen zurück. Branch-Versionsnummern und die fachlichen Unterschiede zwischen den Branches bleiben erhalten.

Arbeitsstand: Die zusätzliche Tiefenprüfung der Kontenabläufe und aller Branches läuft; die bestehenden Zielbranches werden erst nach bestandener Abschlussprüfung fortgeführt. Die übernommenen Commit-Referenzen sind in der jeweiligen Branch-Historie überprüfbar; eine produktive Installation ist ein eigener Arbeitsschritt.
