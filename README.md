# u5CMS 17.0.0 · Responsive renderer

**[Read the u5CMS documentation](docs/README.md)** — practical guides, configuration examples and reproducible exercises in British English.

u5CMS is a multilingual content management system with reusable page objects, media, protected areas and forms backed by MariaDB/MySQL. Start with the task you want to complete:

| I want to… | Step-by-step guide |
| --- | --- |
| Set up an empty website | [Install u5CMS](docs/installation.md) |
| Create, save and publish my first page | [Your first page](docs/first-page.md) |
| Build a form and export its responses | [Workshop registration form](docs/forms.md) |
| Let people maintain their own saved entries | [Equipment lending list](docs/forms/manage-entries.md) |
| Build a form that configures other forms | [DynaForm: the complete room-handover tutorial](docs/dynaform.md) |
| Add attachments and display their generated links | [Two-attachment example](docs/forms/uploads.md#build-a-two-attachment-example) |
| Maintain or update an existing installation | [Configuration](docs/configuration.md) · [Updates and recovery](docs/updates-backups.md) |

**Learn by building:** [first page](docs/first-page.md) → [first form](docs/forms.md) → [editable records](docs/forms/manage-entries.md) → [DynaForm](docs/dynaform.md). Each exercise gives concrete object names, source examples and visible results. The DynaForm exercise supplies [all eight component sources](docs/dynaform/example/README.md).

For other tasks, use the [handbook index](docs/README.md). The [branch guide](BRANCHES.md) identifies which development features belong to V13–V17. Downloads, the original manuals and additional tutorials are on [yuba.ch](https://yuba.ch/u5cms/).

Includes the optional [12.9.5.15 form evidence module](docs/release-12.9.5.15.md).

New standard and conference sites use a responsive template. Navigation, forms and media adapt to the viewport. Existing sites retain their installed template and its mobile behaviour. The renderer continues to use the same page and database content in both modes. The [renderer documentation](docs/responsive-design.md) describes template selection, media sizing and the database table syntax. The [branch guide](BRANCHES.md) records the accepted predecessor and the comparison used to verify the new line.

## About u5CMS

u5CMS combines multilingual page editing, reusable content objects, media, protected areas and forms backed by MariaDB/MySQL. The backend keeps source, metadata and histories together, while visitor-facing pages use the installation's own templates and settings.

The [handbook](docs/README.md) explains supported workflows through practical examples and repeatable observations. The [branch guide](BRANCHES.md) distinguishes the stable source from development features. Existing tutorials and additional reference material are available on the [u5CMS website](https://yuba.ch/u5cms/).

u5CMS is distributed under [GNU GPLv3](LICENSE). Retain the licences and matching dependencies when redistributing it.
