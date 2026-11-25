<a id="u5cms-12956--speichern-und-indexierung-zusammen-absichern"></a>
# u5CMS 12.9.5.6 – coordinating saves and indexing

<a id="befund-und-änderung"></a>
## Finding and change

Successfully saved page content could lack the change marker required for a later full index: `savepage.php` and other confirmation pages loaded `lastsave.php` only in a separate browser iframe. Single-resource indexing also bypassed the full-index mutex and its database wait limit. A closed confirmation frame, concurrent index requests and database locks were therefore independent failure cases.

The new real editor POST check reproduces the missing marker on unchanged v12.9.5.5. It deliberately loads no browser subrequests. With the correction, all 26 save/index checks pass.

The shared server-side resource write lock writes the change marker atomically before mutation and again at request shutdown before releasing the lock. Marking therefore no longer depends on loading a browser frame. It is conservative: an operation later rejected can still make an additional full rebuild due. This neither prevents a save nor declares a full index complete prematurely.

Single-resource and full indexing use the same non-blocking index lock. Before database access they also acquire the existing resource/Rename lock, always in that order. An active or interrupted Rename is not indexed as a completed page state. With the source lock occupied, a full rebuild continues after its normal pause; single-resource indexing returns 409 with up to three browser retries, ten seconds apart. If the frame is closed, the full rebuild remains due.

The single-resource index connection also caps metadata/table and InnoDB row-lock waits at five seconds. Stricter server settings remain effective. Socket read limits are set before connecting. Ordinary backend connections are not reconfigured. The existing source-byte comparison remains in place as an additional check.

<a id="einstieg-zustand-und-koordination"></a>
## Entry points, state and coordination

| Entry point | Authentication / effect | Coordination and completion |
| --- | --- | --- |
| `savepage.php`, metadata, files, copying, archiving, deletion and other coordinated backend writers | Existing backend/CSRF/object permissions; source and history changes | Existing source lock; server-side marking before/after. An occupied lock retains the existing 409 response and editor content. |
| `done.php` and `lastsave.php` | Existing backend login; confirmation and compatible marking | The old iframe remains compatible but is no longer required for coordinated saves. `lastsave.php` publishes atomically. |
| `indexer.php?n=…` | Existing index entry; named resource only, five languages | Shared index mutex, then source mutex; five-second SQL lock wait. No full-index completion timestamp or deletion of full-index progress. |
| Automatic/manual full rebuild and continuation | Existing triggers/options and resumption | Shared locks; at most eight resources per request, time budget between resources, pause/progress. Overall date published only on success. |
| Rename | Existing login, CSRF and archive/code permissions | Its own bounded SQL waits from .5; unchanged recovery plan, source lock and accessible recovery overview. |
| Frontend form and status storage | Existing form/SELF/version checks | Existing server-side change marking is retained. Domain mode and site writers are not redefined by .6. |

<a id="kumulative-geschichte-ab-1295-ohne-suffix"></a>
## Cumulative history from unsuffixed 12.9.5

The unchanged comparison sources were resolved through GitHub and a fresh copy: annotated tag v12.9.5 points from tag object `fa6e8982e85d7ca9ec33683ab33d31eb86a31eb9` to the actual comparison commit `9d366253417822c279692767b89dcf1ff0388c5c` (tree `df8329eef819a7f8a2402df5f4fcfb7e606ec087`). Git comparisons resolve the tag to the same commit/tree. The newer comparison commits are v12.9.5.4 `c7062c680deb29fcac70815eea929ad57ee3a5d2` and v12.9.5.5 `8bcb09937999d4ebb51da293b06a113bda0940d8`.

| Change line | Included behaviour and evidence |
| --- | --- |
| Before .1, within the line from the unsuffixed release | Login target-host checks, international delivery addresses and separately preserved historical account keys, local mail libraries and error handling. Address, host, MIME/SMTP/native-mail checks in stable acceptance; all 24 dependency file hashes compared separately. |
| .1 | Memory-efficient legacy search and suggestions; visibility filters and excerpts preserved. 45 memory/boundary checks, 55,990 original excerpt comparisons and SQL checks of both search entry points. |
| .2 including repairs | Faster Rename with heading protection, five languages, references, files, histories, permissions and resumption. Search text on excluded pages is retained; backend writers and backups coordinate. 893 core/database, 114 HTTP and 92 browser checks on the stable line. |
| .3 including repairs | Optional exact-domain SELF mode and explicitly configured central domains; ordinary statuses 1–4; site writer after form/conflict checks. Personal default, empty administrator list and existing account keys preserved. Ownership/dispatch and login checks in CI. |
| .4 | Bounded resumable full indexing, database wait budget, completion date and accurate notice. Real HTTP, SQL-lock, concurrency and process-termination checks. |
| .5 | Rename SQL wait limits, stopping rather than doubling a lock wait, and authenticated recovery without the original result window. 83 additional real SQL/HTTP/recovery checks per stable profile. |
| .6 | Reliable server-side marking of coordinated saves, shared single/full-index mutex, exclusion of unfinished Rename states and bounded single-index waits/retries. Failure reproduced against .5 and 26 new actual save/index checks. |

