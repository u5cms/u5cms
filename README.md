# u5CMS 16.0.0 · Standard design

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

V16 starts at the published V15 release. New standard and conference installations use the revised typography, navigation, tables, forms and keyboard focus indicators. The fixed desktop columns and mobile script are retained. A site with its own styles can upgrade its application code independently and choose which design changes to adopt. See the [branch guide](BRANCHES.md) for the development history and the [design notes](docs/12.15.0-default-design.md) for the eight style resources and reset procedure.

## About u5CMS

u5CMS combines multilingual page editing, reusable content objects, media, protected areas and forms backed by MariaDB/MySQL. The backend keeps source, metadata and histories together, while visitor-facing pages use the installation's own templates and settings.

The [handbook](docs/README.md) explains supported workflows through practical examples and repeatable observations. The [branch guide](BRANCHES.md) distinguishes the stable source from development features. Existing tutorials and additional reference material are available on the [u5CMS website](https://yuba.ch/u5cms/).

u5CMS is distributed under [GNU GPLv3](LICENSE). Retain the licences and matching dependencies when redistributing it.
