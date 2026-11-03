# Correction review checkpoint — 2026-11-03

Branch: `feature/search-phrases-quotes-highlighting`. Starting commit: `b95ea395be222b54b2a919ba246282e4bf5561fb`.

Scope: preserve original Main contracts, review previous corrections, exercise
`u5conference.sql` and the documented Dat command, correct reproduced faults and
add repeatable branch-specific CI. Main and production are outside this change.

Dat renderer upload remains held by automatic approval review; all other corrections are ready for publication.

Local verification: the corrected branch snapshot passed its complete conference
workflow, including Basic/session browser review edits, role restrictions,
registration and Dat variants. Form-data text transport and HTTPS/oracle checks
also passed. Original-reference, configuration and data-view comparisons showed
no differences in their compared contracts. Shared browser fixtures now use
per-run random values. GitHub CI results must be read from the actual run.

The locally tested Dat correction in `render.inc.php` is not included in this
commit. The remote conference tests are expected to report that outstanding
issue; they are not weakened or marked as passing. Other corrected files and the
complete regression suite are included. The final Dat upload requires explicit
public-source publication approval because automatic review rejected it.
