# Collect a workshop survey over two pages

[Forms and exports](../forms.md) · [Documentation home](../README.md)

## Summary

Use one form per step. Its hidden `thanks` field opens the next page after u5CMS saves the answer. This exercise asks participants to choose a workshop, then give their name and attendance preference. Both forms record the same email address so the organiser can match the two exports and see who stopped after the first step.

**For** form authors and editors with access to **Form data**. **Tested on** u5CMS 12.10.4. **You need** a test installation, four page objects and two browser profiles. The exercise uses a named survey with one response per email address as its working assumption.

## 1. Create the four pages

In the backend, create the page objects below and paste each example into its English content field. Give them the same reading permissions and keep the object and field names identical in each language. If a page called `youremail` already exists, inspect it before using it. Other forms may include it. A separate exercise can use another fragment name if both includes are updated to match.

### `youremail`

```text
[h:]
<label for="youremail_mandatory">Email address</label>
<input type="email" required name="youremail_mandatory" id="youremail_mandatory"
       onchange="document.getElementById('survey-email-store').src='saveyouremail.php?youremail='+encodeURIComponent(this.value)">
<iframe hidden id="survey-email-store" title="Remember the address"></iframe>
<iframe hidden src="writeyouremail.php" title="Restore the address"></iframe>
[:h]
```

The first helper remembers the entered address in the site's `youremail` cookie when the field changes. The second fills the next page's field. Keep the input ID and name as shown. The cookie is persistent and is shared by visits in the same browser profile. For a shared kiosk, keep just the label and input, remove `onchange` and both iframes, and ask for the address on each step.

### `workshopchoice`

```text
[fo:]
[fi:]
[le:]1 of 2. Choose a workshop[:le]
[$$$:youremail]
[h:]
<input type="hidden" name="thanks" value="workshopformat">
<input type="hidden" name="in2my" value="off">
<input type="hidden" name="em2cu" value="off">
<input type="hidden" name="em2me" value="off">
<label for="survey-topic">Workshop</label>
<select id="survey-topic" name="topic_mandatory">
  <option>Map reading</option><option>Bookbinding</option>
</select>
<button type="submit">Save and continue</button>
[:h]
[:fi]
[:fo]
```

### `workshopformat`

```text
[fo:]
[fi:]
[le:]2 of 2. How will you attend?[:le]
[$$$:youremail]
[h:]
<input type="hidden" name="thanks" value="workshopthanks">
<input type="hidden" name="in2my" value="off">
<input type="hidden" name="em2cu" value="off">
<input type="hidden" name="em2me" value="off">
<label>First name <input name="firstname_mandatory" required></label>
<label for="survey-attendance">Attendance</label>
<select id="survey-attendance" name="attendance_mandatory">
  <option>Online</option><option>In person</option>
</select>
<button type="submit">Finish survey</button>
[:h]
[:fi]
[:fo]
```

### `workshopthanks`

```text
[[[Survey complete]]]
Thank you. Your attendance preference has been saved.
```

The first two pages each contain a separate form and one copy of the shared field. The final page contains the confirmation. The three mail switches suppress the automatic notifications for each step of this exercise.

## 2. Walk through as a participant

1. Open `index.php?c=workshopchoice&l=en`. Enter `alex+workshop@example.org`, choose **Map reading**, then **Save and continue**.
2. On page two, check the prefilled address, enter **Alex**, choose **Online** and select **Finish survey**. The **Survey complete** heading appears.
3. In another browser profile, enter `jo@example.org`, choose **Bookbinding**, then finish as **Jo**, **In person**. In a fresh profile, start one more survey as `lee@example.org` and close it after the first step.

## 3. Match the two exports

Open **S → special functions → form data**. On the `workshopchoice` row select **xls** to download its CSV. Repeat for `workshopformat`. Import both files as UTF-8 with a semicolon delimiter. Keep the first export as the list of starts. Match each `youremail*` in that list to the same column in the second export and bring across `firstname*` and `attendance*`. Keep unmatched starts visible.

| Address from step one | Workshop | First name from step two | Attendance |
| --- | --- | --- | --- |
| alex+workshop@example.org | Map reading | Alex | Online |
| jo@example.org | Bookbinding | Jo | In person |
| lee@example.org | Map reading | — | — |

An empty match shows a start without a corresponding finish. Each step has its own record ID and submission time. If an address occurs twice in either export, or appears only in step two, set those rows aside for review before counting people. A shared mailbox or a corrected address can make a one-to-one match ambiguous. For a survey that permits repeat participation, choose a distinct response identifier for each run.

## If the result differs

| Observation | What to check |
| --- | --- |
| Page two opens with an empty or unexpected address | Check the visible address before submitting. Use the same browser profile, check the fragment is inside each form, or type the address again. |
| Step one is present after a participant closed the survey | Each submit saves immediately. Use the matching step-two row when counting completions. |
| The thank-you page opens after the first question | Check the first page's `thanks` value is `workshopformat` and the second's is `workshopthanks`. |

The same final-step records can provide the recipients for a [personalised serial mail](../serial-mail.md). The [original multipage survey guide](https://yuba.ch/index.php?c=u5emultiquest&l=en) describes the supplied email helpers.
