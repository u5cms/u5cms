# u5CMS 12.9.5.8 — media access provisioning

Install the complete distribution over the existing installation and retain its config.php, database, r and fileversions directories. The PHP process needs the existing CMS write permissions. No database migration or cron job is required. Deploy the files as one consistent version.

The first eligible frontend visit or a relevant CMS change builds the reference catalogue under fileversions. An administrator can rebuild it after restoring the database and media together by running `php htaccess.php --rebuild` from the site's root with the site's PHP configuration. Existing assets use their published protection during initial discovery.

Editing a reference updates the old and new target. Uploading the first language variant, copying, reactivating a deleted object, renaming, managing group members and changing backend credentials also schedule work. The source catalogue includes the five languages, metadata, archived rows, literal CMS links and direct media paths. A twice-bracketed example has the legacy literal semantics. A page embedded with $$$ contributes the references in its own stored text. The ordinary search index maintains its own freshness marker.

Public uses, combined closed-group logins and explicit protection pages retain their precedence. A formerly private asset with no remaining references stays restricted to current backend accounts. A previously public orphan remains public. New uploads inherit the existing uses of their name. Video posters are protected with their video. Anonymous and conditional requests for private files must pass the same access checks as ordinary requests.

An interrupted permission update remains pending and is retried on a subsequent eligible request. A pending change to a specific file can temporarily make that file unavailable. Existing SAML users can log in during unrelated publishing work. Saves do not wait for a full reference scan or for indexing. With automatic refresh explicitly disabled, use the repair command if a process was terminated mid-update and no subsequent relevant CMS change occurs.
