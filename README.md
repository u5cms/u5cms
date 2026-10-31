<!-- u5cms-branch-description:start -->
# u5CMS · Explizite PHP-Typen, Encodinggrenzen und aktualisierte Abhängigkeiten

**Branch:** `12.12.3-PHP9-Compatibility`  
**Version in `index.php`:** 12.12.3  
**Basis:** Abgeschlossener Textarea-Anwendungsstand c0748dbedb6200c007438555ca76999aa0d97cea.

Rückbau von tnuoc, saubere Typ- und Encodingbehandlung, aktualisierte Bibliotheken und Vorbereitung auf bekannte kommende PHP-Änderungen.

- Verwendet native Funktionen mit passenden Datentypen statt pauschaler umgekehrt benannter Ersatzfunktionen; tnuoc wird zurückgebaut.
- Behandelt optionale Texte, SQL NULL und Zeichencodierungen an ihren tatsächlichen Übergängen und ersetzt entfallende Encoding-/Bild-/CSV-Operationen.
- Aktualisiert Abhängigkeiten und native Interface-Rückgabetypen reproduzierbar; korrigiert weitere Download-, cURL- und SAML-Cookie-Pfade.

**Nachtrag vom 31. Oktober 2026:** Die überarbeiteten mobilen Laschen für Menü-Schliessen, Desktopansicht und Suche sind in diesem nummerierten Branch enthalten.

**Einordnung:** Neue Kompatibilitätslösung mit PHP >= 8.2. Ausgeführt und geprüft auf PHP 8.3 und 8.5; keine Zertifizierung einer tatsächlichen PHP-9-Laufzeit. Der historische Branch php9_compat wurde entfernt; sein Ansatz ist nicht übernommen.

[Alle Branches, Neuerungen und Integrationsstände](BRANCHES.md) · [Umfang, Laufzeiten und Prüfung](docs/12.14.0-php9.md)
[Gemeinsame Login-Domain- und Pfadkorrektur](docs/login-host-policy.md)
<!-- u5cms-branch-description:end -->

---

https://yuba.ch/u5cms: A small, handy content management system for medium-sized one- to five-language websites, conference / congress / submission administration, review processes, personalized serial mails, PayPal payments and online surveys based on PHP and MariaDB/MySQL and Apache (or IIS with htaccess-Simulation).
u5CMS features
Total 11 images (click the image)
u5CMS features
The out of the box template automatically renders as web app on mobile devices with better usability than typical responsive solutions.
Since version 6, full IDN support, serial mail engine compliant with Email Address Internationalization (EAI).

u5CMS is especially suitable for audio and video embedding (both local data and Youtube channels) and form embedding as well as analyses of surveys. The data is displayed as a chart and is available both as an HTML table and as CSV export for Excel and SPSS. The pages created including forms work in all current browsers and are completely barrier-free. The forms have been successfully tested on either blind or visually impaired subjects.

u5CMS achieves an optimized Google Ranking by means of automatically generated title tags, and features social networking functions (link sharing on Facebook and Twitter; Like and Google+ buttons).

Since u5CMS version 10.0.0 compatibility with PHP 8 is given. It supports Unicode and has a built-in search engine and intranet with user management.

Version 12.12.0 adds optional OpenID Connect federation alongside the existing SAML integration. See [OIDC setup, identity mapping, operation and rollback](docs/oidc.md) and the [validation report](docs/12.12.0-oidc-validation.md). OIDC is disabled until explicitly configured.

Version 12.12.1 replaces ordinary resource renaming with a fast, resumable workflow, preserves title brackets and all configured dependency policies, and audits the cumulative changes against production main. See [rename operation and recovery](docs/rename.md) and [the complete release audit](docs/12.12.1-general-corrections.md).

Build a web application with u5CMS without programming skills
It is very easy to build a web application yourself with u5CMS. Let users log in, enter data, edit data again and publish data. Your web application interacts with the u5CMS database without you having to worry about it. To get started, run the Tutorial※ and then the Scenario 2※※.

  ※ https://yuba.ch/r/rendertutorial/rendertutorial_en.pdf
※※ https://yuba.ch/?c=u5eformedit

Version 12.11.4 includes typed code resource names such as `mobile!js` and
`base!css`, bundled mobile code, and an automatic migration for existing sites.
See [the update and migration guide](docs/12.11.0-code-assets.md),
[the 12.11.1 migration audit](docs/12.11.1-audit.md),
[the 12.11.2 main comparison and restore report](docs/12.11.2-audit.md), and
[the 12.11.3 process-abort and web-server audit](docs/12.11.3-audit.md), and
[the final main comparison and entry-point correction](docs/12.11.4-main-audit.md).
Development continuity: read [the saved implementation status, open boundaries and recovery protocol](docs/development-status.md) before continuing work from another chat.

Version 12.12.2 adds source-text formatting and object actions in both page-editor panes and all five language slots, including P-object Edit in a new tab, guarded target/label replacement, and synchronized undo/redo. See [operation and validation](docs/12.13.0-textarea.md).

Version 12.12.3 removes the remaining reverse-named count workaround, establishes explicit text/encoding contracts and updates the bundled libraries with reproducible native interface return types. It requires PHP 8.2 or newer; executed compatibility checks cover PHP 8.3 and 8.5. See [scope, validation, dependency installation and rollback](docs/12.14.0-php9.md).
