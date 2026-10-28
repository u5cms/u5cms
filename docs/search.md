# Search behavior, integration and tests

The search request, result excerpts and target-page highlighting use one parsed
query and one committed normalization table. The original query and original
page text remain available for display; matching never treats user text as a
regular expression or a SQL `LIKE` pattern.

## Query syntax and results

| Query | Meaning |
| --- | --- |
| `red blue` | Both terms must occur, in either order. A term may occur in the title or indexed body. |
| `"Wort (Wort Wort) Wort"` | One contiguous phrase, including the parentheses, after the normalization below. |
| `login "security key"` | The term `login` and the complete phrase `security key` must occur. |
| `C++ 100% foo_bar` | Three literal terms. `+`, `%` and `_` are not query operators or wildcards. |
| `"say \"hello\""` | A phrase containing literal quotation marks. |

Terms match substrings; they are not restricted to complete words. A phrase must
occur within one field: its beginning cannot be in the title and its end in the
body. ASCII and common typographic double quotation marks delimit phrases.
Backslash escapes a quotation mark or another backslash; otherwise it remains
literal, including in ordinary paths such as `C:\temp`.

The server accepts at most 512 Unicode codepoints and 16 terms/phrases, counting
terms before deduplication. Unclosed quotes, empty quoted phrases and prohibited
control characters produce an explanatory error. The browser field allows 1024
UTF-16 units so it does not prematurely truncate valid supplementary Unicode
characters; the server's codepoint limit is authoritative.

When complete matches exist, the result list contains those matches. If none
exist but some pages contain only part of the query, a separate, explicit
partial-result message explains that distinction. The query is not silently
changed. A link to search without phrase constraints is offered when relevant;
literal quotes and backslashes remain escaped correctly in that new query.

Spelling suggestions are links, not automatic substitutions. They are considered
only when there are no complete or partial results and the query is one unquoted
term of 3-40 normalized characters. The scan stops at 4 MiB of text, 100,000
tokens or 20,000 relevant distinct words; edit distance is at most two and at
most three suggestions are displayed. Protected text is not used when its
preview is disabled. Suggestions are candidates, not an exhaustive list.

Partial results first rank by the number of matched terms. Further ranking
favors title matches and exact title matches; ties use modification time and
then resource name. Results are paginated, with 25 per page by default. A match
available only in the title is identified as such in the excerpt area instead
of inventing a body quotation.

## Shared normalization and original-text ranges

[`search-normalization.json`](../search-normalization.json) is the runtime
authority for both PHP and JavaScript. Each Unicode codepoint is mapped once;
unchanged characters pass through, consecutive ASCII spaces are then collapsed.
The table provides case folding, selected accent/compatibility folding,
ligature expansion, whitespace equivalence, dash-to-space equivalence and
removal of designated invisible characters. For example, `Straße` can match
`STRASSE`, `Œuvre` can match `oeuvre`, and a phrase containing normal spaces can
match nonbreaking spaces or line breaks. Parentheses and ordinary punctuation
remain literal, apart from the documented dash equivalence.

This is a defined matching convention, not unrestricted language normalization.
The generator preserves distinctions such as Japanese voicing marks and script
vowel signs. Its Unicode data version is recorded in the JSON. Runtime PHP and
JavaScript do not separately depend on their platform's Unicode normalization
version. To intentionally update the table:

```bash
python3 tools/generate-search-normalization.py
php tests/search-core-regression.php
node tests/search-normalization-parity.cjs
```

Review and commit the generated JSON together with any corresponding code
changes. The PHP/JavaScript parity test checks the complete table as well as
matched original-text ranges.

Offsets are retained through normalization. An expansion such as `ß` to `ss`
still marks the original `ß`; decomposed accents remain attached to their base
character. PHP ranges use original UTF-8 byte offsets; browser ranges use DOM
UTF-16 offsets. Excerpts and marks retain the spelling of the source text.

## Components and request transport

| Component | Responsibility |
| --- | --- |
| [`search-request.inc.php`](../search-request.inc.php) | Capture and parse the original request before the legacy global sanitizer changes `q`. |
| [`search-core.inc.php`](../search-core.inc.php) | Decode request versions, parse/serialize terms, normalize, match, rank field matches and produce escaped excerpts. |
| [`search-service.inc.php`](../search-service.inc.php) | Apply installation eligibility/preview policies, match indexed rows, sort, paginate and suggest alternatives. |
| [`search-ui.inc.php`](../search-ui.inc.php), [`search-page.inc.php`](../search-page.inc.php) | Render forms, results, complete links and safely encoded browser configuration. |
| [`search-index.inc.php`](../search-index.inc.php), [`indexer.php`](../indexer.php) | Extract index text, preserve legacy storage encoding and manage rebuild completion. |
| [`js/search-highlight.js`](../js/search-highlight.js) | Match authoritative terms against eligible DOM text, wrap original ranges and reveal/scroll to a visible hit. |

