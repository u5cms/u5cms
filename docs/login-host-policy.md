<a id="gemeinsame-korrektur-der-login-domainprüfung"></a>
# Shared correction to login-domain checks

31 October 2026. This correction is carried forward to all 16 existing branches. It changes neither the version number nor the schema or stored user identifiers.

The four existing login checks used transitional IDNA processing. For example, this treats `faß.example` as equal to `fass.example`, although the valid ASCII representation of the first domain is `xn--fa-hia.example`. As a result, a different domain could be accepted and a valid Unicode/Punycode pair rejected.

The checks in `loginsave.php`, `u5admin/loginsave.php`, `saml/login.php` and `u5sys._login.php` now use non-transitional ASCII processing. Existing path, port and protocol checks are retained. Historical CMS normalisation of stored email/user identifiers is a separate contract and is not changed. Without an available IDN API, the previous direct host comparison remains.

The direct backend endpoint also had an incorrect relative include path (`u5admin/u5idn.inc.php` from the `u5admin` directory). The path is now unambiguously relative to its own source file. The failure was reproduced in the real HTTP endpoint, rather than worked around by weakening the domain check.

`tests/login-host-regression.php` executes the actual four checking blocks without database or login side effects. Ten independent permitted and rejected cases per file cover ordinary domains, case differences, distinct IDN domains, the valid Unicode/Punycode pair, relative paths, protocol-relative URLs and different ports.

Before the correction, 16 of 40 cases failed on the cumulative test source. The prepared source on all 16 branches passes 40 cases with PHP 8.3.6 and native intl support (640 cases and 64 syntax checks). The new dependency source also passes the 40 cases using the supplied polyfills on PHP 8.3.6 and 8.5.12. Additional HTTP checks on the cumulative branch use the real endpoints, refuse to follow redirects and contact no external domains.

These are local tests in disposable installations, rather than GitHub CI or changes to a production CMS installation. The original main source remains available at commit `9d366253417822c279692767b89dcf1ff0388c5c` for the evolution comparison.
