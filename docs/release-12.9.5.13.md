# u5CMS 12.9.5.13

## Search indexing

A public visit can resume an eligible checkpoint left by a previous browser. The quiet period after an edit still applies to starting a new automatic rebuild. Resuming always uses a continuation request, so a late visitor cannot restart a rebuild which has already finished. Automatic updates remain subject to the existing site setting, cookie and frame conditions.

The worker records the resource key before processing a row. If the worker fails or is killed, the existing backoff protects the site from repeated immediate attempts. On a later eligible request, the worker can advance beyond the interrupted row. It records the first and last missed rows and revisits that interval after the rest of the pass. Separate errors can narrow to a smaller interval as individual rows recover. Storage is bounded independently of the number of errors. The server error log records the deferred resource key in hexadecimal and its row state. Database connection and discovery failures retain their existing backoff. The last completed watermark, and the format marker on branches with the newer search, are published only after all outstanding rows have been processed.

The existing kernel lock, SQL wait limits, source comparisons, eight-resource batch limit and inter-batch pause remain in effect. A competing browser reports that maintenance is running. An idle continuation reports that the index is current.

## Protected forms

Sites which previously enabled the Google reCAPTCHA setting continue to require server-side verification. Configure the Turnstile site key and secret before upgrading such a site. Missing credentials cause protected submissions to be refused. To deliberately disable verification, both the current and legacy settings must be disabled or removed. Sites with neither setting enabled retain their previous behaviour.

## Update and recovery

Deploy the complete release together, including the background JavaScript. Preserve site configuration, resource data, media and the fileversions directory. An existing 12.9.5.12 checkpoint is readable. Earlier workers can resume from before the first deferred resource when rolling back the complete distribution. No schema change or scheduled task is required. Regular eligible visits or an explicit continuation are still needed to do background work.
