# Mobile controls backport

Authorized on 31 October 2026: backport the info.agov.ch mobile close, desktop
and search treatment to every release branch from 12.11.0 through 12.14.0.
Then create a separate cumulative branch for a restrained default-site design
refresh, without introducing responsive CSS or replacing the mobile script.

## Implementation and operation

The existing controls keep their nodes, onclick handlers, cookies, URLs and
mobile/desktop decision. The appended enhancement supplies SVG icons, 44px
targets, rounded borders, hover and keyboard focus, five-language accessible
names and Enter/Space activation of the legacy div buttons. Closing navigation
returns focus to its opener. Delayed search controls and rebuilding the toolbar
inside the mobile navigation are enhanced too. After asynchronous construction,
the observer watches the navigation rather than the entire page.

The source addendum is in `u5admin/mobiletools-source.inc.php`. Distribution
`r/mobile.js`, its `r/mobile!js.tpl` reset baseline and the mobile C-object in
both SQL seeds contain the same addendum. Existing enhanced AGOV sources are
recognized and retained without appending a second addendum.

Existing installations use the normal complete update, with only `runonce.php`
overwritten in `r/`. Include both new `u5admin/mobiletools-*.inc.php` helpers.
After older unfinished code migrations finish, the update appends the addendum
to the actual mapped mobile C-object and its existing reset template, preserving
custom source and nonempty language variants. It records original/current
history, regenerates exports and finishes a separate guarded checkpoint.

`fileversions/mobiletools-2026-10-31.pending.php` retains a failed/interrupted
plan; resolve its reported error and reopen the backend. Concurrent source or
template edits are detected. The completed `.done.php` retains exact before and
after images for manual rollback. Keep it with the normal file/SQL backup. Do
not delete a pending plan to force an update. Repeating a completed update does
not reappend or overwrite later intentional changes. Deployment and rollback
should use a paired application/database/files backup.

## Executed verification

Validation completed on 31 October 2026. All nine target branches were tested
individually with PHP 8.3.6, MariaDB 10.11.14 and headless Chromium 143.0.7499.4.
The application sources were served by PHP's built-in HTTP server with nosniff.
All databases, accounts, forms and site copies were disposable local fixtures.

* Each branch passes 84 real-browser checks: 375px mobile navigation, all five
  label languages, actual 44px targets/corners/SVGs, clipping/focus margins,
  original handlers and search URLs, hover/focus, Enter/Space, focus return,
  upper/lower openers, toolbar rebuilding, search arriving after ten seconds,
  desktop cookie/navigation and absence of JavaScript exceptions.
* Each branch passes 69 database checks: both SQL seeds, UTF-8 and Latin-1
  source columns, differing history charsets, exact custom-source/template
  prefixes, original history, NULL/empty variants, repeat runs, interruption
  after planning/database/templates/export, concurrent edits, database-managed
  generated/automatic columns and renamed mobile roles/reset templates.
* The existing core migration suite passes on every branch (the historical
  counts differ by release). On the cumulative 12.14.0 branch, the complete
  inherited migration/database suite passes 1,671 checks, including its SIGKILL
  recovery fixtures. The existing web suites pass 51 frontend and 98 backend
  checks, including shared-origin scripts, twelve real uploads, iframe saves,
  Unicode, CSRF, lifecycle operations and Basic/session authentication.
* The canonical enhancement exactly matches the suffix in each branch's two
  SQL seeds, packaged JavaScript export and reset template. Source syntax and
  whitespace checks pass. Browser screenshots were visually inspected; clipped
  left targets and overlap with navigation text were corrected before the final
  browser matrix.

Scope limits: local Chromium and PHP 8.3 were executed, not live production
browsers/sites, external identity providers or PHP 9. These are local test
results, not GitHub Actions results. Main is not changed; merging and deployment
remain separate. The subsequent default-design work belongs to a new branch.

## Work checkpoint

Last completed step: final nine-branch browser/database/core validation and
four-source parity. The branch-local commits publish only this correction and
its regression runners; previous feature differences are preserved.
Changed paths: `r/mobile.js`, `r/mobile!js.tpl`, the two SQL seeds,
`u5admin/codeassets-migrate.inc.php`, the two `u5admin/mobiletools-*.inc.php`
helpers, this report and three `tests/mobiletools-*` runners. The cumulative
branch also updates `docs/development-status.md`.

Next action: verify guarded publication on all nine branch refs, then create
`12.15.0-Default-Design` from the corrected cumulative 12.14.0 head. The design
refresh is authorized; it must keep `u5standard.sql`, `u5conference.sql`, CSS
exports and reset templates consistent without introducing responsive CSS.

| Target branch | Starting SHA |
| --- | --- |
| `12.11.0` | `43ee1e3e1ebc3c0b56b362c60c059447f5cf3641` |
| `12.11.1` | `16377863d62f0b9a53235fbfabf236d1b18a425c` |
| `12.11.2` | `9b3bb2879e19744502b1d8334b556fa8ad8cbe26` |
| `12.11.3` | `049b10325f6cefb3444d3b3a4cad96836500d21d` |
| `12.11.4` | `f349af9b4ffcb14087e332e4cd6e6b8a6b02637f` |
| `12.12.0-OIDC` | `acdd8d6cf362c93803e94897336d347e26d1dba6` |
| `12.12.1-General-Corrections` | `f097070d54ac19003bb7b145ee91f6652a29e534` |
| `12.13.0-Textarea-Interactions` | `c0748dbedb6200c007438555ca76999aa0d97cea` |
| `12.14.0-PHP9-Compatibility` | `7657a112272ab82d0d62915b9e12ca4b3157149b` |
