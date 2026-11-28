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
