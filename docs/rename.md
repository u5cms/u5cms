# Resource renaming in 12.12.1

The Rename form finishes the object, files, references, histories and publication
before reporting success. It no longer navigates away after three seconds or
uses the disappearance of the old database name as proof of completion.

## Preserved policies and dependencies

| Item | Behavior |
| --- | --- |
| Update links: yes/no | Always changes object ownership, file names and form ownership. Only yes rewrites references. |
| `$ignoremanualfullpaths == 'yes'` | Leaves manually entered media paths and `c=`/`n=` query targets untouched. Normal CMS links still follow the object. |
| `$alsorenamelinksinformdatadatacsv == 'yes'` | Also rewrites references in submitted `formdata.datacsv`. Other form columns stay unchanged. Form ownership always follows the object. |
| Five languages | Handles `content`, `title`, `desc` and `key` for all five languages in resources and resource histories. |
| CMS links | Preserves the inclusion/link variants, labels, colon variants, query suffixes, `[go:]...[:go]`, `[lo:]...[:lo]` and `\|name\|`. |
| Titles | Keeps `[[title]]`, `[[[title]]]` and `[[[[title]]]]` literal. A real link nested within a title still follows its target. Literal old bracket-mask strings are never interpreted as delimiters. |
| Templates | Updates `{{{name}}}` and `[_name_]` in the active HTML template and updates dependencies in matching reset templates in `r/`. Publishes active code exports after the database changes. |
| Code contexts | JavaScript arrays, identifiers and CSS selectors remain literal. Explicit CMS inclusions and actual query/media references follow their target, including embedded script/style blocks and JSON-escaped media paths. |
| Free images | Keeps the exact plain-name, `c=name` and `c=name&` target forms working. |
| Relative family links | Updates `[_pagename!_]!suffix` within the matching name family, and in opted-in form data. Underscores are literal characters, never SQL wildcards. |
| Login globals | Updates all five login introductions and all five outros. |
| Files | Renames existing language variants and album positions 100–999 in one directory pass. Keeps unrelated names, alternate formats, dotfiles, permissions and bytes. |
| Video posters | Moves the `v` companion resource, history, directory and language filenames together with a video. A companion occupied by an unrelated resource kind blocks the operation. |
| Protected files | Keeps membership hashes and access rules. Updates the moved directory's absolute `AuthUserFile` reference. Internal symbolic links follow renamed files. |
| Intranet | Preserves membership when both names start with `!`. Clears the renamed page's membership only when leaving the intranet namespace, matching the original intent. |
| Active/archive/deleted | Retains active/archived state. Leaves deleted history ownership under its old name while updating its references. Reused target tombstones are preserved in the guarded row backup before removal. |
| Revisions and search | Marks each actually changed current resource once, advances its revision and invalidates derived search text. Marks the full rendered index pending because indirect dependants can also change. Heading-only matches do not change revision metadata. |
| Rights | Retains backend authentication, CSRF, archive HIADRI and code HIADRI checks, including retries after ownership has moved. |

Typed C resources retain the established resumable code-asset rename, role maps,
static-extension checks and reset-template handling. Their bracket parser now
also protects title delimiters. Existing 12.11 migration and rename checkpoints
keep their identifiers and remain readable.

This rewrites the CMS's recognized dependency syntax. Configuration PHP,
external bookmarks, already sent emails and arbitrary application identifiers
are not a general text-replacement namespace. Keep any installation-specific
external integrations in the site's normal acceptance review.

## Performance and preservation

The old ordinary rename issued hundreds of repeated field/table updates, most
without a history-row filter. It then tested every language and 900 possible
album numbers for every directory entry.

The replacement streams candidates once per relevant table. It transforms each
row once and journals only changed rows. Writes compare original row bytes
before changing anything; duplicate history rows retain their multiplicity.
Original column encodings are used directly rather than round-tripping through
the application's Latin-1 connection. Column capacity is checked before
applying the plan. Search cache changes from a concurrent indexer are treated as
derived data; concurrent changes to source content or metadata are preserved
and stop the rename.

The database and filesystem are not a single transactional store, especially on
legacy MyISAM installations. An interrupted rename can therefore be partly
applied. A guarded immutable checkpoint records precisely how to finish it;
it never reports completion at that intermediate point. This is recovery by
resuming the same plan, not a claim of an atomic cross-filesystem transaction.

CMS page, metadata, upload, album-sort and file-delete writers coordinate with
the existing mutation lock. They return a visible conflict response while the
rename is active or interrupted, keeping editor input. A different rename,
creation, deletion, layout operation or migration cannot pass an unfinished
ordinary rename. Backups can still acquire the lock for recovery and include
the rename checkpoints and referenced row journals.

A pre-existing target directory is moved to
`r/NEW.before-rename-RANDOMTOKEN`; it is never overwritten. A different file
occupying a required destination blocks the plan. Linked resource directories
and linked access-control/reset files require explicit reconciliation before
renaming, preventing accidental changes to shared external content. Avoid FTP,
filesystem administration or non-CMS database edits during a rename. File moves
retain the original bytes without copying. Small files have a SHA-256 guard;
large media use size/mtime and the guarded directory identity under the shared
mutation lock, so renaming a multi-GB video does not read its complete contents.
The tests independently compare the complete bytes/SHA-256 after moving files.

## Retry and recovery

1. Keep the original rename window open until its result arrives. Successful
   completion refreshes the relevant editor/repository only after the entire
   operation has finished.
2. On an error, resolve the reported filesystem, capacity or concurrent-edit
   conflict and click **Retry rename** in that result. The retry uses the saved
   original names and policy, even after the source name has disappeared.
3. Do not remove the pending checkpoint to force another operation through.
   If a source row was edited outside the coordinated writer paths, reconcile
   that row against its saved before/after versions first. The retry will never
   overwrite a different concurrent row merely to obtain success.
4. Keep `fileversions/resource-rename-*.pending.php` while unfinished. Completed
   plans are retained as guarded `.done.php` backups, including an immutable
   per-operation token, and refer to the existing compressed guarded row
   journals. These files contain site data and are PHP-exit guarded.
5. For rollback, restore a matching pre-operation database **and** file backup.
   A reverse rename is another operation and cannot reconstruct every original
   revision or resolve pre-existing naming collisions. Do not restore just one
   half of the site. Retained tombstones, template before-images and archived
   destination directories are available for targeted expert recovery.

The former `rename3.php` history-only GET entry point now returns HTTP 410 after
backend authentication. There are no remaining internal callers; history runs
inside the CSRF-protected Rename request. The old status page is read-only and
never infers success just because the old row is gone.

## Reproduce the checks

Use a disposable local MariaDB, never a live CMS database. The existing test
runtime variables (`U5_TEST_PHP`, `U5_TEST_PHP_ARGS`, `U5_TEST_PLAYWRIGHT`,
`U5_TEST_BROWSER`) also apply here.

```sh
U5CMS_TEST_PORT=3307 php tests/rename-regression.php
U5CMS_TEST_PORT=3307 php tests/rename-benchmark.php
U5CMS_TEST_PORT=3307 U5CMS_RENAME_AUDIT=1 python3 tests/codeassets-web-regression.py
```

The benchmark loads the actual legacy functions from the committed OIDC parent
`acdd8d6cf362c93803e94897336d347e26d1dba6`. It compares legacy reference SQL plus
legacy directory processing against the **complete** new ordinary rename.
It verifies all 20 resource/history text columns and every file, rather than
accepting elapsed time alone. Both sparse and dense dependency cases are
included. See the release audit for measured results and environment limits.
