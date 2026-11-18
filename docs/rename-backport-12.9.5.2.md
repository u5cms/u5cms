# Rename propagation — 12.12.0-OIDC

Authorised 18 November 2026. Exact starting SHA: `dd83667e863eb8e2d553a4cbbc197718ca94f4f3`.
Version remains 12.12.0.

The fast, resumable ordinary Rename is backported from the 12.9.5.2 patch. This branch retains its own exporter, code-object generation, typed-code APIs (where present), searches, configuration and version. No unrelated branch is merged.

Executed on this exact application diff: PHP 8.4.27 / MariaDB 11.8.6; 881
Rename core/database checks (including SIGKILL/resume), 114 authenticated HTTP
checks, 31 changed PHP syntax checks. The HTTP suite exercises Basic and
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
