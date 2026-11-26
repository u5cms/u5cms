# u5CMS documentation

## Summary

If this is your first visit to the backend, start with [Create and publish your first page](first-page.md). It takes you from the page list to a saved page you can share. If you already have a site open, choose the task below that matches what you want to change.

<a id="topics"></a>
<a id="learn-through-an-exercise"></a>

## Edit a website

| I want to… | Guide |
| --- | --- |
| Create a page, choose its language and open the saved result | [Your first page](first-page.md) |
| Write headings, link pages or reuse a passage in several places | [Editing and formatting](editing.md) |
| Recover a passage after an edit or a save conflict | [Compare text versions and recover a passage](editing.md#recover-text-from-an-earlier-version) |
| Put a page on the menu, choose the homepage or change visibility | [Pages, navigation and the homepage](navigation.md) |
| Add an accessible picture, link a PDF or replace a shared file | [Images and documents](media.md) |
| Change the object name used in existing links | [Rename a resource](renaming.md) |
| Give ChatGPT an editorial task with a clear result | [Using ChatGPT with u5CMS](using-chatgpt.md) |

## Build a form or a small application

Start with [a registration form and its CSV export](forms.md#a-worked-registration-form). For the two examples featured on yuba.ch, continue with [the survey and its reviewed results](forms/survey-results.md), then [a list that contributors can edit (Scenario 2)](forms/manage-entries.md).

| When you need to… | Continue with |
| --- | --- |
| Show selected fields from saved records in a list and on a detail page | [Display records with DAT](dat.md) · [DAT parameter reference](dat/parameters.md) |
| Let each person return to one saved response | [One revisable response (Scenario 1)](forms/manage-entries.md#when-each-person-needs-just-one-response) |
| Collect answers over two pages and match the exports | [Multipage surveys](forms/multipage-surveys.md) |
| Send individual messages to selected respondents | [Personalised serial mail](serial-mail.md) |
| Keep evidence of who submitted an authenticated message | [Authenticated messaging with sender evidence](authenticated-messaging.md) |

## Set up and run an installation

| Task | Guide |
| --- | --- |
| Start a site or change the settings of an existing one | [Install a new site](installation.md) · [Configuration](configuration.md) |
| Change a stylesheet and check what visitors receive | [Edit and check a site stylesheet](stylesheets.md) |
| Decide who can sign in, edit pages or see particular form records | [Accounts, permissions and SELF records](permissions.md) |
| Register a browser for an account or manage its allowance | [Trusted Device for account holders](trusted-device/usage.md) · [Setup and troubleshooting](trusted-browsers.md) |
| Find out why a page is missing from search | [Search and indexing](search-indexing.md) |
| Configure mail delivery and investigate failures | [Email delivery](email.md) |
| Update or restore a site | [Updates, backups and recovery](updates-backups.md) |
| Spot capacity problems across several sites | [Server monitoring](server-monitoring.md) |

## Versions and examples

Read the handbook on the branch that matches your installation. The [branch guide](../BRANCHES.md) identifies the stable and development lines. Examples use reserved domains such as `example.org`. Add your site's installation directory to paths such as `/u5admin/` when applicable.

| Development branch | Feature reference |
| --- | --- |
| V13 | [Additional image formats](https://github.com/u5cms/u5cms/blob/V13-more-Img-Formats/u5admin/image-formats/README.md) |
| V14 | [Typed code resources](https://github.com/u5cms/u5cms/blob/V14-JS-CSS-HTML-Exports/docs/12.11.0-code-assets.md) |
| V15 | [OpenID Connect](https://github.com/u5cms/u5cms/blob/V15-Phrase-Search-and-OIDC/docs/oidc.md) |
| V16 | [Default site design](https://github.com/u5cms/u5cms/blob/V16-Default-Design/docs/12.15.0-default-design.md) |
| V17 | [Responsive rendering](https://github.com/u5cms/u5cms/blob/V17-New-Responsive-Layouts/docs/responsive-design.md) |

## Further reference

The [illustrated backend reference (PDF)](../u5admin/u5shortreference.pdf) gives a one-page view of the editor. The [original manual and introduction video](https://yuba.ch/index.php?c=u5cmsmanual&l=en) cover the wider system. For specialist workflows, see the original guides to [conference submissions and reviews](https://yuba.ch/index.php?c=u5econgress&l=en) and [PayPal forms](https://yuba.ch/index.php?c=u5epaypal&l=en).

## Documentation conventions

Authors can use the [documentation guide](documentation-guide.md) and [topic template](_templates/topic.md). The [documentation laboratory](labs/README.md) records which versions and workflows were exercised.
