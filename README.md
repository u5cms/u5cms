<!-- u5cms-branch-description:start -->
# u5CMS · Echtes Responsive Design

**Branch:** `12.13.0-Responsive-Design`

**Version in `index.php`:** 12.13.0

**Basis:** `12.12.4-Default-Design`, Commit `3b9b8054473d41ae3bdd046257db154770942987`.

Das Standardtemplate passt sich per CSS an die verfügbare Breite an. Navigation,
Formulare und Medien bleiben beim Verkleinern und Vergrössern dieselben Elemente;
die bisherige JavaScript-Umwandlung der Seite entfällt im neuen Template.

- Neuer Renderaufbau mit getrennten Stufen für Syntax, Ressourcen und Ausgabe;
  die öffentliche Funktion `render()` bleibt erhalten.
- Bestehende Datenbanken, Syntax und `dat`-Befehle bleiben kompatibel. Keine neue
  Datenbankstruktur für das responsive Design.
- Standard- und Conference-Neuinstallationen verwenden das responsive Template.
  Bestehende individuelle Templates werden erhalten und können gezielt umgestellt
  werden. Ihre bisherigen mobilen Scripts bleiben als Kompatibilitätsbestand verfügbar.
- CSS bestimmt das Layout auch ohne JavaScript. Das optionale Script bedient
  ausschliesslich das auf schmalen Ansichten einklappbare Navigationsmenü.

**Einordnung:** Neuer kumulativer Entwicklungsbranch; keine produktive Installation.

[Architektur, Übernahme und Rückweg](docs/responsive-design.md) ·
[Tiefenprüfung und Korrekturen](docs/responsive-deep-review.md) ·
[Alle Branches](BRANCHES.md) · [Entwicklungsstatus](docs/development-status.md)
<!-- u5cms-branch-description:end -->

---

https://yuba.ch/u5cms: A small, handy content management system for medium-sized one- to five-language websites, conference / congress / submission administration, review processes, personalized serial mails, PayPal payments and online surveys based on PHP and MariaDB/MySQL and Apache (or IIS with htaccess-Simulation).
u5CMS features
Total 11 images (click the image)
u5CMS features
From version 12.13.0, the default template uses a fluid CSS layout at every viewport width. Older custom templates retain their existing mobile behaviour until deliberately migrated.
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
