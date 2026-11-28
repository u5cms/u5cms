# Writing the u5CMS documentation

## Summary

Every practical guide starts with a short **Summary** and an explicit audience/version boundary. Put the shortest usable procedure before background detail. Use numbered actions with visible results, complete examples, exact configuration names and a compact troubleshooting table. Keep one stable topic URL; split long reference material into linked pages.

## Organise around the reader's task

The handbook answers “What do I need to do?” for installation administrators, content authors and everyday users. A topic's opening page gives the outcome, its prerequisites and the route to the next action. Separate a reader's ordinary use from administrator-only setup. Keep implementation detail in reference pages unless it changes a user's decision.

Use a durable topic path such as `docs/authenticated-messaging.md`, with optional detail pages under `docs/authenticated-messaging/`. Link the topic from the documentation index. Tickets should link to the topic's stable URL and add only installation-specific scope, responsibility and acceptance results. Avoid copying a full guide into a ticket.

## Use a consistent structure

| Order | Section | Contents |
| --- | --- | --- |
| 1 | Summary | Three to five bullets or a short paragraph: outcome, essential requirement, next action and a consequential limit. Aim for about 100 words or fewer. |
| 2 | Audience and version | Who performs the task; earliest supported version or tested baseline; installation-wide or per-user scope. |
| 3 | Before you start | Required access, dependencies and decisions. State externally supplied integrations explicitly. |
| 4 | Procedure | Numbered actions in execution order. Give the location, action and expected result. |
| 5 | Check it works | A normal case and the failure/boundary cases that matter. Describe what success looks like. |
| 6 | Everyday use | The user's next task, including how to stop, revoke, export or repeat where relevant. |
| 7 | Troubleshooting | Symptom → likely cause/check → practical next action. Use exact observed errors. |
| 8 | Reference | Complete setting/default/scope tables, mechanism, version limits, source and validation evidence. |

Keep the order predictable, but omit inapplicable sections. A short task page can combine checks with its numbered procedure. A reference page still starts with a Summary; it does not need an invented setup procedure. Longer topics use a brief “Choose your task” table and linked setup, usage/operations and reference pages. Each detail page must make sense when opened directly from a ticket.

Start a new page from the [topic template](_templates/topic.md). Put **For** and **Version** below the summary. Add **You need** or **Scope** when it helps someone decide whether they can follow the steps. Keep the handbook link near the title. Where a reader needs a skill taught in an earlier guide, link to its relevant step.

Place the complete worked path before optional variants and evidence. In [Forms and exports](forms.md), for example, the reader can submit a name and find it in the CSV before reading how the handler validated the field.

## Choose an illustration

Use a cropped screenshot for a control that is easily confused with its neighbour. Show enough of the interface for readers to locate it, use invented example content and name the relevant control in the alternative text. The [first-page guide](first-page.md#write-save-and-open) shows why the round preview control and the adjacent letter **P** need separate instructions.

Use a diagram for relationships across pages, as in [the course directory](dat.md#set-up-the-exercise). Keep file contents and commands as selectable text. A reader should be able to complete the task with images hidden.

## Write concise, usable instructions

- Use British English throughout: for example, *authorised*, *organisation*, *normalise* and *behaviour*. Preserve literal API names, filenames, interface labels and test data.
- Lead with the outcome. Prefer “Set the mode in the private `config.php`” to a narrative about how the feature was developed.
- Give complete, small examples. State where to put them, which values to replace and whether to replace an existing assignment. Preserve unrelated configuration and encoding.
- Give a practical tutorial a concrete story and a complete object manifest. Use the same object names and field names from the first step to the final result. Supply every included component in full, or identify an externally supplied prerequisite before the first step. A reference to an unimplemented renderer is not a working example.
- After each significant action state what the reader should see. Include the first save, a fresh reopen and the next ordinary change. Replace empty callback bodies and instructions such as “connect the component” with the actual code or UI operation.
- Use reserved domains such as `example.org`. Keep real installations, personal data, recipients, credentials and organisation-specific retention rules out of generic documentation.
- Explain defaults, enable/disable behaviour and scope. Distinguish a temporary disable from permanent revocation, or a working copy from retained evidence, whenever that distinction changes an action.
- Use tables for exact configuration or status mappings. Use a diagram only when it explains relationships or branching more clearly than a short procedure.
- Describe only supported capabilities. Call a required integration a prerequisite; do not imply that a configuration switch installs it.
- Link to one authoritative procedure. Avoid duplicated snippets that can silently diverge across guides.

## Verify before publication

Check the current implementation and relevant version history, then try the documented path in an isolated environment where practical. Check examples, relative links and the expected results. Use existing relevant tests to support behavioural claims; record what was tested and where fixtures replace real integrations. Do not claim production acceptance from a simulated login or transport.

Before merging, check that every new handbook page has a Summary, the shortest usable path is easy to find, permissions and prerequisites are explicit, terms are accurate and no private deployment information escaped into an example. Change the guide when behaviour changes; update its version statement and relevant checks in the same change.
