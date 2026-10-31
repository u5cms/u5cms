<!-- u5cms-branch-description:start -->
# u5CMS · Arbeitsbranch · Suche, Zitate, Vorschläge und Hervorhebung

**Branch:** `feature/search-phrases-quotes-highlighting`  
**Version in `index.php`:** 12.11.4  
**Basis:** 12.11.4 vor dem späteren Mobile-Backport; Such-Abschlusscommit 2f5ce37c18b017375dfc31dab6086f325bd836a2.

Wortgruppen und Sonderzeichen, Diakritikatoleranz, Mehrwort-Korrekturvorschläge, bessere Auszüge, Hervorhebung und Aufklappen/Hinscrollen.

- Gemeinsame Suchnormalisierung für Server und Browser; wörtliche Behandlung von Zitaten, Satzzeichen und URL-Transport.
- Explizite Teiltreffer und überprüfte Korrekturvorschläge auch für Mehrwort- und zitierte Anfragen; die eingegebene Anfrage wird erst auf Auswahl ersetzt.
- Hervorhebung über Inline-Markup hinweg, Aufklappen verschachtelter Container und Hinscrollen zu den Fundstellen auf Desktop und Mobile.

**Einordnung:** Historischer Arbeitsbranch. Die Suchüberarbeitung ist ab 12.12.0-OIDC in der nummerierten Reihe enthalten. Zusätzliche spätere Suchvorschläge sind weiterhin eine separate, zurückgestellte Erweiterung.

[Alle Branches, Neuerungen und Integrationsstände](BRANCHES.md) · [Suchverhalten und Prüfung](docs/search.md)
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

Version 12.11.4 includes typed code resource names such as `mobile!js` and
`base!css`, bundled mobile code, and an automatic migration for existing sites.
See [the update and migration guide](docs/12.11.0-code-assets.md),
[the 12.11.1 migration audit](docs/12.11.1-audit.md),
[the 12.11.2 main comparison and restore report](docs/12.11.2-audit.md), and
[the 12.11.3 process-abort and web-server audit](docs/12.11.3-audit.md), and
[the final main comparison and entry-point correction](docs/12.11.4-main-audit.md).
