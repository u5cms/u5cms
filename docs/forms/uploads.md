# Add form uploads and use their generated links

[Documentation home](../README.md) · [Forms and exports](../forms.md) · [MF and MFS](mf-mfs.md)

## Summary

The built-in upload widgets upload a file first, put its generated URL into a numbered hidden field, then store that URL with the submitted form. Choose protected `upload` for attachments that need controlled retrieval. `Pupload` places files in a public directory; a hard-to-guess filename is not an access restriction. A successful upload indicator alone does not mean the enclosing form response has been saved.

**For** form authors and installation administrators. **Version** implementation reviewed on stable 12.10.14. **You need** an upload-enabled practice installation and an agreed access/retention policy for its files.

## Choose the widget

| Script source, case-sensitive | Required file? | Storage | Configuration |
| --- | --- | --- | --- |
| `upload` | No | `fileversions/useruploads/`; protected retrieval | `$allowuseruploads = 'yes'` |
| `upload_mandatory` | Yes | Same protected storage | `$allowuseruploads = 'yes'` |
| `Pupload` | No | `r/P/`; public file URL | `$allowuserPuploads = 'yes'` |
| `Pupload_mandatory` | Yes | Same public storage | `$allowuserPuploads = 'yes'` |

The configuration sample enables ordinary uploads and disables public Puploads. Check the installation's actual settings, the frontend extension allowlist in `configallowedfileextensions.php`, PHP's upload limits and write access to the chosen directory. Enable public uploads only for files intended to be public.

## Add one attachment to an existing form

