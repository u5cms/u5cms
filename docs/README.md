# u5CMS documentation

## Summary

Start with the task you want to complete. Every new handbook guide begins with a short Summary, followed by the prerequisites and practical steps. Longer topics separate setup, everyday use and technical reference so that each reader can go straight to the relevant instructions.

## Topics

| Topic | What you can do |
| --- | --- |
| [Trusted Device](trusted-browsers.md) | Configure trusted browser profiles, register and revoke them, and work with an AI agent. |
| [Authenticated messaging with sender evidence](authenticated-messaging.md) | Set up authenticated message forms, retain original sender evidence, handle notifications and verify the complete deployment. |

## Versions and examples

Use the documentation on the branch that matches your installation. The [branch guide](../BRANCHES.md) distinguishes the stable release from development lines. Each handbook topic states its supported or tested version. A development feature may require additional configuration or may not exist in the stable release.

Examples use reserved addresses such as `alice@example.org` and `https://cms.example.org/`. Replace them with your own installation details. Paths beginning with `/u5admin/` assume that u5CMS is installed at the website root; add the installation's directory when necessary.

## Documentation structure

The [documentation guide](documentation-guide.md) defines the common structure, writing conventions and verification expectations. It includes a reusable [topic template](_templates/topic.md). All maintained handbook text uses British English; literal configuration names, API names, filenames and interface labels retain their exact spelling.

## Further reference

The [u5CMS website](https://yuba.ch/u5cms/) provides the existing manuals and tutorials. The repository also contains release notes, historical acceptance reports and development checkpoints. Those records describe a particular change or verification run; the topic guides explain how to use the supported features.
