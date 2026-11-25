# Editing pages and understanding the source

[Documentation home](README.md)

## Summary

Work from the result you want visitors to see. A page has an object name, one or more language fields and its own source. The heading shown on the page is content; the object name identifies the resource that other pages reference.

**Audience:** content authors and authorised agents. **Version:** worked example exercised on 12.10.0. **Scope:** individual page and language fields.

## A small, testable page

In a disposable installation, create a page object named `docfragment` containing:

```text
A reusable opening.
```

Then place the following in the English field of another page:

```text
[[[[A small workshop]]]]
Use [b]precise words[/] and [i]short sentences[/].
[[[Reusable content]]]
[$$$:docfragment]
[Open the fragment:docfragment]
```

Preview the page, save it and inspect the resulting website. The main heading should have level 1 and the subheading level 2. The text has visual emphasis, the fragment's content appears within the page, and the final reference is a link.

The independent browser exercise verified those results through the actual renderer. It checked the visible font weight rather than assuming that bold text must use a particular HTML element.

## Three different uses of brackets

| Source | Intended result |
| --- | --- |
| `[[[[A small workshop]]]]` | Main heading. |
| `[Open the fragment:docfragment]` | Labelled reference to an object. |
| `[$$$:docfragment]` | Included content from another page object. |

These are different operations. Replacing an object reference with heading brackets changes its meaning. Renaming an object is also different from editing its visible heading; use [Rename](renaming.md) when the identifier itself must change.

## Choose the language deliberately

Confirm the selected language before editing. In the exercise, an empty English field still displayed the first language's text through fallback. A page being readable in English mode therefore does not prove that its English source field contains a translation.

Inspect the stored field, edit the intended language and verify both that language and any relevant fallback. Keep form field names identical across language versions even when their visible labels differ.

## Include HTML deliberately

Use `[h:]` and `[:h]` to delimit an HTML section. Inside it, write HTML rather than expecting ordinary CMS inline formatting to behave as it does outside the section. Page inclusions can still be used there:

```text
[h:]
<section>[$$$:docfragment]</section>
[:h]
```

An HTML section is not a general escaping mechanism for displaying CMS commands. In the stable renderer experiment, a raw closing `[/]` marker was consumed even though its opening marker appeared as text. To show the command itself, encode its brackets in HTML:

```text
[h:]
<p>&#91;b&#93;Keep these markers&#91;/&#93;</p>
[:h]
```

The browser then displays `[b]Keep these markers[/]` verbatim. This encoded example was executed in the documentation laboratory.

## Save, inspect and recover

Confirm the object name and language, make a bounded change, preview it, save and check the saved page. Do not treat a spinner or an open confirmation window as proof of completion. If a save reports a conflict, retain the unsaved source, inspect the newer version and reconcile the changes before retrying.

The current save/index checks exercise successful saves without browser child frames, all five language fields, contention and retained pending work. Text history and matching backups serve different recovery needs: use text history for a content mistake and [matching site backups](updates-backups.md) for a wider recovery.

An agent editing a page should identify the host, account, object and language first, preserve existing syntax and change only the authorised content. The [agent example](trusted-device/agents.md) supplies a complete task prompt. The backend's formatting help and [existing reference material](https://yuba.ch/index.php?c=u5cmsformats&l=en) remain useful when you need a command outside this worked example.
