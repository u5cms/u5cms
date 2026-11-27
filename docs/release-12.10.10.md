# u5CMS 12.10.10

Database backups now retain an empty-string column default as an empty string. The previous legacy schema writer treated an empty default like `NULL`. The shipped `resources.name` column therefore lost its `DEFAULT ''` clause when its backup was restored, even though the row data itself remained present.

The correction distinguishes `NULL` from an empty string at the two existing default-value decisions. It does not change the database, stored rows, backup format, compression, mail delivery, locking or configuration. V13 carries the same legacy-writer correction while retaining its own application version. V14–V17 already use their branch-specific lossless database exporter and require no application change.

## Verification

The regression invokes the shipped authenticated backup endpoint against a disposable MariaDB database, imports the generated structure and data SQL into a second database and compares the result. It checks the actual `resources.name` definition and an old form row containing empty fields, semicolon transport, an apostrophe, a backslash, a line break and multilingual text (`œuvre`, `Müller`, `中文`) byte for byte.

The test fails on v12.9.5, v12.10.8, v12.10.9 and the unchanged V13 source because the generated SQL omits the empty default. It passes after the two-line correction on Stable and V13. The same restore test passes without modification on V14–V17. Existing concurrent-save versus backup checks remain green.
