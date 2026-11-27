# u5CMS 12.10.8

**[Read the u5CMS documentation](docs/README.md)** — practical guides, configuration examples and reproducible exercises in British English.

Version 12.10.8 serialises versioned form edits so simultaneous saves cannot leave two active versions of one response. [Release notes](docs/release-12.10.8.md).

Version 12.10.7 restores CSS editor saving and uses the stored revision when an editor opens a resource. [Release notes](docs/release-12.10.7.md).

Version 12.10.6 preserves compound fragments and short relative return queries after login. It adds a cumulative SQL comparison of ordinary form behaviour since 12.9.5. [Release notes](docs/release-12.10.6.md).

Version 12.10.5 preserves complete backend SAML return URLs, including media resource names, through login. [Release notes and verification](docs/release-12.10.5.md).

Version 12.10.4 corrects the optional Trusted Device installer for locally formatted login forms and commented configuration examples. [Release notes and verification](docs/release-12.10.4.md).

Version 12.10.3 frees obsolete browser places after a password change and returns to browser management after ordinary login with JavaScript disabled. [Upgrade and release notes](docs/release-12.10.3.md).

Version 12.10.2 keeps the trusted-browser management page open for login and returns the account holder there after signing in. [Upgrade and release notes](docs/release-12.10.2.md).

Version 12.10.1 restores trusted-browser registration and revocation in the browser and improves recovery after a database read error. [Upgrade and release notes](docs/release-12.10.1.md).

Version 12.10.0 consolidates the stable 12.9.5 updates. When archived sender evidence is enabled, forms that depend on per-user version checks are rejected before storage or mail delivery. [Upgrade and release notes](docs/release-12.10.0.md).

Optional archived-SAML sender evidence for authenticated forms. [Installation and release notes](docs/release-12.9.5.15.md).

New and edited pages remain searchable during index recovery. Protected forms on the same page have independent Turnstile widgets. [Release notes](docs/release-12.9.5.14.md).

Full indexing resumes after a visitor leaves and can make progress past an interrupted resource. [Release notes](docs/release-12.9.5.13.md).

Switching accounts on a trusted browser now retains the newly selected account and revokes the previous registration on that browser. [Release notes](docs/release-12.9.5.12.md).

Search indexing now runs through background requests after the page has loaded. Batches, pauses and lock handling continue without hidden index frames repeatedly activating the browser tab loading indicator. The same transport handles automatic access updates. [Release notes](docs/release-12.9.5.11.md).

Optional trusted-browser sessions now support a configurable maximum per account, individual revocation and multiple cloud browser profiles. The feature remains disabled by default. [Configuration and user guide](docs/trusted-browsers.md).

The homepage selector loads only page names and homepage flags, including on large multilingual sites. Saving validates the selected page before switching the homepage in one database statement.

Ordinary saves remain available while indexing and backups run. A save marks pending index work on the server. Single-resource and full indexing coordinate through the same lock, respect pending Rename operations and bound database lock waits.

Full indexing runs in resumable batches. The search page shows the date of the last successfully completed full index. An interrupted or failed attempt leaves that date intact. Rename also bounds the lock wait on its own database connection. After a database conflict, the existing recovery plan allows the same operation to finish.

The stable release includes memory-efficient search, corrected resource renaming and configurable SELF permissions. Existing installations continue to associate records with the individual user unless their administrator enables domain-based ownership.

[Trusted-browser sessions in 12.9.5.10](docs/release-12.9.5.10.md) · [Media access provisioning in 12.9.5.8](docs/release-12.9.5.8.md) · [Non-blocking saves and fast Rename in 12.9.5.7](docs/release-12.9.5.7.md) · [Save and index coordination in 12.9.5.6](docs/release-12.9.5.6.md) · [Rename lock handling in 12.9.5.5](docs/release-12.9.5.5.md) · [Cumulative changes since 12.9.5](docs/release-12.9.5.4.md) · [SELF permissions](docs/onlymine.md) · [Branch guide](BRANCHES.md)

## About u5CMS

u5CMS combines multilingual page editing, reusable content objects, media, protected areas and forms backed by MariaDB/MySQL. The backend keeps source, metadata and histories together, while visitor-facing pages use the installation's own templates and settings.

The [handbook](docs/README.md) explains supported workflows through practical examples and repeatable observations. The [branch guide](BRANCHES.md) distinguishes the stable source from development features. Existing tutorials and additional reference material are available on the [u5CMS website](https://yuba.ch/u5cms/).

u5CMS is distributed under [GNU GPLv3](LICENSE). Retain the licences and matching dependencies when redistributing it.