New forms use normal GET submission with `accept-charset="UTF-8"` and `qv=2`.
For `qv=2`, the already URL-decoded `q` is literal query text. Commas, `%uXXXX`
strings and entity-looking text are not silently reinterpreted. Links carry the
complete encoded `q`, language and `qv=2` in their `href`; copying a link or
opening it in another tab preserves highlighting without a click handler.

Older URLs without `qv=2` retain the legacy decoding of comma separators,
`%uXXXX` escapes, surrogate pairs and HTML entities. New output always uses the
new transport. Existing Windows-1252 pages and database storage remain in place:
the search core works in UTF-8, HTML output encodes non-ASCII characters as
numeric references, and embedded configuration is JSON-escaped. Titles retain
their existing numeric-reference interpretation; named entity-looking title
text stays literal, as it does in the page title. The legacy
sanitized `$_GET['q']` remains available for other application code.

The browser receives parsed `{text, normalized, phrase}` terms in
`window.u5SearchConfig`; it does not reparse a search field. Normalization and
script URLs contain file-content hashes so a changed deployment does not reuse
an earlier mapping accidentally. Deploy the PHP files, JavaScript and JSON
together.

## Target-page marks and scrolling

The normal scope is `#content`, falling back to `main` and then `body`. Inline
elements do not interrupt a phrase. Block elements and `<br>` introduce a space;
the indexer and browser use the same structural HTML tag set, independent of
presentation CSS. Overlapping matches are merged before wrapping text in
`span.hitshilite`, and repeated initialization removes only marks owned by this
highlighter. Existing elements, handlers and form values are preserved.

Scripts, styles, forms, controls, editable regions, unrelated marks and hidden
non-expandable UI are excluded. Known u5CMS showblock/showblock2 content and
native `<details>` can be searched while collapsed. The first applicable hit's
ancestor chain is opened without closing an outer accordion panel. The script
then scrolls with room for fixed/sticky headers, respects reduced motion and
does not take keyboard focus. A meaningful URL fragment takes precedence.

Mobile node moves and early image/font/layout changes can trigger bounded scroll
corrections. Trusted user interaction cancels automatic scrolling. This is not
an indefinite observer that repeatedly takes the reader back to a result.
Zero visible hits require no scroll. Failure to load the shared mapping leaves
the page usable and skips client marking instead of guessing different search
semantics.

The public `window.u5SearchHighlight.ready` promise resolves after initial
marking. The `u5-search-highlight` event reports `status`, `count`, `parts` and
`truncated`; it does not announce that later layout corrections have completed.

## Index-format upgrade and freshness

The index still uses `resources.search_1` through `search_5`; no schema migration
is required. The new extractor preserves inline word adjacency, separates
structural blocks, removes markup/raw script/style content and decodes entities
once. Index storage retains Windows-1252 characters where representable and
uses numeric entities for other characters, while preserving literal entity-like
text across a read/write cycle.

**A successful full rebuild is required after deployment.** Absence of
`fileversions/search-index-v2.txt` with value `2` marks the index as outdated,
even if the old timestamp appeared current. Use the installation's existing
full `indexer.php` entry point, or let visitor-triggered automatic updating run
under the configured idle delay. Automatic updating is not an independent cron
job and respects the existing opt-out settings.

Full rebuilds use an OS file lock in `indexerrunning.txt` and a pending marker.
Only successful completion publishes the format marker and completion
checkpoint. A failure releases the lock but leaves work pending; a single-item
run does not declare the entire index current. The completion watermark leaves
saves made during the rebuild eligible for another update. Do not manually
create completion markers to suppress the freshness message.

Rows are updated incrementally, not switched as one database snapshot. Searches
can therefore see old and new rows during a rebuild, and the status message
remains relevant until it finishes. Existing text whose automatic indexing is
intentionally disabled is preserved and converted to the new storage format
during the full upgrade.

## Configuration compatibility and limits

The existing editable search/button labels from `languages.recherche_1` through
`recherche_5` remain in use, along with the existing form IDs and large form's
`unibeform` class. The existing settings remain relevant:

