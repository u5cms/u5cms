# Forms, stored records and exports

[Documentation home](README.md)

## Summary

A working form has several observable stages: the page renders the intended controls, validation accepts or rejects input, the server writes a record, the completion action runs, and the stored value can be viewed or exported. Check each stage separately. A visible confirmation alone does not establish every downstream result.

**Audience:** form authors and installation administrators. **Version:** worked form exercised on 12.10.0. **Scope:** ordinary frontend submissions; optional modes stated separately.

## A worked registration form

The laboratory used this small form in a page named `docform`. Its callback deliberately keeps the visitor on the same page, making successful completion easy to observe. The three mail controls disable notifications for this exercise.

```text
[fo:]
[fi:]
[le:]Workshop registration[:le]
[h:]
<input type="hidden" name="thanks" value="documentationSaved()">
<input type="hidden" name="in2my" value="off">
<input type="hidden" name="em2cu" value="off">
<input type="hidden" name="em2me" value="off">
<label for="participant">Your name</label>
<input id="participant" name="participant_mandatory">
<button type="submit">Register for the workshop</button>
<p id="submission-status"></p>
<script>
function documentationSaved() {
    document.getElementById('submission-status').textContent =
        'Your registration was saved.';
}
</script>
[:h]
[:fi]
[:fo]
```

The CMS form markers provide its existing submission integration. The HTML gives the field an accessible label, a stable machine name and a submit button. The `_mandatory` suffix requests mandatory-field validation. The `thanks` field selects the completion action; it can also name the site's existing confirmation page instead of a callback.

Use the installation's permitted PHP/JavaScript and template configuration. Preserve its existing notification choices when adapting a production form; the exercise's disabled notifications are deliberate test settings. Use one native `[fo:]` wrapper per page.

## What the exercise demonstrated

| Attempt | Observed result |
| --- | --- |
| Empty mandatory field sent to the real handler | Rejected; no record inserted. |
| Extra field absent from the stored form source | Rejected; no record inserted. |
| Valid browser submission | Callback ran and exactly one record was inserted. |
| Value `Zürich – 東京 🙂` | Preserved in the actual backend data view and UTF-8 CSV export. |

The server validates field names against the stored form source. An extra client-side field is not automatically part of the accepted form contract. Correct mismatched names rather than disabling that check to suppress an error. Translated labels can differ; machine field names must remain consistent across languages.

## View and export the result

Open the form-data area for the relevant form, choose the required status and inspect the record. Then use its CSV export and check the intended spreadsheet import settings. Filtering a view changes which rows you see; it does not transfer ownership or grant new permissions.

The internal `formdata.datacsv` representation contains CMS-specific separators and escaped values. It is not the finished export. During the exercise, inspecting that raw value without the normal decoding initially obscured the Unicode text. The CMS's own table view and CSV endpoint reproduced the original value correctly. Use those supported paths for ordinary viewing and exchange.

To show selected fields on a visitor-facing page, build a [course directory with DAT](dat.md). Its list and detail views read the saved records directly.

For ownership, see [SELF permissions](permissions.md). For a custom persistence routine, the technical [site-writer reference](onlymine.md#form-specific-storage) explains its explicit marker, fixed function and required result. Forms without that marker retain the built-in writer.

## Protect public submissions with Turnstile

Configure the site's actual widget keys before enabling protection:

```php
$requireturnstileonfrontendformsave = 'yes';
$u5turnstilesitekey = 'YOUR_SITE_KEY';
$u5turnstilesecret = 'YOUR_SECRET_KEY';
```

Include `grc.php` within each protected form using the installation's existing template integration. The server checks the token; hiding a submit button is only the visible part of the flow. Current widgets keep their own completion, expiry and reset state when more than one protected HTML form is present.

The legacy `$requiregooglerecaptchaonfrontendformsave = 'yes';` setting also requires server verification. Protected installations must supply current Turnstile keys. To deliberately disable verification, disable or remove both protection settings. The [protected-form release notes](https://github.com/u5cms/u5cms/blob/v12.9.5.13/docs/release-12.9.5.13.md#protected-forms) explain the compatibility boundary.

## Optional archived sender evidence

Ordinary forms can leave `$formSenderEvidenceRequired` unset or `'no'`. The separate `saml-archive-v1` mode requires an authenticated SAML archive, signing configuration, schema provisions and compatible append-only forms. Custom writers, payment submissions and per-user version-checked replacement flows need separate compatibility review and can be rejected by that mode.

The current local regression exercised ordinary and enabled modes, identity/archive failures, view restrictions, uncertain delivery and incompatible forms. It did not authenticate against a live identity provider. Follow the complete [authenticated-messaging setup and acceptance procedure](authenticated-messaging.md) before enabling the mode on a real installation.
