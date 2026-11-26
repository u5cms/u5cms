# Edit and check a site stylesheet

[Documentation home](README.md) · [Editing pages](editing.md)

## Summary

Edit the stylesheet in the backend's **Code** list. A successful save updates the source in u5CMS and exports a file under `r/` for visitors. You can check both with a rule that applies to a single paragraph on a practice page.

**For** site maintainers with permission to save code. The default configuration requires **Higher Admin Rights**. **Version** stable 12.10.7 and the maintained V13–V17 branches. Use a practice installation because the stylesheet can be shared across the site.

## Give one paragraph a border

1. Open [a practice page](first-page.md). Add the following to its source, save with **s** and open the visitor page with the **letter P**. You should see the meeting time.

   ```text
   [h:]
   <p class="workshop-time">The workshop starts at 10.00.</p>
   [:h]
   ```

2. In the far-right column of the backend, choose **S**. If the save history opens, follow **special funcs**. Choose the **code** button. If you already see the **Code** list, select the stylesheet directly. These are the layout stylesheet names for the standard designs. A customised template may load a different stylesheet.

   | Installation | Object to open in Code | Export beneath the site directory |
   | --- | --- | --- |
   | Stable 12.10.7 or V13 | `csslayout` | `r/csslayout.css` |
   | V14–V17 with migrated code objects | `layout!css` | `r/layout.css` |

3. Open the object by its name. At the end of its existing source, add this rule and choose **save [Ctrl + S]**.

   ```css
   .workshop-time {
       border-inline-start: 4px solid currentColor;
       padding-inline-start: 1em;
   }
   ```

4. Reopen the same object and find `.workshop-time`. Open the exported file from the table and find the same selector there. Reload the practice page. The meeting time should now have a border on one side, with a gap between the border and the text.
5. To remove the example, delete just that rule in the code editor, save and reload the visitor page. Remove the example paragraph from the page when you finish.

## If the result differs

| What you see | What to check |
| --- | --- |
| A conflict message appears | Keep the attempted edit. Open the current source, compare the versions and reapply the intended lines. See [Recover text from an earlier version](editing.md#recover-text-from-an-earlier-version). |
| The editor reopens with the old source | Check the object name and any save message. For older stable installations, see the [12.10.7 CSS save correction](https://github.com/u5cms/u5cms/blob/v12.10.7/docs/release-12.10.7.md). |
| The editor has the rule, but the export does not | Reload the export with the browser cache disabled. If the rule is still missing, check the export message and the PHP service account's write access to that site's `r/` directory and file. Make corrections in the CMS source so they survive the next export. |
| The export has the rule, but the paragraph has no border | In the browser's developer tools, inspect the paragraph. Check its class, which stylesheet the page loaded and whether another rule overrides the border. |
