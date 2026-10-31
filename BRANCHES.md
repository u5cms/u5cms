# u5CMS – Branches und Neuerungen

Stand: 31. Oktober 2026. Die Beschreibungen beziehen sich auf die veröffentlichten Anwendungsstände; sie belegen keine produktive Installation.

Der aktuelle kumulative Entwicklungsstand ist **[12.12.4-Default-Design](https://github.com/u5cms/u5cms/tree/12.12.4-Default-Design)**. `main` enthält weiterhin den Anwendungsstand 12.9.5. Die nummerierte Entwicklungsreihe führt die vorherigen fachlichen Änderungen fort; spätere Beschreibungs- und Versionskommentare sind davon getrennt zu beurteilen.

## Aktuelle Branches

| Branch | Version in index.php | Neuerungen / Zweck |
| --- | --- | --- |
| [main](https://github.com/u5cms/u5cms/tree/main) | 12.9.5 | u5CMS 12.9.5 mit Cloudflare Turnstile; Ausgangsstand der nachfolgenden Entwicklungsreihe. |
| [12.9.6-Misc-Backend-Optimizations](https://github.com/u5cms/u5cms/tree/12.9.6-Misc-Backend-Optimizations) | 12.9.6 | Stabilere Scrollposition im Backendfilter und bereinigte Tooltips der Objektlisten. |
| [12.10.0-Multi-Optimizations-by-AI](https://github.com/u5cms/u5cms/tree/12.10.0-Multi-Optimizations-by-AI) | 12.10.0 | Zuverlässigeres Speichern und Konflikthandling, schnellere Backendfilter sowie robustere Uploads, ZIP-Backups und Datei-Caches. |
| [12.10.1-Support-More-Image-Formats](https://github.com/u5cms/u5cms/tree/12.10.1-Support-More-Image-Formats) | 12.10.1 | Uploads für HEIC, HEIF, HIF, TIF und TIFF mit lokaler Browserkonvertierung, Originalarchivierung und Schutz bestehender Bilder bei Fehlern. |
| [fix/nosniff-script-mime-types](https://github.com/u5cms/u5cms/tree/fix/nosniff-script-mime-types) | 12.10.1 | Korrekte JavaScript-MIME-Typen für die vier Upload-Einstiegspunkte und die historischen mobilen .css-Scriptdateien. |
| [12.11.0-Code-Resource-Migration](https://github.com/u5cms/u5cms/tree/12.11.0-Code-Resource-Migration) | 12.11.0 | Echte .js-, .css- und .html-Exporte, lokal gebündelte mobile Scripts und automatische, wiederaufnehmbare Migration bestehender Installationen. |
| [12.11.1-Migration-Hardening](https://github.com/u5cms/u5cms/tree/12.11.1-Migration-Hardening) | 12.11.1 | Robustere Codeobjekt-Migration bei Zeichencodierungen, Namenskollisionen, angepassten Reset-Vorlagen, Abbrüchen und Datenbankvarianten. |
| [12.11.2-Backup-Restore-Corrections](https://github.com/u5cms/u5cms/tree/12.11.2-Backup-Restore-Corrections) | 12.11.2 | Vollständigere SQL-/Dateisicherungen und Wiederherstellung sowie Korrekturen bei Referenzen, Codeabhängigkeiten und Editorsynchronisation. |
| [12.11.3-Private-File-Cache-Corrections](https://github.com/u5cms/u5cms/tree/12.11.3-Private-File-Cache-Corrections) | 12.11.3 | Fehlgeschlagene Anmeldung bei privaten Dateien liefert 401 und no-store; verhindert irreführende bedingte 304-Antworten. |
| [12.11.4-Intranet-Login-Corrections](https://github.com/u5cms/u5cms/tree/12.11.4-Intranet-Login-Corrections) | 12.11.4 | Korrigiert den direkten Einstieg in die Intranet-Passwortbestellung nach der Codeobjekt-Umstellung. |
| [feature/search-phrases-quotes-highlighting](https://github.com/u5cms/u5cms/tree/feature/search-phrases-quotes-highlighting) | 12.11.4 | Wortgruppen und Sonderzeichen, Diakritikatoleranz, Mehrwort-Korrekturvorschläge, bessere Auszüge, Hervorhebung und Aufklappen/Hinscrollen. |
| [12.12.0-OIDC](https://github.com/u5cms/u5cms/tree/12.12.0-OIDC) | 12.12.0 | Optionale OIDC-Föderation mit Kontenzuordnung, Berechtigungen, Registrierung und Logout; erstmals neue Suche in einem nummerierten Branch. |
| [12.12.1-General-Corrections](https://github.com/u5cms/u5cms/tree/12.12.1-General-Corrections) | 12.12.1 | Schnellere Objektumbenennung mit Wiederaufnahme, vollständigerem Referenzerhalt und Prüfung der kumulativen Änderungen gegen main. |
| [12.12.2-Textarea-Interactions](https://github.com/u5cms/u5cms/tree/12.12.2-Textarea-Interactions) | 12.12.2 | Formatieren markierter Quelltexte, kontextabhängige Objektaktionen, P-Objekt-Edit im neuen Tab sowie synchronisiertes Undo/Redo. |
| [12.12.3-PHP9-Compatibility](https://github.com/u5cms/u5cms/tree/12.12.3-PHP9-Compatibility) | 12.12.3 | Rückbau von tnuoc, saubere Typ- und Encodingbehandlung, aktualisierte Bibliotheken und Vorbereitung auf bekannte kommende PHP-Änderungen. |
| [12.12.4-Default-Design](https://github.com/u5cms/u5cms/tree/12.12.4-Default-Design) | 12.12.4 | Verfeinertes Standarddesign für Typografie, Navigation, Formulare und Tabellen, synchron in beiden SQL-Seeds, CSS-Exporten und TPL-Reset-Vorlagen. |

## Integration und Nachträge

| Änderung | Erster nummerierter Stand / Umfang |
| --- | --- |
| Korrekte MIME-Typen bei aktiviertem nosniff | Ab 12.11.0-Code-Resource-Migration; dort mit echten Dateiendungen statt der provisorischen Mobile-MIME-Regel. In 12.9.6, 12.10.0 und 12.10.1 fehlt die Anwendungskorrektur weiterhin. |
| Typisierte Codeobjekte und automatische Migration | Ab 12.11.0-Code-Resource-Migration. |
| Neue Suche mit Zitaten, Vorschlägen, Hervorhebung und Hinscrollen | Ab 12.12.0-OIDC. Der Such-Arbeitsbranch basiert auf dem damaligen 12.11.4-Stand. |
| Optionale OIDC-Föderation | Ab 12.12.0-OIDC; bis zur expliziten Konfiguration deaktiviert. |
| Textarea-Formatierung und Objektaktionen | Ab 12.12.2-Textarea-Interactions. |
| Neue PHP-Kompatibilitätslösung | Ab 12.12.3-PHP9-Compatibility; PHP >= 8.2, ausgeführte Prüfungen auf PHP 8.3 und 8.5. Keine tatsächliche PHP-9-Laufzeit zertifiziert. |
| Überarbeitete mobile Laschen | Nachträglich in allen neun nummerierten Branches von 12.11.0 bis 12.12.3 enthalten; in 12.12.4 weitergeführt. Die historischen Arbeitsbranches wurden nicht mit diesem späteren Backport aktualisiert. |
| Dezentes Standarddesign | Ab 12.12.4-Default-Design; beide SQL-Seeds, CSS-Exporte und TPL-Reset-Vorlagen synchron. |

Die Versionskommentare von 12.10.1 und des darauf aufbauenden nosniff-Arbeitsbranches werden auf 12.10.1 berichtigt. Der Such-Arbeitsbranch trägt die Basisversion 12.11.4; Arbeitsbranch und nummerierte Folgeveröffentlichung bleiben unterscheidbar.

## Umbenennungen vom 31. Oktober 2026

Die fünf rein numerischen Branches erhalten ihren fachlichen Schwerpunkt als Namenszusatz. Die letzten drei Erweiterungen werden als 12.12.2, 12.12.3 und 12.12.4 fortgeführt. GitHub hat die Branches mit seiner Umbenennungsfunktion verschoben.

| Bisheriger Name | Aktueller Name | Anwendungsstand vor der Beschreibungs-/Versionskorrektur |
| --- | --- | --- |
| `12.11.0` | [12.11.0-Code-Resource-Migration](https://github.com/u5cms/u5cms/tree/12.11.0-Code-Resource-Migration) | [c11d8ff](https://github.com/u5cms/u5cms/commit/c11d8ffb3e37620eda88a32bb987279498869c7b) |
| `12.11.1` | [12.11.1-Migration-Hardening](https://github.com/u5cms/u5cms/tree/12.11.1-Migration-Hardening) | [8cf4154](https://github.com/u5cms/u5cms/commit/8cf4154426035f7e1fce9f36488db9521defcca2) |
| `12.11.2` | [12.11.2-Backup-Restore-Corrections](https://github.com/u5cms/u5cms/tree/12.11.2-Backup-Restore-Corrections) | [cdbece8](https://github.com/u5cms/u5cms/commit/cdbece8b89a66cbbfbee9e6662e20ec1cd1c3602) |
| `12.11.3` | [12.11.3-Private-File-Cache-Corrections](https://github.com/u5cms/u5cms/tree/12.11.3-Private-File-Cache-Corrections) | [b1b12de](https://github.com/u5cms/u5cms/commit/b1b12ded84df1f5c214f50edc96a33de01dd08b7) |
| `12.11.4` | [12.11.4-Intranet-Login-Corrections](https://github.com/u5cms/u5cms/tree/12.11.4-Intranet-Login-Corrections) | [df572a2](https://github.com/u5cms/u5cms/commit/df572a2fceee814c95ebd3e0f1ed776c0b7202a0) |
| `12.13.0-Textarea-Interactions` | [12.12.2-Textarea-Interactions](https://github.com/u5cms/u5cms/tree/12.12.2-Textarea-Interactions) | [a7cc167](https://github.com/u5cms/u5cms/commit/a7cc167d83a9cd4be01da1e6ae5d0f634b401051) |
| `12.14.0-PHP9-Compatibility` | [12.12.3-PHP9-Compatibility](https://github.com/u5cms/u5cms/tree/12.12.3-PHP9-Compatibility) | [cb876c9](https://github.com/u5cms/u5cms/commit/cb876c924af9193a243e01edaacdfc773a4637c7) |
| `12.15.0-Default-Design` | [12.12.4-Default-Design](https://github.com/u5cms/u5cms/tree/12.12.4-Default-Design) | [ec1524f](https://github.com/u5cms/u5cms/commit/ec1524fc6999387e2037b253a7becac46f8a3367) |

Die früher erstellten Prüfberichte behalten ihre Dateinamen, damit vorhandene Berichtlinks gültig bleiben. Bei den drei umnummerierten Erweiterungen erläutert ein Hinweis im Bericht die aktuelle Versionsbezeichnung. Die früheren Testläufe werden dadurch nicht als neue Testausführung ausgegeben.

## Historischer PHP-Kompatibilitätsversuch

`php9_compat` gehört nicht zur aktuellen Branch-Reihe und wird auf ausdrücklichen Wunsch entfernt. Letzter historischer Commit: [b5e1556](https://github.com/u5cms/u5cms/commit/b5e1556ed2d3ab923f39ba7b4bfb005ded593fcc). Sein Ansatz mit umgekehrt benannten Ersatzfunktionen ist nicht in die neue Lösung übernommen. Massgebend sind 12.12.3-PHP9-Compatibility und dessen Folgestände.
