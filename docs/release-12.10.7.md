# u5CMS 12.10.7

The CSS editor again saves changes when used through its ordinary form. A regression introduced in 12.9.5.7 made the save handler expect fields belonging to the page editor. The CSS form submits the code in a single text area. The handler stopped before updating the resource, exporting the stylesheet or acknowledging the save.

The handler updates the submitted text fields and retains other stored data, including fields used by customised forms. Text versions retain the submitted code and the accompanying metadata. An empty stylesheet is a valid edit.

The stable page, code and metadata editors now use the revision of the record shown when the form opens. This avoids false conflicts when several saves in one second advance the stored revision beyond the wall clock. Earlier open forms still go through the normal conflict check.

## Verification

The regression test opens the shipped code editor over HTTP, submits the fields in its HTML form and reopens it. It checks the stored text, generated CSS, version history, an empty stylesheet and a stale submission against a disposable MariaDB database. The same test covers the legacy CSS naming convention and the typed code resources on the development branches.
