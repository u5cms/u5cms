# Reopen a personal or shared form with MF and MFS

[Documentation home](../README.md) · [Forms and exports](../forms.md) · [Editable lists](manage-entries.md)

## Summary

Use `[mf]` for one revisable response per signed-in CMS user. Use `[mfs]` when all authorised users should work on the same response. Both reopen saved values and retain former versions when the built-in writer replaces an eligible record. They select a saved response; they do not grant access, define a form's fields or create a general list of items.

**For** form authors and installation administrators. **Version** behaviour reviewed on stable 12.10.14. MFS is also documented in the original guide from 6.5.1; check older installations separately. **You need** a practice installation, two ordinary reader accounts and explicit logins on the form page.

## Choose the right pattern

| Requirement | Pattern | Which saved response reopens? |
| --- | --- | --- |
| Each person revises their own preference | `[mf]` | Newest eligible row for that form and the exact normalised CMS username, with a nonempty stored owner. |
| A team revises one common checklist | `[mfs]` | Newest eligible row for that form, irrespective of the saved owner. |
| A person creates several separate items | [Editable list with add/edit/delete links](manage-entries.md) | The row selected by its edit link; omit MF/MFS from the ordinary add form. |
| A form is assembled from saved field definitions | [DynaForm composition](../dynaform.md) | A separate definition/rendering layer; choose MF, MFS or an editable list for its responses. |

Eligibility is the administrator's `$mfwhereclause`, normally `'status=1'`. If a response is moved to another status, it can disappear from automatic prefilling. Change that installation-wide condition only after checking every MF and MFS workflow which uses it.

MF uses the individual CMS username. Changing the SELF scope to domain sharing does not turn `[mf]` into an organisation-wide form. MFS shares the response among everyone allowed into that form; it does not automatically limit sharing to one organisation.

## 1. Start with the personal example

