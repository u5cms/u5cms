# Configurable HTTPS detection

`$evaluateifhttpsisinuse` is trusted site configuration containing PHP. Existing
installations can either assign `$httpsisinuse` or return the detection result.
A non-null return takes precedence; it is converted using PHP's existing boolean
semantics. `true`, `1`, `"1"` and `"on"` indicate HTTPS; `false`, `0`, `"0"` and
`""` do not. With no return, or a null return, an assigned value is retained.

The site-specific expression must correctly detect its actual connection or
trusted proxy. This change does not read or trust an additional HTTP header.
Frontend and backend HTTPS enforcement retain their separate configuration
switches. Existing session, authentication and authorization policy is preserved.

The optimization introduced in 12.10.0 accepted only boolean returns. A site
whose working detector returned `1` or `"on"` therefore redirected an HTTPS
request again when HTTPS enforcement was enabled. Every affected development
branch now preserves the earlier scalar-return convention. The available SAML
and OIDC detector blocks follow the same convention.

Run `php tests/https-detection-regression.php` to execute the actual detector
blocks through fourteen return/assignment profiles. The evening evolution audit
also records fresh original-first HTTP tests that inspect redirects without
following them, complete branch view comparisons, and cumulative integration
checks. Real provider acceptance remains a separate deployment check.

No database or installed configuration migration is required. Reverting the
one-line detector edits restores the previous branch behavior.
