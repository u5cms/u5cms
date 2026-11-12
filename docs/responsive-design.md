# Responsive rendering – implementation checkpoint

Authorized on 12 November 2026: new cumulative u5CMS branch, true responsive
layout without JavaScript mobile conversion, redesign the rendering pipeline,
preserve old databases, u5CMS syntax and effects wherever possible, especially
all `dat` query/output semantics documented at https://yuba.ch/dat.

Branch: `12.13.0-Responsive-Design`.
Exact starting commit: `3b9b8054473d41ae3bdd046257db154770942987`
(`12.12.4-Default-Design`). All 19 current remote branches were inspected.
The acceptance/correction audit heads have no additional application differences
from this base; older releases retain their separate feature sets. No historical
branch is merged, no production installation or main change is authorized.

Current phase: architecture review and disposable runtime preparation.
No application change has been implemented or validated yet.

Compatibility contract:
- Keep the public render() API, inserts, raw HTML escape regions, all PIDVESA
  resource notation, language fallback, preview and PHP execution policy.
- Keep dat parameters a–n, filters, ordering, field and record delimiters,
  conditions, placeholders, upload links and output modes. No new data schema.
- Keep custom templates, styles, legacy mobile assets and existing data intact.
- New templates opt into a CSS-driven layout; forms/media/navigation are not
  recreated when the viewport changes. Existing templates remain executable.
- Test the actual application and imported databases, compare outputs against
  the base renderer, test browser resize, no-JavaScript layout and form state.

Next action: finish the dat documentation review, split the renderer into
syntax/resource/output stages without changing legacy output, implement the
responsive template/media presentation, and run differential/database/browser
regressions. Final scope/results and remaining acceptance limits belong here.
