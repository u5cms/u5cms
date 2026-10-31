<!-- u5cms-branch-description:start -->
# u5CMS · Codeobjekt-Migration und lokale mobile Scripts

**Branch:** `12.11.0-Code-Resource-Migration`  
**Version in `index.php`:** 12.11.0  
**Basis:** fix/nosniff-script-mime-types.

Echte .js-, .css- und .html-Exporte, lokal gebündelte mobile Scripts und automatische, wiederaufnehmbare Migration bestehender Installationen.

- Codeobjekte verwenden ! als Endungstrenner: mobile!js wird zu r/mobile.js, base!css zu r/base.css und htmltemplate!html zu r/htmltemplate.html.
- runonce.php migriert Datenbankobjekte, Referenzen, Dateien, Sprachfelder, Historien und Reset-Vorlagen mit Sicherungen und Wiederaufnahme.
- Der mobile Basisscript wird lokal gebündelt. Beide Installations-SQL-Dateien, Exporte und Vorlagen sind auf das neue Modell abgestimmt.

**Nachtrag vom 31. Oktober 2026:** Die überarbeiteten mobilen Laschen für Menü-Schliessen, Desktopansicht und Suche sind in diesem nummerierten Branch enthalten.

**Einordnung:** Erster nummerierter Stand mit nosniff-Korrektur und typisierten Codeobjekten. Funktional in allen nachfolgenden nummerierten Branches enthalten; neue Suche erst ab 12.12.0-OIDC.

[Alle Branches, Neuerungen und Integrationsstände](BRANCHES.md) · [Migration und Aktualisierung](docs/12.11.0-code-assets.md)
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

Build a web application with u5CMS without programming skills
It is very easy to build a web application yourself with u5CMS. Let users log in, enter data, edit data again and publish data. Your web application interacts with the u5CMS database without you having to worry about it. To get started, run the Tutorial※ and then the Scenario 2※※.

  ※ https://yuba.ch/r/rendertutorial/rendertutorial_en.pdf
※※ https://yuba.ch/?c=u5eformedit

Version 12.11.0 introduces typed code resource names such as `mobile!js` and
`base!css`, bundled mobile code, and an automatic migration for existing sites.
See [the update and migration guide](docs/12.11.0-code-assets.md).
