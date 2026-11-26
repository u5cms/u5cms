# Search and index maintenance

[Documentation home](README.md)

## Summary

Saving source content and rebuilding the search index are different events. A successfully saved page can be current for visitors while the last completed full index still describes an earlier state.

**For** content authors and installation administrators. **Version** recovery and named updates exercised on 12.10.0. **Scope** derived search state in one installation.

## A useful first experiment

In a disposable installation, put a distinctive word such as `atlasquartz` in a public page's English source. Refresh the named resource's index using the installation's normal indexing path, then search for that word.

The documentation exercise invoked the actual named worker for `docsearch`, then opened the real English search page. The result linked to the intended page. The last completed **full** rebuild watermark remained unchanged. A named update therefore confirms work on that resource rather than completion of the whole site's rebuild.

## Understand the three operations

| Operation | Purpose | What completion establishes |
| --- | --- | --- |
| Save a resource | Store source and mark indexing work due. | The write succeeded; it does not declare the full index complete. |
| Index one named resource | Refresh that resource's derived search text. | Its named indexing request completed. |
| Complete a full rebuild | Process the site's eligible resources in bounded batches. | Publish a successful full-rebuild watermark after outstanding work is complete. |

Changes to an included fragment can affect other pages' rendered text. Full rebuilding is therefore still relevant even when a single changed object has received a named update.

## Automatic operation

Automatic work needs an eligible page visit and the installation's normal background launcher. It is not a continuously running server daemon. A quiet installation may need an explicit maintenance visit or continuation to advance.

The automatic starter uses a quiet period after the last save, **1,800 seconds by default**, before beginning a new rebuild. The corresponding setting is `$waitsecondstillindexingsincelastsave`. Existing progress can be resumed through continuation requests; that is different from starting a new pass.

```php
$waitsecondstillindexingsincelastsave = 1800;
```

The existing `$blockautoincluderefresherforhtaccessandindex = 'yes';` setting suppresses automatic frontend launching of both index and access-provisioning work. The index starter also checks the normal session-cookie and running-work conditions. Review those conditions before assuming that every page load should start indexing.

## What to observe during a rebuild

The current worker handles at most eight resources per batch, checks a time budget between resources, pauses between batches and uses a shared lock to avoid competing index workers. One slow dynamic resource can exceed the between-resource budget; the budget is not a hard deadline for every PHP page.

The date notice reports the **last successfully completed full rebuild**. If a pass is interrupted or still has an outstanding resource error, that date remains the previous successful date. A changing checkpoint can show progress while the date correctly stays unchanged.

The local HTTP exercise completed the seeded site in six requests. It also exercised interrupted rows, competing work, clock recovery and new content becoming searchable while an older row remained faulty. Six is a fixture result, not a promised request count for another site.

## Investigate apparent staleness

1. Check that the intended page and language were saved and are eligible for search.
2. Check the site's visibility and protected-content settings; a page can exist without being a public result.
3. Distinguish an old full-completion date from the current named resource state.
4. Check whether automatic launching is disabled, the quiet period still applies or eligible visits have stopped.
5. Inspect server errors for a deferred resource or database problem. Repair the failing resource and continue the existing work.

Do not delete progress files merely to make the notice disappear. The current recovery policy retains outstanding work and only declares full completion when it is complete. Likewise, do not create a tight external polling loop around the worker; use the CMS's bounded background flow.

The main visibility settings are `$doesfindpasswordprotectedcontent`, `$doesshowpreviewofsuchcontent`, `$doeshideintranetcontenttopublic` and `$excludedobjecttypesinsearchresults`. They control different aspects of finding and displaying content. Test both a public visitor and an authorised account after changing them.

See the [current recovery notes](https://github.com/u5cms/u5cms/blob/v12.9.5.14/docs/release-12.9.5.14.md#search-indexing), [background transport notes](release-12.9.5.11.md) and [laboratory](labs/README.md) for implementation and executed checks. Development lines with the newer search add their own phrase, suggestion and highlighting behaviour; use the matching branch's technical search guide.
