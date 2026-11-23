# u5CMS 12.9.5.11 — quiet background maintenance

Search indexing no longer keeps a browser tab visibly loading through hidden iframe requests and repeated frame navigations. Automatic full rebuilding and indexing after editor saves use `fetch()` after the containing document has finished loading. Direct browser visits to the indexer first receive a short controller page and then use the same background transport.

The existing server-side work policy is retained: at most eight resources per full-index batch, a two-second budget checked between resources, ten seconds of rest, the same kernel locks and source-digest guards, the existing crash backoff and publication of the full-index timestamp only after completion. Named index retries still stop after three deferred retries. An early continuation reports its remaining wait instead of clearing or advancing the checkpoint.

The automatic access-update launcher previously used another hidden iframe on the same pages. It now shares the background runner, so it cannot keep the tab loading after the index transport has been corrected. The provisioner's permission checks, durable tickets and publication algorithm are unchanged. Its background controller makes at most three deferred retries while access work remains busy. CLI access repair remains synchronous.

## Transport and failure behaviour

- One maintenance request runs at a time per document. Index rest periods may be used for another queued maintenance job. Duplicate requests within that document are ignored.
- The worker uses same-origin credentials, `no-store` and a structured JSON response. It never executes returned HTML or follows an authentication redirect.
- Continuations must stay on the same origin and endpoint. Their delay is bounded and cannot produce a tight polling loop.
- Network errors and invalid responses have at most two additional transport attempts. A server failure or an exhausted named retry stops the browser runner and leaves durable work pending for recovery.
- Each request has a two-minute client timeout when AbortController is available. Closing the document still leaves the existing server checkpoint and work markers intact.
- Existing completion title markers and audio cues remain available; completing access work no longer reloads a backend frame merely as a notification.
- Non-browser HTTP clients and CLI callers retain the previous response format unless they explicitly request the JSON transport with `X-U5-Background-Work: 1`. Browser navigation is detected from standard document headers and receives the controller page.

No new lock, cron job, database schema or configuration setting is introduced.

## Installation

Deploy the complete 12.9.5.11 distribution and retain the existing `config.php`, contents, uploads and site-specific settings. Install the new `background-work.inc.php` and `js/u5-background-work.js` before the updated callers. The entry points, named retry handler and save-result pages belong to the same change. No manual SQL is required.

V13–V17 receive the same transport and tests while retaining their own versions, rendering, OIDC configuration and newer-search index format checks.

## Verification

The transport tests exercise initial page-load completion, sequential requests, duplicate suppression, rest intervals, same-origin continuation validation, login HTML, bounded failures and notification errors. The Chromium test checks that the actual launcher creates no child frames, the document is complete while maintenance is still active, and continuation causes no frame navigation.

Real HTTP/database tests run the full index in both response formats, check its unchanged successful timestamp, early continuation, named updates, exhausted busy retries and the structured access-update response. Existing save/index competition, database lock budgets, killed-worker recovery, search, ownership, Rename, provisioning and authentication checks remain part of release acceptance on PHP 8.3 and 8.4.
