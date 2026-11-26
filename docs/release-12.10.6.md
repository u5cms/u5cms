# u5CMS 12.10.6

## Summary

Login now retains the full fragment of a return URL containing more than one `#`. A relative target such as `?c=page&l=fr` also retains its final query value: the login timestamp is appended as a separate parameter. Both login-save entry points have the same two-line correction. These pre-existing edge cases were reproduced independently during the cumulative review.

The redirect policy, authentication, transaction protection, configuration and database schema are unchanged. The source correction does not alter form storage or mail handling. Development branches retain their own version identifiers and features.

## Verification

The actual rendered backend button passes 18 cases. The full login return-chain fixture passes 44 round trips and 26 invalid-target denials. It checks the separator of the added timestamp as well as preserving every byte of the original target; merely removing the timestamp could conceal corruption of the last query value.

The new [form evolution fixture](../tests/form-evolution-regression.py) executes the actual create, edit and delete handlers, version checks and SQL compatibility layer using connection-private MariaDB tables. Eighteen scenarios cover ordinary submissions, disabled sender evidence, captured receipts, excluded columns, callbacks, repeated submissions, two form steps, replacement/history, stale versions, different owners, shared and per-user forms, administrator edit/delete and invalid submissions. It compares stored records and captured messages across releases from 12.9.5 and the five maintained future lines. Rendering and authentication are explicit fixture boundaries; wider browser and permission suites remain separate.

See the [evolution checkpoint and acceptance scope](evolution-audit-2026-11-26.md). Native PHP 8.3/8.4 and the wider release workflows must pass on the published candidate before merge. Their actual final status is recorded on the release pull request; this document does not turn an uncompleted workflow into acceptance.

## Upgrade and rollback

Use the normal complete release update, preserving configuration, site content, uploaded files and local integrations. No database or configuration migration is required. A manual targeted repair consists only of the two corresponding return-target lines in `loginsave.php` and `u5admin/loginsave.php`; retain the installed variant and compare it before editing.

Check ordinary and federated login, a metadata edit with fresh reopening, and the installation's own complex forms in its disposable acceptance environment. Source acceptance and production deployment are separate operations. Restore the saved matching files to roll back the targeted repair; the previous edge cases then return.
