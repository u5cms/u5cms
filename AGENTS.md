# u5CMS development rules

These are standing project requirements from Stefan Minder. Apply them to every
change, including changes described as small, preparatory, or performance-only.

* Study the whole u5CMS system before implementing a change: its architecture,
  configuration switches, entry points, authentication, authorization, rendering,
  resources and their references, publication, histories, backup and restore.
  A search for the immediate function name is not sufficient system analysis.
* Read the existing behavior and its history to understand the complete idea
  behind a feature. Preserve the intent as well as the apparent happy path.
  Discover existing options independently; do not rely on the user to list them.
  For example, federation includes frontend selection and substring exceptions,
  backend enforcement, enrollment, messages, attributes, logout and local flows.
* Examine all current development branches before choosing a base. Preserve the
  cumulative development work. Do not merge an unrelated historical branch
  without understanding and testing its differences.
* For a general correction or regression review, inspect every change against
  production `main`, including inherited changes, and test for collateral damage
  and lost functionality. Do not limit the review to the latest commit.
* Optimizations must retain all functionality and safeguards. Object renaming
  must update every dependency while preserving brackets in titles, literal
  text, encodings, localization, histories and the intended object semantics.
  Use realistic large-site cases to measure performance and verify the result.
* Respect the existing Latin-1/Windows-1252 application sources and UTF-8 database
  boundary. Do not silently re-encode existing files. Review PHP, database
  resources, `r/`, templates, histories and exports together.
* Test the actual application paths, success cases, denials, malformed input,
  failures, interruptions and repeated operations in disposable installations
  and databases. Maintain meaningful expected results; do not weaken tests to
  obtain a passing run. Record exact scope, runtime and material limits.
* Document configuration, supported behavior, operation, rollback and validation.
  Commit the tested result to a new branch when requested. Do not change `main`
  or install into a production CMS unless explicitly instructed.
