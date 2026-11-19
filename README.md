# u5CMS 12.9.5.5

Der Vollindex arbeitet in begrenzten, wiederaufnehmbaren Teilaufrufen. Datenbanksperren werden auf seiner eigenen Verbindung zeitlich begrenzt. Die Suchanzeige nennt den Indexstand des letzten erfolgreich abgeschlossenen Gesamtlaufs; laufende oder fehlgeschlagene Versuche setzen diesen Stand nicht vorzeitig hoch.

Auch Rename begrenzt die Datenbank-Sperrwartezeiten auf seiner eigenen Verbindung. Nach einem SQL-Konflikt bleibt ein bereits angelegter Wiederaufnahmeplan erhalten; die gleiche Umbenennung kann kontrolliert fertiggestellt werden.

Enthalten sind die speicherschonende Suche, die korrigierte Objektumbenennung und die optionalen SELF-Berechtigungen aus der 12.9.5-Patchlinie. Bestehende Installationen behalten die persönliche Datensatzzuordnung, solange sie keinen Domainmodus konfigurieren.

[Rename-Sperrschutz und Abnahme 12.9.5.5](docs/release-12.9.5.5.md) · [Kumulative Änderungstabelle gegenüber 12.9.5](docs/release-12.9.5.4.md) · [SELF-Konfiguration](docs/onlymine.md) · [Branches](BRANCHES.md)

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
