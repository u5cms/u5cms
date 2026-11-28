# DynaForm field definitions and renderer contract

[DynaForm overview](../dynaform.md) · [Documentation home](../README.md)

## Summary

One saved definition record represents one question or explanatory block. Its stable name ties the rendered answer control to saved response values; its labels, help and layout can change independently. This reference records the inspected composition pattern and supplies a neutral definition form. A compatible site renderer is required: the form below collects definitions but does not install that renderer.

**For** composer implementers and trusted form designers. **Version** method reviewed on 28 November 2026, using core u5CMS 12.10.14 semantics.

## Definition fields

| Stored definition field | Meaning in the inspected pattern |
| --- | --- |
| `name_mandatory` | Unique stable question name within one form family, restricted to lowercase letters, digits and underscore. Do not rename a name with saved answers. |
| `prologue` | Introductory text before the question; may use the site's trusted markup policy. |
| `label` | Visible question/field label. |
| `type` | `INPUT`, `TEXTAREA`, `SELECT` or `PROLOGUEONLY`. |
| `attributes` | Trusted HTML control attributes, such as an input's `type`, `required` or length limit. These are code-bearing designer input, not respondent text. |
| `values` | Default text for INPUT/TEXTAREA; option definitions for SELECT. |
| `placeholder` | Optional hint; keep a persistent visible label as well. |
| `help` | Supplementary help displayed by the renderer. |
| `epilogue` | Explanation after the answer control. |
| `oca` | Outer-container attributes used by the site's layout/conditional behaviour. Restrict these to trusted designers. |
| `notes` system column | Ordering value in the inspected implementation; maintained by a separate ordering/renumbering helper, not by the respondent. |

Some implementations pack language variants into one definition value, separated by `|||`, then choose a language with fallback. This is a composer convention, not the delimiter of `headcsv`/`datacsv`. Confirm the renderer's exact language order and fallback before importing translations. Keep machine keys and option codes invariant across languages.

## Neutral definition form

Create a protected page `roomcheck!edit`. Give only trusted designers access and configure its edit/delete steering comments to refer to that same designer policy. This complete form collects the fields above; use the standard backend form-data view or a separate authorised list to open existing definition rows.

```text
[fo:]
[fi:]
[le:]Define a room-check question[:le]
[h:]
<input type="hidden" name="thanks" value="roomcheck!edit">
<input type="hidden" name="in2my" value="off">
<input type="hidden" name="em2cu" value="off">
<input type="hidden" name="em2me" value="off">
<input type="hidden" name="firstsaverwins" value="">
<!-- TOEDITITEMS_LOGINS_SAME_AS_[roomcheck!edit] -->
<!-- TODELETEITEMS_LOGINS_SAME_AS_[roomcheck!edit] -->
<label>Stable name <input name="name_mandatory" pattern="[a-z0-9_]+" required></label>
<label>Prologue <textarea name="prologue"></textarea></label>
<label>Label <input name="label"></label>
<label>Type <select name="type">
<option value="INPUT">INPUT</option>
<option value="TEXTAREA">TEXTAREA</option>
<option value="SELECT">SELECT</option>
<option value="PROLOGUEONLY">PROLOGUE ONLY</option>
</select></label>
<label>Control attributes <input name="attributes"></label>
<label>Default value or options <input name="values"></label>
<label>Placeholder <input name="placeholder"></label>
<label>Help <textarea name="help"></textarea></label>
<label>Epilogue <textarea name="epilogue"></textarea></label>
<label>Outer-container attributes <input name="oca"></label>
<button type="submit">Save question definition</button>
[:h]
[:fi]
[:fo]
```

No MF/MFS marker is used here: each submission adds a separate question definition. Use an explicit edit link for an existing row. The browser's `pattern` helps the designer; the composer must also enforce the allowed name pattern and per-family uniqueness on the server. Set the ordering values using the implementation's ordering helper or the backend's notes control.

## A three-question design

| Stable name | Type | Label | Attributes | Values |
| --- | --- | --- | --- | --- |
| `room` | `INPUT` | Room | `type="text" required maxlength="80"` | Empty default |
| `condition` | `SELECT` | Condition at handover | `required` | `Ready==ready\|Needs attention==attention` |
| `accessnotes` | `TEXTAREA` | Access notes | `rows="4" maxlength="1000"` | Empty default |

The backslash in the Markdown table only escapes the table's vertical bar. Enter the dropdown definition as:

```text
Ready==ready|Needs attention==attention
```

The inspected SELECT convention splits options at `|` and splits each label/code pair at `==`. Without an explicit code, it uses the option's one-based position. Prefer explicit codes so reordering choices does not change existing answer meaning. Separators occurring within labels/codes need an explicit escaping or alternative representation in the composer; do not assume that the packed string supports them.

## Renderer contract

| Concern | Required behaviour |
| --- | --- |
| Definition selection | Read the intended family only, normally status `1`, with deterministic ordering. Treat a request-driven family selector as untrusted and restrict it to allowed families. |
| Packed values | Match the field names in `headcsv` to the corresponding values in `datacsv`; preserve the CMS's semicolon escaping and encoding conventions. Do not split arbitrary SQL/HTML strings as a substitute for this mapping. |
| Response field names | The inspected pattern uses `N_<name>` for answers, `I_<name>`/`O_<name>` as boundary markers and `S_<name>` for a selected option's display text. These are composer conventions, not new core commands. Preserve them when reusing that renderer and its output parser. |
| INPUT/TEXTAREA | Emit one named control, a persistent associated label and the chosen attributes/default. Escape literal values in their HTML context. |
| SELECT | Emit an initial blank option, the explicit value/label pairs and a stable answer field. Store the value as the machine code; use separate display text when the output format requires it. |
| PROLOGUEONLY | Render explanatory content without creating an answer control. |
| Required answers | HTML `required` provides browser validation. The CMS server's mandatory convention uses a field name ending in `_mandatory`; the composer must map/enforce required definitions accordingly or implement equivalent server validation. HTML attributes alone do not establish that server rule. |
| Prefilling | Use the same response field names across visits/languages; run conditional/calculated display after the loader's `starterscript` callback. Compose shared hooks instead of overwriting them. |
| Uploads | Keep widget order/numbered fields stable, use the standard upload scripts and retain the generated URL with the answer. Custom wrappers must keep their IDs unique. |
| Read-only output | Select an authorised response independently, align its keys with the correct definition version and escape ordinary answer text. Do not execute respondent input as markup, HTML or PHP. |
| Designer UI | Enforce definition edit/delete permissions on the server. Hiding designer controls is only presentation. |

If an installation uses different prefixes or a different schema, document that adapter explicitly. Do not change an existing naming scheme just to match this reference: older responses and exports can depend on it.

## Version and retirement policy

Keep a recoverable definition set when questions change. At minimum record when a definition changed and preserve the previous wording/codes so an older answer remains interpretable. A stronger design associates each response with an immutable definition revision. The core form history does not automatically make a custom renderer select the appropriate definition revision.

Retire a question without reusing its name for a different meaning. Preserve old values and upload references according to the site's retention policy. Check both newly submitted and older responses before publishing a changed definition set.
