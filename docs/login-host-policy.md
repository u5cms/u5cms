# Gemeinsame Korrektur der Login-Domainprüfung

31. Oktober 2026. Diese Korrektur wird in allen 16 bestehenden Branches nachgeführt. Sie ändert weder die Versionsnummer noch das Schema oder gespeicherte Benutzerkennungen.

Die vier vorhandenen Login-Prüfungen benutzten den transitionalen IDNA-Modus. Dieser setzt zum Beispiel `faß.example` mit `fass.example` gleich, während die gültige ASCII-Darstellung der ersten Domain `xn--fa-hia.example` lautet. Dadurch konnte ein fremder Domainname akzeptiert und eine gültige Unicode-/Punycode-Paarung abgewiesen werden.

Die Prüfungen in `loginsave.php`, `u5admin/loginsave.php`, `saml/login.php` und `u5sys._login.php` verwenden nun den nichttransitionalen ASCII-Modus. Bestehende Pfad-, Port- und Protokollprüfungen bleiben bestehen. Die bisherige CMS-Normalisierung gespeicherter E-Mail-/Benutzerkennungen ist ein anderer Vertrag und wird dadurch nicht umgestellt. Ohne verfügbare IDN-API bleibt der bisherige direkte Hostvergleich erhalten.

Der direkte Backend-Endpunkt hatte zusätzlich einen falschen relativen Include-Pfad (`u5admin/u5idn.inc.php` aus dem Verzeichnis `u5admin`). Der Pfad ist jetzt relativ zu seiner eigenen Quelldatei eindeutig angegeben. Dieser Fehler wurde im echten HTTP-Endpunkt reproduziert und nicht durch Abschwächen der Domainprüfung umgangen.

`tests/login-host-regression.php` führt die tatsächlichen vier Prüfblöcke ohne Datenbank-/Anmelde-Nebenwirkungen aus. Zehn unabhängige erlaubte und verbotene Fälle pro Datei prüfen normale Domains, Gross-/Kleinschreibung, verschiedene IDN-Domains, die gültige Unicode-/Punycode-Paarung, relative Pfade, Protokoll-relative URLs und abweichende Ports.

Vor der Korrektur scheiterten 16 von 40 Fällen am kumulierten Prüfstand. Die vorbereiteten Quellstände aller 16 Branches bestehen jeweils 40 Fälle mit PHP 8.3.6 und nativer intl-Erweiterung (640 Fälle und 64 Syntaxprüfungen). Der neue Abhängigkeitsstand besteht die 40 Fälle auch mit den mitgelieferten Polyfills auf PHP 8.3.6 und 8.5.12. Zusätzliche HTTP-Prüfungen im kumulierten Branch benutzen die echten Endpunkte, unterbinden das Folgen von Redirects und kontaktieren keine fremden Domains.

Es handelt sich um lokale Tests in Einweginstallationen, nicht um GitHub-CI oder eine Änderung einer produktiven CMS-Installation. Der Originalstand von main bleibt als Commit `9d366253417822c279692767b89dcf1ff0388c5c` für den Evolutionsvergleich verfügbar.
