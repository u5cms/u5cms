# Updates, backups and recovery

[Documentation home](README.md)

## Summary

An installation is more than its PHP files. Recovery needs a matching set of application code, private configuration, database content, site resources and uploaded files. An interrupted operation can also require its saved recovery plan.

**For** installation administrators. **Version** coordination exercised on 12.10.0; migrations depend on the target branch. **Scope** a matching application, configuration, database and file set.

## Identify what must travel together

| Component | Why it matters |
| --- | --- |
| Application version and bundled dependencies | Defines the code and supported data formats. |
| Existing `config.php` and private integration settings | Retains the site's database, mail, authentication and optional-feature choices. |
| Database | Contains resources, accounts, form records, metadata and histories. |
| `r/` | Contains site media, templates and other published resources. |
| `fileversions/` and user uploads | Contains retained originals, backups, uploaded data and operation state. |
| Pending Rename or migration data | Allows a partially applied operation to finish consistently. |

Know whether the backup you are holding includes application files. The CMS file archive is explicitly named `FilesExclCMSsys`; its name is a reminder that it does not replace retaining the matching CMS distribution.

## Before an update

1. Record the current version, target version and chosen branch.
2. Read the target's release notes and inventory local code/configuration exceptions.
3. Finish an outstanding Rename with its matching code, or preserve its complete recovery set for expert handling.
4. Take a recoverable database and file backup and retain the previous application and private configuration.
5. Stage the target release with representative site data and verify the workflows the site actually uses.

Do not replace a maintained site's resources with a fresh demonstration seed. Likewise, a general configuration sample cannot reconstruct private site settings.

## Install one consistent source version

Use the complete matching release rather than selecting individual files whose names look relevant. Background JavaScript, form handlers, authentication helpers and bundled libraries can form one change. Preserve the site's configuration, assets and data according to its established deployment procedure.

After deploying the files, sign in to the backend at `u5admin/` beneath the installation directory. The backend runs the release's `r/runonce.php` when it is present. Follow any migration or recovery message before opening the updated site to readers. The [installation and update notes](../___readme_howtoinstall_and_license.txt) accompany the release.

Verify ordinary page viewing and editing, each active language, login/logout, a representative form, access-protected media, search and any site-specific integration. If the layout looks different, [compare the stored stylesheet with its export](stylesheets.md#if-the-result-differs). If an optional feature is enabled, check its additional prerequisites and failure handling as well.

For example, archived sender evidence requires compatible code and private configuration together. Downgrading only the handler while retaining the evidence guard is expected to stop incompatible access. That is a recovery boundary, not a reason to bypass the guard.

## Understand a failed backup

A backup taken while source content changes must not quietly replace the last known complete snapshot with an inconsistent one. Current backup coordination checks the generation before publishing its result. An ordinary editor save can continue while backup work is running; the backup may have to fail and be repeated in a quieter window.

The local backup exercise uses two real HTTP servers: one pauses a database or ZIP backup before publication while the other saves a resource. It checks the actual response and retained previous output. This distinguishes “a backup job ran” from “a consistent new backup was published”.

Settings `$backup_compress` and `$backup_send_mail` control compression and email delivery of database backups. Mailed attachments are another copy of the backup output, rather than independent proof that a restore works.

## Recover the right layer

| Problem | Recovery approach |
| --- | --- |
| A page's text was edited incorrectly | [Compare two text versions and recover the passage](editing.md#recover-text-from-an-earlier-version) in the current editor. |
| A Rename stopped part-way through | Preserve the plan, resolve the reported conflict and resume the same operation. |
| An application update must be reversed | Restore the previous matching application and private configuration; consider any data migration separately. |
| Database and media must be restored | Use the matching backup set and check ownership, access protection and derived state afterwards. |

A reverse Rename is not a complete rollback. Restoring just the database while leaving renamed files, or vice versa, can leave references inconsistent.

## Verify restoration

Restore into an isolated copy first. Check a known page and language, a form value with non-ASCII text, an attachment's actual bytes and a protected file's access decision. Retain the restore output and compare the restored values with the selected backup's expected contents.

After a matched database/media restore, an administrator can rebuild the media-reference catalogue with the installation's PHP configuration using `php htaccess.php --rebuild` from the site root. Search has its own rebuild and completion watermark. Neither derived catalogue replaces missing source content or files.

A successful local test establishes the fixture and runtime it exercised. Production cutover, external identity providers, provider mail delivery and installation-specific backup retention need their own acceptance. See the [Rename recovery reference](rename.md#retry-and-recovery), [stable upgrade notes](https://github.com/u5cms/u5cms/blob/main/docs/release-12.10.0.md) and [laboratory evidence](labs/README.md).
