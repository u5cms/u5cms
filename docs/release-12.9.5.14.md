# u5CMS 12.9.5.14

## Search indexing

A full rebuild with an outstanding resource error retains the last successful
completion date. Between retry rounds the worker checks the site's save marker
at intervals of up to 15 minutes when eligible visits continue. If content was
saved during or since its last full sweep, the next sweep starts at the beginning
and takes a new upper bound. Newly created pages and previously scanned pages can
therefore appear in search while the original resource still needs repair.
Edits during an active sweep never rewind it. A check with no new edits touches
only checkpoint files and preserves the scheduled database retry. Batch size,
SQL wait limits, the inter-batch pause, automatic-update settings and the
exclusive worker lock still apply. Quiet sites need an eligible visitor or
an explicit continuation to advance.

The automatic starter and worker recognise a completed index timestamp ahead
of the server clock. A full rebuild replaces it with a valid timestamp. A
checkpoint that claims to have started in the future is rebuilt as well.

## Forms

Two protected forms on the same page have independent Turnstile widgets,
submit controls, expiry callbacks and resets. Each submission carries a
form-local widget number so its iframe response resets the submitting widget.
The number is discarded before saving or mailing. Server-side token
verification remains mandatory whenever either protection setting is enabled.
Previously protected installations still require their Turnstile site key
and secret.

## Installation and rollback

Install the complete distribution, including the form include, the form-save
and PayPal handlers, and the form validator. Preserve the site's configuration,
data, media and fileversions directory. Existing 12.9.5.13 checkpoints are
readable. Rolling application files back together to .13 preserves safe cursor
recovery but restores its older retry policy. No schema change is needed.
The logout tooltip for this release reads `logout u5CMS 12.9.5.14`.

The HTTP/MariaDB regression holds an old error open while checking two real
public search results. It also checks completion of the outstanding work after
repair and recovery of a future completion date. The Chromium suite exercises
two forms, expiry and an iframe response with an isolated provider fixture.
