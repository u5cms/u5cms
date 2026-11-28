# u5CMS 12.10.13

When a saved search link opens a page that no longer contains a visible match, the legacy highlighter now leaves the scroll position alone. Previously it tried to read the position of a missing highlight and raised a JavaScript exception. Visible matches still scroll into view after any matching showblocks open.

This correction requires no database or configuration changes.
