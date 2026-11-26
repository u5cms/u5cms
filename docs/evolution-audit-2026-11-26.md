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

## Reproduced defects and acceptance checkpoint

Two pre-existing return-reference defects were reproduced on 12.10.5: a second fragment delimiter lost the remaining anchor text, and a query beginning at offset zero or one received the wrong timestamp separator. The correction is two exact line changes in each login-save entry point. All other application changes on the stable candidate are its index comment and logout version. Form handlers, configuration, database structure and security guards retain their bytes.

Candidate `94c344e3fa23b1d6a118b2e27cee9688fe10364c` passed cumulative form evolution on native PHP 8.3 and 8.4 in run 45838285625: 515 assertions, 18 scenarios across 28 lines (22 stable tags, five future branches and the candidate). The initial fixture failed because V15's request helper had not been copied into its disposable directory; including the actual helper resolved that fixture dependency without changing application code or expected results.

Local PHP 8.4.27 WebAssembly checks passed 55,990 HTML length/slice comparisons, a 75 MB streaming search corpus, 632 Rename grammar cases, 158 international mail checks, index notices, background protocol and trusted-browser policy. Native database and rollout checks are acceptance boundaries of the GitHub workflows; WebAssembly results are not substituted for them.

The added generated login corpus passes 512 full-chain round trips with duplicate keys, encodings, fragments and short relative references. Permission dependency grouping matches a separate reachability oracle over 1,500 random graphs (79,134 pair comparisons) and retains a 30,000-node chain. The final candidate also runs these tests and cumulative PHP linting in native CI.

Live inspection confirmed the protected complex-form site opens after fresh federation login, including new-form rendering and the separate record-number login route. No live form submission or notification was issued. This is not an end-to-end certification of a production site's bespoke integrations.

Next action: complete the final native workflows on all six candidates, verify expected remote heads and identical accepted trees, then publish the patch release. Record final workflow/merge/tag receipts separately from this in-progress checkpoint.
