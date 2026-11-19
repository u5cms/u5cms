# Temporary conflicts during resource changes

The historical correction of 2 November 2026 applied to ten branches from 12.11.1 onwards, including the search working branch. Several operations that already used file locks returned HTTP 500 during a conflicting update. A dedicated exception now distinguishes the expected conflict. The affected entry points return HTTP 409 with an explanation and a two-second Retry-After header. Other errors still take their own failure paths.

The changes covered `codeassets.inc.php`, `new2.php`, `copy2.php`, `delete2.php`, `archiveunarchive2.php`, `coding2.php` and `rename2.php`. The six earlier affected branches also received `resource-mutation.inc.php`. The targeted edits preserved surrounding source bytes, database schemas and existing records.

The historical test made 100 blocked HTTP writes across the ten branches and checked that resources, history and files were intact. It resumed four interrupted renames and exercised duplicate requests and stale editors. Each corrected branch subsequently passed 51 frontend and 98 backend browser checks. These results describe the affected versions at that time. Current save and Rename behaviour is documented in the release notes for the installed version.

The disposable installations used PHP 8.3.6, MariaDB 10.11.14 and Chrome Headless Shell 156.0.8078.80. The historical verification covered published source and did not involve a production installation or a merge. [Report, shared tools and before-and-after evidence](https://github.com/u5cms/u5cms/blob/da687f5c58c560da0fcbdfa9cce357084ba3dba7/docs/2026-11-02-evolution-review.md)
