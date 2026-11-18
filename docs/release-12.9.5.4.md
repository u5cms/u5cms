# u5CMS 12.9.5.4 – kumulative Abnahme

Vergleichsbasis ist das unveränderte Release **v12.9.5** (`fa6e8982e85d7ca9ec33683ab33d31eb86a31eb9`). Die Tabelle erfasst die gesamte Änderungslinie bis 12.9.5.4, einschliesslich der vorherigen Mail-/Login-Korrekturen und der Patches .1, .2 und .3.

**Urteil:** Im beschriebenen Prüfumfang wurde keine neue Regression festgestellt. Die Anwendung ist zur Veröffentlichung freigegeben. „Hoch“ bezeichnet konkrete Quellen-, Originalvergleichs- und Laufzeitnachweise; es ist keine statistisch gemessene Prozentangabe und keine Zusage für jede individuelle Site-Erweiterung.

Der Anwendungscode des Kandidaten `3f53ddd00e5c1cd3c0d5a1c16750e5436e463945` bestand die [GitHub-Abnahme auf PHP 8.3 und 8.4](https://github.com/u5cms/u5cms/actions/runs/44407572855), einschliesslich **je 92 echter Chromium-Prüfungen**. Der abschliessende Commit ergänzt Berichte und den zusätzlichen SIGKILL-Test, ohne diese Anwendungssourcen zu verändern. Die abschliessenden Checks sind bei [PR 197](https://github.com/u5cms/u5cms/pull/197) sichtbar.

## Änderungen, Nachweise und Einschätzung

| Nr. | Änderung | Verhalten gegenüber 12.9.5 | Nachweis | Einschätzung |
| --- | --- | --- | --- | --- |
| S1 | Suchtreffer und Sichtbarkeit | Beide alten Sucheinstiege laden nur Metadaten, fünf Fallback-Titel und den gewählten Sprachtext. Schutz-, Intranet-, Hidden-, Typ- und Zusatzfilter bleiben erhalten. | 34 echte SQL-Prüfungen, beide Einstiegspunkte; PHP 8.3/8.4 in CI. | Hoch |
| S2 | Suchvorschläge und Speicher | Ein gestreamter Durchlauf ersetzt den vollständigen Vokabularspeicher und rekursive Korrekturläufe. Grenzen betreffen nur Vorschläge: 16 Begriffe, je höchstens 255 Bytes. | 45 Fehler-/Grenz-/Sprachprüfungen; 75 MB Quelltext bei rund 4,2 MB PHP-Spitzenspeicher. | Hoch |
| S3 | HTML-Auszüge und Fehlerfälle | Entity-Grenzen werden gestreamt. Länge, positive/negative Teilbereiche und bisherige Byte-Semantik bleiben erhalten; fehlende Trefferknoten werfen keinen Callback-Fehler. | 55’990 Vergleiche mit dem ursprünglichen HTML-Verfahren; 25,2 MB Belastungsfall. | Hoch |
| I1 | Vollindex und Einzelindex | Vollaufbau in höchstens acht Ressourcen je HTTP-Anfrage, Zwei-Sekunden-Budget zwischen Ressourcen und anschliessender Pause. Einzelindexierung bleibt getrennt. | 12 Batch-Prüfungen; echter Renderer beendet den Vollaufbau in sechs Anfragen; anschliessender Leerlauf und Einzelindex geprüft. | Hoch |
| I2 | Parallelität und Wiederaufnahme | Kernel-Dateisperre verhindert konkurrierende Vollindexer; atomarer Fortschritt und verzögerte Wiederholung erhalten den Arbeitsstand bei Abbruch. Gleichzeitige Quelländerungen werden durch den SQL-Vergleich geschützt. | Batch-Prüfungen; echter konkurrierender Prozess und SIGKILL-Test; unveränderter geprüfter SQL-Quellvergleich aus der .3-Korrektur. | Hoch im geprüften Umfang |
| I3 | Datenbanksperren | Nur die Vollindex-Verbindung begrenzt Metadaten- und InnoDB-Sperrwartezeiten auf höchstens fünf Sekunden. Bereits strengere Serverwerte bleiben durch LEAST erhalten. | Echter HTTP-Aufruf bei gesperrter MyISAM-Tabelle: Abbruch nach etwa 5,03 s; null wartende SQL-Anweisungen und freie Dateisperre. MariaDB 11.4 und 11.8. | Hoch |
| I4 | Suchlaufdatum und Einblendung | Der Stand wird erst nach erfolgreichem Gesamtlauf veröffentlicht; Änderungen während des Laufs bleiben fällig. Die Anzeige benennt diesen Stand und verspricht keinen baldigen Lauf. Fehlende/ungültige Marken ergeben kein Epochendatum. | 13 Prüfungen der tatsächlichen Anzeige mit Start, Teillauf, Wiederaufnahme, Abschluss, weiteren Änderungen und ungültigen Marken. | Hoch |
| R1 | Umbenennen von Objekten | Ressourcen, Referenzen, Metadaten, fünf Sprachen und Formzuordnung werden zusammen nachgeführt. Überschriftenklammern und wörtlicher Text bleiben geschützt; Formularnutzdaten bleiben optional. | 893 Rename-Prüfungen; 114 echte HTTP-Prüfungen und je 92 Chromium-Prüfungen auf PHP 8.3/8.4. | Hoch |
| R2 | Dateien, Exporte und Historien | Sprach-, Album- und Begleitdateien, kontrollierte symbolische Links, Zugriffsschutz, Vorlagen und Historien werden konsistent umbenannt. Vorhandene Suchfelder werden nicht geleert. | Rename-, HTTP- und Chromium-Suite; grosser Originalvergleich bestätigt alle 20 Textspalten und jede der 955 Dateien je Fixture. | Hoch |
| R3 | Fehler, Rechte und Wiederholungen | Geschützte Journale erlauben dieselbe Umbenennung nach Fehlern fortzusetzen. CSRF, Admin-/Archivberechtigungen, Namenskonflikte und idempotente Wiederholungen werden geprüft. Der frühere separate schreibende Historienaufruf liefert 410. | Erfolg, Ablehnung, Unterbrechung und Wiederaufnahme in den 893/114/92-Prüfreihen. | Hoch |
| R4 | Andere Backend-Schreibfunktionen und Backups | Wartungs-, Seiten-, Metadaten- und Dateischreiber koordinieren sich mit Rename. Gleichzeitige Änderungen werden abgewiesen; die vorhandene Recovery-Sicherung reist mit passenden Backups. | HTTP-Prüfung der betroffenen Schreib-Einstiege und Erhalt der Daten bei 409; Rename-/Recovery-Suite; Mail-Anhangsbytes geprüft. | Hoch im geprüften Umfang |
| B1 | Persönliche SELF-Berechtigungen | Die Voreinstellung bleibt user: vollständige Benutzerkennung und bisherige persönliche Zuordnung. Fehlende oder falsche Scope-Werte aktivieren keinen Domainzugriff. | Ownership-Suite mit Standardmodus, fehlerhaftem Scope, Datensatzstatus, Formulargrenzen und fremden Konten. | Hoch |
| B2 | Optionaler Domainmodus und zentrale Verwaltung | Exakte normalisierte Domains teilen SELF-Datensätze; Subdomains bleiben eigenständig. Organisationsübergreifender Zugriff ist auf die explizite Konfigurationsliste und bestätigte Identitäten begrenzt. IDN-Aliase werden nicht zusammengelegt. | Ownership-/Formularsuite mit positiven und negativen Fällen; bestätigter Login und Domainzuordnung; PHP 8.3/8.4. | Hoch; Site-Aufnahmeverfahren muss E-Mail-Adressen verifizieren |
| F1 | Formularspeicherung und Konflikte | Ein fest deklarierter, vertrauenswürdiger Site-Writer kann die Standardspeicherung übernehmen. Formularname und Versionskonflikt werden davor geprüft; gewöhnliche Formulare verwenden weiter den bestehenden Writer. | Dispatch-/Ownership-Prüfungen, exakt ein Writer-Aufruf; Quellprüfung beider Speicherpfade und standardmässig deaktivierter Erweiterung. | Hoch im geprüften Umfang |
| L1 | Login-Zieldomain und Backend-Include | Vier Login-Einstiege vergleichen IDNs nichttransitional. Unterschiedliche Domains mit ß/ss bleiben getrennt; Unicode/Punycode-Paarungen funktionieren. Direkter Backend-Include ist korrigiert. | 40 erlaubte/verbotene Host-/Pfad-/Portfälle pro Runtime; Basic-/Session-Authentisierung über echte HTTP- und Browserpfade. | Hoch |
| M1 | Mailadressen und Zeichencodierung | Versandnormalisierung erhält Local-Part-Schreibweise und verwendet explizites UTS46 für Domains; Entities und historische Browser-Escapes werden kontrolliert dekodiert. Historische Login-Schlüssel behalten ihren eigenen bisherigen Pfad. | 158 Adress-/IDN-/API-Prüfungen; 31 Original-/Kandidaten-MIME-Vergleiche; bestehende Schlüsselimplementierung gesondert erhalten. | Hoch im geprüften Laufzeitprofil |
| M2 | Versand, SMTPUTF8 und Fehler | Gewöhnliche Adressen behalten den bisherigen Kanal. Internationale Local-Parts verwenden den vorhandenen SMTP-Aufbau mit ausgehandeltem SMTPUTF8; keine stillen Adressumschriften oder Erfolgsmeldungen bei Ablehnung. | 100 echte Loopback-SMTP-Prüfungen und 15 native-/Backup-/Fallback-Fälle einschliesslich TLS, Authentisierung, Ablehnung, Cc/Bcc und erhaltenen Nutzdaten. | Hoch für geprüfte Protokollfälle; produktive Mailprovider separat |
| M3 | Einladungen, Wiederherstellung und Benachrichtigungen | Bei Bedarf getrennte Original-Versandadresse in optionalem accounts.email_delivery. Alte Kontoschlüssel bleiben erhalten. Passwortänderung erfolgt erst nach angenommener Nachricht; Einladungsfehler werden angezeigt. | Adress-/Kontofunktionen, originaler Mailvergleich und Versandfehler-Suite; optionale Schema-/Recovery-Verträge aus dem mitgelieferten Mailnachweis geprüft. | Hoch im nachgewiesenen Umfang; keine neue Vollzertifizierung jedes Produktionskontos |
| M4 | Serienmails, Formmails und Backup-Mails | Plaintext/HTML, Empfängernamen, Cc/Bcc, Protokolle und Anhänge bleiben erhalten. Der bestätigte native Doppelversand zählt bei primärem Fehler weiterhin als Erfolg. | 31 semantische Vorher-/Nachher-Vergleiche, 100 SMTP- und 15 Native-/Anhangsprüfungen; keine reale Testmail an Dritte. | Hoch |
| M5 | Lokale Mailbibliotheken | IDN-/Normalizer- und SMTPUTF8-Bibliotheken werden privat gekapselt und vollständig lokal mit Lizenzen ausgeliefert. Vorhandenes vendor/ wird nicht ersetzt. | Alle 24 Datei-Hashes stimmen mit mail/DEPENDENCIES.json überein; Syntax- und Versandtests. | Hoch für Integrität und geprüfte Nutzung |
| D1 | Version, Dokumentation und Abnahme | 12.9.5.4 in index.php; vollständige Vergleichstabelle, Backport-Commits und wiederholbare stabile GitHub-Abnahme. .4 enthält keine feste Novemberpause. | Dateibaum-/Diffprüfung gegen v12.9.5; PHP 8.3/8.4 mit Chromium; 20 nachgeführte Branches samt Head- und Versionsprüfung. | Hoch; Freigabe gilt für den geprüften Quellstand |

## Grosssite-Vergleich mit dem ursprünglichen Rename-Code

Zwei getrennte Einwegdatenbanken verglichen die tatsächlichen ursprünglichen SQL-/Dateifunktionen mit dem vollständigen neuen Rename. Alle 20 Ressourcen-/Historien-Textspalten und die Nutzdaten sämtlicher Dateien blieben erhalten.

| Fixture | Ressourcen | Historieneinträge | Dateien | Alter SQL-/Dateianteil | Neues vollständiges Rename |
| --- | ---: | ---: | ---: | ---: | ---: |
| Wenige Abhängigkeiten | 5’000 | 25’000 | 955 | 20,43 s | 0,41 s |
| Dichte Abhängigkeiten | 2’000 | 5’000 | 955 | 7,36 s | 3,99 s |

Das sind reproduzierbare lokale Messungen mit PHP 8.4.27/MariaDB 11.8.6, keine Produktions-Latenzgarantie. Zusätzlich bestanden alle 108 gegenüber der Basis geänderten PHP-Dateien die Syntaxprüfung und alle 24 mitgelieferten Mailbibliotheks-Dateien ihren Integritätsvergleich.

## Backports und Umfang

Die Datenbank-Wartebegrenzung und die Datumsanzeige sind auf **20 weiteren Entwicklungsbranches** nachgeführt. Je Branch bestanden zwölf Batch- und 13 Anzeigeprüfungen sowie die Syntaxprüfung der drei geänderten Anwendungsdateien. Die veröffentlichten Heads, die Beschränkung auf fünf Pfade und der unveränderte Versions-/Rendererstand wurden von GitHub zurückgelesen. Die [vollständigen Ausgangs- und Folgecommits](release-12.9.5.4-backports.json) sind beigefügt. Historische audit/-Branches bleiben Vergleichsstände. Die vorhandenen umfassenden Entwicklungsbranch-Pipelines sind von dieser gezielten Prüfung zu unterscheiden.

Die lokale Novemberbedingung auf Policies ist kein Release-Bestandteil. Im Release liegen keine Site-Zugangsdaten oder produktiven config.php-Werte. Gegenüber 12.9.5 wurden die SQL-Seeds und der bestehende vendor/-Baum nicht verändert. Individuell gepflegte r/-Ressourcen werden durch diesen Release-Nachweis nicht als ausgeliefert oder geändert erklärt.

## Installation und Rückfall

Das vollständige zusammengehörige Release einschliesslich mail/ und der neuen Hilfsdateien übertragen; vorhandene config.php, Datenbanken, Medien und Site-Ressourcen erhalten. Die persönliche SELF-Zuordnung bleibt ohne Konfigurationsänderung Standard. Für Domainmodus und zentrale Verwaltung gelten die expliziten Einstellungen in [onlymine.md](onlymine.md). Die neue .4-Datenbankbegrenzung benötigt keine Schemaänderung. Die seit der Basis enthaltene Mailerweiterung kann bei Bedarf das optionale Feld accounts.email_delivery anlegen; alter Code ignoriert es.

Für einen Rückfall den vorherigen vollständigen Quellstand gemeinsam wiederherstellen. Ein vorhandenes Rename-Journal samt passender Sicherung erhalten und eine unterbrochene Umbenennung konsistent fortsetzen. Keine Datenbank oder individuellen Quellen aus einem fremden Site-Backup über eine Installation schreiben.

## Grenzen dieses Nachweises

Frisch ausgeführt wurden PHP 8.3/8.4 mit MariaDB 11.4 auf GitHub und PHP 8.4.27/MariaDB 11.8.6 lokal. Historische PHP-7-/MySQL-Installationen, produktive SMTP-Provider, sämtliche individuellen PHP-Ressourcen und ein neues Deployment auf jede Plesk-Installation sind keine mit dieser Abnahme behaupteten Ergebnisse. Die vollständigen älteren Mailnachweise bleiben unter [international-email-2026-11-08.md](international-email-2026-11-08.md) erhalten. Ein Prozessabbruch-Test und ein SQL-Sperrtest belegen die beschriebenen Wiederanlauf-/Sperreigenschaften; das Ressourcenbudget wird zwischen Seiten geprüft, weshalb eine einzelne aufwendige Site-PHP-Ressource länger als zwei Sekunden dauern kann.

## Vollständiges Dateiverzeichnis gegenüber v12.9.5

A = hinzugefügt, M = verändert. Die Gruppen verweisen auf die oben erläuterten Auswirkungen, Prüfnachweise und Einschätzungen. Die Liste umfasst Anwendungsdateien, lokale Abhängigkeiten, Tests und Dokumentation.

| Status | Datei | Änderungsgruppe |
| --- | --- | --- |
| A | `.github/workflows/stable-release.yml` | D1 |
| M | `.gitignore` | D1 |
| A | `BRANCHES.md` | D1 |
| M | `README.md` | D1 |
| M | `addreadrights.inc.php` | B1–B2 |
| M | `alloc.inc.php` | S1–S3 |
| M | `attempt.php` | M1–M5 |
| M | `checkaddself.inc.php` | B1–B2 |
| M | `checkdelself.inc.php` | B1–B2 |
| M | `checkeditself.inc.php` | B1–B2 |
| M | `config.php.sample` | B1–B2 |
| M | `connect.inc.php` | I1–I4 |
| M | `delreadrights.inc.php` | B1–B2 |
| A | `docs/development-status.md` | D1 |
| A | `docs/international-email-2026-11-08.md` | D1 |
| A | `docs/login-host-policy.md` | D1 |
| A | `docs/onlymine.md` | D1 |
| A | `docs/release-12.9.5.4-backports.json` | D1 |
| A | `docs/release-12.9.5.4.md` | D1 |
| A | `docs/rename-backport-12.9.5.2-benchmark.json` | D1 |
| A | `docs/rename-backport-12.9.5.2-branches.json` | D1 |
| A | `docs/rename-backport-12.9.5.2-publication.json` | D1 |
| A | `docs/rename-backport-12.9.5.2.md` | D1 |
| A | `docs/rename.md` | D1 |
| A | `docs/search-memory-2026-11-18.md` | D1 |
| A | `docs/search-memory-backports.json` | D1 |
| A | `docs/search-memory-starting-heads.json` | D1 |
| M | `editformsave.php` | F1 |
| M | `editreadrights.inc.php` | B1–B2 |
| M | `formsave.php` | F1 |
| M | `htaccessandindexer.inc.php` | I1–I4 |
| M | `index.php` | D1 |
| M | `indexer.php` | I1–I4 |
| M | `indexupdate.inc.php` | I1–I4 |
| M | `levenshtein.inc.php` | S1–S3 |
| M | `login.idn.inc.php` | B1–B2 |
| M | `login.inc.php` | B1–B2 |
| M | `loginformsave.inc.php` | B1–B2 |
| M | `loginsave.php` | L1 |
| M | `loginsi.inc.php` | B1–B2 |
| A | `mail/DEPENDENCIES.json` | M1–M5 |
| A | `mail/accounts.php` | M1–M5 |
| A | `mail/addresses.php` | M1–M5 |
| A | `mail/delivery.php` | M1–M5 |
| A | `mail/header.php` | M1–M5 |
| A | `mail/idn/Idn.php` | M1–M5 |
| A | `mail/idn/Info.php` | M1–M5 |
| A | `mail/idn/LICENSE` | M1–M5 |
| A | `mail/idn/Resources/unidata/DisallowedRanges.php` | M1–M5 |
| A | `mail/idn/Resources/unidata/Regex.php` | M1–M5 |
| A | `mail/idn/Resources/unidata/deviation.php` | M1–M5 |
| A | `mail/idn/Resources/unidata/disallowed.php` | M1–M5 |
| A | `mail/idn/Resources/unidata/disallowed_STD3_mapped.php` | M1–M5 |
| A | `mail/idn/Resources/unidata/disallowed_STD3_valid.php` | M1–M5 |
| A | `mail/idn/Resources/unidata/ignored.php` | M1–M5 |
| A | `mail/idn/Resources/unidata/mapped.php` | M1–M5 |
| A | `mail/idn/Resources/unidata/virama.php` | M1–M5 |
| A | `mail/message.php` | M1–M5 |
| A | `mail/normalizer/LICENSE` | M1–M5 |
| A | `mail/normalizer/Normalizer.php` | M1–M5 |
| A | `mail/normalizer/Resources/unidata/canonicalComposition.php` | M1–M5 |
| A | `mail/normalizer/Resources/unidata/canonicalDecomposition.php` | M1–M5 |
| A | `mail/normalizer/Resources/unidata/combiningClass.php` | M1–M5 |
| A | `mail/normalizer/Resources/unidata/compatibilityDecomposition.php` | M1–M5 |
| A | `mail/phpmailer/LICENSE` | M1–M5 |
| A | `mail/phpmailer/SMTPUTF8.md` | M1–M5 |
| A | `mail/phpmailer/src/Exception.php` | M1–M5 |
| A | `mail/phpmailer/src/OAuthTokenProvider.php` | M1–M5 |
| A | `mail/phpmailer/src/PHPMailer.php` | M1–M5 |
| A | `mail/phpmailer/src/SMTP.php` | M1–M5 |
| A | `mail/smtp.php` | M1–M5 |
| M | `myfunctions.inc.php` | M1–M5 |
| A | `onlymine.inc.php` | B1–B2 |
| M | `render.inc.php` | B1–B2 |
| M | `reset2.php` | M1–M5 |
| M | `saml/login.php` | L1 |
| A | `search-legacy-suggestions.inc.php` | S1–S3 |
| M | `sendintranetlogin.php` | M1–M5 |
| A | `tests/browser-runtime.cjs` | D1 |
| A | `tests/fixture-environment.cjs` | D1 |
| A | `tests/fixture_environment.py` | D1 |
| A | `tests/index-update-notice.php` | D1 |
| A | `tests/indexer-batch-regression.php` | D1 |
| A | `tests/indexer-crash-regression.py` | D1 |
| A | `tests/indexer-database-lock-regression.py` | D1 |
| A | `tests/indexer-http-regression.py` | D1 |
| A | `tests/international-email-evidence-2026-11-08.json` | D1 |
| A | `tests/international-email-native.py` | D1 |
| A | `tests/international-email-parity.py` | D1 |
| A | `tests/international-email-smtp.py` | D1 |
| A | `tests/international-email-worker.php` | D1 |
| A | `tests/international-email.php` | D1 |
| A | `tests/login-host-regression.php` | D1 |
| A | `tests/onlymine-domain.php` | D1 |
| A | `tests/rename-benchmark.php` | D1 |
| A | `tests/rename-browser.cjs` | D1 |
| A | `tests/rename-crash-worker.php` | D1 |
| A | `tests/rename-http.py` | D1 |
| A | `tests/rename-regression.php` | D1 |
| A | `tests/rename-web-fixture.php` | D1 |
| A | `tests/rename-web-regression.py` | D1 |
| A | `tests/rename-web-support.php` | D1 |
| A | `tests/search-legacy-database-regression.php` | D1 |
| A | `tests/search-legacy-html-regression.php` | D1 |
| A | `tests/search-legacy-memory-regression.php` | D1 |
| A | `tests/search-memory-branch-verification.json` | D1 |
| M | `u5admin/albumsort.php` | R1–R4 |
| M | `u5admin/archiveunarchive2.php` | R1–R4 |
| M | `u5admin/backupdb.php` | R1–R4 |
| M | `u5admin/backupfiles.php` | R1–R4 |
| M | `u5admin/checkco.php` | R1–R4 |
| M | `u5admin/cleanlog.php` | R1–R4 |
| M | `u5admin/coding2.php` | R1–R4 |
| M | `u5admin/copy2.php` | R1–R4 |
| M | `u5admin/definelanguages2.php` | R1–R4 |
| M | `u5admin/defineloginglobals2.php` | R1–R4 |
| M | `u5admin/delete2.php` | R1–R4 |
| M | `u5admin/deletefile.php` | R1–R4 |
| M | `u5admin/deletefilea.php` | R1–R4 |
| M | `u5admin/invite.php` | M1–M5 |
| M | `u5admin/ishomepage2.php` | R1–R4 |
| M | `u5admin/kill2.php` | R1–R4 |
| M | `u5admin/loginsave.php` | L1 |
| M | `u5admin/mailrenderfunctions.inc.php` | M1–M5 |
| M | `u5admin/mailsend.php` | M1–M5 |
| M | `u5admin/meta2.php` | R1–R4 |
| M | `u5admin/new2.php` | R1–R4 |
| M | `u5admin/phperror.php` | R1–R4 |
| M | `u5admin/reactivateinitialu5cmslayout.php` | R1–R4 |
| M | `u5admin/rename.php` | R1–R4 |
| M | `u5admin/rename2.php` | R1–R4 |
| M | `u5admin/rename3.php` | R1–R4 |
| M | `u5admin/renaming.php` | R1–R4 |
| A | `u5admin/resource-mutation.inc.php` | R1–R4 |
| A | `u5admin/resource-rename-support.inc.php` | R1–R4 |
| A | `u5admin/resource-rename.inc.php` | R1–R4 |
| M | `u5admin/savepage.php` | R1–R4 |
| M | `u5admin/sendfile2.php` | R1–R4 |
| M | `u5admin/sendfilea2.php` | R1–R4 |
| M | `u5admin/u5idn.inc.php` | M1–M5 |
| M | `u5admin/uniquenames.php` | M1–M5 |
| M | `u5admin/updateintranet.php` | R1–R4 |
| M | `u5sys._login.php` | L1 |
| M | `u5sys._search.php` | S1–S3 |
| M | `u5sys._searchsi.php` | S1–S3 |
| M | `zendmail.php` | M1–M5 |
