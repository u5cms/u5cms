# Turn a survey into a reviewed results table

[Documentation home](../README.md) · [Forms and exports](../forms.md)

## Summary

Collect workshop suggestions, see the distribution of preferred times in the backend, and publish the suggestions you have reviewed. The results page reads only records marked **4) done**. Changing a record back to **1) new** takes it off that page.

**For** form authors and the editor reviewing the responses. **Version** checked on u5CMS 12.10.4. **You need** a test installation, permission to create pages and access to **Form data**. Use sample workshop names and an image you can share with the intended readers.

## 1. Create the survey and its results page

Create two page objects named `workshoppoll` and `pollresults`. Give both pages a title and set their intended readers. For a rehearsal, restrict both to the test accounts. Paste the following sources in the English fields and save. Keep the field names the same in each translation.

### `workshoppoll`

```text
[fo:]
[fi:]
[le:]Suggest a workshop[:le]
[h:]
<input type="hidden" name="thanks" value="pollresults">
<input type="hidden" name="in2my" value="off">
<input type="hidden" name="em2cu" value="off">
<input type="hidden" name="em2me" value="off">
<label>Workshop title <input name="workshop_mandatory"></label>
<label>Preferred time
<select name="slot_mandatory">
<option value="">Choose a time</option>
<option value="Morning">Morning</option>
<option value="Afternoon">Afternoon</option>
</select>
</label>
<fieldset><legend>Optional workshop image</legend>
<script src="upload"></script>
</fieldset>
<button type="submit">Send suggestion</button>
[:h]
[:fi]
[:fo]
```

The option values are also the words readers will see. The first optional upload on a form is stored as `userupload1`. Wait for the upload's **OK** before submitting. For a text-only survey, leave out the image fieldset and remove the image heading and `userupload1` from the results example. The three mail settings silence notifications for this exercise.

### `pollresults`

```text
[h:]
<table>
<caption>Reviewed workshop suggestions</caption>
<thead><tr><th scope="col">Workshop</th><th scope="col">Preferred time</th><th scope="col">Image</th></tr></thead>
<tbody>
[workshop*,slot*,userupload1|workshoppoll|ALL||4|<tr><td>|</td></tr>|</td><td>|id ASC||120x0:dat]
</tbody>
</table>
[:h]
```

The headings appear immediately. DAT adds one row per reviewed suggestion. Its three markup positions open a row, close it and separate its cells. `120x0` sets a maximum image width and keeps the proportions. The workshop title and time convey the result even when the image cell is empty.

## 2. Submit and review two suggestions

1. Open `workshoppoll` as a participant. Enter **Map reading**, choose **Morning**, optionally upload a small PNG, and select **Send suggestion**. Submit **Bookbinding** with **Afternoon** and no image as a second response.
2. Open the results page directly using `index.php?c=pollresults&l=en` in a second tab. You should see the column headings and no data rows. A successful submission arrives as **1) new**. The results page selects **4) done**.
3. In the backend, open **Form data** and find `workshoppoll`. Its **html** view shows the two individual records. Open **chart** for the new records. The time question should show one response for each option, 50% apiece.
4. In **html**, change only the **Map reading** row to **4) done**. Refresh the results tab. It should show one row, **Map reading | Morning**, and the image if supplied. The **Bookbinding** response remains in the backend for review.
5. To withdraw the published suggestion while keeping the response, open the **4) done** records in **Form data** and move the row back to **1) new**. Refresh the results tab and check that the row has disappeared.

The form opens its thank-you destination by posting to it. Refreshing that particular tab can prompt Firefox to resend form data. The direct results address opens a fresh view that you can refresh throughout the review.

### Start with a single field

To see how the table is built up, put this shorter version on an optional test page with the same readers. After step 4 it displays **Map reading** and after step 5 it is empty.

### `polltitles`

```text
[workshop*|workshoppoll|ALL||4:dat]
```

Keep the same source, selection and status when adding columns. The full table adds the time and upload field to the first position, then supplies the row and cell markup. This lets you diagnose a missing record before working on its presentation.

## Using an existing spreadsheet

For an existing spreadsheet, **Form data** also offers **import from Excel**. The importer accepts tab-separated data with a header row. Remove the system status column from the pasted data and check the target form name. Match the form's machine field names and inspect the imported records' status before including them in a reviewed view. See [forms and exports](../forms.md#view-and-export-the-result) for the reverse journey.

## If the result differs

| Observation | Check |
| --- | --- |
| The backend has two records but the results table is empty | Check the saved name `workshoppoll` and the status of the individual rows. |
| The chart and the published table have different totals | Compare their status selections. The exercise opens the chart for new responses and the table for reviewed ones. |
| The image is missing | Check that uploads are enabled for the installation, the file type is permitted and the upload completed before submission. Test image access with an intended reader. |
| A value is missing in a translated form | Check the input's `name` and the option's stored `value` in both languages. |

For linked list and detail pages, use the [course directory](../dat.md). The [DAT reference](../dat/parameters.md) explains all fourteen positions. The original [form-rendering tutorial](https://yuba.ch/r/rendertutorial/rendertutorial_en.pdf) illustrates the progression with a jazz survey.

Continue with [Scenario 2, a list that contributors can edit](manage-entries.md). There the contributor can change a saved response directly.
