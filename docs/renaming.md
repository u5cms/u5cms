# Renaming resources without losing their relationships

[Documentation home](README.md)

## Summary

A resource name is an identifier used by pages, files and sometimes form records. Changing it is a coordinated operation. Use the CMS Rename action rather than editing the database name or moving a media directory yourself.

**For** content authors and installation administrators. **Version** current workflow exercised on 12.10.0. **Scope** one resource and its recognised dependencies.

## Decide what needs to change

| Intended change | Appropriate action |
| --- | --- |
| Change the heading visitors read | Edit the page's content. |
| Change a title or description | Edit the relevant metadata field. |
| Change the object's internal name | Use Rename and choose its link-update policy. |
| Correct an external bookmark or an already sent link | Handle that external reference separately. |

Save any open edits before renaming. Note the source and target names, the affected languages and whether the object owns files or form records.

## Walk through a rename

The workshop organiser has a page whose draft name should become its permanent identifier. Use a practice site for this exercise.

1. Follow [Create your first page](first-page.md#create-the-page) to create `workshopdraft`. Put `[[[[Workshop meeting place]]]]` on its first line and `Meet at the library.` on its second line; save it.
2. Create a second page, `workshopindex`, in the same language. Paste and save this complete source:

   ```text
   [[[[Our workshop]]]]
   [Read the meeting instructions:workshopdraft]
   [$$$:workshopdraft]
   ```

3. Open `workshopindex` with the editor's **letter P**. It shows a link and, below it, the included heading and meeting sentence. Follow the link once to check the draft page, then return to the backend.
4. In **P**, find the `workshopdraft` row and select its **R** (Rename) action. Enter the unused destination `workshopready`. Leave **Update links? yes** selected and submit the Rename form. Keep the result window open until the complete operation reports success.
5. Reopen `workshopindex` in its source editor. The link now ends in `:workshopready]` and the inclusion reads `[$$$:workshopready]`. Open the visitor page again. The included text remains visible; the link now opens a URL whose `c=` identifies `workshopready`.

The exercise changes one page identifier and its two referring commands. When adapting it to a real resource, also inspect each relevant language and its associated media or form records. The visitor-facing link is the completion check; disappearance of the old row alone is insufficient.

The executed browser regression covered the real Rename form, five languages, rendering, histories, permissions, failure and retry. It also exercised genuine table and row locks. The normal five-second lock policy and a stricter one-second server policy both released the mutex and allowed the exact saved operation to resume.

## Understand the link-update choice

With **yes**, recognised references follow the object. With **no**, the object's ownership, matching files and form ownership still move; references are not rewritten. “No” is therefore not a metadata-only change.

Two existing settings refine the operation:

| Setting | Effect |
| --- | --- |
| `$ignoremanualfullpaths = 'yes';` | Leaves manually typed media paths and `c=`/`n=` targets unchanged while ordinary CMS references still follow the object. |
| `$alsorenamelinksinformdatadatacsv = 'yes';` | Also rewrites recognised references inside submitted form payloads. Other form columns are not generally replaced. |

Headings and literal code are not arbitrary object-reference namespaces. Recognised links nested in content can change without treating every matching word, JavaScript identifier or CSS selector as a reference.

## If another editor is saving

Ordinary source writers can proceed together. An actual Rename takes the exclusive coordination gate; a conflicting save reports a conflict and retains editor input. An idle recovery plan is different from a live process holding that gate.

Avoid external file or database edits during Rename. Such changes bypass the coordination and can introduce a conflict that the saved recovery plan must refuse to overwrite.

## Recover an interrupted operation

1. Preserve the result and its reported retry action.
2. Correct the specific filesystem, capacity or source conflict.
3. Use **Retry rename** or the authenticated recovery overview to continue the same saved plan.
4. Verify the final object, references and files after completion.

Do not delete pending plans to force another operation through. They retain the evidence needed to finish after the old name has already moved. If a later edit conflicts with the saved before/after values, reconcile it explicitly.

Database and filesystem changes are not one atomic transaction. A reverse Rename is another operation; it cannot reconstruct every original revision or resolve every pre-existing collision. Full rollback needs a matching pre-operation database and file backup, including recovery data.

Finish an outstanding Rename with its matching system version before upgrading. The [technical Rename reference](rename.md) gives the dependency rules, file guards, journals and repeatable verification commands. [Updates and backups](updates-backups.md) explains the recovery set to retain.
