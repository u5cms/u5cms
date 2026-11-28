# u5CMS DynaForm: build a form that configures other forms

[Documentation home](README.md) · [Complete component sources](dynaform/example/README.md) · [Field and code reference](dynaform/schema.md)

## Summary

Build a small form builder for room handovers. Alice sets it up once and then uses a configuration form to add questions. Bob opens the resulting questionnaire, saves his handover and returns to revise it. Alice can change a question's wording through the configuration form without editing the questionnaire's page source. Every component used below is supplied in full.

**For** a u5CMS administrator setting up the builder, followed by a trusted form designer. **You need** a separate practice installation with working ordinary forms, PHP execution in CMS page content and access to **Form data**. The example uses the stable line's native form handler, personal MF selection and the usual `status=1` current records. It adds CMS page objects; it changes no core PHP file or database schema.

## The story and the finished result

A community centre lends out two rooms. Alice needs three questions: which room was used, whether it is ready for the next group, and any access notes. Later she wants to ask how many keys were returned. She wants to maintain these questions through a form.

Bob's first handover will read **Blue room / Needs attention / Side door sticks**. After reopening it, he will change the notes to **Side door repaired**. Another respondent starts with their own empty handover. The configuration records remain separate from all these answers.

## 1. Reserve the eight object names

Use **P → new** as shown in [Create your first page](first-page.md#create-the-page). Create these eight **page objects**. Put each supplied source in the first configured language's content field and save it with **s**. Keep the other language fields empty for the first run so the same source is used through fallback. If any of these names already exists, use a clean practice installation; replacing an unfamiliar existing object is not part of this exercise.

| Create this exact object | Complete source | Role |
| --- | --- | --- |
| `df!library` | [df-library.txt](dynaform/example/df-library.txt) | Shared functions: choose the form family, unpack records, order definitions and read the current user's answer. |
| `df!edit` | [df-edit.txt](dynaform/example/df-edit.txt) | The reusable configuration form. One submission defines one question. |
| `df!form` | [df-form.txt](dynaform/example/df-form.txt) | The reusable composer that turns definitions into answer controls. |
| `df!room!edit` | [df-room-edit.txt](dynaform/example/df-room-edit.txt) | The room form's entry point for adding or editing a definition. |
| `df!room!design` | [df-room-design.txt](dynaform/example/df-room-design.txt) | Alice's question list, with Add and Edit links. |
| `df!room!inc` | [df-room-inc.txt](dynaform/example/df-room-inc.txt) | A reusable introduction included on the answering page. |
| `df!room!rdr` | [df-room-rdr.txt](dynaform/example/df-room-rdr.txt) | Bob's generated answering form. Native MF reopens his own answer. |
| `df!room!pub` | [df-room-pub.txt](dynaform/example/df-room-pub.txt) | A read-only view of the current user's saved handover. |

These are CMS object names, not server filenames. For example, paste the contents of `df-form.txt` into the page named `df!form`; uploading that text file to the web directory would not create the object.

`!edit`, `!design`, `!rdr`, `!pub` and `!inc` are naming conventions, not access settings. The `!pub` page in this exercise remains protected. A **leading** `!` has a separate built-in intranet meaning; none of these eight names starts with it.

## 2. Set up Alice and Bob's access

Use Alice's existing backend account for the administrator/designer. Bob is a **page reader**, without a backend account. In the page editor choose **i → Publicity status**. Set each exercise page to **public, no indexing**; in the same controls set its **Usernames & passwords** field as below and save with **s**.

| Objects | Page-logins field |
| --- | --- |
| `df!library`, `df!edit`, `df!form`, `df!room!edit`, `df!room!design` | `?` — no additional reader credential; access is through existing backend accounts. |
| `df!room!rdr`, `df!room!pub`, `df!room!inc` | Add Bob's chosen reader credential in the UI's `?username:password;` format. For example, the username is `bob`; choose the password privately. Add a second reader, `clara`, on its own line for the separation check. Use the same pairs on all three pages. |

Existing backend accounts can open protected reader pages. They are trusted administrators/designers in this example. Adding a person's name to a page is not a way to restrict a backend account's existing administrative powers. On a federated or intranet installation, have its administrator apply the equivalent established policy rather than introducing local reader passwords.

Page inclusions are expanded inside their parent page; the parent's access matters. The designer wrappers and their edit steering comments are the write boundary. Bob must not be able to open `df!room!design`, `df!room!edit` or an existing definition's edit URL.

For this practice installation confirm `$executephp = 'onallpages';` and `$mfwhereclause = 'status=1';` in its private configuration. Use the installation's PHP policy on an existing site; switching a live site's policy for an exercise would affect other pages. Leave the ordinary form-field checker enabled. The sources silence all three automatic email routes.

**Expected now:** Alice opens `index.php?c=df!room!design` and sees an empty Question definitions table. Bob can sign in to `index.php?c=df!room!rdr`; it says no questions are configured. Without login, both answering and saved-result pages require authentication.

## 3. Understand the configuration form you just installed

The entire reusable form is below. It collects **definitions**, not room handovers:

```text
[$$$:df!library]
[fo:]
[fi:]
[le:]Configure one question[:le]
[h:]
<input type="hidden" name="thanks" value="[_pagename!_]!design">
<input type="hidden" name="in2my" value="off">
<input type="hidden" name="em2cu" value="off">
<input type="hidden" name="em2me" value="off">
<input type="hidden" name="firstsaverwins" value="">
<p><label>Position
<input type="number" name="position_mandatory" min="1" step="1" required></label></p>
<p><label>Stable name
<input name="name_mandatory" pattern="[a-z][a-z0-9_]*" required></label></p>
<p>Names are unique within this form. End a required answer's name in
<code>_mandatory</code>. Keep a name unchanged after collecting answers.</p>
<p><label>Label <input name="label"></label></p>
<p><label for="df-type">Type</label> <select id="df-type" name="type">
<option value="INPUT">INPUT</option>
<option value="TEXTAREA">TEXTAREA</option>
<option value="SELECT">SELECT</option>
<option value="PROLOGUEONLY">PROLOGUE ONLY</option>
</select></p>
<p><label>Control attributes <input name="attributes"></label></p>
<p><label>Default value or SELECT options <input name="values"></label></p>
<p><label>Placeholder <input name="placeholder"></label></p>
<p><label>Prologue <textarea name="prologue"></textarea></label></p>
<p><label>Help <textarea name="help"></textarea></label></p>
<p><label>Epilogue <textarea name="epilogue"></textarea></label></p>
<button type="submit">Save question</button>
<p><a href="?c=[_pagename!_]!design">Return to the question list</a></p>
[:h]
[:fi]
[:fo]
```

Its room-specific wrapper contains only the inclusion and the two server-side edit/delete steering comments:

```text
[$$$:df!edit]
[h:]
<!-- TOEDITITEMS_LOGINS_SAME_AS_[df!room!design] -->
<!-- TODELETEITEMS_LOGINS_SAME_AS_[df!room!design] -->
[:h]
```

When this wrapper is opened, `[_pagename!_]` means `df!room`: the core removes the final `!edit` from the current page name. Thus `thanks` returns to `df!room!design`. A definition is saved under `formdata.formname = 'df!room!edit'`, because that is the calling form's page name. The shared object `df!edit` does not own the submitted records.

## 4. Configure Alice's first three questions

Open `index.php?c=df!room!design` as Alice. Choose **Add a question**. Fill in one row of this table, select **Save question**, then repeat for the other rows. Leave fields not shown here empty.

| Position | Stable name | Label | Type | Control attributes | Default value or SELECT options |
| --- | --- | --- | --- | --- | --- |
| `10` | `room_mandatory` | Room | INPUT | `type="text" maxlength="80"` | Empty |
| `20` | `condition_mandatory` | Condition at handover | SELECT | Empty | `Ready==ready\|Needs attention==attention` |
| `30` | `accessnotes` | Access notes | TEXTAREA | `rows="4" maxlength="1000"` | Empty |

For the dropdown, enter this literal value, with no Markdown escape:

```text
Ready==ready|Needs attention==attention
```

On the third definition, also enter **Tell us about doors, alarms or keys.** in **Help**. On the answering form the name ending `_mandatory` requests a required control and the normal CMS mandatory-field check. The visible label may change later; the stable name must remain unchanged.

**Expected now:** Alice's list contains three definition records. The answering page shows Room, Condition at handover and Access notes in position order. Room and condition have an asterisk. The dropdown begins with a blank choice. The help appears beside Access notes. The composer detects duplicate names, invalid names, unknown types and malformed option definitions when it reads the configuration.

The list itself displays rows in record-ID order and shows their positions. To move a question, use its **Edit** link, change Position and save. This example stores position in the definition's own `position_mandatory` field; it needs no missing renumbering service. An existing installation may instead order by the system `notes` column and use separate ordering components.

## 5. See how definitions become an answering form

The room wrapper is complete:

```text
[$$$:df!room!inc]
[fo:]
[fi:]
[le:]My room handover[:le]
[$$$:df!form]
[h:]
<input type="hidden" name="thanks" value="df!room!pub">
<input type="hidden" name="in2my" value="off">
<input type="hidden" name="em2cu" value="off">
<input type="hidden" name="em2me" value="off">
<input type="hidden" name="firstsaverwins" value="">
<button type="submit">Save handover</button>
[:h]
[mf]
[:fi]
[:fo]
```

`df!form` reads only the active definitions belonging to `df!room!edit`. It creates `N_room_mandatory`, `N_condition_mandatory` and `N_accessnotes`. These stable control names are what the native form handler stores. The `df!library` code is supplied in full in the [code reference](dynaform/schema.md#complete-shared-library); there is no `dynacompose` placeholder to implement.

The whole composer is:

```text
[$$$:df!library]
[h:]
<?php
$u5dfFields = u5dfExampleDefinitions(u5dfExampleBase());
if (count($u5dfFields) === 0) {
    echo '<p>No questions have been configured yet.</p>';
}
foreach ($u5dfFields as $u5dfField) {
    $u5dfKey = $u5dfField['key'];
    $u5dfName = 'N_'.$u5dfKey;
    $u5dfId = 'df-'.$u5dfKey;
    $u5dfLabel = u5dfExampleText($u5dfField['label'] ?? '');
    $u5dfHelp = u5dfExampleText($u5dfField['help'] ?? '');
    $u5dfPrologue = u5dfExampleText($u5dfField['prologue'] ?? '');
    $u5dfEpilogue = u5dfExampleText($u5dfField['epilogue'] ?? '');
    $u5dfPlaceholder = u5dfExampleText($u5dfField['placeholder'] ?? '');
    $u5dfDefault = u5dfExampleText($u5dfField['values'] ?? '');
    $u5dfAttributes = (string) ($u5dfField['attributes'] ?? '');
    $u5dfRequired = substr($u5dfKey, -10) === '_mandatory';

    echo '<div class="df-question">';
    if ($u5dfPrologue !== '') echo render($u5dfPrologue);
    if ($u5dfField['type'] !== 'PROLOGUEONLY') {
        echo '<label for="'.u5dfExampleEscape($u5dfId).'">'
            .u5dfExampleEscape($u5dfLabel).($u5dfRequired ? ' *' : '').'</label>';
        $u5dfCommon = ' id="'.u5dfExampleEscape($u5dfId).'" name="'
            .u5dfExampleEscape($u5dfName).'"'
            .($u5dfRequired ? ' required' : '')
            .($u5dfHelp !== '' ? ' aria-describedby="'.$u5dfId.'-help"' : '')
            .' placeholder="'.u5dfExampleEscape($u5dfPlaceholder).'"';
        // Attributes are code supplied only by trusted form designers.
        if ($u5dfField['type'] === 'INPUT') {
            echo '<input'.$u5dfCommon.' '.$u5dfAttributes.' value="'
                .u5dfExampleEscape($u5dfDefault).'">';
        } elseif ($u5dfField['type'] === 'TEXTAREA') {
            echo '<textarea'.$u5dfCommon.' '.$u5dfAttributes.'>'
                .u5dfExampleEscape($u5dfDefault).'</textarea>';
        } else {
            echo '<select'.$u5dfCommon.' '.$u5dfAttributes.'><option value=""></option>';
            foreach (u5dfExampleOptions($u5dfField['values'] ?? '') as $code => $label) {
                echo '<option value="'.u5dfExampleEscape($code).'">'
                    .u5dfExampleEscape($label).'</option>';
            }
            echo '</select>';
        }
        if ($u5dfHelp !== '') echo '<p id="'.$u5dfId.'-help">'
            .u5dfExampleEscape($u5dfHelp).'</p>';
    }
    if ($u5dfEpilogue !== '') echo render($u5dfEpilogue);
    echo '</div>';
}
?>
[:h]
```

The trusted designer supplies `attributes`, prologue and epilogue; these can affect HTML and CMS rendering. Keep this authoring right with trusted designers. Use the control attributes shown in the table; do not introduce a second `name`, `id`, form destination or submit control in that field. Ordinary answers are escaped for display and are never interpreted as CMS/PHP source.

## 6. Save and reopen Bob's handover

1. In a separate browser profile, open `index.php?c=df!room!rdr` as Bob. Use this explicit page URL, including `c=`; MF selects its source form from that name.
2. Enter **Blue room**, select **Needs attention** and enter **Side door sticks**. Choose **Save handover**. The page `df!room!pub` displays those three values. A SELECT stores `attention`; the output maps it to its visible label.
3. Choose **Revise my handover**. Wait for saved values to appear. Change only the notes to **Side door repaired** and save again. The result shows the revised text.
4. In the backend, open **S → special funcs → form data**. There are two separate form families: `df!room!edit` contains the three question definitions; `df!room!rdr` contains the response. Open **html** on the latter. Its current row is status **1) new** and the former handover is available as **6) former version**.
5. In another profile, sign in as Clara and open the answering page and saved-result page. Her answer starts empty. After she saves her own handover, Bob still sees his own values.

