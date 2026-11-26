# Send personalised serial mail from form records

[Email delivery](email.md) · [Documentation home](README.md)

## Summary

The **mail** view creates one message per selected form record. Put a column variable in **To**, use the participant's name in the subject and choose a sentence from their attendance preference. Preview two different records, save the job and run **test**. Only **send** starts delivery.

**For** backend editors with **Form data** access and permission to contact the selected recipients. **Version** checked on u5CMS 12.10.4. **You need** a configured sender and mail transport. For a first run, use a test installation and a local mail capture or mailboxes under your control.

## 1. Select the recipients

Open **S → special functions → form data** and choose **mail** beside the form. The [two-page workshop survey](forms/multipage-surveys.md) provides a ready-made dataset. Use its final step, `workshopformat`, to address the people who finished. These are the two example records.

| youremail* | firstname* | attendance* |
| --- | --- | --- |
| alex+workshop@example.org | Alex | Online |
| jo@example.org | Jo | In person |

Check the status selector and any text filter. The **mail** view displays up to ten rows above the editor. The count beside the mailing area covers the whole selection. Check for repeated addresses because two selected rows with the same address can produce two messages.

**Starting with a spreadsheet.** In **Form data**, choose **import from Excel**. Enter a distinct target name, paste the tab-separated cells including the header row and choose **1st row in my data** for the headings. Select **start import now**. The rows appear under **copy&paste csv/excel import** (status 7). Reopen **Form data**, choose **mail** beside that target and select status 7 there as well. With eleven imported rows you will see ten in the table and eleven in the selection count. Use the column names shown under **Available variables** for your own template.

## 2. Compose the job

1. Open **Send serial mails to the above data selection**, choose **new mailjob** in the right-hand column and give it the subject below.
2. Fill the fields in the middle column from the example. Replace the **From** address with a sender accepted by your installation. Leave **Cc** and **Bcc** empty. A fixed copy address receives a copy for each selected record.
3. Paste the three message lines into **Message**. The editor also inserts a variable when you place the cursor in a field and click its name under **Available variables**.

```text
From: Workshop office <workshops@example.org>
To: [youremail*]
Subject: Workshop arrangements for [firstname*]

Dear [firstname*],
[attendance*|Online|We will send your joining link.][attendance*|In person|Please meet us at the library.]
Thank you for registering with the workshop office.
```

Use the headings exactly as displayed, including the star on a mandatory field. `[firstname*]` inserts that row's first name. In `[field|value|text]`, a matching value inserts the text and another value produces an empty string. The adjacent conditions above should produce one sentence per recipient. `[?ID]` is the form record's own ID if you need a reference number.

## 3. Preview, test and send

1. Use the arrows in the left preview to read each example. For Alex, check the **To** address including the plus sign, the subject **Workshop arrangements for Alex** and the joining-link sentence. For Jo, check **jo@example.org**, her own name in the subject and the library sentence.
2. Select **save mailjob** in the middle column, then **test** beside that job on the right. Read every recipient and any errors. The **TESTED, NOTHING SENT** button returns to the list. A corrected job needs another save and test.
3. For a controlled delivery check, confirm that the selection contains only your two test recipients. Choose **send** on the tested job and confirm. Open **info** to see the job's recipients and status. At the receiving end, check that each message contains only its intended address, name and attendance sentence. On a real mailing, confirm the intended selection again immediately before sending.

## When delivery is paced

The administrator controls the cadence in the installation's private `config.php`. With `$serialmailmethod = 0`, the job attempts direct dispatch, subject to the installation's batch limit. A positive value queues the job and sets how many messages one eligible call to `mailingcron.php` processes. The site needs a scheduled caller or the operator must keep the supplied caller page open. `$minimumwaitingbetweenserialmailcroncallinseconds` sets the minimum interval. Use **info** to check progress. Deleting a job from the list leaves an already started queue running.

## If the result differs

| Observation | What to check |
| --- | --- |
| **To** is blank or **test** lists a bad address | Compare the variable with **Available variables**, including `*`. Check that each selected row contains a usable recipient address. Correct the rows or selection and test again. |
| The greeting is correct but the attendance sentence is missing | Compare the stored option value, including case, with the condition. Add a condition for any further choice in your own form. |
| **send** is unavailable | Open the job using **edit**, save its current contents, then run **test**. Review all reported errors before sending. |
| The job is marked sent but a mailbox is empty | Check **info**, the queue if enabled, then the configured transport and receiving mailbox. |

The [delivery guide](email.md) covers SMTP configuration and international addresses. The [original serial mail guide](https://yuba.ch/index.php?c=u5eserialmails&l=en) introduces the editor and variable syntax.
