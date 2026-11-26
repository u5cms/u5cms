# Using ChatGPT to edit a u5CMS website

[Documentation home](README.md)

## Summary

With an interactive browser and an authorised editor account, ChatGPT can operate the u5CMS backend: open an object, edit its source, preview it, save it and check what visitors see. Start with one change whose result you can recognise. Give ChatGPT the backend address, the page and language, the change it may save and a way to check the outcome.

**For** content editors. **Version** editor controls described for stable u5CMS 12.10.1. **Scope** editorial work within the permissions of the account used for login.

## Make a first edit

The [illustrated first-page guide](first-page.md#write-save-and-open) shows the editor controls used in this example.

1. Choose a practice page that you may edit. Copy its visitor URL and the address of its backend. On a root installation the backend is usually at `/u5admin/`. Choose a sentence on the page and write its replacement.
2. Start a ChatGPT session with an interactive browser. Paste the prompt below, replacing the text in braces. When the website asks you to log in, complete the login in that browser and return control to ChatGPT.
3. Follow the page link in the reply. Select the requested language and check the new wording. In the 12.10.1 editor, the **P radio button** shows the preview, the **s button** saves the edit and clicking the **letter P** opens the visitor page. The visitor page is where you can check the saved result.

> Open {backend URL} and let me complete the login if needed. Find the page at {visitor URL} and open its {language} source. Replace “{old sentence}” with “{new sentence}” in the opening paragraph. If the original sentence differs, show me the current paragraph before editing. Otherwise preview and save the change. Reopen the visitor page, check the sentence and give me its URL and the wording you found there.

## Prompts for everyday tasks

Start each task with **“Open {backend URL} in the browser and let me complete the login if needed.”** Add one of the prompts below. When a task involves two websites, identify both backends. The examples authorise the edits they describe. For a review before publication, replace the instruction to save with **“Show me the proposed changes and wait for my approval before saving.”**

### Build a page from an existing example

> Study {reference page URL} in {reference backend URL}. Identify the page objects, included fragments and images that give it its layout. On {destination site}, create a page named {object name} for {purpose}. Reuse suitable objects already on the destination site. Make these changes to the example: {list}. Put wording specific to the new page in that page. Preview and save it, then open it and test its links and controls. Give me the new URL and identify the objects you reused.

### Complete the translations of a page

> Use the {source language} version of {page URL} as the master. Open the stored field for each language offered on this site. Fill gaps and retain accurate existing translations. Match the headings, lists and emphasis. Translate captions and link labels, and follow internal links to check their destination language. Save, reopen each field and check each version through the site's language selector. Report which languages you completed.

A site can display its first language when another field is empty. Checking the stored field as well as the displayed page catches a missing translation that looks complete in the browser. See [Editing and formatting](editing.md).

### Create an illustrated article with image enlargement

> Create {object name}, an illustrated article about {topic} for {audience}, in {language}. Research the facts and cite the sources. Choose images licensed for this use and include a descriptive caption and the required credit for each. Use native u5CMS formatting throughout. Upload the illustrations as standard image objects and reference them in the article. Set the page to “public, no indexing”. Save, follow the direct URL and click each image to check that it opens at a larger size. Send me the page URL.

On stable 12.10.1, choose RGB JPEGs for standard image objects when you want the built-in enlargement. The free image section handles other formats and has no built-in enlargement control. The **public, no indexing** setting applies to all languages of the page. A visitor who has the URL can still open an otherwise public page.

### Proofread a website and report the changes

> Check the editorial copy on {site or section} in {languages}. Correct clear spelling and grammar errors using {house style}. Preserve technical terms, quotations, link destinations, object names and formatting commands. Save the corrections and check the affected pages. Report the number of pages and language versions examined. For each correction, give the URL, language and wording before and after. List anything you could not check. Flag wording that requires an editorial decision.

To receive the report by email, connect a mail service that can send from your account and add **“Email the report to {recipient address}.”**

### Repair a link used on several pages

> Within {section}, find links to {obsolete URL} and replace them with {confirmed current URL}. Inspect the source to see whether the destination is held in a shared link object, an included fragment or individual pages. Check where a shared object is used before editing it. If changing it would affect pages outside this section, report those uses and apply the correction within scope. Save, follow the corrected links in each affected language and report where they land.

### Make a table readable on a narrow screen

> On {page URL}, improve the table under “{heading}” so visitors can match each value to its heading on a phone. Keep the figures, units, row order and footnotes, and use the site's existing u5CMS formatting. Compare the saved table with the original row by row. Open it at a narrow browser width and a desktop width. Report what you observed at each width.

### Replace a document that several pages share

> Replace the {language} file of document object {object name} with {supplied file}. This edition should reach every page that uses this language version. Keep the existing object name so those references still resolve. Save, follow the download from {page A} and {page B}, and check the edition date or version printed inside each downloaded document. Report the pages and edition you verified.

## If the outcome is unclear

| What you see | What to do |
| --- | --- |
| ChatGPT can describe a public page but cannot reach its editor. | Use a session with an interactive browser, open the backend there and complete the login. Check that the account can edit the intended object. |
| Another editor has changed the source since it was opened. | Retain the proposed edit, reopen the latest source and reconcile the changes before saving. |
| The preview looks right but the visitor page still shows old wording. | Reopen the saved source and check its host, object name and language. If the wording comes from an included fragment, inspect that fragment too. |

For exact formatting commands, use the [u5CMS formatting reference](https://yuba.ch/index.php?c=u5cmsformats&l=en). For a deliberately registered browser profile that will be reused across sessions, see [Working with an AI agent](trusted-device/agents.md).