The saved-result object uses the authenticated CMS identity and a fixed form name. It does not select another person's response from an `id` or username supplied in the URL. Its [complete source](dynaform/example/df-room-pub.txt) also shows the empty-result message and the return link.

MF and its output must agree on current-record eligibility. This example uses status `1` throughout. MFS would deliberately share one response; changing only `[mf]` to `[mfs]` without changing the output selection would make these pages disagree. Use the [MF/MFS guide](forms/mf-mfs.md) for that separate design.

## 7. Change the questionnaire through the configuration form

As Alice, edit the condition definition and change only its Label to **Condition when returning the key**. Bob reopens his handover: the saved selection remains **Needs attention**.

Now add this fourth definition:

| Position | Stable name | Label | Type | Control attributes |
| --- | --- | --- | --- | --- |
| `40` | `keycount` | Keys returned | INPUT | `type="number" min="0" max="20"` |

Bob reopens his existing answer. The original three values remain; the new optional field is empty. He enters `2`, saves and sees all four values in his saved result. Alice has changed the questionnaire through the configuration form; she has not edited `df!room!rdr` or the composer.

Keep existing stable names and SELECT codes. Changing a required field's suffix changes its stored key, so decide requiredness before collecting answers. Keep recoverable definition versions and retire used questions deliberately: this small reader uses the current definition set and does not reconstruct historical wording automatically. Native answer history and definition revision history have different jobs.

