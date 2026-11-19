# u5CMS branches and changes

As at 19 November 2026. This guide describes published source revisions. Deployment to a particular site is recorded separately.

The latest cumulative development revision covered by this guide is **[12.13.0-Responsive-Design](https://github.com/u5cms/u5cms/tree/12.13.0-Responsive-Design)**. The mainline revision recorded here is 12.9.5.6 on the 12.9.5 series. Each numbered development stage carries forward the preceding functional changes. Later edits to descriptions and version comments are recorded separately.

## Current branches

| Branch | Version in `index.php` | Changes and purpose |
| --- | --- | --- |
| [12.9.5.3-Onlymine](https://github.com/u5cms/u5cms/tree/12.9.5.3-Onlymine) | 12.9.5.3 | [Configurable SELF record permissions](docs/onlymine.md). |
| [main](https://github.com/u5cms/u5cms/tree/main) | 12.9.5.6 | Saving now marks indexing work on the server. Single-resource and full indexing are coordinated, and single-resource SQL lock waits are bounded. Includes bounded database lock waits for Rename and resumable full indexing, a reliable index completion date, memory-efficient search, Rename fixes and optional SELF permissions. |
| [12.9.5.4-Indexer-Final](https://github.com/u5cms/u5cms/tree/12.9.5.4-Indexer-Final) | 12.9.5.4 | Verified release candidate. [Cumulative acceptance results against 12.9.5](docs/release-12.9.5.4.md). |
| [12.9.5.2-Faster-Rename](https://github.com/u5cms/u5cms/tree/12.9.5.2-Faster-Rename) | 12.9.5.2 | Patch series based on 12.9.5.1. Fast, resumable object renaming preserves headings, references, history, files, existing code objects and exports. |
| [12.9.6-Misc-Backend-Optimizations](https://github.com/u5cms/u5cms/tree/12.9.6-Misc-Backend-Optimizations) | 12.9.6 | Keeps the scroll position stable when filtering in the backend and corrects tooltips in object lists. |
| [12.10.0-Multi-Optimizations-by-AI](https://github.com/u5cms/u5cms/tree/12.10.0-Multi-Optimizations-by-AI) | 12.10.0 | Improves saving and conflict handling, speeds up backend filters and makes uploads, ZIP backups and file caches more reliable. |
| [12.10.1-Support-More-Image-Formats](https://github.com/u5cms/u5cms/tree/12.10.1-Support-More-Image-Formats) | 12.10.1 | Adds HEIC, HEIF, HIF, TIF and TIFF uploads with local conversion in the browser. The originals are archived and existing images are protected if conversion fails. |
| [fix/nosniff-script-mime-types](https://github.com/u5cms/u5cms/tree/fix/nosniff-script-mime-types) | 12.10.1 | Corrects JavaScript MIME types for the four upload entry points and legacy mobile scripts served with a `.css` extension. |
| [12.11.0-Code-Resource-Migration](https://github.com/u5cms/u5cms/tree/12.11.0-Code-Resource-Migration) | 12.11.0 | Exports code resources as real `.js`, `.css` and `.html` files, bundles mobile scripts locally and automatically migrates existing installations. Interrupted migrations can resume. |
| [12.11.1-Migration-Hardening](https://github.com/u5cms/u5cms/tree/12.11.1-Migration-Hardening) | 12.11.1 | Improves code-object migration across character encodings, name collisions, customised reset templates, interruptions and database variants. |
| [12.11.2-Backup-Restore-Corrections](https://github.com/u5cms/u5cms/tree/12.11.2-Backup-Restore-Corrections) | 12.11.2 | Makes SQL and file backup and restore more complete and fixes references, code dependencies and editor synchronisation. |
| [12.11.3-Private-File-Cache-Corrections](https://github.com/u5cms/u5cms/tree/12.11.3-Private-File-Cache-Corrections) | 12.11.3 | Returns `401` and `no-store` after failed authentication for private files, preventing misleading conditional `304` responses. |
| [12.11.4-Intranet-Login-Corrections](https://github.com/u5cms/u5cms/tree/12.11.4-Intranet-Login-Corrections) | 12.11.4 | Restores direct access to the intranet password request after code-object migration. |
| [feature/search-phrases-quotes-highlighting](https://github.com/u5cms/u5cms/tree/feature/search-phrases-quotes-highlighting) | 12.11.4 | Adds phrase and special-character searches, diacritic-tolerant matching, multi-word spelling suggestions and improved excerpts. Results highlight matches, expand their containing sections and scroll them into view. |
| [12.12.0-OIDC](https://github.com/u5cms/u5cms/tree/12.12.0-OIDC) | 12.12.0 | Adds optional OIDC federation with account mapping, permissions, registration and logout. This is the first numbered branch to include the new search. |
| [12.12.1-General-Corrections](https://github.com/u5cms/u5cms/tree/12.12.1-General-Corrections) | 12.12.1 | Speeds up object renaming, adds recovery, preserves more references and checks cumulative changes against `main`. |
| [12.12.2-Textarea-Interactions](https://github.com/u5cms/u5cms/tree/12.12.2-Textarea-Interactions) | 12.12.2 | Adds formatting for selected source text, context-sensitive object actions, P-object editing in a new tab and synchronised undo and redo. |
| [12.12.3-PHP9-Compatibility](https://github.com/u5cms/u5cms/tree/12.12.3-PHP9-Compatibility) | 12.12.3 | Removes `tnuoc`, improves type and encoding handling, updates libraries and prepares for known upcoming PHP changes. |
| [12.12.4-Default-Design](https://github.com/u5cms/u5cms/tree/12.12.4-Default-Design) | 12.12.4 | Refines the default typography, navigation, forms and tables. Both SQL seeds, the CSS exports and the TPL reset templates are kept in sync. |
| [12.13.0-Responsive-Design](https://github.com/u5cms/u5cms/tree/12.13.0-Responsive-Design) | 12.13.0 | Cumulative development revision with responsive design and the new search. |

## Integration history and backports

The database lock-wait limit and corrected index completion date display from 12.9.5.4 were also backported to all 20 affected development branches. Each retained its own application revision and version number. [Exact commits before and after each backport](docs/release-12.9.5.4-backports.json).

| Change | First numbered version and scope |
| --- | --- |
| Search memory fix of 18 November | Released on `main` in 12.9.5.1 and adapted to all existing branches. The search feature branch and 12.12.0 onwards keep the new search implementation. Only the legacy helper include required the fix there. [Report](docs/search-memory-2026-11-18.md). |
| Correct MIME types with `nosniff` enabled | From 12.11.0-Code-Resource-Migration, real file extensions replace the temporary mobile MIME rule. Revisions 12.9.6, 12.10.0 and 12.10.1 still lack this application fix. |
| Fast, resumable object renaming | Originally introduced in 12.12.1. On 18 November 2026 it was backported to 12.9.5.2 and the eleven earlier development branches. Five later branches retained their Rename implementation and gained additional write locks for maintenance operations. Branch versions and search functionality were preserved. |
| Typed code objects and automatic migration | From 12.11.0-Code-Resource-Migration. |
| Search with quoted phrases, suggestions, highlighting and scrolling to matches | From 12.12.0-OIDC. The search feature branch was based on the 12.11.4 revision current at the time. |
| Optional OIDC federation | From 12.12.0-OIDC. Disabled until explicitly configured. |
| Textarea formatting and object actions | From 12.12.2-Textarea-Interactions. |
| Revised PHP compatibility implementation | From 12.12.3-PHP9-Compatibility. Requires PHP ≥ 8.2. Tests ran on PHP 8.3 and 8.5. An actual PHP 9 runtime has yet to be certified. |
| Revised mobile tabs | Backported to all nine numbered branches from 12.11.0 to 12.12.3 and carried forward into 12.12.4. The historical working branches predate this backport. |
| Refined default design | From 12.12.4-Default-Design. The two SQL seeds, CSS exports and TPL reset templates are kept in sync. |

The version comments on 12.10.1 and its `nosniff` working branch were corrected to 12.10.1. The search working branch carries its base version, 12.11.4, so it can be distinguished from the subsequent numbered release.

## Branch renames on 31 October 2026

The five previously numeric branch names gained suffixes describing their purpose. The final three changes continued as 12.12.2, 12.12.3 and 12.12.4. The branches were moved using GitHub’s rename function.

| Previous name | Current name | Application revision before the description and version corrections |
| --- | --- | --- |
| `12.11.0` | [12.11.0-Code-Resource-Migration](https://github.com/u5cms/u5cms/tree/12.11.0-Code-Resource-Migration) | [c11d8ff](https://github.com/u5cms/u5cms/commit/c11d8ffb3e37620eda88a32bb987279498869c7b) |
| `12.11.1` | [12.11.1-Migration-Hardening](https://github.com/u5cms/u5cms/tree/12.11.1-Migration-Hardening) | [8cf4154](https://github.com/u5cms/u5cms/commit/8cf4154426035f7e1fce9f36488db9521defcca2) |
| `12.11.2` | [12.11.2-Backup-Restore-Corrections](https://github.com/u5cms/u5cms/tree/12.11.2-Backup-Restore-Corrections) | [cdbece8](https://github.com/u5cms/u5cms/commit/cdbece8b89a66cbbfbee9e6662e20ec1cd1c3602) |
| `12.11.3` | [12.11.3-Private-File-Cache-Corrections](https://github.com/u5cms/u5cms/tree/12.11.3-Private-File-Cache-Corrections) | [b1b12de](https://github.com/u5cms/u5cms/commit/b1b12ded84df1f5c214f50edc96a33de01dd08b7) |
| `12.11.4` | [12.11.4-Intranet-Login-Corrections](https://github.com/u5cms/u5cms/tree/12.11.4-Intranet-Login-Corrections) | [df572a2](https://github.com/u5cms/u5cms/commit/df572a2fceee814c95ebd3e0f1ed776c0b7202a0) |
| `12.13.0-Textarea-Interactions` | [12.12.2-Textarea-Interactions](https://github.com/u5cms/u5cms/tree/12.12.2-Textarea-Interactions) | [a7cc167](https://github.com/u5cms/u5cms/commit/a7cc167d83a9cd4be01da1e6ae5d0f634b401051) |
| `12.14.0-PHP9-Compatibility` | [12.12.3-PHP9-Compatibility](https://github.com/u5cms/u5cms/tree/12.12.3-PHP9-Compatibility) | [cb876c9](https://github.com/u5cms/u5cms/commit/cb876c924af9193a243e01edaacdfc773a4637c7) |
| `12.15.0-Default-Design` | [12.12.4-Default-Design](https://github.com/u5cms/u5cms/tree/12.12.4-Default-Design) | [ec1524f](https://github.com/u5cms/u5cms/commit/ec1524fc6999387e2037b253a7becac46f8a3367) |

Earlier test reports retain their filenames so existing links continue to work. The reports for the three renumbered changes identify the current versions. Their test results refer to the original runs.

## Historical PHP compatibility experiment

The historical `php9_compat` branch was removed from the development series at the maintainer’s request. Its final commit was [b5e1556](https://github.com/u5cms/u5cms/commit/b5e1556ed2d3ab923f39ba7b4bfb005ded593fcc). The later implementation dispensed with its reverse-named substitute functions. See 12.12.3-PHP9-Compatibility and its successors for the maintained implementation.

The Rename lock-wait safeguards and authenticated recovery from 12.9.5.5 were applied to all 18 branches using this Rename implementation. Each retained its version, search and rendering behaviour. [Backport evidence and test results](docs/release-12.9.5.5-backports.json).
