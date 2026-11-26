# Evolution audit from u5CMS 12.9.5

## Checkpoint — 26 November 2026

Authorised scope: review the current stable release and all five maintained future lines for defects and cumulative regressions since v12.9.5. Investigate complex form storage, per-user versions, site writers, ownership, exports and mail alongside authentication, media editing, indexing, provisioning, backup and Rename. Correct reproduced defects, add regression coverage and publish a subsequent patch release when justified.

Starting stable commit: `da576ca12b054a40ae9c0481584e22c9aaaac1a5` (v12.10.5).

| Maintained line | Starting commit |
| --- | --- |
| V13-more-Img-Formats | 0aa95d476a3676b3b488c65e1f5b6774c5c8d381 |
| V14-JS-CSS-HTML-Exports | 35a767d4d334cbcd683fdfaee3ac855edafc7f54 |
| V15-Phrase-Search-and-OIDC | a107cd55101bd30c7e1023e041589e2969f53753 |
| V16-Default-Design | 602fd5c14e9002c2ef310805a94987f38a2ec8e9 |
| V17-New-Responsive-Layouts | 7c9219d22b57075f6ad72236f73dc19e97f91403 |

The repository history and release tags are available. The cumulative application diff and existing acceptance coverage are being reviewed. Production read-only entry checks are recorded separately from disposable submission tests. This checkpoint is not a claim that the audit or any new release has passed.

Next action: construct reproducible differential and fault cases for the uncovered paths, run them against actual source, then record exact results and any corrective scope. Preserve each development line's version, encodings and features. Reconcile current remote heads before publication.