## 8. Configure a second form with the same builder

The supplied library already permits a second family, `df!kit`. Create these five page objects by copying the corresponding room source to a **new** object and replacing `df!room` with `df!kit` throughout each copied source. Retain the three shared objects unchanged.

| New object | Copy source from |
| --- | --- |
| `df!kit!edit` | `df!room!edit` |
| `df!kit!design` | `df!room!design` |
| `df!kit!inc` | `df!room!inc` |
| `df!kit!rdr` | `df!room!rdr` |
| `df!kit!pub` | `df!room!pub` |

Apply the same designer/reader access choices to the new family. Change its visible headings to **Equipment return**. In `df!kit!design`, add position `10`, name `item_mandatory`, label **Equipment**, type INPUT; then position `20`, name `returned_mandatory`, label **Returned?**, type SELECT, options `Yes==yes|No==no`. Leave other fields empty.

Open `index.php?c=df!kit!rdr` as Bob. Only the two equipment questions appear. Saving them does not change his room handover. Each family has its own definitions (`!edit`) and answers (`!rdr`). Creating the five small wrapper objects is the administrator's per-form setup; later question maintenance is done entirely through the configuration form. For a third family, also add its fixed base name to the allowed list in `df!library`.

## 9. Add an attachment when the form needs one

Complete [the upload exercise](forms/uploads.md#build-a-two-attachment-example) first. It supplies two complete named pages and explains which reader can retrieve a protected file.

For this builder, create a definition at position `50`, name `supportingfile`, type PROLOGUEONLY. Put the following in its Prologue; leave the other fields empty:

```text
[h:]
<p>Supporting document. After uploading, save the handover.</p>
<script src="upload"></script>
[:h]
```

The composer renders the standard protected widget inside Bob's answering form. Upload a small permitted test file, wait for **OK**, then **Save handover**. In the backend, Alice opens the `df!room!rdr` response and its `userupload1` link. The native protected-file endpoint requires its own authorised retrieval; Bob's reader login alone does not confer backend-file access. The simple `!pub` reader above lists answer controls and deliberately does not build a custom file-sharing route.

Keep upload positions stable. If a second widget is needed, append it after the first. Moving or inserting numbered upload widgets can connect an old `userupload1` value to a different visible question.

## If the result differs

| Observation | Check and next action |
| --- | --- |
| PHP source is visible or controls never appear | Check the installation's CMS PHP policy and that every complete source was pasted into the correctly named page. |
| No questions appear | Check `df!room!edit` in Form data, status `1`, and the exact eight object names. Saving definitions under the shared `df!edit` page is the wrong entry point. |
| A duplicate/invalid-name message appears | Open the design list and correct the relevant new definition before collecting answers. Keep used keys stable. |
| Bob can edit definitions | Check that Bob is a reader without backend administration, that the design pages contain no Bob reader credential and that the two steering comments target `df!room!design`. |
| Saved values vanish | Check the same login, explicit `c=df!room!rdr`, current status `1` and unchanged `N_` field names. |
| Another form's questions appear | Compare the family name in the wrapper, definition form and library's allowed bases. |
| A SELECT is empty or its meaning changes | Use explicit `Label==code` options, one `|` between options and unchanged codes in translations. |
| A file uploaded but its link is absent from Form data | Save the enclosing handover after the widget reports OK. |

The [reference](dynaform/schema.md) explains the exact field map, the library and the relation to existing `s!edit`/`s!form`, `!rdr`, `!pub`, `!inc` and ordering components. The supplied example is a small independent teaching implementation of that method, rather than an export of a private site's application and access integration.

The [teaching-check record](labs/dynaform-2026-11-28.md) identifies the source version, runtime, observed form/save/edit paths and limits of the local verification.
