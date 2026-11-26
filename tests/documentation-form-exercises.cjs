const assert = require('node:assert/strict');
const path = require('node:path');

async function reviewSurvey({participant, editor, open, origin}) {
    async function suggest(title, slot, image) {
        await open(participant, 'workshoppoll');
        await participant.getByLabel('Workshop title').fill(title);
        await participant.getByLabel('Preferred time').selectOption(slot);
        if (image) {
            const uploader = participant.frameLocator('iframe[name="iuserupload1"]');
            await uploader.locator('input[type="file"]').setInputFiles(image);
            await uploader.getByText(/OK:/).waitFor();
        }
        await participant.getByRole('button', {name: 'Send suggestion'}).click();
        await participant.waitForURL(url => url.searchParams.get('c') === 'pollresults');
    }

    async function changeStatus(from, to) {
        await editor.goto(origin + '/u5admin/formdata2.php?n=workshoppoll&s=' + from);
        const row = editor.getByRole('row').filter({hasText: 'Map reading'});
        const saved = editor.waitForResponse(response => {
            const url = new URL(response.url());
            return url.pathname.endsWith('/statussave.php') && url.searchParams.get('status') === to;
        });
        await row.locator('select[id^="sel"]').selectOption(to);
        assert.equal((await saved).status(), 200);
    }

    await suggest('Map reading', 'Morning', path.join(process.env.U5_DOCUMENTATION_SITE, 'images', 'lupe.png'));
    await suggest('Bookbinding', 'Afternoon');
    const results = participant.getByRole('table', {name: 'Reviewed workshop suggestions'}).locator('tbody tr');
    assert.equal(await results.count(), 0);

    await editor.goto(origin + '/u5admin/formdata.php');
    const formRow = editor.getByRole('row').filter({hasText: 'workshoppoll'});
    const opened = editor.waitForEvent('popup');
    await formRow.getByRole('link', {name: 'chart', exact: true}).click();
    const chartPage = await opened;
    const chart = chartPage.frameLocator('iframe[id^="chrtIfr"]').first();
    for (const option of ['Morning', 'Afternoon']) {
        const optionCell = chart.getByRole('cell', {name: option, exact: true});
        await optionCell.waitFor();
        assert.match(await optionCell.locator('..').innerText(), /1\s*\(50%\)/);
    }

    await chartPage.close();
    await changeStatus('1', '4');
    await open(participant, 'pollresults');
    assert.equal(await results.count(), 1);
    assert.deepEqual((await results.locator('td').allTextContents()).slice(0, 2), ['Map reading', 'Morning']);
    const photo = results.getByRole('img');
    assert.equal(await photo.count(), 1);
    assert.ok(await photo.evaluate(img => img.complete && img.naturalWidth > 0));

    await open(participant, 'polltitles');
    await participant.getByText('Map reading', {exact: true}).waitFor();
    assert.equal(await participant.getByText('Bookbinding', {exact: true}).count(), 0);

    await changeStatus('4', '1');
    await open(participant, 'pollresults');
    assert.equal(await results.count(), 0);
    console.log('PASS survey handbook: real image upload, chart counts, per-record review, publication and withdrawal');
}

