# u5CMS documentation

## Summary

Practical guidance for people who configure, administer and use u5CMS, including people working with an AI agent. Start with the task you want to complete.

## Topics

| Topic | What you can do |
| --- | --- |
| [Trusted Device](trusted-browsers.md) | Configure trusted browser profiles, register and revoke them, and work with an AI agent. |
| [Authenticated messaging with sender evidence](authenticated-messaging.md) | Set up message forms, retain original sender evidence and handle notifications. |
| [Configuration](configuration.md) | Identify the applicable settings and change one installation deliberately. |
| [Accounts and SELF permissions](permissions.md) | Distinguish login, backend rights, personal records and organisational sharing. |
| [Editing and formatting](editing.md) | Build a small page, choose its language, include content and display literal syntax. |
| [Using ChatGPT to edit a u5CMS website](using-chatgpt.md) | Start with one verified edit, then adapt prompts for pages, translations, media and site maintenance. |
| [Forms and exports](forms.md) | Follow a submission from validation to storage, confirmation, table view and CSV. |
| [Display form records with DAT](dat.md) | Build a course directory, link to individual records and show contributors their own entries. |
| [Search and indexing](search-indexing.md) | Understand named updates, full rebuilds, background work and recovery. |
| [Renaming resources](renaming.md) | Move an identifier and its recognised relationships, and recover interruptions. |
| [Email](email.md) | Configure delivery, handle international addresses and interpret failures. |
| [Updates, backups and recovery](updates-backups.md) | Keep a matching recovery set and verify the result of an update or restore. |
| [Server monitoring](server-monitoring.md) | Plan early capacity warnings across a hosting server and its PHP pools. |

## Learn through an exercise

Start with [two trusted browser profiles](trusted-device/walkthrough.md), compare the [ownership matrix](permissions.md#an-exercise-with-four-records), or build the [small workshop page](editing.md#a-small-testable-page). Each exercise connects a desired outcome with a concrete observation.

The [documentation laboratory](labs/README.md) records 40 independent scenarios and the supporting regression runs. The guides distinguish tested local behaviour from configuration which still needs acceptance on the actual installation.

## Versions and examples

Use the documentation on the branch that matches your installation. The [branch guide](../BRANCHES.md) distinguishes the stable release from development lines. A feature on a development branch may require additional configuration or may not exist in the stable release.

Examples use reserved addresses such as `alice@example.org` and `https://cms.example.org/`. Replace them with your own installation details. Paths beginning with `/u5admin/` assume that u5CMS is installed at the website root; add the installation's directory when necessary.

## Further reference

The [u5CMS website](https://yuba.ch/u5cms/) provides the existing manuals and tutorials. The repository also contains release notes, historical acceptance reports and development checkpoints. Those records describe a particular change or verification run; the topic guides explain how to use the supported features.

Development features have their own references on the applicable branches: [additional image formats in V13](https://github.com/u5cms/u5cms/blob/V13-more-Img-Formats/u5admin/image-formats/README.md), [typed code resources in V14](https://github.com/u5cms/u5cms/blob/V14-JS-CSS-HTML-Exports/docs/12.11.0-code-assets.md), [OIDC in V15](https://github.com/u5cms/u5cms/blob/V15-Phrase-Search-and-OIDC/docs/oidc.md), [default site design in V16](https://github.com/u5cms/u5cms/blob/V16-Default-Design/docs/12.15.0-default-design.md) and [responsive rendering in V17](https://github.com/u5cms/u5cms/blob/V17-New-Responsive-Layouts/docs/responsive-design.md). Availability on a development branch is not a promise about an older stable installation.

## Documentation conventions

The [documentation guide](documentation-guide.md) and [topic template](_templates/topic.md) define the common structure. Every practical page starts with a Summary and states its audience and version boundary.

All u5CMS documentation is written in British English. Configuration names, API names, filenames, quoted interface labels and literal test data retain their exact spelling. New topic guides explain the purpose, prerequisites, configuration, routine use, failure handling and relevant version limits, with examples drawn from the implementation.
