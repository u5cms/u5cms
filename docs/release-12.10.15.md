# u5CMS 12.10.15

Two people could open the same shared form and save within one second. The configured first-saver-wins check compared whole-second timestamps, so the second submission could replace the first active value without a conflict warning.

Forms now carry a fingerprint of the loaded row alongside their opening timestamp. The save handler compares it while holding the existing write lock. A stale submission receives the existing conflict warning and keeps its draft available for copying. Reopening the form loads the latest value, and a successful callback save refreshes the fingerprint for the next edit.

The configured `all`, `foreign` and `none` policies retain their meaning. Personal monofill rows retain their owner scope. Stored form timestamps remain numeric. Existing data needs no migration. Forms already open during an update continue to use their older timestamp check until refreshed.

`u5cms_formversion` is reserved internal transport metadata. Do not use this name for a business field. Save, edit and delete handlers consume it before checking or storing the form fields. Both the built-in storage routine and a declared site writer refresh it before a successful callback continues editing.

The issue was reproduced through shipped HTTP form actions on the origin release, the direct stable predecessor and all six maintained lines. The regression fixture also exercises reload, invalid required input, duplicate submission and two independent PHP workers. The corresponding correction is maintained on V13–V17 with each line's own request validation and version number.

Additional collateral checks cover stale and refreshed deletion screens, personal and shared callback forms, key-based selection, an MF marker outside the form, retained upload URLs and dynamic field groups. These use a disposable installation and synthetic SQL records. They do not certify a site's complete upload workflow or live deployment.
