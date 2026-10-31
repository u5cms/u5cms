<!-- u5cms-branch-description:start -->
# u5CMS · Arbeitsbranch · korrekte MIME-Typen bei aktiviertem nosniff

**Branch:** `fix/nosniff-script-mime-types`  
**Version in `index.php`:** 12.10.1  
**Basis:** 12.10.1-Support-More-Image-Formats.

Korrekte JavaScript-MIME-Typen für die vier Upload-Einstiegspunkte und die historischen mobilen .css-Scriptdateien.

- Upload-Einstiegspunkte liefern JavaScript mit explizitem Content-Type aus.
- Eine gezielte Regel in r/.htaccess behandelt die historischen mobilen .css-Dateien als JavaScript. nosniff bleibt eingeschaltet.
- Diese Zwischenlösung ist kein Ersatz für die vollständige Codeobjekt-Migration ab 12.11.0.

**Einordnung:** Historischer Arbeitsbranch. Die Anwendungskorrektur ist ab 12.11.0-Code-Resource-Migration enthalten; dort lösen echte Dateiendungen die provisorische MIME-Regel ab.

[Alle Branches, Neuerungen und Integrationsstände](BRANCHES.md)
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

Build a web application with u5CMS without programming skills
It is very easy to build a web application yourself with u5CMS. Let users log in, enter data, edit data again and publish data. Your web application interacts with the u5CMS database without you having to worry about it. To get started, run the Tutorial※ and then the Scenario 2※※.

  ※ https://yuba.ch/r/rendertutorial/rendertutorial_en.pdf
※※ https://yuba.ch/?c=u5eformedit
