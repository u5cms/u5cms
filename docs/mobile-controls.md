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

## Work checkpoint

Current phase: implementation prepared on the 12.14.0 baseline. Syntax and 132
inherited core checks pass on PHP 8.3.6. Browser and real-database verification,
all-nine-branch publication and the separate design branch are still pending.
This is an explicit incomplete-work checkpoint, not a completion or CI report.

Next action: execute the mobile browser and interruption/database matrix, fix
any failures, and backport the isolated correction using guarded remote heads.

Starting heads and final measured evidence are recorded below when complete.

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
