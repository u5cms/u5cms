# Display form records on a page with `:dat`

[Documentation home](README.md) · [Parameter reference](dat/parameters.md)

## Summary

Use `:dat` in a page's source to display selected fields from saved form records. This exercise builds a shared course directory, links each course to its details and gives each contributor a view of their own courses. Choose the pages' readers first. A list and its detail page each select records independently.

**Audience:** form authors and content editors. **Version:** worked example checked on stable 12.10.3 and 12.10.4, with personal SELF scope. **Scope:** a form and its display pages on one installation.

## Set up the exercise

1. Use a test site and two accounts, Alice and Bob, that can sign in as readers. Keep the default personal SELF scope for this exercise. An administrator can check the site's [ownership settings](permissions.md#personal-ownership-the-default).
2. Create five page objects named `courseentry`, `courses`, `coursedetail`, `mycourses` and `mycourse`. Set their login restrictions so both accounts can open them. Check in a signed-out browser that the pages require login.
3. Paste each example below into the English field of the page with the matching name. Save each page. The example links keep the visitor in English and work when the CMS is installed in a subdirectory.

## Enter and display two courses

### `courseentry`

```text
[fo:]
[fi:]
[le:]Add a course[:le]
[h:]
<input type="hidden" name="thanks" value="courses">
<input type="hidden" name="in2my" value="off">
<input type="hidden" name="em2cu" value="off">
<input type="hidden" name="em2me" value="off">
<label>Course title <input name="course_mandatory"></label>
<label>Town <input name="town_mandatory"></label>
<button type="submit">Add course</button>
[:h]
[:fi]
[:fo]
```

The two field names end in `_mandatory`. DAT accepts `course*` and `town*` as their short forms. The form opens the shared list after submission. Its three mail settings keep the exercise local. See [Forms and exports](forms.md) for the submission workflow.

### `courses`

```text
[h:]
<ul>
[course*,town*|courseentry|ALL||1|<li><a href="?c=coursedetail&amp;l=en&amp;id=$id">|</a></li>| / |id ASC:dat]
</ul>
[:h]
```

The command reads both fields from `courseentry`, selects all records with status `1` (new) and orders them by increasing record ID. Each record becomes one linked list item. The opening and closing list tags sit outside the command because they appear once. u5CMS fills in `$id` separately for each link.

### `coursedetail`

```text
[h:]
[course*,town*|courseentry|?||1|<p><strong>|</p>|</strong> in :dat]
[:h]
```

Here `?` in the third position takes the record ID from the URL. Open details by following a generated link with a positive ID. On this renderer, opening the page with no ID can show the first matching row. A detail-page address alone therefore makes a poor menu link.

1. In one browser profile, sign in as Alice, open `courseentry` and submit **Map reading** in **Bern**.
2. In a second profile, sign in as Bob and submit **Bookbinding** in **Biel**.
3. Open `courses` in either profile. Expect **Map reading / Bern** followed by **Bookbinding / Biel**. Open the second link. Its address contains the second record's ID and the detail reads **Bookbinding in Biel**.

## Show each contributor their own courses

### `mycourses`

```text
[h:]
<ul>
[course*,town*|courseentry|ALL|SELF|1|<li><a href="?c=mycourse&amp;l=en&amp;id=$id">|</a></li>| / |id ASC:dat]
</ul>
[:h]
```

### `mycourse`

```text
[h:]
[course*,town*|courseentry|?|SELF|1|<p><strong>|</p>|</strong> in :dat]
[:h]
```

`SELF` selects records using the owner stored with the submission and the site's ownership policy. Keep it on both personal pages. The detail page handles a new request, including when someone opens a saved link.

| Check in the two profiles | Expected with personal SELF scope |
| --- | --- |
| Alice opens `mycourses` and follows her link | One course, Map reading in Bern. |
| Bob opens `mycourses` and follows his link | One course, Bookbinding in Biel. |
| Bob opens the personal detail URL Alice copied from her profile | The page displays no course fields for Alice's record. |
| Either account opens the shared `courses` page | Both courses appear. |

## If a row or a field is missing

| What you see | What to check |
| --- | --- |
| An empty list | Open **Form data** for `courseentry`. Check the source object's exact name and the record's status. The examples select status `1`. |
| A row with a missing title or town | Match the input's `name`, including its case and mandatory suffix. The visible label can change independently. |
| The shared list works but the personal list is empty | Check which account was signed in at submission and which account is reading. Confirm the site's SELF scope. |
| Other people in the same organisation see the same personal records | Check whether the administrator enabled domain ownership or a central allowance. See [Accounts and SELF permissions](permissions.md). |

For sorting, uploads, line breaks, conditional output and the meaning of each position, use the [DAT parameter reference](dat/parameters.md). The [original DAT manual](https://yuba.ch/dat) contains additional tutorials.