| Setting | Behavior |
| --- | --- |
| `doesfindpasswordprotectedcontent` | Whether protected resources may be found. Resource login metadata and directory `.htaccess` protection are considered. |
| `doesshowpreviewofsuchcontent` | Whether protected body excerpts may be displayed. |
| `doeshideintranetcontenttopublic` | Public-search exclusion of `!` intranet resources. The intranet entry point enables that scope after authentication or the explicit intranet-login opt-out below. |
| `automaticallyskipintranetlogin` | With `yes`, intranet search can include `!` resources anonymously. It does not authenticate the visitor or automatically enable previews of ordinary protected resources. |
| `excludedobjecttypesinsearchresults` | Existing resource-type exclusions. |
| `searchenginesqladditionalbooland` | Existing trusted administrator SQL restriction; it is not query text. |
| `resulttitlemaxlength` | Result-title display length, defaulting to 60 when unset/invalid. |
| `searchresultspagesize` | Optional page size; default 25, constrained to 5-100. |
| `blockautoincluderefresherforhtaccessandindex`, `waitsecondstillindexingsincelastsave` | Existing automatic-update opt-out and idle-delay behavior. |
| `donotautoindexifthisstringisfoundinfinalpagecontent` | Existing automatic-content-indexing exclusion marker. |

`u5SearchAuthenticated` means credentials were actually verified and both the
username and password are nonempty. Only that authenticated state makes the
intranet entry point enable protected-result finding and previews globally.
The explicit `automaticallyskipintranetlogin=yes` option instead applies the
`openIntranet` exception only to `!`-prefixed resources, matching the frontend
login policy. For an anonymous visitor using that option, ordinary protected
pages still follow the configured public finding and preview settings.

Search eligibility still excludes deleted/hidden/code resources and requires a
completed index value in the selected language slot. An indexed empty body can
still match its title; `NULL` remains an unindexed value. Matching streams eligible stored
index rows through PHP, independent of SQL collation and wildcard semantics.
It retains result metadata and reloads body text for the displayed result page,
rechecking eligibility and preview permissions before producing excerpts.
Pagination limits returned results and fetched excerpt bodies; it does not
remove the cost of scanning the eligible index on each request.

There are deliberate limits to what can be quoted or marked:

- Title/metadata-only hits may have no corresponding text in the target content
  area. Downloaded files, PDFs, media and external destinations cannot be marked
  by this page script; this change adds no binary-document text extractor.
- Indexed text may include form labels or other content excluded from target
  marking. Hidden non-expandable UI, later-inserted widgets and omitted template
  areas are not promised a visible mark.
- Excerpts are representative and show bounded context, normally up to three
  fragments. They need not quote every term or occurrence; repeated overlapping
  phrases do not justify an unbounded quotation.
- The browser processes at most 1,000,000 source UTF-16 units, 1,000 merged
  occurrences and 5,000 marked text portions. Its `truncated` flag reports a
  reached bound. These are highlighting limits, not a claim that search found
  no additional occurrences.

## Tests

Run from the repository root. PHP tests need `mbstring`; database integration
also needs `mysqli`. Browser tests need Node.js, Playwright and a compatible
installed browser.

```bash
php tests/search-core-regression.php
php tests/search-index-regression.php
php tests/search-service-regression.php
node tests/search-normalization-parity.cjs
node tests/search-highlight-browser.cjs
```

`U5CMS_TEST_PHP` selects a PHP executable/wrapper for child processes in the
index and parity tests. The parity runner also supports `--write-input` and
`--php-results` when PHP must be run separately. The browser helper accepts
`U5_TEST_BROWSER` for a Chromium executable and `U5_TEST_PLAYWRIGHT` for a
nonstandard Playwright module path; engine overrides are defined in
[`tests/browser-runtime.cjs`](../tests/browser-runtime.cjs).

The full HTTP/database/browser runner creates a disposable site and database,
indexes through the real HTTP endpoint and exercises actual result links,
encoding, authorization and highlighting:

```bash
python3 tests/search-web-regression.py
```

Use a test database account able to create/drop the runner's disposable
database. The database host is `127.0.0.1`, with port `33361` by default.
Configure it with `U5CMS_TEST_PORT`, `U5CMS_TEST_USER` and
`U5CMS_TEST_PASSWORD`. This runner uses `U5_TEST_PHP` for the PHP executable,
optional JSON-array `U5_TEST_PHP_ARGS`, and the same browser overrides. Its HTTP
port is ephemeral unless `U5CMS_TEST_HTTP_PORT` is supplied. It cleans up its
temporary site, server and database; it is not a test to run against a live
installation directory.

Each runner prints its checks and returns a failing exit status when a
regression is detected.
