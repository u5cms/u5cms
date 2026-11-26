# Pages, navigation and the homepage

[Documentation home](README.md) · [Create your first page](first-page.md)

## Summary

A saved page can have a direct address before anyone adds it to the menu. Choose how visitors should reach it and whether it may appear in search. The standard site menu, homepage selection and page access each have their own control.

**For** editors who maintain pages and site navigation. Changing the homepage requires access to the relevant special function. **Version** u5CMS 12.10.5 with the standard navigation. Custom templates can build their own menus.

| What a visitor needs | Where you make the change |
| --- | --- |
| A link in the middle of an article | Edit the referring page and add a [labelled link](first-page.md#make-it-reachable). |
| An entry in the standard site menu | Edit the page object named `navigation` in the appropriate language. |
| A particular page when opening the site's root address | Choose **S** in the far-right column, then **special funcs** and **define homepage**. |
| A reachable page omitted from the site's search | Open the page's **i** controls, then **Publicity status**. |

## Add an entry to the standard menu

1. First open the destination page using the **letter P** in its editor. Note its object name and language.
2. Select `navigation` in the page selector and choose the same language. Find the entry that should precede yours. The number of leading `#` characters gives the menu level. In the example, *Workshop notes* is a child of *Workshops*.

   ```text
   #[Workshops:workshops]
   ##[Workshop notes:workshopnotes]
   ```

3. On your site, keep the existing parent line and its other children. Insert your new line at the intended level. Use your own destination's object name after the colon.
4. Save `navigation`. Open a visitor page, expand the parent if needed and follow the new entry. Repeat the menu edit in each language you maintain. The visible label can be translated while the target stays the same.

## Choose the homepage

Open **define homepage**, select the intended page and choose **save & close**. Open the site's root address without a `c=` parameter. In the stable selector, eligible pages have **public, indexing ok** status. Changing the homepage is a site-wide setting.

## Set page visibility

Select the page in the editor. Choose **i**, then **Publicity status**, select a value and save with **s**. The choice applies to every language of the page.

| Status in the editor | What it does |
| --- | --- |
| **public, indexing ok** | The page can open directly and is eligible for indexing, subject to its login settings. |
| **public, no indexing** | The page can open directly. The site omits it from internal search and asks external search engines to omit it. |
| **offline** | Visitors cannot open the page. Existing links or menu entries may still point to it. |
| **htaccess forcer only** | A special-purpose page used to help protect referenced files. It does not serve as a visitor page. |

For material with a restricted readership, configure the required logins and check access to the page and any linked files as an intended reader. [Accounts and permissions](permissions.md) · [Images and documents](media.md#replace-a-shared-file)
