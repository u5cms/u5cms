# u5CMS 12.9.5.5 – bounded Rename database waits

This patch adds the database lock-wait protection already used by the full
indexer to the actual backend Rename request. The comparison base is
v12.9.5.4 (`c7062c680deb29fcac70815eea929ad57ee3a5d2`). The existing cumulative
[change and acceptance table against v12.9.5](release-12.9.5.4.md) remains
applicable, with the additional Rename protection described below.

## Change and functional evidence

| Change | Result | Evidence |
| --- | --- | --- |
| Rename connection | Metadata/table and InnoDB row-lock waits are capped at five seconds before CMS reads. Stricter server limits remain effective. Other backend connections retain their existing policy. | Real authenticated connection probes with 20-second defaults and one-second stricter limits. |
| Optional history index | A lock timeout, deadlock or cancelled index-creation statement stops the operation instead of entering another blocked query. Lack of ALTER privileges retains the existing fallback. | A real MyISAM history-table blocker initially exposed two consecutive waits; after the correction the request returned after 5.01 seconds. |
| Failure before mutation | A blocked history-table operation returns failure and offers the same Rename retry. No waiting SQL or process mutex remains. | Real HTTP request, SQL process-list inspection, independent operating-system lock acquisition, unchanged source/history/form rows and successful retry. |
| Failure after file publication | A locked InnoDB source row times out after files have already moved. The immutable pending plan remains and protects coordinated writers. | Real HTTP request returned after 5.02 seconds; a writer returned 409; released SQL blocker followed by the exact original retry completed without duplicate revision changes. |
| Recovery without the original window | The authenticated recovery overview lists saved ordinary Renames and submits the original names/policy through the existing CSRF- and permission-checked POST entry. Blocked writers link to this overview. GET does not mutate the operation. | A partially applied InnoDB-blocked Rename was resumed using the overview token; unauthenticated access and tokenless mutation were denied; the overview cleared after completion. |
| Strict server policy | A one-second server limit is retained. | Real locked-table request returned after 1.01 seconds, followed by successful retry. |
| Existing behaviour | Link policies, five languages, headings, file bytes, histories, rights, wait/result window and crash recovery remain covered by the existing suites. Indexing is unchanged. | Local 893 Rename checks, 114 HTTP checks, full HTTP index completing in six requests, and the index database-lock regression all passed. |

The new regression has 83 assertions and is run by the existing web-suite entry
point in both HTTP and Chromium acceptance profiles. It restores the original
global wait settings in its disposable database server even if a check fails.
It must never run against a production database.

## Meaning of the protection

The five-second limit applies to one SQL lock wait. It is not a whole-operation
deadline for query execution, network or filesystem I/O. Actual Rename remains
one coordinated request, unlike the full indexer's separate batches.

A terminated process leaves no owned kernel mutex. A partly applied Rename
keeps a durable pending plan and deliberately blocks other coordinated backend
writers. Resolve the underlying cause and resume that same Rename. Expiring
this barrier by age would allow writes into a partly applied operation.

Database and filesystem changes are not one atomic transaction. Frontend
readers can observe a transient state; this patch does not introduce a global
read barrier. The result window reports success only after the complete plan
has been applied; caught failures retain the Retry rename action. If the original result window is lost, open the authenticated unfinished-Rename
overview linked from blocked writers and retry the recorded operation there.

## Acceptance and installation

Local evidence: PHP 8.4.24, MariaDB 11.8.6, disposable TCP database and real
authenticated PHP HTTP requests. The complete stable acceptance passed on **PHP 8.3 and 8.4** in
[Actions run 44471208093](https://github.com/u5cms/u5cms/actions/runs/44471208093),
including the existing 92-check Chromium suite and the new 83-check SQL/recovery
suite in each profile. Its tested application/test commit is
`a2e201ea4798d2708b62b1bbd17cad7d87a2c6b2`; the closing additions are reporting
and branch inventories, with those application/test blobs unchanged.

The protection was carried to **18 maintained branches**. Each branch passed
its existing Rename suite (887–893 checks, depending on its architecture),
114 existing HTTP checks and all 83 new SQL/recovery checks. The original
versions, renderer and search sources were retained. The five branches using
the newer storage API keep their own journal version and namespace; no legacy
helper was copied over that architecture. The exact starting/final commits,
paths and counts are in the [branch inventory](release-12.9.5.5-backports.json).
The branches' comprehensive Actions pipelines are separate from these local
checks and can be inspected at their recorded final commits.

The initial stable CI attempt passed the new lock tests but exposed opcode
caching of the fixture's dynamically switched Basic/session configuration.
The disposable web server now disables its opcode cache explicitly; application
cache settings and test expectations were not changed. The repeated complete
acceptance then passed in both PHP profiles.

Install the complete release and preserve site configuration and data. Finish
any pending Rename with its matching current source and recovery files before
switching CMS versions. No production deployment is claimed by this report.
The Policies-only November condition remains outside the release.

## Complete changed-path inventory against v12.9.5.4

| Path | Change |
| --- | --- |
| `u5admin/connect.inc.php` | Rename-only session wait limits before CMS queries. |
| `u5admin/rename2.php` | Marks the actual Rename request for those limits. |
| `u5admin/resource-rename-support.inc.php` | Stops the optional history-index fallback after a blocking/cancelled SQL error. |
| `u5admin/resource-mutation.inc.php` | Links blocked writers to authenticated recovery. |
| `u5admin/renaming.php` | Lists and resumes saved ordinary Rename plans without the original window. Existing named status remains read-only. |
| `tests/rename-database-lock-regression.py` | New real HTTP/SQL/mutex/recovery suite. |
| `tests/rename-web-regression.py` | Runs the new suite and isolates dynamically switched fixture configuration from opcode caching. |
| `index.php` | Version 12.9.5.5. |
| `README.md` | Current release and functional summary. |
| `BRANCHES.md` | Current stable version and scope. |
| `docs/rename.md` | Wait limits, recovery, visibility and operational limits. |
| `docs/development-status.md` | Accepted work and continuation checkpoint. |
| `docs/release-12.9.5.5.md` | This acceptance report. |
| `docs/release-12.9.5.5-backports.json` | Exact maintained-branch provenance and test counts. |
