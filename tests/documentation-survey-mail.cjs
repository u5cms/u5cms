const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const participants = [
    {address: 'alex+workshop@example.org', topic: 'Map reading', name: 'Alex', attendance: 'Online', sentence: 'We will send your joining link.'},
    {address: 'jo@example.org', topic: 'Bookbinding', name: 'Jo', attendance: 'In person', sentence: 'Please meet us at the library.'},
];

async function checkImportedSelection(editor) {
    const importPopup = editor.waitForEvent('popup');
    await editor.getByRole('link', {name: 'import from Excel'}).click();
    const sheet = await importPopup;
    const contacts = Array.from({length: 11}, (_, index) => 'Person ' + (index + 1) + '\tperson' + (index + 1) + '@example.org');
    await sheet.locator('input[name="target"]').fill('trainingcontacts');
    await sheet.locator('select[name="h"]').selectOption({label: '1st row in my data'});
    await sheet.locator('#csv').fill(['name\temail', ...contacts].join('\n'));
    await sheet.getByRole('button', {name: 'start import now'}).click();
    await sheet.waitForURL(url => url.pathname.endsWith('/formdata2.php') && url.searchParams.get('s') === '7');
    assert.equal(await sheet.locator('select[id^="sel"]').count(), 11);
    await sheet.close();

    await editor.reload();
    const mailPopup = editor.waitForEvent('popup');
    await editor.getByRole('row').filter({hasText: 'trainingcontacts'}).getByRole('link', {name: 'mail', exact: true}).click();
    const selection = await mailPopup;
    await selection.getByRole('combobox').filter({has: selection.getByRole('option', {name: /copy.*paste csv.excel import/})}).selectOption('7');
    await selection.getByText(/Serial mail will be sent to all 11 records/).waitFor();
    assert.equal(await selection.locator('select[id^="sel"]').count(), 10);
    await selection.close();
    console.log('PASS spreadsheet source: eleven imported records in status 7, ten displayed in the mail view, eleven selected');
}

async function surveySteps({reader, open, origin}) {
    for (const person of [...participants, {address: 'lee@example.org', topic: 'Map reading'}]) {
        const page = await reader('bob@example.org');
        try {
            await open(page, 'workshopchoice');
            const address = page.getByLabel('Email address');
            assert.equal(await address.inputValue(), '');
            await address.fill(person.address);
            const remembered = page.waitForResponse(response => {
                const url = new URL(response.url());
                return url.pathname.endsWith('/saveyouremail.php') && url.searchParams.get('youremail') === person.address;
            });
            await address.press('Tab');
            assert.equal((await remembered).status(), 200);
            await page.getByLabel('Workshop', {exact: true}).selectOption(person.topic);
            await page.getByRole('button', {name: 'Save and continue'}).click();
            await page.waitForURL(url => url.searchParams.get('c') === 'workshopformat');
            if (!person.name) continue;
            await page.waitForFunction(value => document.getElementById('youremail_mandatory')?.value === value, person.address);
            await page.getByLabel('First name').fill(person.name);
            await page.getByLabel('Attendance').selectOption(person.attendance);
            await page.getByRole('button', {name: 'Finish survey'}).click();
            await page.getByRole('heading', {name: 'Survey complete'}).waitFor();
        } finally {
            await page.context().close();
        }
    }

    const editor = await reader('alice@example.org');
    await editor.goto(origin + '/u5admin/formdata.php');
    const formRow = name => editor.getByRole('row').filter({hasText: name});
    async function exportedRows(name) {
        const href = await formRow(name).getByRole('link', {name: 'xls'}).getAttribute('href');
        const response = await editor.request.get(new URL(href, editor.url()).href);
        assert.equal(response.status(), 200);
        assert.match(response.headers()['content-disposition'], /\.csv/i);
        const [head, ...lines] = (await response.text()).trim().split(/\r?\n/);
        const columns = head.split(';');
        return lines.map(line => Object.fromEntries(line.split(';').map((value, index) => [columns[index], value])));
    }

    const starts = await exportedRows('workshopchoice');
    const finishes = await exportedRows('workshopformat');
    assert.equal(starts.length, 3);
    assert.equal(finishes.length, 2);
    const joined = starts.map(start => {
        const key = start['youremail*'];
        const finish = finishes.find(row => row['youremail*'] === key);
        return [key, start['topic*'], finish?.['firstname*'], finish?.['attendance*']];
    });
    assert.deepEqual(joined.sort(), [
        ['alex+workshop@example.org', 'Map reading', 'Alex', 'Online'],
        ['jo@example.org', 'Bookbinding', 'Jo', 'In person'],
        ['lee@example.org', 'Map reading', undefined, undefined],
    ]);
    console.log('PASS multipage survey: separate browser profiles, remembered plus address, two CSV exports, two matches and one incomplete response');

    await checkImportedSelection(editor);
    const popup = editor.waitForEvent('popup');
    await formRow('workshopformat').getByRole('link', {name: 'mail', exact: true}).click();
    return popup;
}

