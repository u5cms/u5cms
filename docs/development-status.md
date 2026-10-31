# u5CMS development checkpoint

Snapshot: 31 October 2026, Europe/Zurich.
Repository: https://github.com/u5cms/u5cms

This is the durable handoff for work spread across chats. GitHub refs and
committed evidence establish saved work; a chat promise or a quiet period does
not establish completion, failure, or whether another chat is running.
Refresh the snapshot against remote refs before resuming work.

## Active authorized sequence (31 October 2026)

1. Implement and validate textarea source actions on `12.13.0-Textarea-Interactions`, based on `f097070d54ac19003bb7b145ee91f6652a29e534`.
2. Immediately afterwards, create `12.14.0-PHP9-Compatibility` from the completed textarea branch. Study `php9_compat` for intent, but correct types and encoding at their real boundaries, remove `tnuoc` call-site workarounds, and do not adopt the historical blanket-wrapper approach.

Initial system/branch review completed; the textarea implementation is being built. Next action: validate parser, authenticated object lookup, actual editor controls, synchronization, undo/redo and save paths before publication. The PHP9 stage remains authorized and queued.

## Saved implementation

| Item | Evidence | State |
| --- | --- | --- |
| Production source branch | `main` at `9d366253417822c279692767b89dcf1ff0388c5c`, last changed 9 October 2026 | Version 12.9.5; the later work below has not been merged into this branch. Installed production sites were not inspected in this checkpoint review. |
| Current cumulative development branch | `12.12.1-General-Corrections` | Use this as the starting point for the current development line, after fetching and inspecting the current remote head. |
| Latest application implementation | `f842f5d1fca8d6856e67640e645519e09dfd05cf`, committed 30 October 2026 | 18 commits ahead of the main SHA above, zero behind; 296 changed paths. Later checkpoint-only commits do not imply a new application test run. |
| Search | `2f5ce37c18b017375dfc31dab6086f325bd836a2`; [search guide](search.md) | Included in the current cumulative branch. Multiword and quoted suggestions, partial results, excerpts, normalization, highlighting, container opening and scrolling are implemented. |
| Optional OIDC | `acdd8d6cf362c93803e94897336d347e26d1dba6`; [OIDC validation](12.12.0-oidc-validation.md) | Included; committed implementation and recorded local protocol/database/browser validation. Real provider acceptance remains separate. |
| Rename and complete main comparison | `f842f5d1fca8d6856e67640e645519e09dfd05cf`; [cumulative audit](12.12.1-general-corrections.md) | Included; committed implementation and recorded validation, including interruption/retry and inherited application suites. |
| Pull requests | GitHub open-pull-request collection checked on the snapshot date | No open pull requests observed. |
| Automated GitHub checks | Actions runs and commit statuses checked on the snapshot date | No workflow runs or commit status checks observed. The committed test reports must not be described as passing GitHub CI. |

The current cumulative branch retains the 12.9.6 backend, 12.10 optimizations,
12.10.1 image formats, MIME correction, 12.11.0-12.11.4 code-resource migration
and recovery, search and OIDC development. It is a continuation of that history,
not a fresh copy of main.

There are two separate historical heads:

* `12.9.6-Misc-Backend-Optimizations` has one commit not in the current
  cumulative branch: `c035f25331d9690c5ce890d540c307dc3bb62e1d`.
  Inspection shows that it only changes the version comment in `index.php`
  from 12.9.5 to 12.9.6. Its earlier functional commits are ancestors of the
  cumulative branch; do not merge the old version marker over a newer release.
* `php9_compat` is a separate old branch, observed one commit ahead and
  143 behind main. It has not been accepted into the cumulative release.

## Completion and remaining boundaries

The OIDC and General Corrections implementation is saved remotely and has
committed validation reports. The most recent application audit records, among
other suites, 886 rename checks, 1,671 migration/database checks, 476 extended
main-diff checks, 162 OIDC core checks, 247 HTTPS endpoint checks, 73 OIDC browser
checks and the search and image-upload suites. Read the reports for the exact
scope, environments and limitations; counts are not a universal correctness
guarantee.

This checkpoint review verified remote branches, commit ancestry, reports,
open pull requests and the absence of automated GitHub check results. It did
not rerun application tests. It makes no claim about the runtime of old chats
or their private workspaces.

| Remaining item | Treatment |
| --- | --- |
| Live eIAM/AGOV registration, exact provider policy and logout acceptance | Deployment-specific acceptance remains open; local TLS/IdP fixtures are not live-provider certification. |
| Real external SAML interoperability | Existing mock-path compatibility is recorded; live federation was not newly certified. |
| Additional browser/server/site-specific acceptance | Follow the limits in the cumulative report, including the current branch's Firefox/WebKit and server-specific coverage limits. |
| Merge/release/deployment | Not performed by the completed implementation tasks or this checkpoint review. Await the user's explicit instruction for these actions. |
| Textarea interactions and P-object Edit actions | Authorized on 31 October 2026; active first stage above. |
| Further search relevance, passage links and match navigation | Deferred discussion; the user said this might be done later. No such remote branch was present in the branch inventory. An earlier proposed branch name is not evidence of delivered work. |
| Site-specific long-page pauses around 21-22 entries | Separate CMS-object work. These core branch commits are not evidence that the site-specific issue is resolved; do not close that assignment through this checkpoint. |

The initial checkpoint-only request is complete. The user subsequently authorized the two application branches above; merge and deployment remain separate actions.

## How to resume after an interrupted chat

1. Read this file and the root `AGENTS.md`. Fetch all remote branches and inspect
   their current heads, ancestry, and the local working tree before selecting a
   base. Do not discard another workspace's uncommitted work.
2. Compare the actual user instructions with the saved implementation and
   reports. Keep requests on hold separate from unfinished authorized work.
   If a needed earlier workspace cannot be accessed, record that limit rather
   than asserting that its changes were recovered.
3. Record the task, target branch, exact starting SHA, changed paths, last
   completed step, last test result, unresolved issue and one concrete next
   action. If testing is in progress, record the actual run/process handle when
   available; a proposed command is not a running test.
4. Preserve a useful checkpoint before a long verification phase and after each
   meaningful completed phase. Commit and push safe, reviewable work or an
   explicit incomplete-work checkpoint within the authorized scope. Do not rely
   on chat history or uncommitted scratch files as the only recovery copy.
5. Before publishing, reread the remote head. Use a normal fast-forward push or
   an expected-head comparison. If the branch moved, inspect and reconcile the
   other work; do not overwrite it. One chat must not claim ownership of work in
   another chat without verifying its status.
6. Run the checks required by the actual changes and project rules. Once the
   authorized scope and required checks pass, finish. Repeating or broadening
   tests requires a new change, a failure or a specific unresolved concern.
   Record additional ideas as deferred work instead of extending the task
   indefinitely.
7. Verify the published remote commit. Finish with the branch and commit link,
   actual verification scope, remaining acceptance boundaries and next action.
   Distinguish implemented, validated, pushed, merged and deployed. A missing
   final chat message can then be recovered from these records.

This document is a recovery and completion protocol, not a background monitor.
No cross-chat process inspection, automatic restart, recurring watcher or
scheduled task is established by this documentation.
