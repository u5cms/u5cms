# Repeatable regression contracts

The workflow in `.github/workflows/regressions.yml` tests pushes to development
branches and pull requests with PHP 8.3, 8.4 and 8.5, an isolated MariaDB 11.4,
Chromium and disabled mail delivery. Opcode caching is disabled for mutable PHP fixtures. Actions are pinned to commit SHAs and the
workflow has read-only repository permissions. It does not deploy or merge.

The shared browser fixtures generate their credentials and protected sample
contents afresh for every process group. Those values stay in the disposable
test environment and are not committed. Standalone Python fixture drivers set
them automatically; direct helper calls require the same environment.

`tests/regression-capabilities.json` explicitly lists each branch's required
tests and HTTPS routes. Missing declared files fail the run. Older branches do
not acquire later application features just to run their tests. Adding a feature
requires updating this manifest and its regression coverage.

Run locally with PHP extensions mysqli, mbstring, intl, iconv, ctype, curl, GD,
ZIP, fileinfo and exif; Python dependencies from `tests/ci-requirements.txt`; and
Playwright 1.62.1 with Chromium. Set `U5_TEST_PHP`, `U5_TEST_PLAYWRIGHT` or
`U5_TEST_BROWSER` if their default executables/modules are unavailable.

Point `U5CMS_TEST_PORT`, `U5CMS_TEST_USER` and `U5CMS_TEST_PASSWORD` only at a
disposable TCP database server, then run:

```sh
python3 tests/run-regressions.py
```

For the 24 MiB single-row backup/restore fixture, that disposable server needs
`max_allowed_packet=128M`: the exported hex SQL statement is larger than its raw
row. CI sets this explicitly, matching the local audit runtime. The PHP backup
export itself is still tested with a 64 MiB memory limit.

The runner refuses an implicit database port, creates randomized temporary
databases, removes them, and exports the fixed original commit
`9d366253417822c279692767b89dcf1ff0388c5c` for independent comparisons. A full Git
history is needed. `--baseline PATH` can supply that immutable export directly.
Groups `contracts` and `features` can run separately. Results are written to
`ci-results`; a failed command stops its group and returns failure.

On branches with the dependency patch oracle, CI downloads locked, unmodified
upstream archives into a separate temporary installation. That download ignores
upstream PHP constraints solely to obtain the exact bytes for the oracle; the
application's committed dependencies are not replaced. Laminas Mail/Mime still
declare support through PHP 8.3. The PHP 8.4/8.5 matrix measures the repository's
compatibility work and does not change those upstream support declarations.

The conference test imports the actual `u5conference.sql`, exercises its pages,
submission, reviewer assignments, review edits/history, acceptance/publication,
registration and documented Dat modes. Its users and addresses are synthetic.
The legacy import format remains unchanged; `csv_escape=rfc4180` parses quoted
fields without duplicating their quotes. Form-data mutation endpoints follow the
existing `formdataRqHIADRI` switch, including explicitly unrestricted deployments.

Rollback uses the parent application commit; databases and installed site
templates are not rewritten by these corrections. A green local run is distinct
from a green GitHub Actions run. Repository workflow permissions or required
status-check settings must be assessed from actual GitHub results.

The seed-reference contract additionally runs the original conference application
first and compares 38 seeded pages in German, English and French under five
roles (570 cases per installation). It requires the actual abstract/registration
controls to be present, checks nine destructive oracle mutations, and preserves
page access, form actions, methods, targets and existing input/selection controls.
Additive features are allowed. The deliberately modernized global navigation
search has its own real-browser suite and is excluded from this business-form
comparison. On profiles which include mobile controls, CI also runs the 84-check
mobile browser suite (five languages, keyboard, toolbar replacement, delayed
search and desktop switch).

On streaming-backup profiles, the packet regression additionally runs the actual
original Main exporter as a control: its 24 MiB row restores under a 32 MiB packet
limit. The corrected dump must do so too. Tests also cover mixed Unicode/Latin-1,
binary/NULL/empty/typed values, plain and gzip imports with a 1 MiB packet limit,
fresh connections during an import, failure before an incomplete row is inserted,
retry and cleanup. The disposable test account must be able to adjust its own
server's packet limit; the original value is restored in a finally block.

Oversized dump rows retain hexadecimal byte encoding and use bounded fragment
statements. Import temporarily creates a randomly named staging table, using the
source table's engine. It is cleared between rows and dropped on completion;
restarting the same dump clears its own interrupted staging state. Staging data
survives import connection changes. The importer must retain/reapply the standard
dump session header (charset, SQL mode and time zone), just as for the ordinary
rows. Full schema/data restore already requires table creation/deletion rights.
No staging table is created in the database being backed up. The existing large
HTTP backup export is still checked with a 64 MiB PHP memory limit. The CI service
keeps its 128 MiB packet budget for the independent whole-file restore helper;
that setting does not substitute for these constrained per-statement tests.