Follow the complete [`timepreference` exercise](manage-entries.md#when-each-person-needs-just-one-response). Put `[mf]` after the HTML block's `[:h]` and before `[:fi]`. Use the same machine field names in every language, and include:

```html
<input type="hidden" name="firstsaverwins" value="">
```

Open the form at an explicit page URL such as `index.php?c=timepreference&l=en`. The markers obtain the form name from `c=`; simply showing this form as the bare homepage omits that information. Wait until prefilling has finished before editing or saving.

## 2. Make a shared checklist

Create a new page named `sharedchecklist` and restrict it to the intended readers. In a practice installation, give Alice and Bob access; leave an unrelated visitor without access. Paste and save this complete source:

```text
[fo:]
[fi:]
[le:]Shared room checklist[:le]
[h:]
<input type="hidden" name="thanks" value="sharedchecklist">
<input type="hidden" name="in2my" value="off">
<input type="hidden" name="em2cu" value="off">
<input type="hidden" name="em2me" value="off">
<input type="hidden" name="firstsaverwins" value="">
<label>Room <input name="room_mandatory"></label>
<label>Handover notes <textarea name="handover"></textarea></label>
<button type="submit">Save shared checklist</button>
[:h]
[mfs][:fi]
[:fo]
```

Open `index.php?c=sharedchecklist&l=en`. Alice saves **Blue room / Projector checked**. Bob opens the same page in another browser profile: he should see those values. Bob changes the notes and saves; Alice should see his revision after reopening. This is deliberately one shared response for this form, not one per user.

Use one of `[mf]` and `[mfs]` in a form. Switching an existing form from personal to shared changes who can see/revise eligible responses; it does not merge or reassign the stored records.

## 3. Check access, versions and conflicts

| Action | Expected result |
| --- | --- |
| Alice saves a personal MF response; Bob opens that MF form | Bob has his own response or a blank form, not Alice's response. |
| Bob opens the shared MFS form after Alice saves | He sees the common saved values. |
| A signed-out or unauthorised visitor opens either protected form | The page and its prefilling/save handlers enforce the configured login. |
| Two readers open the shared form; one saves and the other submits an older copy | With `firstsaverwins` and the normal conflict policy, the older save is rejected with a newer-version warning. Preserve the unsaved text, reopen and reconcile. |
| An editor opens **S → form data**, the form's **html** view and **former version** | The replaced value is retained under status `6`; the eligible current value remains available. |
| The current record is moved outside `$mfwhereclause` | It no longer prefills through that selection; verify this deliberately before changing a workflow's status. |

The conflict policy is controlled by `$checksaveversionconflictinfrontendforms`: `'all'` checks all saves, `'foreign'` checks another author's saves, and `'none'` disables that check. Keep the normal check for shared work. Hiding fields, disabling a button or hiding links is not a replacement for server-side access checks.

## Keyed responses and the two different IDs

| Identifier | Meaning |
| --- | --- |
| The `id` in an ordinary `e/?n=loanentry&id=123` edit link | The `formdata.id` database row selected by the editable-list workflow. |
| The optional nonzero `id` in `index.php?c=someform&id=123` for MF/MFS | A logical key matched against the first stored value in `datacsv`, in addition to the form/status/owner conditions. It is not automatically the database row ID. |
| The posted field `id_mandatory` in a keyed MF/MFS form | The logical key used by the replacement save to find the matching eligible response. Its value/order must agree with the loader. |

A keyed composer must place `id_mandatory` first among the fields that are actually stored, before notification/control fields that also enter `datacsv`. It must provide the intended key consistently when rendering, reopening and saving. Do not add `&id=` to the simple shared example above and expect database-row selection. For multiple ordinary items, start with the editable-list guide instead.

## Run scripts after prefilling

When a calculation or conditional section needs saved values, initialise it through the `starterscript` hook in the form's HTML block:

```html
<script>
function starterscript() {
    // Read the now-prefilled fields and update the form's display here.
}
</script>
```

The loader calls this after filling fields, including the first visit with no saved response. A normal `load` handler may run too early. A newly added ordinary form without MF/MFS needs its ordinary initialisation path as well; the hook is also called when an existing entry is opened for editing.

## Uploads and field changes

Upload widgets create numbered `userupload…` fields. Keep their order and machine names stable: changing the order can associate a saved attachment with another question. Prefilling restores both the saved URL and its upload indicator when the file remains available. See [uploads and generated links](uploads.md).

Translate labels and explanatory text, not machine names or stored option codes. Renaming a field does not migrate older responses. Add new optional fields first, review old responses, then plan a deliberate migration before making them mandatory. Deleting a definition is not a safe way to erase saved data or attachments.

## Troubleshooting and evidence

| Symptom | Check and next action |
| --- | --- |
| The form is blank after a previous save | Check `c=`, exact form name, login username, status eligibility and field names; for keyed forms also check the logical key and first stored field. |
| Everyone sees one person's response | Check for `[mfs]`; this is its shared selection. Restore the intended design and assess already-exposed records before reopening access. |
| Several users were expected to share, but they each see their own response | Check for `[mf]`; domain SELF settings do not change its selection. |
| A multi-item add form always reopens an older item | Remove unintended MF/MFS selection and use the list's explicit edit links. |
| JavaScript uses empty values on reopening | Initialise through `starterscript`, after prefilling. |
| A save reports a different/multiple login | Use separate browser profiles for different CMS accounts; check that the MF prefill completed under the same login. |

Source review: [`monofill.php`](../../monofill.php), [`monofillshared.php`](../../monofillshared.php), [`remotefill.inc.php`](../../remotefill.inc.php), [`formsave.php`](../../formsave.php), [`saveversionconflict.inc.php`](../../saveversionconflict.inc.php) and the markers supplied by [`r/runonce.php`](../../r/runonce.php). The existing [form exercises](../../tests/documentation-form-exercises.cjs) cover a personal revisable response; the [form evolution suite](../../tests/form-evolution-regression.py) includes personal/shared handler scenarios. These are distinct from acceptance of a site's custom composer.
