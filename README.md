# u5CMS 12.9.5.8

Ordinary saves remain available while indexing and backups run. A save marks pending index work on the server. Single-resource and full indexing coordinate through the same lock, respect pending Rename operations and bound database lock waits.

Full indexing runs in resumable batches. The search page shows the date of the last successfully completed full index. An interrupted or failed attempt leaves that date intact. Rename also bounds the lock wait on its own database connection. After a database conflict, the existing recovery plan allows the same operation to finish.

The stable release includes memory-efficient search, corrected resource renaming and configurable SELF permissions. Existing installations continue to associate records with the individual user unless their administrator enables domain-based ownership.

[Media access provisioning in 12.9.5.8](docs/release-12.9.5.8.md) · [Non-blocking saves and fast Rename in 12.9.5.7](docs/release-12.9.5.7.md) · [Save and index coordination in 12.9.5.6](docs/release-12.9.5.6.md) · [Rename lock handling in 12.9.5.5](docs/release-12.9.5.5.md) · [Cumulative changes since 12.9.5](docs/release-12.9.5.4.md) · [SELF permissions](docs/onlymine.md) · [Branch guide](BRANCHES.md)

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
