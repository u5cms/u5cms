# u5CMS 12.13.0: Responsive Design

Branch: `12.13.0-Responsive-Design`, based on the cumulative
`12.12.4-Default-Design` commit `3b9b8054473d41ae3bdd046257db154770942987`.
The 19 existing remote heads were examined before branching. The newer audit
heads have no additional application differences from this base. Historical
branches keep their separate feature sets; no unrelated branch was merged.

Authorized on 12 November 2026: replace JavaScript mobile conversion with real
responsive design, redesign the renderer, preserve older databases and the
u5CMS syntax/effects, with particular attention to https://yuba.ch/dat.
This is a separate source branch. It does not update main or any live website.

## Rendering and compatibility

The public entry point is **render.inc.php**, with the unchanged `render()` API.
It now composes these stages:

| Module | Responsibility |
| --- | --- |
| `render/markup.inc.php` | Ordered database insert rules, escaping, paragraphs, generated IDs and raw `[h:]` regions. |
| `render/resources.inc.php` | Existing resource notation, reference variants, language fallback, images, albums, links and media. |
| `render/output.inc.php` | Preview forms, final placeholders, private-media proxy URLs and upload tokens. |
| `render/dat.inc.php` | The original dat implementation and its helpers, moved byte-for-byte without semantic changes. |
| `render/layout.inc.php` | Explicit template selection and preferred media dimensions for responsive pages. |

Syntax and dat processing are shared by both presentation modes. There is no
second parser for phones. Standalone calls, including mail and form consumers,
remain in legacy mode unless their caller explicitly activates a responsive
template. The frontend selects its mode after resolving the actual site template.
Commented examples, script/style content and values of unrelated attributes do
not activate responsive rendering. If template recognition fails, it stays legacy.

Legacy render output is compared byte-for-byte with the immutable base renderer,
including a corpus of stored pages. Responsive output differs intentionally in
renderer-owned video/audio geometry: configured widths become preferred maximum
sizes, video proportions remain unchanged and audio controls keep their height.
Images scale through CSS; horizontal albums retain their configured image height
and scroll within the content width. Arbitrary `[h:]` markup is not rewritten.

The documented dat parameters a–n, including all expanded explanation sections,
were read on 12 November 2026. Their existing implementation is retained:

| Contract | Verification |
| --- | --- |
| a/b: fields and form, mandatory `*` aliases, system `?` fields | Real seeded formdata rows; missing fields/forms; exact output comparison. |
| c/d/e: record IDs, GET selection, notes, SELF/authuser, status, LIKE, boolean terms and full WHERE | Real queries, independent selected-row assertions, disabled-WHERE denial and malformed GET types. |
| f/g/h: record wrappers, field/record separators and `@@` | Byte comparisons including custom HTML and placeholders. |
| i/j: ordering, limits, GET sorting and newline replacement | Exact comparisons plus independent descending-order and limit assertions. |
| k: upload links, thumbnails, dimensions, `x`, `+` and `!` variants | Real fixture files and expected download/thumbnail URLs. |
| l/m/n: conditional replacement and output modes 0–5, with/without linkification | Exact old/new output comparisons. |
| Notes, placeholders, PHP policy and encoding | Private `|||` notes, system placeholders, PHP execution switches, Windows-1252 bytes and Unicode entities. |

No formdata/resource schema change, data conversion or new dependency is required
for this feature. Existing bootstrap migrations from preceding releases still
apply. The inherited runtime requirement remains PHP 8.2 or newer; old databases
do not imply support for obsolete PHP interpreters. Existing Latin-1/Windows-1252
source and database transport boundaries are preserved.

## Layout and interaction

The shipped templates use `class="u5-responsive" data-u5-layout="responsive"`
on the body. `css/u5-responsive.css` is scoped to that class and loaded after the
site styles. The main area is fluid up to 1330px, with three columns above 68rem,
two up to 68rem, and one up to 48rem. Header, search, languages, navigation, content,
news, footer, images, forms, albums and media adapt to the available viewport.
Wide data tables keep their table markup and can scroll inside the content area;
preformatted text retains local scrolling. Existing IDs, template slots and
optional split navigation remain available.

