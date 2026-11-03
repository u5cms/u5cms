# Correction and anti-regression review — 3 November 2026

Status: renderer corrections are published; the additional anti-regression pass
has found and corrected one large-row backup regression. The new full CI matrix
is still required. This checkpoint does not claim that final matrix has passed.

## Authorized scope and completed renderer phase

All 15 development branches plus `audit/correction-contracts-20261103` are in
scope: compare original/current Main, prior branch states and intended workflows;
correct confirmed findings and publish every affected branch without further
routine confirmation. Main and production remain held.

The specifically approved three renderer variants were accepted and published
with expected-head guards. All 16 tips/blobs were verified. Their 48 GitHub jobs
(PHP 8.3/8.4/8.5) then succeeded, including conference/Dat and independent feature,
browser and recovery suites. That earlier green matrix did not cover the smaller
backup packet limit found by the additional independent pass.

## Additional pass: executed evidence

A fresh comparison verified 17,816 application blobs and inventoried 1,005
unique application diffs across original/current Main and previous states.
The current-Main comparisons passed on all 15 branches: 576 behavioral checks,
2,080 configuration and 672 complete-view observations, no differences. Against
the pre-correction cumulative source, the two latest profiles passed 108 checks,
390 configuration and 126 complete-view observations. These are new executions.

The seed-reference pass checked 9,120 page/language/role combinations: original
Main plus all 15 branches, 38 conference pages, three languages and five roles.
Existing business-form actions/methods/targets/controls and access are retained.
Positive abstract/registration controls and nine destructive oracle mutations
prevent vacuous comparisons. The deliberately modernized navigation search has
its own real-browser suite. Ten mobile profiles passed 84 checks each. Both
rename implementations passed sparse/dense large-site comparisons with actual
legacy SQL functions, conserving all 20 text columns and every file.

## Confirmed backup finding and correction

Original Main's actual exporter restores a 24 MiB row under a 32 MiB packet
limit. The streaming hex exporter produced a 50,331,731-byte INSERT and failed
under the same valid limit. Increasing the CI server's packet budget would not
correct that application regression.

The correction keeps lossless hexadecimal encoding and the existing small-row
format. Oversized rows use bounded fragments in an internal staging table in
the restore target. Fragments survive connection changes. An incomplete value
raises an INSERT error even in non-strict mode; it cannot silently become NULL.
The staging table is cleared between rows, dropped at completion and cleaned by
a retry of the same dump. The database being backed up is not modified. Engine
selection uses actual metadata, not text guessed from SHOW CREATE comments.
This applies to nine development branches plus the audit branch.

The dedicated regression has passed 28 checks on both current and historical
PHP-helper profiles: real original control; 32 MiB restore; typed Unicode,
Latin-1, binary, NULL, empty, numeric/time/JSON values; plain/gzip under a 1 MiB
packet limit; verified fresh sessions during import; fail-before-write; retry;
and staging cleanup on MyISAM and InnoDB. The strict real-browser editor/SQL/ZIP
restore suite passed 507 checks, including a 24 MiB row exported under 64 MiB PHP.

## Publication and final acceptance still required

Publish the corrected backup and regression tests on affected branches. CI now
also includes the independent multilingual/role conference reference everywhere
and the mobile browser suite on every supported profile. The manifest requires
the tests it declares. Recheck all actual matrix jobs and final remote bytes,
then record completion. Local runtime: PHP 8.4.27, MariaDB 11.8.6, Chromium 156;
CI uses PHP 8.3/8.4/8.5 and MariaDB 11.4 with disposable sites/databases.

Main remains `78cadbf87b7307203b8bd4ff96929b306af7d982`. No production installation,
merge or release replacement is performed. Mail transport is disabled; real
external mail/payment/federation acceptance and an actual PHP 9 runtime are not
certified here. The separate detailed earlier audit payload remains excluded.
Operation, packet/session requirements, staging lifecycle and rollback are in
`docs/regression-ci.md`; rollback uses each guarded commit's parent.
