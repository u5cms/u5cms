# Unabhängige Vorher-Prüfung des Editor-Speicherns bei laufendem Einzelindex

Stand 19. November 2026. Getestete stabile Version: v12.9.5.6, Commit `ac6e3bf059a365b2912a70429596dbdb6f08604d`. Alle 1’190 Dateipfade und Git-Blob-Hashes des von GitHub heruntergeladenen Tag-ZIP stimmten mit dem Commitbaum überein. PHP 8.4.24, MariaDB 11.8.6, nur isolierte Installationen mit synthetischen Ressourcen.

## Reproduktion

Zwei getrennte PHP-HTTP-Server arbeiten auf derselben Installation und Datenbank. Der erste lädt die authentisierte Backend-Maske und ermittelt den aktuellen Formulartoken. Der zweite führt einen echten benannten Indexaufruf aus. Eine nur in der Testkopie eingefügte Barriere hält ihn unmittelbar nach erfolgreichem Erwerb der Index- und Quellsperre an. Während dieses deterministisch geöffneten Zeitfensters schickt der erste Server den gewöhnlichen Editor-POST mit fünf neuen Sprachfassungen, korrekter Version und gültigem Token. Direkte SQL-Rücklesung überprüft alle fünf Inhalte. Der Test gibt den Index anschliessend frei und prüft seine HTTP-Antwort und den gespeicherten Inhalt. Während des gesamten Versuchs findet kein Rename statt.

**Befund:** Auf allen überprüften Ständen erhielt der Editor HTTP 409, und keine der fünf Änderungen wurde gespeichert. Die Reproduktion benötigt weder einen alten Lockfile-Inhalt noch einen offenen Renameplan. Die .6-eigene Save-/Index-Suite erwartet diese Abweisung im Konkurrenzfall ausdrücklich und kann deshalb trotz dieser Regression grün werden.

In der stabilen Linie nimmt der Indexer in `indexer.php` zuerst den Indexmutex, dann über `u5IndexMutationLock()` die exklusive Sperre in `u5RenameStorageLock()`. Die 20 Backend-Einstiege mit direktem Include von `resource-mutation.inc.php` versuchen denselben exklusiven Lock ohne Warten. Dazu gehören Seiten- und Metadatenspeicher, Code, Dateien, Albumreihenfolge, Kopieren, Neuanlage, Archivierung und Löschen. Die direkte End-to-End-Reproduktion unten betrifft den Seitenspeicher. Bei den weiteren Einstiegspunkten ist der gemeinsame Aufrufer im Code belegt.

## Wiederholung an den veröffentlichten Branchständen

| Branch | Commit | Editor-HTTP | Fünf Inhalte gespeichert | Zusätzliche bestandene Testsuiten¹ |
|---|---|---:|---|---:|
| 12.10.0-Multi-Optimizations-by-AI | `4265baac231b831166c5492234d3884caa6a487a` | 409 | nein | 6 |
| 12.10.1-Support-More-Image-Formats | `8bc41edd58fb3f8453a2d6c027b9d89b5174c9b1` | 409 | nein | 6 |
| 12.11.0-Code-Resource-Migration | `b3b672f3faa98379de52bbb07a1ce003d1494aca` | 409 | nein | 6 |
| 12.11.1-Migration-Hardening | `630c61a0c1a86154e8468d22b1f32e7718e5c57e` | 409 | nein | 6 |
| 12.11.2-Backup-Restore-Corrections | `587cf044a427ea908c8215c623b556c77a52a5e9` | 409 | nein | 6 |
| 12.11.3-Private-File-Cache-Corrections | `a2e277a87ecd85362315846f67ef1b80417a54b2` | 409 | nein | 6 |
| 12.11.4-Intranet-Login-Corrections | `c8d21fec12da35273672cfc020aab003747f18a0` | 409 | nein | 6 |
| 12.12.0-OIDC | `d45be47e321c0824957b5511dc947a5715c70ff2` | 409 | nein | 6 |
| 12.12.1-General-Corrections | `364d078ff3b0f764ed4bba0679361b4d7cfb8890` | 409 | nein | 6 |
| 12.12.2-Textarea-Interactions | `f96da117d52c8b4ba808e7458e1cfc737c094ff3` | 409 | nein | 6 |
| 12.12.3-PHP9-Compatibility | `736d4375d604876259f8460b70d6fac4a32cfbf4` | 409 | nein | 6 |
| 12.12.4-Default-Design | `598f244bffae2296593327f1c23f488f73e71e11` | 409 | nein | 6 |
| 12.13.0-Responsive-Design | `167f6a0058ff5d17c66c929765feacd7c7517dc3` | 409 | nein | 6 |
| 12.9.5.2-Faster-Rename | `1dd21d533727368a26b0810a5a21d4e58409eee6` | 409 | nein | 5 |
| 12.9.5.3-Onlymine | `b13a22783a1fce9eef50ae22bdb43874ba7462ae` | 409 | nein | 6 |
| 12.9.5.4-Indexer-Final | `3cda3d6d8e538565b1cd38ec080dbd39fe5c212e` | 409 | nein | 6 |
| 12.9.5.6-Index-Save-Hardening | `950c4acfce4346ce59b772b7c574d5bda800e0fd` | 409 | nein | 6 |
| 12.9.6-Misc-Backend-Optimizations | `d4d7333bbad47e2aeb9811dcc5469ec34e8917d8` | 409 | nein | 6 |
| feature/search-phrases-quotes-highlighting | `6738d5b2ec5d009794ea0b79a80957bf7c0f8880` | 409 | nein | 6 |
| fix/indexer-db-lock-wait-12952 | `e2cbcd60b01897a0fc9ce01eeee1d74e700f4fa2` | 409 | nein | 4 |
| fix/indexer-db-lock-wait-12953 | `6295b528e5a77fb6b5dad42bce0fbaac947bb8fd` | 409 | nein | 5 |
| fix/nosniff-script-mime-types | `b3e6a18fe0cfb6b289951c2c296755ece3795a71` | 409 | nein | 6 |
| main | `ac6e3bf059a365b2912a70429596dbdb6f08604d` | 409 | nein | 6 |

¹ Je nach Ausstattung des Branches umfassten die separaten Läufe ONLYMINE, Login-Zielhosts, Index-Teilaufrufe, Indexdatumsanzeige, historische Datenbanksuche bzw. neuere Indexierung und internationale E-Mail-Adressen. Die Anzahl bezeichnet separat erfolgreich ausgeführte Suite-Dateien.

## Ergänzende Befunde am stabilen Tag

Die 26 bestehenden Save-/Indexprüfungen, 893 Rename-Kernprüfungen, 83 Rename-Datenbanksperrprüfungen und 114 Rename-HTTP-Prüfungen bestanden. Auch die Indexer-Prüfungen für Prozessabbruch, HTTP und Datenbanksperren bestanden. Der lokale Chromium-Prozess wurde vor Öffnung einer Seite durch die Einschränkung für Unix-Sockets beendet. Eine eigene Browserabnahme ist hier offen.

Diese Ergebnisse sind der Vorher-Stand. Die .7-Abnahme muss denselben Editor-POST bei aktivem Index erfolgreich ausführen und danach die fünf neuen Inhalte und den Suchindex prüfen. Zusätzlich offen sind unter anderem die Abnahme paralleler Schreiber, tatsächlicher und unterbrochener Renames, Upgrade, Statusanzeigen sowie die fachliche Bewertung branchspezifisch abweichender Implementierungen seit v12.9.5.

