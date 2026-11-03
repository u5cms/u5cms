# Additional installation acceptance — 3 November 2026

Status: in progress, explicitly authorized after the completed correction review.

Starting point: `2072296c8da5db161b26396faab36d3ef9909767` on `audit/correction-contracts-20261103`; latest application `68a9500a0e2a8aa1527075414858b40a699c8945`. All development heads and Main were read again and match the completed review. Work continues on `audit/acceptance-20261103`; Main and production remain held.

This finite pass adds different evidence: an isolated restore of an available real-installation database export; original/current application comparisons against those data where the supplied files permit them; additional backup-format boundaries; and complete conference/Dat workflows under additional identity and configuration transitions. It does not substitute a repeated green matrix for new coverage.

The real database export is private test input and will not be committed, quoted, or included in public fixtures. Credentials are replaced in disposable copies. External mail and outbound application calls are disabled. Site configuration and site-specific filesystem assets are not supplied with this database export; this is therefore not a complete production-clone certification.

Last completed: remote heads and clean starting checkout verified; real database export resolved. No new application defect is confirmed yet and no new acceptance test is claimed as executed.

Next action: restore the private export into a disposable database and establish source/target conservation observations before exercising application paths. The earlier report and its 48 CI jobs remain historical evidence for their exact tested commits.

## Confirmed finding and candidate correction

The real-data upgrade returned controlled HTTP 500: `Cannot parse code reference tags.` Long unquoted attributes exhaust the HTML-tag splitter's default PCRE stack. A synthetic 16 KiB attribute reproduces the published candidate's PCRE JIT-stack exhaustion. Disjoint possessive tokens retain the same delimiters and quoting rules with bounded backtracking. The candidate passes 52 synthetic conservation/idempotence checks under normal PCRE limits, with JIT both on and off. The old published helper fails the same new test. Only `codeassets.inc.php` changes in application code.

New executed evidence so far: exact full real-export backup/restore (18 tables, 950,272 rows, unchanged source); 117 session-transition browser/database checks each on Main and the candidate in DE/EN/FR; 17 backup schema checks for generated/invisible columns, quoted Unicode identifiers, Aria/InnoDB, exact types and plain/gzip restore. Corrected real-data migration and complete page comparison are still pending.

The corrected real-data migration now completes (65.5 seconds locally). All 4,824 page/language/access cases complete on both installations, with matching text, forms, controls and status codes. Twelve cases differ only in installation-bound private download capabilities; their actual file bindings are being checked separately.

Backport validation also exposed an older omission: escaped examples such as `[[cssbase]]` were rewritten in seven earlier development profiles. The later tested literal-protection rule is being applied to the matching older parser versions, with an independent 16-check literal/idempotence regression. This correction is limited to `codeassets.inc.php`; it does not import later feature implementations.
