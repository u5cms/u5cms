# u5CMS development checkpoint

## Save coordination and large Rename rows, 19 November 2026

Authorised: carry the .7 correction into this branch while retaining its version,
rendering, search, configuration and migration APIs. Previous branch head: `63f5951a89981b2b4fe0b5ef03cd5744fbd3cf45`.
Ordinary saves share only the live Rename gate; no index/backup wait or idle-plan
blocker is introduced. Large Rename guards use byte digests; new large values are
staged in connection-local binary variables before one conditional row UPDATE.
Both resource and code Rename storage APIs are covered where present.
Local PHP 8.4.24 / MariaDB 11.8.6 passed the actual five-language/current/history
packet regression (58 assertions at 4 MiB), plus this line's applicable source,
concurrency, index, Rename and code-migration suites. Tested packet tree: `52481df381612d461b7b51d036893ffa9b3c8945`.
Next: complete the required full branch CI, then consult the final .7 branch
receipt and release report on main. No production deployment by this work.

## .7 save/Rename correction in progress, 19 November 2026

Authorised: preserve this branch's cumulative work and forward-port .7.
Target: 12.11.1-Migration-Hardening. Exact base: 630c61a0c1a86154e8468d22b1f32e7718e5c57e.
Candidate: process-owned Rename gate, concurrent ordinary writes, index yielding,
nonblocking recovery plans, real Rename phase/progress and crash recovery tests.
This branch's renderer, version, search format, migration namespace and encoding
remain distinct. Its existing monotonic resource update API is preserved where
present. Tests and expected-head publication are still pending at this checkpoint.
No production deployment or rollout email. Next: actual local HTTP/concurrency/
Rename tests and this branch's comprehensive pipeline before release acceptance.

## Save/index coordination, 19 November 2026

Base 10ebbfb806a54ce7af19de5a1318019bb5c56a7c. Target 12.11.1-Migration-Hardening.
Bound named/full index work and server-side save invalidation; preserve this
branch's version, renderer, index format and migration/Rename APIs.
Local PHP 8.4.24/MariaDB 11.8.6: 26 real save/index checks and the branch's
existing batch, notice (where present), Rename and ownership checks passed.
Modern-search branches retain all 96 index expectations; only their isolated
fixtures gain the actual new helper dependencies. The new test is registered
in the existing capability manifest/workflow when present. This is targeted
local verification, distinct from the branch's comprehensive Actions pipeline.
No production deployment. Published ref must be checked against the new SHA.


## Rename SQL wait guard, 19 November 2026

Authorised follow-up: bounded database lock waits for Rename and safe recovery
when the original result window is lost. Target: `12.11.1-Migration-Hardening`.
Exact base: `26952ad2ba2f2a6f09cba20a4855820e9754f72f`.
Changed source/test paths: `u5admin/connect.inc.php`, `u5admin/rename2.php`, `u5admin/resource-mutation.inc.php`, `u5admin/renaming.php`, `tests/rename-web-regression.py`, `tests/rename-database-lock-regression.py`, `u5admin/resource-rename-support.inc.php`.
Local PHP 8.4.24/MariaDB 11.8.6 passed 887 Rename, 114 HTTP
and 83 real SQL-lock/recovery assertions. The branch-specific journal namespace
and version are retained. index.php, rendering and search sources are unchanged.
The independent stable patch passed PHP 8.3/8.4 with Chromium in Actions run
44471208093; that is distinct from this branch's own comprehensive pipeline.
No production deployment. Next action: verify the expected-head publication
and the corresponding branch CI result.


## Rename-Nachfuehrung abgeschlossen (18. November 2026)

Branch `12.11.1-Migration-Hardening`, Ausgangs-SHA `88f5f93acd4fdb1707fdd029ca46a56d6da5de92`, Version 12.11.1.
Autorisierter Umfang und geaenderte Pfade: [Rename-Bericht](rename-backport-12.9.5.2.md)
und zugehoeriger JSON-Nachweis. 881 Rename- und 114 HTTP-Pruefungen bestehen;
Version und Suchdateien sind bytegleich zum Ausgangsstand. Keine produktive
Installation. Veroeffentlichung wird mit erwartetem Remote-Head bewacht; die
abschliessende Remotepruefung bestaetigt den gespeicherten Commit.
Naechste Aktion: keine weitere Implementierung fuer diesen Auftrag.
