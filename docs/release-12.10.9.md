# u5CMS 12.10.9

CSV exports now protect ordinary form cells that begin with a spreadsheet formula prefix. Previously, values beginning with `=`, `+`, `@` or `-` could be interpreted as formulae when an administrator opened the exported file in spreadsheet software. This also affected historical values whose leading character was stored as an HTML entity and decoded during export.

The correction applies to form labels, values, notes, account names and IP fields. It prefixes only affected cells with the established spreadsheet text marker. Empty fields, semicolon transport, UTF-8 content and existing archived-sender-evidence columns retain their previous representation. There is no schema or configuration change.

## Verification

The regression invokes the shipped export endpoint against a disposable MariaDB database. It exports 1,024 current and historical rows containing all four formula prefixes, leading whitespace, empty cells, semicolon transport, HTML entities and multilingual text. The same test fails on v12.9.5 and 12.10.8 with 5,380 unprotected cells and passes after the correction without losing the UTF-8 byte-order mark or changing the fixture values.

The resource-activity regression also submits a rapid duplicate Rename while an actual Rename is paused at its publication checkpoint. The second request is rejected with the live source and target, while the original plan completes or resumes unchanged.
