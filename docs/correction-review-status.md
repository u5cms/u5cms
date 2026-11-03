# Correction contract review — checkpoint

Status: in progress (2026-11-03).

Starting application: `45ab30b839988f8d4650092dacd5cb1552a4b243` on `12.12.4-Default-Design`.

Authorized scope: review prior corrections against their parents and original Main; preserve valid configuration, extension, identity, encoding, data and recovery contracts; include the fresh `u5conference.sql` review workflow and documented `:dat` variants; correct reproduced faults on affected development branches and add repeatable CI. Main and production stay outside this task.

Local checkpoint: 114 conference/Dat checks pass after narrowly scoped fixes for dynamic ordering, controlled query errors, restricted form-data mutations, authentication before output and explicit RFC import quote handling. HTTPS oracle mutation checks also reject removed detectors and the previous bool-only detector. A browser fixture is enlarged so centering is an asserted precondition instead of a font-dependent assumption. Original-reference and complete browser comparison are running; these are not yet claimed as passed.

Changed implementation paths: `formsave.php`, `render.inc.php`, `u5admin/import2.php`, `u5admin/mass.php`, `u5admin/notesave.php`, `u5admin/statussave.php`. New/updated tests are in this commit. No CI result is claimed at this checkpoint.

Next: finish independent before/after comparisons, validate branch-specific backports and publish the CI runner. This concise checkpoint contains no detailed earlier audit payload; its separate disclosure restriction remains in effect.
