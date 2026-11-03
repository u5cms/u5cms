# Correction and anti-regression review — 3 November 2026

Status: specifically approved renderer publication in progress. A further complete
anti-regression review is authorized after publication; no final completion is
claimed by this checkpoint.

Scope: all 15 development branches and `audit/correction-contracts-20261103`;
compare original Main, each branch's prior state and intended workflows, including
`u5conference.sql`, review assignment/creation/editing, registration/publication,
and the documented Dat command. Correct reproduced regressions on every affected
branch and verify the actual GitHub files and CI. Main and production are held.

Before renderer publication, all 48 independent feature jobs (PHP 8.3, 8.4, 8.5
on 16 branches) succeeded. Only the contract step failed. The published renderer
still lacked the locally tested Dat/nullable-value corrections at that point.
The corrected local conference sequence passed 136 checks on older profiles and
138 on explicit RFC-import profiles. Strict readiness is enabled only on branches
which declare it. The strengthened typing/Undo/Redo browser test passes in CI.

The user explicitly approved public renderer publication on these branches on
3 November. All three exact prepared renderer blobs have now been accepted.
Branch updates use expected-head guards and preserve the original CP1252 bytes.
The earlier publication checkpoint verified 776 expected file states without
mismatch, including unchanged renderer bytes while disclosure was held. That
hold is now superseded by this explicit authorization; the separate restriction
on the detailed earlier audit payload remains unaffected.

Published application corrections outside the renderer concern form-submit
response/auth ordering, formdata write authorization, nullable text transport,
explicit RFC-mode import, and optional first-review/form-mail metadata. Tests
use per-run synthetic credentials and disposable local installations/databases.
CI disables opcode caching for mutable PHP fixtures and permits 128 MiB database
packets for the unchanged 24 MiB restore case. No mail transport is invoked.

Next: publish the exact renderer variants; verify all branch tips/files and Main;
then execute the newly authorized complete anti-regression review, inspect all
actual PHP-matrix results, fix confirmed findings, and replace this checkpoint
with the final evidence and remaining acceptance boundaries. A passing report
must not be substituted for an actual CI result. No production installation,
external mail/payment acceptance, merge or release replacement is included.
