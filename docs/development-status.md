# u5CMS development checkpoint

## Rename SQL wait guard, 19 November 2026

Authorised follow-up: bounded database lock waits for Rename and safe recovery
when the original result window is lost. Target: `feature/search-phrases-quotes-highlighting`.
Exact base: `3a6214ec9e44629613e7c9e850c4ba31f8b4a88d`.
Changed source/test paths: `u5admin/connect.inc.php`, `u5admin/rename2.php`, `u5admin/resource-mutation.inc.php`, `u5admin/renaming.php`, `tests/rename-web-regression.py`, `tests/rename-database-lock-regression.py`, `u5admin/resource-rename-support.inc.php`.
Local PHP 8.4.24/MariaDB 11.8.6 passed 887 Rename, 114 HTTP
and 83 real SQL-lock/recovery assertions. The branch-specific journal namespace
and version are retained. index.php, rendering and search sources are unchanged.
The independent stable patch passed PHP 8.3/8.4 with Chromium in Actions run
44471208093; that is distinct from this branch's own comprehensive pipeline.
No production deployment. Next action: verify the expected-head publication
and the corresponding branch CI result.


## Rename-Nachfuehrung abgeschlossen (18. November 2026)

Branch `feature/search-phrases-quotes-highlighting`, Ausgangs-SHA `81802f83817b24143eec913c8484f78456427c53`, Version 12.11.4.
Autorisierter Umfang und geaenderte Pfade: [Rename-Bericht](rename-backport-12.9.5.2.md)
und zugehoeriger JSON-Nachweis. 881 Rename- und 114 HTTP-Pruefungen bestehen;
Version und Suchdateien sind bytegleich zum Ausgangsstand. Keine produktive
Installation. Veroeffentlichung wird mit erwartetem Remote-Head bewacht; die
abschliessende Remotepruefung bestaetigt den gespeicherten Commit.
Naechste Aktion: keine weitere Implementierung fuer diesen Auftrag.
