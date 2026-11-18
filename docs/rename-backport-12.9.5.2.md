# Rename propagation — 12.13.0-Responsive-Design

Authorised 18 November 2026. Exact starting SHA: `a28ebd87208645bc88bbe49da4281e38d97bc1eb`.
Version remains 12.13.0.

The existing modern Rename, typed code APIs and checkpoint formats are retained. Eight additional resource maintenance writers now use the existing mutation lock.

Executed on this exact application diff: PHP 8.4.27 / MariaDB 11.8.6; 886
Rename core/database checks (including SIGKILL/resume), 114 authenticated HTTP
checks, 9 changed PHP syntax checks. The HTTP suite exercises Basic and
cookie sessions, CSRF, archive HIADRI before/after ownership moves, five
languages, rendered references, files and histories before success, forced
publication failure, all coordinated writer denials and exact retry.

Index.php and all search application paths match the starting SHA byte for
byte. No new browser execution: Chromium cannot create its UNIX singleton
socket in this environment (EPERM). These are actual local DB/HTTP tests, not
GitHub Actions. No production installation or main merge.

The shared branch catalogue identifies `12.9.5.2-Faster-Rename` as the patch
line. Main and historical 12.9.5.1 patch/tag retain their release identity.
All 16 branch results and changed-path inventories are recorded in
`rename-backport-12.9.5.2-branches.json`.
