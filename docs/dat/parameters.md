# DAT parameter reference

[Course directory exercise](../dat.md) · [Documentation home](../README.md)

## Summary

A DAT command has up to fourteen positions, separated by vertical bars. The form object's name and the requested fields determine the source. The other positions select records and control their display. Start with the [working list and detail pages](../dat.md#enter-and-display-two-courses), then change one position at a time.

**For** form authors who already have saved records. **Version** stable 12.10.3 and 12.10.4. **Scope** the `:dat` command in a page source.

```text
[a|b|c|d|e|f|g|h|i|j|k|l|m|n:dat]
```

Leave unused intermediate positions empty. Trailing positions can be omitted. In `[course*|courseentry|ALL||1:dat]`, the double bar leaves the record filter empty and puts `1` in the status position.

| Position | Purpose | How to use it |
| --- | --- | --- |
| a | Fields to display, in order | Use the case-sensitive input names, separated by commas. `course*` refers to `course_mandatory`. Prefix a system column with a question mark, as in `?id` or `?notes`. |
| b | Source form | Enter the page object that contains the form, such as `courseentry`. Keep its name tight against the separators. |
| c | Record selection | Use `ALL` for a list, a positive ID for one known record, or `?` for the URL's `id`. Link to the detail page with an actual positive ID. |
| d | Filter within the source form | Leave empty for the shared selection. Use `SELF` for the configured ownership scope. Ordinary text matches a substring in the staff notes. `(authuser)` matches an account mentioned in parentheses in those notes. |
| e | Record status | `1` selects new records. An empty position leaves the status unrestricted. Check the status in **Form data** before building the view. |
| f | Before each record | Insert the opening markup for one item or row. A row placeholder such as `$id` can be used in a link. |
| g | After each record | Close the item or row opened in **f**. Put the outer list or table around the entire DAT command. |
| h | Between fields | Supply a separator, for example ` / ` or `</td><td>`. For different successive separators, separate them with `@@` and provide one per gap. |
| i | Order and limit | Use a fixed database column and direction, for example `id ASC` or `id DESC LIMIT 3`. A form field such as `town_mandatory` is packed in the record data and is not a standalone sort column. |
| j | Line breaks in a stored value | Choose a replacement such as `<br>`. With no replacement the renderer normally inserts a spaced vertical bar. |
| k | Uploaded files and images | The usual output is a file link. `300x0` sets a maximum image width and preserves the ratio. `300+0` sets a width. A trailing `!` requests the thumbnail route where supported. A lone `!` returns the stored field value. |
| l | Value to match | Test the selected field against a literal value. Use with **m**, for example `Bern` and `Local course`. This operates on the field being displayed. |
| m | Replacement on a match | Output the replacement for a matching field value. An unmatched value produces empty field output. The record's wrappers still appear. |
| n | Interpretation and links | Empty or `0` displays text. Modes `1`–`5` change how the renderer treats markup, as described below. Add `!` after the digit to turn web addresses into links. |

## Output modes

Use mode `0` for visitor-supplied text. The example needs no PHP execution or full SQL clauses. Choose a richer mode only for fields whose authors are trusted to supply the corresponding markup.

| n | Effect |
| --- | --- |
| `0` or empty | Displays field values as text. |
| `1` | Allows HTML in system fields such as `?notes`. Submitted fields still display as text. |
| `2` | Allows HTML in submitted fields. System fields use text output. |
| `3` | Allows HTML in both submitted fields and system fields. |
| `4` | Renders u5CMS syntax in submitted fields. System fields use text output. |
| `5` | Renders u5CMS syntax and HTML in submitted fields. Use the CMS HTML delimiters for HTML sections. The usual line-break replacement in **j** does not apply to those submitted fields. |

## Try two changes on the same courses

After completing the course directory, put this example on an additional page named `datcheck`, with the same readers. The first command shows the most recent of the two records. The second replaces the town value `Bern` with a label. With the exercise data the two paragraphs read **Bookbinding** and **Local course**.

### `datcheck`

```text
[h:]
<h2>Latest course</h2>
<p id="latest-course">[course*|courseentry|ALL||1||||id DESC LIMIT 1:dat]</p>
<h2>Local label</h2>
<p id="local-label">[town*|courseentry|ALL||1|||||||Bern|Local course|0:dat]</p>
[:h]
```

## Notes, ownership and links

- `?notes` displays the staff notes up to the first `|||` stored **in the notes**. The notes filter can still match text after that marker.
- Use `SELF` for personal or domain-scoped record selection. `(authuser)` looks for an account written into the notes. The [ownership guide](../permissions.md) explains which records a signed-in account receives under each policy.
- The renderer substitutes `$id` for each row's numeric ID. Other row values include `$st` for status, `$se` for the stored submission timestamp, `$hu` for readable submission time and `$la` for the last modification. Choose only fields appropriate for the page's readers.
- Position **d** also supports a fixed `LIKE` filter, a filter supplied by the `notes` URL parameter when the position contains `?`, and a full clause written by the page author when the installation permits it. Position **i** can read a sort selection from the URL. Visitor-controlled criteria should only narrow data the reader is already entitled to see. Keep record authorisation in the page and ownership rules.
- Administrators can check `$allowfullwhereasdatparameterd` for full clauses and `$showdatcommandsqlerrors` for the display of database errors. Neither setting needs changing for the course exercise.
- For a fixed shared view, keep the filter and sort settings in its source. The [original reference](https://yuba.ch/dat) also describes request-driven selection, advanced filtering and installation settings. The [V17 compatibility report](https://github.com/u5cms/u5cms/blob/V17-New-Responsive-Layouts/docs/responsive-design.md#rendering-and-compatibility) records comparisons for positions **a–n**.
