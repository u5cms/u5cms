# Correction review checkpoint — 2026-11-03

Branch: `12.9.6-Misc-Backend-Optimizations`. Starting commit: `032970e4a2f00595f78c371f0a97e146a118f834`.

Scope: preserve original Main contracts, review previous corrections, exercise
`u5conference.sql` and the documented Dat command, correct reproduced faults and
add repeatable branch-specific CI. Main and production are outside this change.

All corrections in this review are ready for publication and CI verification.

Local verification: the corrected branch snapshot passed its complete conference
workflow, including Basic/session browser review edits, role restrictions,
registration and Dat variants. Form-data text transport and HTTPS/oracle checks
also passed. Original-reference, configuration and data-view comparisons showed
no differences in their compared contracts. Shared browser fixtures now use
per-run random values. GitHub CI results must be read from the actual run.

Next: verify this commit with the PHP 8.3/8.4/8.5 workflow and record its result.
