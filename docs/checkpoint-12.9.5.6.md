# 12.9.5.6 implementation checkpoint

Authorised by Stefan on 19 November 2026: implement .6 after published .5, perform independent cumulative review from v12.9.5, preserve and verify all maintained future branches, publish tested .6/Main/tag/release, then email Markus Gaschen with Michael Rolli in Cc to request the deployment ticket. No production deployment by this task.

Owner: foreground implementation in the original .6 conversation, workspace /workspace/scratch/68435f713f4a/u5cms-review. Do not duplicate implementation while a newer active checkpoint exists. Re-read current branch and open PRs before mutation. This initial checkpoint is not an implementation or acceptance result.

Verified source refs obtained from GitHub and a fresh clone:
- v12.9.5: fa6e8982e85d7ca9ec33683ab33d31eb86a31eb9
- v12.9.5.4: c7062c680deb29fcac70815eea929ad57ee3a5d2
- v12.9.5.5/Main: 8bcb09937999d4ebb51da293b06a113bda0940d8
- .5 release published 19 November 2026 06:54:55 UTC.
- No open PR or .6 release when starting.

Initial source findings, NOT newly executed runtime tests:
- Actual page saves use u5admin/savepage.php, not only done.php.
- savepage.php writes source/history, then launches indexer.php?n=NAME and lastsave.php in separate browser iframes. done.php does likewise for other actions.
- lastsave.php alone writes lastsave.txt. Thus a completed source write can outlive a closed/failed confirmation-frame request without the expected marker.
- Named indexing renders five language versions without U5IndexSlice's mutex and without the full-index database wait guard.
- Source-byte digests protect index writes from concurrent direct source changes, but coordination with partial Rename and dependency changes needs explicit verification.
- Preserved rules: intentional no-autoindex search text, separate active/archive row identity, existing source encodings, domain/user defaults, branch-specific modern search format/pending handling, typed-code migration/journal namespaces and renderer.

Plan: map entrypoints/state/locks/dirty markers; move source invalidation to coordinated server mutations; guard named/full indexing consistently against SQL waits and simultaneous Rename/writers without invalidating source; meaningful real save/closed-frame/parallel/blocker/failure tests; rerun stable cumulative suites; architecture-aware forward ports and exact head/test inventory; release and mail only after passing acceptance.

Current step: source analysis, disposable runtime preparation. Next: implement and reproduce the source findings, recording actual outcomes and limitations before publication.