async function manageEntries({first, second, open}) {
    function rows(page) {
        return page.getByRole('table', {name: 'My equipment'}).locator('tbody tr');
    }

    async function store(page, action, list) {
        await page.getByRole('button', {name: action, exact: true}).click();
        await page.waitForURL(url => url.searchParams.get('c') === list);
    }

    async function add(page, item, place) {
        await open(page, 'myloans');
        await page.getByRole('link', {name: 'Add an item'}).click();
        await page.getByLabel('Item', {exact: true}).fill(item);
        await page.getByLabel('Collection point').fill(place);
        await store(page, 'Save item', 'myloans');
    }

    await add(first, 'Tripod', 'Studio');
    await add(first, 'Projector', 'Library');
    await add(second, 'Screen', 'Hall');
    assert.deepEqual(await rows(first).locator('td:first-child').allTextContents(), ['Tripod', 'Projector']);
    assert.deepEqual(await rows(second).locator('td:first-child').allTextContents(), ['Screen']);
    const tripod = rows(first).filter({hasText: 'Tripod'});
    const edit = tripod.getByRole('link', {name: 'Edit'});
    const olderCopy = await first.context().newPage();
    await olderCopy.goto(new URL(await edit.getAttribute('href'), first.url()).href);
    await olderCopy.waitForFunction(() => document.forms.u5form.firstsaverwins.value > 0
        && document.forms.u5form.place_mandatory.value === 'Studio');
    await edit.click();
    await first.waitForFunction(() => document.querySelector('[name="place_mandatory"]')?.value === 'Studio');
    // The CMS records its version times in whole seconds.
    await new Promise(resolve => setTimeout(resolve, 1100));
    await first.getByLabel('Collection point').fill('Workshop');
    await store(first, 'Save item', 'myloans');
    assert.deepEqual((await tripod.locator('td').allTextContents()).slice(0, 2), ['Tripod', 'Workshop']);
    assert.equal(await rows(first).count(), 2);

    await olderCopy.getByLabel('Collection point').fill('Other venue');
    const warning = olderCopy.waitForEvent('dialog').then(async dialog => {
        const message = dialog.message();
        await dialog.accept();
        return message;
    });
    await olderCopy.getByRole('button', {name: 'Save item'}).click();
    assert.match(await warning, /newer than/);
    await olderCopy.close();
    await open(first, 'myloans');
    assert.deepEqual((await tripod.locator('td').allTextContents()).slice(0, 2), ['Tripod', 'Workshop']);

    await rows(first).filter({hasText: 'Projector'}).getByRole('link', {name: 'Delete'}).click();
    await first.waitForFunction(() => document.querySelector('[name="item_mandatory"]')?.value === 'Projector');
    await store(first, 'delete', 'myloans');
    assert.deepEqual(await rows(first).locator('td:first-child').allTextContents(), ['Tripod']);
    await open(second, 'myloans');
    assert.deepEqual(await rows(second).locator('td:first-child').allTextContents(), ['Screen']);
    console.log('PASS editable list handbook: two ordinary accounts, separate lists, add, prefilled edit, stale-edit conflict and confirmed deletion');
}

async function revisitPreference({first, second, editor, open, origin}) {
    async function load(page) {
        await open(page, 'timepreference');
        await page.waitForFunction(() => new URL(document.forms.u5form.action).searchParams.has('o'));
    }
    for (const venue of ['Town hall', 'Library']) {
        await load(first);
        assert.equal(await first.getByLabel('Preferred venue').inputValue(), venue === 'Town hall' ? '' : 'Town hall');
        await first.getByLabel('Preferred venue').fill(venue);
        await Promise.all([
            first.waitForEvent('framenavigated', {predicate: frame => frame === first.mainFrame()}),
            first.getByRole('button', {name: 'Save preference'}).click(),
        ]);
    }
    await load(first);
    assert.equal(await first.getByLabel('Preferred venue').inputValue(), 'Library');
    await load(second);
    assert.equal(await second.getByLabel('Preferred venue').inputValue(), '');
    for (const [status, expected] of [['1', 'Library'], ['6', 'Town hall']]) {
        await editor.goto(origin + '/u5admin/formdata2.php?n=timepreference&s=' + status);
        assert.equal(await editor.locator('select[id^="sel"]').count(), 1);
        assert.match(await editor.locator('body').innerText(), new RegExp(expected));
    }
    console.log('PASS single response handbook: one current response, its previous version and a separate account');
}

async function exerciseFormGuides({reader, open, origin}) {
    const first = await reader('bob@example.org');
    const second = await reader('charlie@example.org');
    const editor = await reader('alice@example.org');
    await reviewSurvey({participant: first, editor, open, origin});
    await manageEntries({first, second, open});
    await revisitPreference({first, second, editor, open, origin});
}

module.exports = {exerciseFormGuides};
