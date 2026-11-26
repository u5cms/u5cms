# Editing pages and understanding the source

[Documentation home](README.md)

## Summary

Use a workshop page to learn how headings, links and shared passages work. Edit the heading to change what visitors read. Keep the object name when you want existing links to continue to reach the page.

**For** content authors and authorised agents. **Version** worked example exercised on 12.10.0. **Scope** individual page and language fields.

## A small, testable page

On a practice site, follow [Create your first page](first-page.md) to create an object named `docfragment`. Put the following line in its English field and save it.

```text
A reusable opening.
```

Open a second practice page in the editor. Paste the following into its English field.

```text
[[[[A small workshop]]]]
Use [b]precise words[/] and [i]short sentences[/].
[[[Reusable content]]]
[$$$:docfragment]
[Open the fragment:docfragment]
```

Choose **s**, then open the saved page with the **letter P**. Under *Reusable content* you should see “A reusable opening.” followed by a link labelled *Open the fragment*. Change the sentence in `docfragment`, save it and refresh the workshop page. The included sentence changes in place. The link still opens the fragment as a separate page.

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

## Give the page a browser title and description

Choose the language in the editor, then choose **m**. Put a short identifying title in **Title** and a one-sentence description in **Description**. Choose **s** and open the saved page with the **letter P**. The browser tab uses the metadata title, together with any site-wide title suffix. The heading in the page body comes from the heading markup in its source.

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
