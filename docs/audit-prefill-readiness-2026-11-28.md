# Prefill readiness audit — 28 November 2026

Published source correction: [v12.10.16](https://github.com/u5cms/u5cms/releases/tag/v12.10.16), Release ID 461765756, published 28 November 2026 at 18:24:27 UTC. The Release API reports Latest, not draft or prerelease; its target and direct tag ref are exactly `0e704d9d78e80c1d82892966b2e7dd740cea5787`. The direct predecessor is now v12.10.15. Publication does not close the site acceptance or intermittent findings below.

## Authorised scope and acceptance contract

Investigate active-answer loss through the shipped edit/delete and MF/MFS browser paths, compare the resolved v12.9.5 source and the direct predecessor with the released stable source and all five maintained future lines, and repair only reproduced failures. No production or reference installation was changed by this isolated audit. Database contents, identities and browser input are synthetic.

A form for an existing answer must not submit initial values while its actual prefill loader is pending or has failed. A completed loader must still permit ordinary editing/deletion, personal ownership, shared answers, deliberately append-only personal answers and a genuinely new answer. Reloading after a failure must permit the intended operation. Existing callback, conflict, timestamp, storage, export and update contracts must be retained.

## Reproduced defect

[Run 46168915880](https://github.com/u5cms/u5cms/actions/runs/46168915880), audit commit `5ed5c2caff54ec86ceaaaaeb9a29c7f563554f73`, ran exact archives of origin `9d366253417822c279692767b89dcf1ff0388c5c` (annotated tag object `fa6e8982e85d7ca9ec33683ab33d31eb86a31eb9`), predecessor v12.10.14 `0af39943e39f25b8ff3594675b2134801e5ee7ea`, released v12.10.15 `94455fc63b63097eb40908dd1d2cafb4c4f5902c` and the five then-maintained future heads. All eight job logs were read. In PHP 8.4, disposable MariaDB and Chromium/Firefox, 16 loaded controls worked and 32 paused/aborted actual `formdataedit2.php` requests allowed the real `editformsave.php` POST to replace the active saved multilingual title and empty optional value with new initial values. History survived; the active answer was wrong. These are reproduced failures, not passing safety checks.

[Run 46170258193](https://github.com/u5cms/u5cms/actions/runs/46170258193), audit commit `e2009b93f5264de5a302ee6c3a5f3c051a09f911`, separately confirmed the shared-response path on exact v12.10.15: two loaded controls worked; four paused/aborted actual `monofillshared.php` requests permitted the real `formsave.php` POST to replace the active shared answer.

## Minimal correction and exact sources

A small shared helper guards submit events and direct `form.submit()` until the appropriate loader finishes prefill and the existing starter hook. Edit/delete readiness is separate from Monofill readiness. The renderer recognises the actual named, initially sourceless Monofill frames; personal/shared completion also permits an empty selection for a new answer. No server storage, policy, schema or configuration contract is replaced. Original Latin-1/Windows-1252 source blobs were checked against Git hashes before byte-preserving ASCII edits. V17 retains its split renderer; future lines retain their own versions.

| Target | Reviewed PR head | Actual merge |
| --- | --- | --- |
| main | `208836e8792e14c7d48cba89f2a3fb570618d0e0` | `0e704d9d78e80c1d82892966b2e7dd740cea5787` |
| V13-more-Img-Formats | `860ed64a8ee506f6cf13c871273145cec9f897a3` | `f96ca3b9956b929566f9bfbfed6cf0facb8a68e2` |
| V14-JS-CSS-HTML-Exports | `77992bed561ae04027a3ad50afa1a966a1cac9a1` | `8b2bb82defcf2d0e392f60b09d5e325b31a5c4e3` |
| V15-Phrase-Search-and-OIDC | `c0bbd6bad21abaf4faf1aad32ceda2b64ef10106` | `2918c6e7da42139cd680f39a3f22e08d1d013ca7` |
| V16-Default-Design | `c7482c6e2b0ea4ef8024ef2916edd8cc4c1f03d4` | `cc04ffa22fe18cb18295d5e2d816bda00a79b09f` |
| V17-New-Responsive-Layouts | `428e797355283fe62d8654977f92f55c62e67be8` | `a2de4250f0237fc99ac9497ac229bb14e467d31c` |

The Git tree of every merge was fetched and equals its reviewed PR-head tree. This is a source identity check, distinct from runtime acceptance.

## Compatibility counterexample caught before merge

A standard template passed, but a legal template omitting the optional closing `head` tag exposed an incomplete draft insertion anchor. [Before run 46172820646](https://github.com/u5cms/u5cms/actions/runs/46172820646), audit commit `ed18aa1a106981dce9e8526e23dbbbd916042a0e`, used candidate `b482d7fd1a56c2bb67cad43fcea29f8cfdf6d0c0`: four loaded edit/delete controls worked in CSS1Compat; all four paused cases still sent POSTs. This was caught before merge/publication. A fallback inserts the same guard before the content component when no closing-head insertion occurred.

[After run 46173308400](https://github.com/u5cms/u5cms/actions/runs/46173308400), audit commit `5f7f2cee0028fa6254516276adec6d01b983f10f`, pins corrected main `208836e8792e14c7d48cba89f2a3fb570618d0e0`: all eight cases and four resumptions passed in Chromium and Firefox, preserving CSS1Compat. This counterexample is retained in normal acceptance.

## Final runtime matrix

Each PHP 8.3/8.4 acceptance job runs 44 edit/delete observations (36 standard plus eight omitted-head cases) and 60 MF/MFS observations, including loaded, paused and aborted requests; button, Enter and direct submission; quoted multilingual saved values, empty optional values, real row/history checks, authenticated personal ownership, shared answers, intentional append-only answers and a new response. Each job has 64 blocked attempts with no POST and unchanged database state, followed by 64 successful resumptions; 40 loaded cases are immediately allowed.

All twelve actual merge-head PHP 8.3/8.4 acceptance logs were retrieved and individually evaluated: **1,248 observations passed**, comprising 528 edit/delete and 720 MF/MFS observations. All 768 premature attempts had zero POSTs and unchanged database state; all 768 subsequent resumptions succeeded. No page-error events were recorded by these fixtures. These counts exclude all superseded PR-head repetitions and the separate focused diagnostics.

| Line | Actual remote merge | Successful push runs |
| --- | --- | --- |
| main | `0e704d9d78e80c1d82892966b2e7dd740cea5787` | 46173716240 |
| V13 | `f96ca3b9956b929566f9bfbfed6cf0facb8a68e2` | 46173633353 / 46173633355 |
| V14 | `8b2bb82defcf2d0e392f60b09d5e325b31a5c4e3` | 46173782106 / 46173782138 |
| V15 | `2918c6e7da42139cd680f39a3f22e08d1d013ca7` | 46174223004 / 46174223002 |
| V16 | `cc04ffa22fe18cb18295d5e2d816bda00a79b09f` | 46174258724 / 46174258726 |
| V17 | `a2de4250f0237fc99ac9497ac229bb14e467d31c` | 46174698973 / 46174698882 |

All eleven push runs completed successfully, including the V17 PHP 8.3/8.4/8.5 regression contracts. The stable release still requires separate tag/Release verification; merge and CI are not publication or deployment.

Existing release acceptance also runs CSS, metadata, update/runonce, ownership, ordinary add/edit/delete, conflicts, callbacks, CSV, mail, Rename and Chromium/Firefox checks. Green PR checks are not used to conceal a later failing push.

## Separate intermittent V17 typing check

Fix-branch push [46173242577](https://github.com/u5cms/u5cms/actions/runs/46173242577), attempt 1, PHP 8.3 job `141066158815`, failed at the ordinary-typing Ctrl+Z assertion in `tests/textarea-browser.cjs:53`: both editors retained the typed `x`. PHP 8.4/8.5 passed. Attempt 2 on unchanged sources passed. This does not explain the original failure.

The failed log also contains renderer memory fatals at the fixture's 64 MiB limit. A source review found that `textareatarget` was seeded with an inclusion of itself. [Focused diagnosis 46174941223](https://github.com/u5cms/u5cms/actions/runs/46174941223), audit commit `880da400f4b6da83f78038e4f9e044cba074d85e`, uses four independent PHP 8.3/MariaDB/Chromium jobs: unchanged published V17 `159bce1d80287f70948e8a42293dfc216f01c3ca` and candidate `428e797355283fe62d8654977f92f55c62e67be8`, each with the original fixture and a non-recursive target control. Read-only history tracing preserves the original undo assertions; 0/10/100/950 ms profiles and two repetitions cover Basic/session modes.

All four logs were read. All 64 typing/undo/redo profiles and the remaining actual editor/browser/HTTP checks passed (219 assertions per job; not 876 distinct user journeys). Both original-fixture jobs produced exactly two memory fatals; both non-recursive controls produced none. The fixture self-inclusion therefore explains the separate memory messages, independently of the prefill correction. It does not prove the cause of the intermittent Ctrl+Z failure. No memory limit, timeout or expected result was relaxed, and no speculative application fix was made.

## Excluded attempts and remaining limits

The first baseline diagnostic stopped at response-body access after navigation. Early draft candidates used an absent opening-head anchor, then an iframe `src` absent from the real Monofill markup. The real admin hint had to be closed before clicking Delete; personal MF tests needed the protected source page required by that feature's authentication contract. These failed/aborted fixture or draft runs are not final passing evidence. No forced click or weaker payload/ownership assertion replaces the real contract.

This audit does not constitute full site-specific Dossier upload/export acceptance or acceptance of a particular migrated customer design. Custom database components are outside the runtime patch. The historical Firefox mail-popup cause and the new one-off V17 typing cause remain explicitly open. The agreed reassignment behaviour remains unchanged: a reviewer may finish a review opened before reassignment under their own identity, but cannot freshly reopen it afterwards. No deployment approval, deployment message or production rollout is supplied by this source audit.
