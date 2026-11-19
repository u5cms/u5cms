# Responsive deep review – 12 November 2026

The requested additional review starts at
`feade4b35ce34c049611c98de9d36044cede4d6e` and updates only
`12.13.0-Responsive-Design`. The current main reference was fetched and frozen at
`bf6863337cd6dd64284cf429f65da986d821d741`. The pre-responsive renderer reference
remains `3b9b8054473d41ae3bdd046257db154770942987`.

## Confirmed findings and corrections

1. **Free-image dimensions and captions.** F-objects repeated their fixed height
   on both the image and its surrounding definition list. Their captions extended
   beyond the frame; shrinking the available width could retain the original
   image height and change the intended proportions. The new responsive path
   separates frame and image dimensions, keeps the caption in normal flow and
   preserves explicitly configured proportions. Width-only images keep their
   native ratio. Height-only raster images derive the preferred width from their
   native dimensions; unavailable metadata has a bounded CSS fallback. Explicit
   zero/invalid numeric dimensions retain their historical meaning. Legacy-mode
   markup remains byte-identical.
2. **Template recognition.** A body-prefixed custom element, a body example
   inside another tag's attribute, raw text or an inert template could be treated
   as the page body. Entity-encoded marker values were missed. A legitimate
   200,000-byte unquoted attribute also caused PCRE JIT error 6 and silently
   disabled the mode. Recognition now consumes complete tags, tracks raw-text
   and template context, uses exact element/attribute names, decodes the marker
   value and handles long attributes without recursion per character. Failed
   recognition still defaults to legacy mode.
3. **Unrelated malformed links.** The global disclosure handler called `new URL`
   for every link. An authored `href="http://["` raised a JavaScript TypeError,
   even if the link's own click handler prevented navigation. Parsing failure is
   now contained; the menu handler leaves such links alone.

All three findings have failing-before/passing-after reproductions and permanent
regression coverage. No speculative changes were made to dat semantics or the
backend include paths: the actual backend block-element checker was compared
with the pre-split renderer and its response is byte-identical.

## Executed validation

The complete declared runner finished successfully against the **current main**
reference: **49 commands**, including the new backend and navigation regressions.
This covers inherited functionality as well as the responsive changes: original
and configuration contracts, formdata views, Conference seed/workflows, migration,
rename, search, OIDC, editors, private media, backup/restore, actual interruption
and recovery, mail boundaries and locked dependency installation/patches.

The source inventory covers every difference from that main reference: 923 paths
with Git rename folding, 948 without it (369 application, 450 dependency and 129
test/evidence paths). Existing functionality is checked through the executable
contracts and dependency oracles; this is not a claim of a new manual line-by-line
audit of every third-party library.

| Additional verification | Passed |
| --- | --- |
| Legacy renderer output against the pre-responsive version | 186 byte comparisons |
| Responsive syntax/dat output with intentional media differences excluded | 126 byte comparisons |
| Independent dat effects and isolated denials | 13 effects, 4 denials |
| Actual backend block-element checker | Original/current HTTP 200, identical output |
| Template/media boundaries, final repeats | 68 with PCRE JIT, 68 without JIT |
| Standard seed in Chromium / Firefox / WebKit | 323 / 323 / 323 browser checks |
| Conference seed in Chromium / Firefox / WebKit | 323 / 323 / 323 browser checks |
| Focus, resize, fragments, repeated initialization and malformed-link handling | 11 per engine |
| Changed source syntax | 3 PHP files, 3 JavaScript files; clean Git diff check |

That is 1,938 responsive seeded-browser checks plus 33 focused navigation checks.
The browser suites include JavaScript enabled/disabled, widths 320–1920px,
configured video/F-image ratios, captions, albums, tables, forms, real submission
and upload, retained nodes/state/events, five languages, search, split navigation
and printing. Phone/desktop screenshots were inspected, including WebKit.

Runtime: PHP 8.4.24, MariaDB 11.8.6, Chromium Headless Shell 150.0.7871.181,
Firefox 153.0 and WebKit 26.5. WebKit's local launcher was adjusted to retain the
path to extracted shared libraries; the browser binaries were not modified.
All executions used disposable installations/databases and local mail test
transports. No external mail, production write or deployment was performed.

[Machine-readable evidence](../tests/responsive-deep-review.json) records the
actual command outcomes, references, findings and application hashes. The earlier
[initial evidence](../tests/responsive-validation.json) remains a historical run;
it is not substituted for the new results. The complete runner used
`--group all --baseline <frozen-current-main>`; browser engine repeats use
`U5_TEST_ENGINE=firefox` or `webkit` and an installed Playwright engine.

## Compatibility and remaining boundaries

Dat and its helpers remain byte-for-byte unchanged from the pre-responsive
renderer. This review changes no schema, SQL seed, site data, custom template or
legacy mobile asset. Existing templates continue to select the legacy render
contract. [Activation and rollback](responsive-design.md) remain applicable.

No confirmed defect remains open in the exercised scope. These are local test
results, not an assertion about GitHub Actions. WebKit execution is not testing
the Safari application on actual macOS/iOS devices. Private site customizations,
physical devices, screen readers and an actual PHP 9 runtime remain outside this
acceptance. No automatic follow-up process remains active after completion.
