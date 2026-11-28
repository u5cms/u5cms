# u5CMS 12.10.16

## Summary

Existing response forms now wait for their saved values to finish loading before accepting a browser submission. A slow or failed prefill request could previously let initial values replace the current response. The former response remained in history, but the active answer was wrong. The defect is inherited from v12.9.5 and also affects v12.10.15.

The correction covers the standard edit and delete pages, personal MF responses and shared MFS responses. Ordinary form submissions, new MF/MFS responses and deliberately append-only personal forms retain their established behaviour after loading completes.

## What changed

- Edit and delete screens install a submission guard before their form is rendered. Only their actual response loader releases it after filling a saved row and completing the existing starter hook.
- The renderer recognises the named Monofill frames before their script-driven navigation. Personal and shared loaders release their own guard after prefilling; a different frame cannot release an edit-page guard.
- Button clicks, Enter and direct `form.submit()` calls are covered. A failed request leaves the response unchanged; reloading and completing the prefill permits the intended operation.

The existing timestamp, row fingerprints, ownership and conflict policies, callback handling, storage and export formats remain unchanged. The maintained V13–V17 lines receive the correction with their own renderer, template paths and version numbers.

## Verification

The [original eight-source browser diagnosis](https://github.com/u5cms/u5cms/actions/runs/46168915880) reproduced 32 premature edits alongside 16 correctly loaded controls, using the actual handlers on v12.9.5, v12.10.14, v12.10.15 and V13–V17. A [separate shared-response diagnosis](https://github.com/u5cms/u5cms/actions/runs/46170258193) reproduced the MFS path on v12.10.15 in Chromium and Firefox. Both used disposable databases and synthetic data.

The new fixtures exercise loaded, paused and aborted requests, empty saved optional fields, quoted multilingual text, click/Enter/direct submission, deletion, recovery, authenticated personal ownership, shared responses, intentional append-only responses and a genuinely new response. Full release acceptance also retains the existing CSS, update, export, ownership, conflict and MF/MFS collateral checks. Failed diagnostic and draft-candidate runs are retained separately from successful final acceptance; their status must not be counted as passing.

## Updating

Deploy the complete runtime update, including the new `formdata-prefill.inc.php` helper. Preserve private configuration, the favicon and local resource content as in the normal update procedure. The existing `r/runonce.php` update mechanism remains part of that procedure; this correction introduces no additional schema or configuration migration.

This runtime correction does not rewrite custom database components or replace site-specific acceptance of complete end-user workflows.