1. Start with the [worked registration form](../forms.md#a-worked-registration-form), using a practice page with the intended login restrictions.
2. Put this inside its `[h:]…[:h]` block, before the submit button:

   ```html
   <p>Supporting document (optional)</p>
   <script src="upload"></script>
   ```

3. Save the page and open its visitor URL through the CMS. Upload an allowed small test file and wait for **OK**. Submit the enclosing form and wait for its normal saved/thanks result.
4. In **S → form data → html**, find the response and its `userupload1` value. Open the generated file link using the intended authorised account. Test the same retrieval without that account as part of access acceptance.
5. If the form allows later editing, reopen the saved response and check that the file indicator and link remain associated with the same question. Save a text-only change and check the attachment again.

Use the exact script source above. The CMS renderer adds its upload authorisation parameter. Copying a rendered widget URL, iframe or temporary key into page source is not the installation procedure.

## Build a two-attachment example

The community centre wants a handover record with a signed checklist and a supporting document. This complete exercise uses protected files which a backend editor can retrieve. Use two authorised **backend test accounts** if both people must open the protected download links; an ordinary page-reader login alone does not grant that file access. Keep the installation's personal SELF scope for the exercise.

1. In **P → new**, create `attachmentform` and `attachmentlinks`. Choose **i → Publicity status → public, no indexing** and enter `?` in each page's logins field so access requires an existing backend account. Save the source below into each page's first configured language field. The [first-page guide](../first-page.md#create-the-page) shows the exact creation controls.
2. Confirm `$allowuseruploads = 'yes';` in this practice installation's private `config.php`, and that small PDF files are allowed by `configallowedfileextensions.php` and the host's upload limits. This example needs no public Pupload setting.

### `attachmentform`

```text
[fo:]
[fi:]
[le:]My room-handover attachments[:le]
[h:]
<input type="hidden" name="thanks" value="attachmentlinks">
<input type="hidden" name="in2my" value="off">
<input type="hidden" name="em2cu" value="off">
<input type="hidden" name="em2me" value="off">
<input type="hidden" name="firstsaverwins" value="">
<label>Room <input name="room_mandatory" required></label>
<fieldset><legend>Signed checklist</legend>
<script src="upload"></script>
</fieldset>
<fieldset><legend>Supporting document</legend>
<script src="upload"></script>
</fieldset>
<p>Wait for both uploads to finish, then save the handover.</p>
<button type="submit">Save attachments</button>
[:h]
[mf]
[:fi]
[:fo]
```

### `attachmentlinks`

```text
[[[[My saved handover attachments]]]]
[h:]
<table>
<caption>My current handover</caption>
<thead><tr><th>Room</th><th>Signed checklist</th><th>Supporting document</th></tr></thead>
<tbody>
[room*,userupload1,userupload2|attachmentform|ALL|SELF|1|<tr><td>|</td></tr>|</td><td>|id DESC LIMIT 1:dat]
</tbody>
</table>
[:h]
[Revise my attachments:attachmentform]
```

3. Open `index.php?c=attachmentform` as the first test account. Enter **Blue room**. Upload one small test PDF in Signed checklist and a **different** PDF in Supporting document. Wait for each widget's **OK**, then select **Save attachments**. `attachmentlinks` shows one row with the room and two generated links.
4. Open each link and check the actual PDF contents. In **S → form data → attachmentform → html**, check that `userupload1` refers to the checklist and `userupload2` to the supporting document. The link text may be the extension, such as **pdf**; compare the file contents, not only that label.
5. Choose **Revise my attachments**. Wait for the existing room and upload indicators. Change only Room to **Green room**, save and open both files again. The files should be unchanged.
6. Reopen once more. In **Supporting document**, choose the **×** beside its OK indicator (tooltip **DELETE FILE**) and confirm **DELETE FILE?**. The second file picker reappears. Choose the replacement PDF, wait for OK and save. Leave the first widget untouched. The first link still retrieves the original checklist; the second retrieves the replacement document. This widget action clears that field's current association before replacement; it does not purge the older stored file. The form's former version remains available through the backend, subject to the site's retention policy.
7. Open `attachmentlinks` in the other test account's browser profile. Under personal SELF scope it has no row until that account saves its own handover. A signed-out visitor must authenticate before opening either page.

The two identical-looking script lines deliberately produce different numbered fields by their order in the form. Keep that order unchanged when adapting the example. The `!pub`/`!rdr` suffixes used in a composed application are optional naming conventions; this exercise's two names are `attachmentform` and `attachmentlinks` throughout.

## How a generated link reaches the response

| Step | What exists |
| --- | --- |
| Render the form | Each widget creates its own hidden field, starting with `userupload1`; mandatory variants add their required suffix. |
| Upload a file | The server chooses a filename and writes its URL back to that field. The visible widget reports success. |
| Save the form | The form handler stores the URL as a field value in the response's `datacsv`, alongside the other values. |
| Show a result or reopen it | Backend views and DAT can render the stored upload value as a link; MF/MFS/edit loaders restore it into its matching widget. |

For a list, use the stored upload field through [DAT](../dat.md) and its [parameter reference](../dat/parameters.md). The standard renderer recognises `userupload…` values and handles upload links. Preserve its protected-file routing; do not publish a raw storage path or construct a public URL for a protected attachment.

The protected file endpoint used by the standard form-data views is `ffff.php`. Its ordinary protected-upload access is associated with backend accounts, not automatically with the person who submitted a reader form. If submitters need to download their attachments, explicitly design and test that access path. Public Pupload needs no such login and is unsuitable as a shortcut for private sharing.

## Keep identity, access and retention separate

Generated names can contain an uploader identifier according to `$showemailinuploadfilenames` or `$showemailinPuploadfilenames`. Review that naming policy before collecting personal data or distributing links. A stored author name or URL does not prove sender identity; [authenticated sender evidence](../authenticated-messaging.md) is a separate feature.

Files and response rows have separate lifecycles. Closing a page after upload can leave an unsubmitted file; removing a field from a form does not remove its older data. The normal cleanup routine examines references in `formdata`, record status and file age. Its ordinary policy can remove unreferenced old uploads or files associated only with sufficiently old deleted records. The enabled sender-evidence mode bypasses that cleanup to retain attachments. Plan any business retention policy explicitly; do not infer it from a filename or from MFS versioning.

Back up the database and both relevant upload directories together. Test replacement, removal, restoring an older response and restoring a full backup before relying on long-lived links. Keep upload field order stable in a composed form.

## Troubleshooting and evidence

| Symptom | Check and next action |
| --- | --- |
| The page says uploads are unavailable | Check the matching `allowuser…` setting and the script's capitalisation. |
| Upload reports an extension or size error | Check the frontend extension allowlist and the hosting/PHP limits; use an allowed small file first. |
| Upload says OK, but there is no response | Submit the enclosing form and check its validation result. Upload and response save are separate steps. |
| The wrong saved file appears beside a question | Compare widget order and field names with the original definition; avoid renumbering existing uploads. |
| The file opens for an editor but not a reader | Check the actual protected retrieval policy; form access does not itself confer backend-file access. |
| A supposedly private file opens without a login | Check whether it was uploaded through public Pupload and assess its published URLs. |

Implementation: [`upload/index.php`](../../upload/index.php), [`upload/uploaddone.php`](../../upload/uploaddone.php), [`Pupload/uploaddone.php`](../../Pupload/uploaddone.php), [`remotefill.inc.php`](../../remotefill.inc.php), [`ffff.php`](../../ffff.php) and [`u5admin/cleanuseruploads.php`](../../u5admin/cleanuseruploads.php). The existing [survey example](survey-results.md) and [form exercises](../../tests/documentation-form-exercises.cjs) include an actual attachment upload. Access through a custom download component needs its own deployment check.
