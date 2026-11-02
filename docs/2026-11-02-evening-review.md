# Erneute u5CMS-Evolutionsprüfung – Abend, 2. November 2026

Status: in Arbeit. Dieser neue Auftrag ist keine Wiederverwendung früherer Prüfergebnisse.

Autorisierung: «Mach es sehr gründlich und inspiriert.» Gesamte Evolution gegen ursprüngliches und aktuelles Main prüfen, bestätigte Regressionen in allen betroffenen Entwicklungsbranches minimal korrigieren und nachprüfen. Main und produktive Installationen sind nicht zur Änderung freigegeben.

Ausgang: 16 veröffentlichte Branches am 2. November erneut über die GitHub-API verifiziert; aktuelle Main-Referenz `78cadbf87b7307203b8bd4ff96929b306af7d982`, ursprüngliche Funktionsreferenz `9d366253417822c279692767b89dcf1ff0388c5c`, kumulativer Ausgang `b915e358d3d0319524f49116e5c1306c38521ab9` (12.12.4-Default-Design). Eigene isolierte Arbeitskopie; bestehende Arbeitskopien werden nicht verändert.

Prüfauftrag: ursprüngliche Konfigurations- und Erweiterungsverträge, alle kumulativen Anwendungsänderungen, vollständige Altbestandsabläufe, Migration/Umbenennen/Publikation/Backup/Restore, Rechte und Fehlerfolgen. Frühere Tests dienen als Hilfsmittel; neue unabhängige Fragestellungen und tatsächliche Ausführungen werden separat dokumentiert.

Letzter Schritt: AGENTS.md, Entwicklungscheckpoint, frühere Berichte und aktuelle Remote-Refs gelesen und verglichen. Noch kein neuer Anwendungsbefund bestätigt. Dieser Commit enthält nur den Wiederaufnahmecheckpoint.

## Zwischenstand: weitere Regression bestätigt und Korrektur in Prüfung

Die neue Original-/Branch-Matrix hat 2’176 echte Ansichtenaufrufe ausgeführt,
einschliesslich leerer Datenbestände und unterschiedlicher Redaktionsrechte.
Erwartete Inhalte, vollständige Seitenabschlüsse und Datenerhalt stimmen im
ausgeführten Vergleich überein. Der ursprüngliche Bestand bleibt die Referenz;
direkt aufgerufene Include-Dateien ohne ihren Aufrufer sind keine gültigen
Anwendungsabläufe und werden nicht als bestandene fachliche Funktionen gezählt.

Eine unabhängige HTTPS-Konfigurationsprüfung hat eine weitere Regression in
allen 14 Entwicklungsbranches ab 12.10.0 bestätigt. Rückgaben `1`, `"1"` und
`"on"`, die das ursprüngliche Frontend akzeptiert, wurden durch eine reine
`is_bool`-Prüfung verworfen. 498 direkte Prüfungen ergeben 105 Abweichungen in
35 vorhandenen Bootstrap-Blöcken. Der echte HTTP-Nachweis bestätigt 84 falsche
Weiterleitungen bei aktivierter HTTPS-Erzwingung; ursprüngliches und aktuelles
Main sowie 12.9.6 zeigen sie nicht. Bei unverändertem Detektor kann die
Weiterleitung auch nach der Rückkehr über HTTPS erneut erfolgen.

Die Kandidaten ändern je betroffenem Bootstrap eine einzige ASCII-Zeile:
Nicht-null-Rückgaben werden wie in Main boolesch ausgewertet; ohne Rückgabe
bleibt die bereits unterstützte Zuweisung erhalten. Übrige Quellbytes bleiben
nachweislich identisch. Die 498 direkten Nachprüfungen bestehen. Echte
HTTP-Nachprüfung über alle Branches und die notwendige Integrationswiederholung
laufen; eine abgeschlossene Korrektur wird hier noch nicht behauptet.

Die neue Hauptreferenzprüfung mit Browserübermittlungen besteht ebenfalls über
alle 16 Branches. Die ausgeführten Migration-/Rename-/Mobile-Gruppen bestehen
mit 1’671 / 886 / 69 Prüfungen. Der erste kumulative Browserlauf bestand alle
159 / 51 / 98 / 92 / 504 Anwendungsassertionen, scheiterte danach aber beim
Aufräumen der Datenbank an einer liegen gebliebenen InnoDB-Tablespace-Datei.
Dieser Prozess ist als fehlgeschlagen gespeichert. Der vollständige Lauf wird
mit gemeinsamem InnoDB-Tablespace wiederholt; die Daten-/Schemaassertionen
bleiben unverändert. Suche, lokales OIDC/TLS, Bild-Upload und beide Seed-Designs
bestanden in diesem neuen Lauf.

Geänderte Anwendungspfade des kumulativen Kandidaten: `connect.inc.php`,
`u5admin/connect.inc.php`, `saml/login.php`, `oidc/bootstrap.inc.php`.
Neue Nachweise: `tests/evolution-route-reference.py`,
`tests/https-evaluation-reference.py`, `tests/https-bootstrap-http.py`,
`tests/https-detection-regression.php`, `docs/https-detection.md`.

Nächster Schritt: echte HTTPS-Nachprüfung und Integration abschliessen, dann
getestete Einzeilenkorrekturen mit erwarteten Ausgangs-Heads in allen 14
betroffenen Branches veröffentlichen und die Remote-Bytes nachprüfen. Main
und produktive Installationen bleiben unverändert.
