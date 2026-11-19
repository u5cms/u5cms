# Retrying an editor save after an error

The correction of 2 November 2026 applied to the 14 affected development branches from 12.10.0 onwards, including the MIME and search working branches.

The shared resource editor retained the submitted text after a failed request, but its notification covered the Save button and intercepted pointer events. An ordinary retry click timed out in the browser. The notification now appears at the bottom right, fits within the viewport and is announced as an alert. Pointer events pass through it. The editor still retains the text and its modified state, tracks revisions, detects conflicts and waits for the save acknowledgement. All 14 branches started with identical copies of the affected application file, `u5admin/resourcesave.js`, and received the same correction.

Browser tests on 12.11.1, 12.12.3 and 12.12.4 used Basic and cookie authentication. They exercised a real file lock, a rejected request, retained input, an ordinary retry click, a successful save, a competing source editor, retained history, byte-exact UTF-8 export and reopening the saved content. The two backend windows edited different pages so that synchronisation between two views of one page could not mask the result.

The three runs passed 63, 73 and 73 checks respectively. The 12.11.1 run covered the editing behaviour implemented in that version. Coordination of ordinary page writes was introduced in 12.12.1. The disposable installations used PHP 8.3.6, MariaDB 10.11.14 and Chrome Headless Shell 156.0.8078.80. The historical test did not involve a production installation or a merge. [Tools and results](https://github.com/u5cms/u5cms/blob/da687f5c58c560da0fcbdfa9cce357084ba3dba7/docs/2026-11-02-evolution-review.md)
