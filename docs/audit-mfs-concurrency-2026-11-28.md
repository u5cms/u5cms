# Shared form concurrency audit — 28 November 2026

## Reproduced behaviour

With the configured first-saver-wins policy, two people could load the same shared form and submit within the same whole second. Both submissions were accepted and the second replaced the first active value. The older version remained in history. This was inherited behaviour, rather than a regression introduced after the origin release.

The [before run](https://github.com/u5cms/u5cms/actions/runs/46135003448), audit commit `5fcab5bbdb65c59ede4f6e5a072794f0e693e69f`, measured six same-second pairs on each of eight source trees: origin `9d366253417822c279692767b89dcf1ff0388c5c` (annotated tag `v12.9.5`), `v12.10.13` and the then-current stable and V13–V17 heads. All 48 pairs missed the conflict. The fixture used the shipped tutorial form, its rendered POST action, real Basic identities, PHP and a disposable MariaDB database. Opening and mutation times were measured, not mocked.

## Candidate sources

| Line | Exact tested commit |
| --- | --- |
| main | `6108de9908f794c5a64a6800912a4dc5b493cb9d` |
| V13-more-Img-Formats | `ff799c472ce84aa880647690307507f75a7e0235` |
| V14-JS-CSS-HTML-Exports | `5881b546d0be3fc74f6caa9df4c98df2594cd777` |
| V15-Phrase-Search-and-OIDC | `5be5370f5c166be01c71d28cb5ad8123d2d25940` |
| V16-Default-Design | `84a6cb92c4e0ef6cef5f74957738723f3eba670c` |
| V17-New-Responsive-Layouts | `d6e9e4366bb5c02774c5f79042e4628b25d7e63d` |

A separate hidden field identifies the row loaded by the existing monofill transport. Frontend Save, Edit and Delete compare it under the existing database write lock. The internal field is removed before normal field checking and storage. The opening timestamp remains numeric. The existing all/foreign/none policies and personal-owner scope are retained. Callback saves refresh the loaded fingerprint. Existing pre-update forms without a fingerprint retain their older timestamp check until refreshed. No schema or configuration migration is required.

## Executed confirmation

The independent [HTTP matrix](https://github.com/u5cms/u5cms/actions/runs/46139505347) and [browser matrix](https://github.com/u5cms/u5cms/actions/runs/46139505360) pin audit commit `9d31d48d1e0147816e71cb7bce168ef60882e518` and the six application commits above. All twelve job logs were read.

* HTTP: six measured same-second stale pairs per line, 36 in total. Every stale submission was rejected and the complete observed row state remained unchanged. Reloading allowed the new author to save.
* Policy: all rejects a stale save by the same author; foreign permits that author's own later save but rejects a different author; none permits the later author. All four cases passed on each line.
* Empty mandatory input did not mutate storage; correcting the input allowed resumption. A duplicate submission was rejected without another version.
* Two independent PHP workers submitted the same loaded version: exactly one edit was accepted, one was rejected, and one active row remained on each line.
* Chromium and Firefox each completed the shared stale-tab/reload/CSV path, two consecutive callback saves, and independently opened personal forms. This gives 36 completed browser paths across six lines. Accented and non-Latin values, identity and active export were asserted.

The existing release acceptance also passed on each candidate with PHP 8.3 and 8.4, including both browser engines, ordinary add/edit/delete, stale-edit rejection, actual CSV export, sender evidence, search, indexing, Rename, backup and recovery. These are additional contract checks, not additional distinct concurrency scenarios.

## Failed drafts and limits

Earlier draft checks were not counted as successful confirmation. Existing contracts exposed accidental legacy-byte rewriting, a non-numeric timestamp prototype, changed conflict wording and an unhandled internal field on allowed deletion. The final candidates preserve the original byte encoding and numeric timestamp contract, retain the established warning where its timestamp explanation applies, and handle the internal field on all three mutation paths. The existing assertions remain in place.

The independent browser fixture uses HTTPS session login and the shipped monofill transport; its concurrency test is the separate two-worker HTTP fixture. It does not certify a particular customised deployment, authentication provider or customer database. Already-open pre-update forms need a refresh to acquire the new fingerprint. The separately tracked historical Firefox popup timeout remains unexplained; green contemporary browser runs are not a retrospective root-cause proof. No production installation was written to or load-tested by this audit.