`js/u5-responsive.js` only controls navigation disclosure on narrow views. It
uses a native button, `aria-expanded`, Escape/focus return, fragment reveal and
focus-preserving breakpoint transitions. CSS owns layout without JavaScript;
without it the complete navigation remains visible. Resizing never reconstructs
forms, uploads, media or navigation. No screen-width/UA detection, alternate
mobile DOM, layout reload or mobile view cookie is required by the new template.
Old mobile query parameters/cookies do not force the responsive layout to a fixed
width; their existing behaviour remains available to legacy templates.

## Installation, activation and rollback

New installations using **either** supplied SQL seed get the responsive template.
The database object, exported `r/htmltemplate.html` and reset source
`r/htmltemplate!html.tpl` contain the same template (apart from cache tokens).
Only that template object changed in the seeds; other seed data and schema did not.

Existing installations keep their template and site assets during a normal
update. Their old mobile scripts remain in the distribution because removing them
would break these templates and site-specific integrations. The responsive default
neither includes nor calls them. No existing custom database object is erased.

To deliberately convert an existing site:

1. Back up its database and files together, including configuration, template,
   styles, code exports and reset sources. Test on a disposable copy first.
2. Install the **complete** branch update, including the new `render/`,
   `css/u5-responsive.css`, `js/u5-responsive.js` and
   `u5sys.responsivecontrols.php`. Updating only render.inc.php is insufficient.
3. Edit the active HTML template object through the normal CMS code-resource
   workflow. Keep its existing body ID/classes and add the responsive class and
   `data-u5-layout="responsive"`. Include the responsive stylesheet after the site
   styles, and `<script src="js/u5-responsive.js" defer></script>` in the head.
4. Keep the viewport meta tag and existing layout IDs. Add
   `{{{responsivecontrols}}}` before `#outer`. Remove the old mobile script include
   and its `u5mkmobile()` call **from this converted template**. If using the stock
   layout, the distributed template shows the complete result.
5. Check site-specific CSS and custom HTML at narrow/wide widths, with keyboard
   input and without JavaScript. Custom designs with different structural IDs or
   intentionally fixed content need corresponding CSS adaptation; arbitrary
   third-party designs cannot be converted by a database schema migration.
6. Preserve/update the site's reset baseline deliberately if reset should restore
   the new design. An update must not silently overwrite an old custom baseline.

To return only the presentation to the previous design, restore the saved template
and its matching styles/mobile includes. The new renderer defaults to the legacy
output contract for it. There is no responsive data migration to reverse. For a
full application downgrade, restore the matched database/files backup; migrations
inherited from earlier releases are separate from this feature.

## Validation

Executed locally on PHP 8.4.24, MariaDB 11.8.6 and Chromium Headless Shell
150.0.7871.181 with Playwright. Only disposable databases/installations were used;
external resources were blocked in responsive browser tests; mail tests used local
transport doubles and sent no external mail.

- Renderer: 186 legacy byte comparisons against the base, 129 unchanged
  responsive syntax/dat comparisons, 13 independent expected effects and four
  isolated denials compared across old/legacy/responsive execution.
- Layout selection and media boundaries: 47 assertions, including false opt-ins,
  request reset, malformed templates and a deliberately exhausted regex budget.
- Both SQL seeds: 179 browser checks each. Widths 320, 375, 768, 800, 1024, 1440
  and 1920px, repeated resizing, JavaScript on/off, a separately fixed screen size,
  preserved DOM identity/values/events, real form submission and file upload,
  five languages, search, keyboard navigation, fragment reveal, split navigation,
  printing, video ratio, albums and wide tables. Phone and desktop screenshots
  were inspected.
- Original-reference contracts and the declared inherited feature suites cover
  old databases, configuration, formdata, Conference workflows, migration and
  interruptions, rename, search, OIDC, editor/backend operations, backup/restore,
  private media, mail boundaries and dependency patches. Legacy-layout suites
  explicitly use the frozen pre-responsive template so their original mobile
  assertions remain active; new responsive suites independently exercise the new
  imported defaults.

The executable entry point is `python3 tests/run-regressions.py --group all` with
a disposable TCP MariaDB, the documented test environment and browser dependencies.
The responsive reference SHA is pinned in `tests/regression-capabilities.json`.
Detailed execution evidence is recorded in `tests/responsive-validation.json`.
These are local results, not a claim about a GitHub Actions execution.

Limits: this run validates PHP 8.4 and Chromium, not actual PHP 9, Safari, Firefox,
physical devices, screen readers, external identity providers or live mail delivery.
No live private database or every individual site's custom HTML/CSS was tested.
Such installations need their own acceptance after deliberate template conversion.