async function sendSerialMail(mail) {
    const guide = fs.readFileSync(path.join(process.env.U5_DOCUMENTATION_SOURCE, 'docs', 'serial-mail.md'), 'utf8');
    const template = guide.match(/^```text\nFrom: ([^\n]+)\nTo: ([^\n]+)\nSubject: ([^\n]+)\n\n(.+?)\n```$/ms);
    assert.ok(template, 'Expected the copyable mail template in the handbook');
    const [, from, to, subject, message] = template;
    const captured = () => fs.readdirSync(process.env.U5_DOCUMENTATION_MAILBOX).length;

    await mail.getByText(/Serial mail will be sent to all 2 records/).waitFor();
    await mail.locator('#filt').fill('Online');
    await mail.getByRole('button', {name: 'set', exact: true}).click();
    await mail.getByText(/Serial mail will be sent to all 1 records?/).waitFor();
    await mail.getByRole('button', {name: 'unset', exact: true}).click();
    await mail.getByText(/Serial mail will be sent to all 2 records/).waitFor();
    await mail.getByRole('link', {name: 'Send serial mails to the above data selection'}).click();
    await mail.getByText('[youremail*]', {exact: true}).waitFor();
    const list = mail.frameLocator('iframe[name="ml"]');
    const editor = mail.frameLocator('iframe[name="me"]').locator('form[name="form"]');
    const previewArea = mail.frameLocator('iframe[name="mp"]');
    const shown = previewArea.frameLocator('frame[name="mapr_view"]');
    const controls = previewArea.frameLocator('frame[name="mapr_menu"]');

    mail.once('dialog', dialog => dialog.accept(subject));
    await list.getByRole('button', {name: /new mailjob/}).click();
    await editor.locator('[name="mfrom"]').fill(from);
    await editor.locator('[name="mto"]').fill(to);
    await editor.locator('[name="mmessage"]').fill(message);
    assert.equal(await editor.locator('[name="msubject"]').inputValue(), subject);
    const address = shown.locator('#mto');
    await shown.locator('#mmessage').filter({hasText: 'Thank you for registering'}).waitFor();
    const visited = new Set();
    for (let position = 0; position < participants.length; position++) {
        const remaining = participants.filter(person => !visited.has(person.address));
        const displayed = (await address.innerText()).trim();
        const record = remaining.find(entry => entry.address === displayed);
        assert.ok(record, displayed);
        assert.equal(await shown.locator('#mailsubject').innerText(), 'Workshop arrangements for ' + record.name);
        const body = await shown.locator('#mmessage').innerText();
        assert.ok(body.includes('Dear ' + record.name + ',') && body.includes(record.sentence));
        assert.ok(!body.includes(participants.find(entry => entry !== record).sentence));
        visited.add(record.address);
        if (position === 0) {
            const next = participants.find(entry => !visited.has(entry.address));
            await controls.getByRole('button', {name: '>', exact: true}).click();
            await shown.getByText(next.address, {exact: true}).waitFor();
        }
    }
    assert.equal(visited.size, 2);
    await editor.getByRole('button', {name: /save mailjob/}).click();
    await list.getByRole('link', {name: 'test', exact: true}).click();
    await list.getByRole('button', {name: /TESTED, NOTHING SENT/}).waitFor();
    assert.equal((await list.locator('#errors').innerText()).trim(), '');
    for (const {address} of participants) assert.ok((await list.locator('#recipients').innerText()).includes(address));
    assert.equal(captured(), 0);
    await list.getByRole('button', {name: /TESTED, NOTHING SENT/}).click();

    mail.once('dialog', dialog => dialog.accept());
    await list.getByRole('link', {name: 'send', exact: true}).click();
    if (process.env.U5_DOCUMENTATION_MAIL_MODE === 'paced') {
        await list.getByText(/will be sent not at once but step-by-step/).waitFor();
        assert.equal(captured(), 0);
        const cron = new URL('/mailingcron.php', mail.url()).href;
        async function advanceQueue() {
            const response = await mail.request.get(cron);
            assert.equal(response.status(), 200);
            return response.text();
        }
        await advanceQueue();
        assert.equal(captured(), 1);
        const waiting = (await advanceQueue()).match(/next sending possible in (\d+) seconds/);
        if (waiting) {
            assert.equal(captured(), 1);
            await new Promise(resolve => setTimeout(resolve, (Number(waiting[1]) + 1) * 1000));
            await advanceQueue();
        }
        assert.equal(captured(), 2);
        assert.match(await advanceQueue(), /nothing to send/);
        console.log('PASS paced serial mail: queued without delivery, one message per eligible call, empty queue after completion');
    }
    await list.getByRole('button', {name: 'ok', exact: true}).click();
    const popup = mail.waitForEvent('popup');
    await list.getByRole('link', {name: 'info', exact: true}).click();
    const info = await popup;
    for (const {address} of participants) await info.getByText(address, {exact: false}).first().waitFor();
    assert.equal(captured(), 2);
    await info.close();
    await mail.close();
    console.log('PASS serial mail editor: filtered selection, both previews, saved job, dry run, confirmed dispatch and job information');
}

async function exerciseSurveyAndMail(options) {
    const mail = await surveySteps(options);
    await sendSerialMail(mail);
}

module.exports = {exerciseSurveyAndMail};
