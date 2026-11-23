# u5CMS 12.9.5.12

An explicit account selection takes precedence over a previous trusted-browser registration. The previous registration for this browser is revoked when the selected account changes. The newly selected account goes through the regular authentication checks. Other browsers registered to the previous account retain their own registrations.

Install the complete distribution using the normal update procedure. Retain the installation's configuration, site-specific files and data. This update requires no database migration. The existing trusted-browser configuration and browser quotas continue to apply.

The database regression covers equivalent account names, switching accounts, restarting the browser after the switch and the status of a second registered browser.
