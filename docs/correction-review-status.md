# Correction contract review — checkpoint

Status: final verification in progress (2026-11-03).

Starting application: `45ab30b839988f8d4650092dacd5cb1552a4b243`.
Scope: review previous corrections against their parents and original Main;
include the actual conference seed, review workflow and documented Dat options;
retain deployment configuration and valid edge cases; propagate reproduced
corrections to affected development branches and establish repeatable CI.

The corrected cumulative application passed 138 conference/Dat/browser checks,
including Basic/session review creation and editing, assignment visibility,
history, acceptance/publication, registration and explicit import modes. The
original-reference comparisons cover all 15 development branches with no
observed differences in their compared contracts. Strict OIDC HTTP/browser tests
pass after nullable text transport is normalized; 10 targeted transport checks
also pass. Upload checks and mail-construction comparisons pass.

Current implementation paths: `formsave.php`, `render.inc.php`,
`u5admin/formdata.intro.php`, `u5admin/import2.php`, `u5admin/mass.php`,
`u5admin/notesave.php`, `u5admin/statussave.php`. Branch backports also introduce
only the shared request helper where required. Tests assert their own fixture
preconditions, reject removed HTTPS detectors and read actual CSRF tokens.

GitHub CI has started; earlier candidates exposed configuration-cache and strict
null-input gaps. Their corrections are included in the next candidate. No final
green CI result is claimed here. Next: complete the branch checks, verify the
published bytes and actual CI results, and replace this checkpoint with the
completion state. Main is not modified and no production CMS is installed.

This concise technical checkpoint excludes the detailed earlier audit payload;
its separate disclosure restriction remains in effect.
