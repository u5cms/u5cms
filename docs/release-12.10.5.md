# u5CMS 12.10.5

## Who needs this update

Sites using backend SAML login should update. Signing in while opening a media editor or another backend URL with several query parameters could lose the resource name, or turn its query separator into `&amp;amp;`. The media type check then correctly refused to display a form without the requested resource.

The correction preserves the complete return URL through the existing login route. It requires no database migration, configuration change or new trusted-browser registration.

## What changed

The backend SAML button encodes the return URL as one query value. Both login-save entry points undo exactly the one HTML-escaping layer applied by their connection bootstrap before enforcing the existing redirect policy. The common request sanitiser and the SAML provider remain unchanged. Literal entity text and already URL-encoded query values keep their meaning.

Resource/type matching, same-site and port restrictions, rejected schemes, control-character checks, authentication and save conflict checks remain in force. Ordinary password and HTTP Basic authentication are retained. Development lines keep their own OIDC, rendering, search and version behaviour.

## Verification

`U5_TEST_PHP=php node tests/backend-saml-return-url.cjs` renders the actual signed-out PHP prompt and executes its JavaScript button. Sixteen cases cover media types, parameter order, nested encoded URLs, literal entity text, fragments, language fields, subdirectory installations and a non-default HTTP port. The full-chain fixture executes the actual sanitiser function, redirect guard blocks, SAML URL expressions and final return construction for both login-save entry points: 32 round trips and 26 rejected redirect targets. It uses no live credentials or database.

The original backend button fails by losing `name`. The original login-save files fail the full-chain test by producing `&amp;amp;name`. These failures were reproduced before applying the corresponding changes. Local PHP 8.4 WebAssembly execution is recorded separately from native PHP/GitHub acceptance. The focused workflow runs native PHP 8.3 and 8.4, including the existing 40-case host/IDNA policy regression. Existing wider release workflows remain independent acceptance checks; the focused tests are not a claim of full application certification.

## Installation and rollback

Use the normal complete release update and preserve local configuration, content, files and site-specific integrations. If applying the narrow correction manually, retain the installed files and apply only the corresponding edits in `u5admin/usercheck.inc.php`, `loginsave.php` and `u5admin/loginsave.php`. Compare local variants first. Do not replace a locally adapted file with a different development line.

Verify an ordinary metadata edit, save, close and fresh reopen. In a separately signed-out test profile, open an editor URL with both `typ` and `name`, complete SAML login, and check that it returns to the same resource with its fields intact. An existing malformed editor tab must be closed and opened again through the media list.

Rollback restores the saved versions of those three files; there is no data or configuration migration to reverse. Restoring them also restores the previous return-URL defect. A production deployment and live acceptance must be recorded separately from source publication.