The complete earlier [change and file table from the unsuffixed release](release-12.9.5.4.md) and [.5 additions](release-12.9.5.5.md) remain traceable. The .6 file list and branch commits are in the associated inventory.

<a id="prüfung-und-branch-erhalt"></a>
## Verification and branch preservation

Fresh local execution used PHP 8.4.24 with native intl/ICU, MariaDB 11.8.6, private copied installations and isolated test databases. Alongside the new save check, passing groups covered full-index completion in six requests, SQL termination without a remaining waiting statement, SIGKILL/resumption, Rename, SELF/writer, search memory/SQL/excerpts, 158 address and 40 host checks. Complete stable CI on PHP 8.3 and 8.4 with MariaDB 11.4 and Chromium first passed at `ac3e5821a37ae9ce8639e89f861c8202f9691676` ([Actions 44481530011](https://github.com/u5cms/u5cms/actions/runs/44481530011)). The final application candidate `88dab36c2ff3c422f4c6421df4652e9656a6622d` passed the same complete PHP 8.3/8.4 acceptance, including Chromium, in [Actions 44483223585](https://github.com/u5cms/u5cms/actions/runs/44483223585). Subsequent inventory/report changes do not change those application sources or stable tests.

All 21 published branch heads and every changed program/test file were independently read back from GitHub and compared byte-for-byte with the checked local preparations. Versions, renderers and existing search/migration files were also compared with their respective starting blobs. The unchanged .5 source archive contains 1,185 files and matches its Git tag completely.

The updates preserve each branch's version, renderer, modern search with its format/pending/running contracts, and existing migration and Rename APIs. On modern-search branches, only the isolated fixture gains the new production helpers; all 96 original expectations remain unchanged. The commit/path/test inventory records actual targeted branch acceptance separately from comprehensive branch pipelines.

<a id="installation-rückfall-und-grenzen"></a>
## Installation, rollback and limits

Transfer the complete matching release, including `index-work.inc.php`; preserve configuration, databases, media and site resources. Before changing version, complete any open Rename with its matching system and recovery source. To roll back, restore the matching previous code together and retain existing recovery plans. There is no new .6 database schema change or mandatory configuration.

Counter still requires `$u5onlyminescope = 'domain';` and `$u5onlymineadmindomains = array('bk.admin.ch');`. Other installations retain their local settings; personal ownership without central domains is the default. The local Policies November condition is not part of the release and must be preserved separately during deployment.

Five seconds bounds one SQL lock wait, rather than every SQL execution, network/filesystem operation or complete dynamic PHP resource. One difficult page can exceed the two-second budget checked between pages. A persistently failing renderer continues to retry with delay and needs repair; it is not silently marked successfully indexed. Automatic full rebuilding still needs eligible visits and normally waits 30 minutes after the last save. It remains due after single-resource retries run out.

The database and filesystem are not one atomic transaction. Uncoordinated external database/file changes and arbitrary site PHP extensions are not blanket-accepted flows. This review is not complete recertification of old PHP/MySQL runtimes or a production SMTP provider. No real test messages were sent to third parties and no production counter data was changed. Production deployment or acceptance is not claimed here.

<a id="branch-umfang-und-prüffortsetzung"></a>
### Branch scope and continued verification

The [complete update inventory](release-12.9.5.6-backports.json) contains 21 starting/result commits, changed paths and explicitly unchanged version/renderer/search/migration blobs. Every branch passed 26 actual save/index checks, twelve batch checks and its matching Rename suite; existing ownership and notice checks also passed. All 96 original index checks passed on the seven modern-search sources. Two older indexer working sources do not yet contain the later notice suite; the inventory records it as absent.

Comprehensive branch pipelines also detected missing new helpers in isolated layout-reset, restore and upload fixtures. Those fixtures gained the real production dependencies. Expectations and application code were neither weakened nor changed for that purpose. Existing code-object/database checks were rerun locally on all twelve affected branch sources and passed (1,210 to 1,673 checks). All 17 comprehensive GitHub pipelines present on those final branch heads completed successfully. The inventory gives each exact tested head, Actions URL and result. The other four maintenance sources have no workflow there; their targeted local save/index/Rename evidence is explicitly separate.

The three historical audit/ branches remain unchanged comparison sources. Already integrated working sources fix/international-email-20261108, fix/search-memory-12.9.5.1 and fix/rename-database-lock-waits remain historical references; their changes are included in the cumulative stable line and maintained feature branches. Older partial sources are not presented as new distribution bases.
