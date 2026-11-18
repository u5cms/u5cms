# Search memory correction — 18 November 2026

Authorized scope: patch release **12.9.5.1**, based exactly on main
`bf6863337cd6dd64284cf429f65da986d821d741`, and branch-local backports to every
existing branch after inspecting both search generations. The user additionally
authorized a backed-up practical test in policies.eiam.admin.ch through Plesk.

Implementation checkpoint, not a completed release or deployment.

Findings: main and the numbered 12.9.6–12.11.4 branches use the old recursive,
buffered full-site vocabulary scan. The feature search branch and 12.12.0 onward
use the newer streamed search with bounded suggestion budgets, per-resource
witnesses, visibility checks, and phrase/multiword support. Those contracts must
be preserved; the newer engine must not be replaced with the old one. The unused
legacy include still contains the old unbounded scan on every branch.

Planned paths: legacy suggestion helper and include; both legacy result entry
points where present; focused regression tests; documentation. Only main changes
its public version marker. No database schema or content changes are required.

Completed: all remote heads inventoried; both engines and configuration,
authentication, indexing, rendering, history and backup boundaries examined.
Next: run memory/failure/real-entry-point regressions, inspect branch-specific
differences, publish the verified correction with expected-head guards, and
perform the authorized Plesk practical test. No test or deployment claimed yet.
