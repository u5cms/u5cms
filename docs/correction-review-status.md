# Correction contract review — publication checkpoint

Status: application corrections published except the held renderer; final CI and
renderer publication remain incomplete (2026-11-03).

Scope: compare corrections with original Main and their branch contracts;
include the actual `u5conference.sql` seed, review workflow and documented Dat
options; preserve valid deployment configuration and edge cases; propagate
reproduced corrections to 15 development branches and establish repeatable CI.

## Verified publication

All 15 development branches and `audit/correction-contracts-20261103` received
corrections and regression CI using guarded fast-forward ref updates. A fresh
fetch verified 776 expected file states, including required fixture helpers,
with zero mismatches after correcting two test-package omissions. Each branch
retains its previous `render.inc.php` bytes. Main remains
`78cadbf87b7307203b8bd4ff96929b306af7d982`; no production installation was changed.

Published application corrections cover authorization/status transport in
`formsave.php`, `u5admin/statussave.php`, `u5admin/notesave.php` and
`u5admin/mass.php`; nullable database text in `u5admin/formdata.intro.php`;
explicit RFC-mode CSV import on the branches which implement it; and nullable
optional metadata in `editformsave.php`, `delformsave.php` and
`saveversionconflict.inc.php`. Original CP1252 application bytes are preserved.

## Local corrected application

All 15 locally corrected branch snapshots passed the conference/Dat workflow:
136 checks per older branch and 138 on the explicit RFC-import branches. The
latter also enable the strict readiness guard. Coverage includes actual seeded
pages, submission, mandatory/auth denial, Basic/session review creation and
editing, assignment visibility, protected direct access, history,
acceptance/publication, registration and documented Dat alternatives.
Original-first reference, configuration and complete-view comparisons observed
no contract differences across the 15 branch profiles. Strict OIDC HTTP/browser
checks, nullable text transport, upload, mail-construction, recovery and editor
checks also passed locally. These are finite tested contracts, not a proof that
all possible defects are absent.

## GitHub verification

CI runs PHP 8.3, 8.4 and 8.5 with isolated MariaDB and browser dependencies.
The full independent feature suite passed on the audit candidate
`15338ff963b8a6785cf1c457dccee903fc0dfc08` under PHP 8.4 and 8.5. PHP 8.3 reported
one ordinary-typing undo assertion; the next test candidate verifies the exact
typed character and waits for both editors to receive it before Undo, retaining
the exact Undo/Redo assertions. That candidate passed 161 local browser checks.
Other branch matrix jobs are still running or queued. No overall green result
is claimed. Contract failures remain visible while independent feature checks
continue. CI explicitly disables opcode caching because fixtures change PHP
configuration between requests; it sets a 128 MiB database packet limit for the
unchanged 24 MiB restore fixture.

## Remaining publication hold

Automatic upload review rejected public disclosure of renderer variants, citing
possible sensitive implementation or fixture material. Consequently no further
renderer upload is attempted on any of the 15 branches or the audit branch.
The local changes cover Dat ordering without a LIMIT, scalar request validation,
controlled SQL failure, and historical empty-string behavior for missing/NULL
values. The published renderer therefore still fails the new Dat/readiness
contracts. Explicit consent for these exact public file disclosures is required
before publishing them, rerunning the matrix and considering any release.

The detailed earlier audit payload has a separate disclosure restriction and is
excluded from this checkpoint. Tests use disposable local data and per-run
synthetic authentication. Mail construction is tested with transport disabled;
external mail/payment providers and a production deployment are not exercised.
