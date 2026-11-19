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
authenticated PHP HTTP requests. Stable PHP 8.3/8.4 CI and maintained-branch
forward-port results are recorded after completion of those checks.

Install the complete release and preserve site configuration and data. Finish
any pending Rename with its matching current source and recovery files before
switching CMS versions. No production deployment is claimed by this report.
The Policies-only November condition remains outside the release.
