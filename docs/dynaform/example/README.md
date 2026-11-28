# DynaForm example: the complete set of page sources

[Start the step-by-step tutorial](../../dynaform.md)

## Summary

These eight UTF-8 text files are complete **CMS page contents**. Create the matching page objects and paste their text into the CMS editor. They contain native CMS markers and, for three components, PHP. They are not files to upload into the site's web directory and are not an automatic database importer.

| CMS page object | Source file |
| --- | --- |
| `df!library` | [df-library.txt](df-library.txt) |
| `df!edit` | [df-edit.txt](df-edit.txt) |
| `df!form` | [df-form.txt](df-form.txt) |
| `df!room!edit` | [df-room-edit.txt](df-room-edit.txt) |
| `df!room!design` | [df-room-design.txt](df-room-design.txt) |
| `df!room!inc` | [df-room-inc.txt](df-room-inc.txt) |
| `df!room!rdr` | [df-room-rdr.txt](df-room-rdr.txt) |
| `df!room!pub` | [df-room-pub.txt](df-room-pub.txt) |

The [access table](../../dynaform.md#2-set-up-alice-and-bobs-access) is part of the setup. Preserve its separation between trusted designers and ordinary respondents. The tutorial's second form reuses the first three objects and creates five new `df!kit!…` wrappers. No core patch, schema migration, external composer or private Dossier component is needed.
