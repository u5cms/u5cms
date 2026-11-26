# Let contributors manage their own entries

[Documentation home](../README.md) · [Forms and exports](../forms.md)

## Summary

Give each signed-in contributor a list of equipment they can lend. They can add several items, reopen one, correct its details and withdraw it. In personal ownership mode the form checks who owns the record for both edit and delete. The list selects that contributor's active records.

**For** form authors. **Version** checked on u5CMS 12.10.4. **You need** two ordinary reader accounts and two protected pages on a test installation. Ask the administrator to confirm [personal SELF ownership](../permissions.md#personal-ownership-the-default). Domain sharing deliberately gives a different result.

If you are following the two examples featured on yuba.ch, the [survey tutorial](survey-results.md) introduces the review and publication of saved responses. This scenario adds an edit and delete action for the person who owns each record.

## 1. Create the entry form and the management list

Create two page objects named `loanentry` and `myloans`. Set the login restrictions on **both** so Alice and Bob can read them after signing in. A signed-out visitor should be asked to log in. Paste each example into the English field of its page and save. Use the same machine names in other languages.

### `loanentry`

```text
[fo:]
[fi:]
[le:]Equipment to lend[:le]
[h:]
<input type="hidden" name="thanks" value="myloans">
<input type="hidden" name="in2my" value="off">
<input type="hidden" name="em2cu" value="off">
<input type="hidden" name="em2me" value="off">
<input type="hidden" name="firstsaverwins" value="">
<!-- SELF_TOEDITITEMS_LOGINS_SAME_AS_[loanentry] -->
<!-- SELF_TODELETEITEMS_LOGINS_SAME_AS_[loanentry] -->
<label>Item <input name="item_mandatory"></label>
<label>Collection point <input name="place_mandatory"></label>
<button type="submit">Save item</button>
<a href="?c=myloans&amp;l=en">Return to my list</a>
[:h]
[:fi]
[:fo]
```

The two comments belong in the **form source**. They instruct u5CMS to use the named page's login and the configured ownership check for edits and deletions. Use the actual page name inside the square brackets. The empty `firstsaverwins` field requests a conflict check when an existing record is saved. After a successful submission, `thanks` sends the contributor back to their list.

### `myloans`

```text
[h:]
<a href="?c=loanentry&amp;l=en">Add an item</a>
<table>
<caption>My equipment</caption>
<thead><tr><th scope="col">Item</th><th scope="col">Collection point</th><th scope="col">Actions</th></tr></thead>
<tbody>
[item*,place*|loanentry|ALL|SELF|1|<tr><td>|</td><td><a href="e/?n=loanentry&amp;id=$id&amp;l=en">Edit</a> <a href="d/?n=loanentry&amp;id=$id&amp;l=en">Delete</a></td></tr>|</td><td>|id ASC:dat]
</tbody>
</table>
[:h]
```

Each row supplies its own ID to the built-in edit and delete pages. **Add an item** opens the fresh form. The `SELF` selection and status `1` keep the list to that contributor's active entries. The same form can be used many times.

## 2. Try the complete round trip

| Action | Expected result with personal ownership |
| --- | --- |
| Alice adds **Tripod**, collection point **Studio**, then **Projector**, **Library** | Her list has two separate rows. |
| Bob signs in with another browser profile and adds **Screen**, **Hall** | His list shows Screen. Alice still sees her two items. |
| Alice chooses **Edit** beside Tripod | The form opens with Tripod and Studio already filled in. After she changes Studio to **Workshop** and saves, the same row reads **Tripod / Workshop**. |
| Alice chooses **Delete** beside Projector, checks the displayed item and presses the CMS **delete** button | Projector disappears from her active list. Tripod remains. Bob still sees Screen. |

Use the row's **Edit** link to open a saved item. Opening `loanentry` from **Add an item** starts a new entry. To check the list afresh after saving, use its direct address, `index.php?c=myloans&l=en`. With the standard conflict check, opening the same item in two tabs can leave one tab behind. Wait a few seconds after opening both tabs, then save a change in the first and try to save the older copy. The CMS warns that a newer version exists. Reopen the item from the list, review the saved values and apply the intended change there.

## When each person needs just one response

For one revisable response per account, such as a workshop preference, use the other pattern from the original guide (**Scenario 1**). On a protected form put `[mf]` after `[:h]` and before `[:fi]`. Keep the hidden `firstsaverwins` field. Reopening that form loads the signed-in person's eligible response. The default `$mfwhereclause` selects status `1`. Create a separate protected page named `timepreference`. Save a preference, close the page and reopen it from the same account. Change the wording and save it again. The next visit should show the revised preference. The editor can see the earlier value under **former version** in **Form data**. A second account starts with its own blank field.

### `timepreference`

```text
[fo:]
[fi:]
[le:]My workshop preference[:le]
[h:]
<input type="hidden" name="thanks" value="timepreference">
<input type="hidden" name="in2my" value="off">
<input type="hidden" name="em2cu" value="off">
<input type="hidden" name="em2me" value="off">
<input type="hidden" name="firstsaverwins" value="">
<label>Preferred venue <input name="venue_mandatory"></label>
<button type="submit">Save preference</button>
[:h]
[mf][:fi]
[:fo]
```

## If the result differs

| Observation | Check |
| --- | --- |
| The list shows a colleague's items | Check the DAT filter and the installation's personal or domain [ownership policy](../permissions.md). |
| The edit form is empty | Follow **Edit** on a saved row and let the stored fields load. Check the form name, row ID and machine field names. |
| A reader can open the list but cannot edit their own entry | Check the login on the form itself and the spelling of the two steering comments, including the target page name. |

The [original Scenario 2](https://yuba.ch/index.php?c=u5eformedit&l=en) also describes a shared catalogue and optional interface refinements. The [DAT reference](../dat/parameters.md) covers the record selection used here.
