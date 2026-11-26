# Backend SAML return URL maintenance, 26 November 2026

Authorised scope: repair the media editor, publish stable 12.10.5 and carry the correction to the five maintained future branches. The database is not changed. The maintenance changes the backend login button and the URL value in each login-save entry point. Original application bytes and source line endings outside those changes are retained. Main also updates its version comment, logout tooltip and release index; future lines keep their own application versions.

Completed locally: both defects were reproduced independently; the corrected actual PHP prompt, sanitiser function, SAML transition expressions, redirect guard blocks and final return construction pass on each branch. Sixteen button cases, 32 full-chain round trips and 26 redirect denials pass per line using PHP 8.4 WebAssembly. The metadata type guard, authentication, SQL writes, save conflicts, OIDC, renderers and search implementations are unchanged.

Native PHP 8.3/8.4 and existing host/IDNA checks are scheduled by the focused workflow. The local WebAssembly runtime lacks intl, so the existing IDNA suite was not claimed as a local pass. Existing broader branch workflows run separately. Source publication, workflow success and live deployment are distinct outcomes.

Live acceptance: the corrected installed variant opened the requested image editor, saved three caption fields and returned all 15 fields unchanged on fresh comparison. The public caption and keyboard zoom were verified. A fresh native browser form was used after token-rejected automated interactions; token generation and validation were not modified. This was a narrow correction on an installed site, not deployment of every development branch.

Current next action: publish the tested candidate, inspect native checks and retain any concurrent branch changes. No credentials or real fixture accounts are part of the tests.
