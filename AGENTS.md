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

## Checkpoints and completion across chats

* Start by reading [the development checkpoint](docs/development-status.md).
  Verify it against current remote refs and the local working tree; do not
  treat its dated snapshot or prior chat claims as a live process status.
* Record the authorized task, target branch, exact starting SHA, changed paths,
  last completed step, test evidence, blockers and one concrete next action
  before long verification and at each meaningful phase boundary. Keep a safe,
  reviewable remote checkpoint so scratch files and chat history are not the
  only recovery copy. Do not store secrets or personal fixture data.
* Respect explicit holds, such as the textarea discussion and deferred search
  proposals. A proposed branch or promised action is not a delivered change.
  Preserve inaccessible or incomplete work as an explicit uncertainty.
* Check for another chat's work before publishing to a shared branch. Use a
  fast-forward push or an expected-head guard; inspect and reconcile a moved
  remote head. Never overwrite other work or infer that another chat stopped
  merely because no recent commit is visible.
* Complete the authorized scope and required checks, then finish. Repeat or
  broaden verification only for a new change, failure or specific unresolved
  concern. Keep optional improvements separate instead of extending the task
  indefinitely.
* Close with a verified remote branch/commit, actual validation scope, remaining
  acceptance boundaries and next action. Keep implemented, validated, pushed,
  merged and deployed distinct. Committed reports are not GitHub CI results,
  and documentation does not establish background execution or auto-recovery.
