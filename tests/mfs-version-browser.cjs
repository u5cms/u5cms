const assert = require('node:assert/strict');
const fs = require('node:fs/promises');
const {launchBrowser} = require('./browser-runtime.cjs');

(async () => {
    const origin = process.env.U5_DOCUMENTATION_ORIGIN;
    const browser = await launchBrowser();
    const contexts = [];
    const errors = [];
    async function account(name) {
        const context = await browser.newContext({ignoreHTTPSErrors:true,acceptDownloads:true});
        contexts.push(context);
        await context.route('**/*', route => new URL(route.request().url()).origin === origin ? route.continue() : route.abort());
        context.on('page', page => page.on('pageerror', error => errors.push(error.message)));
        const result = await context.request.post(origin + '/loginsave.php?u=/index.php', {
            form:{u:name,p:process.env.U5CMS_FIXTURE_PASSWORD}
        });
        assert.equal(result.status(),200);
        return context;
    }
    async function ready(page, expected, shared) {
        await page.waitForFunction(({expected, shared}) => {
            const form = document.forms.u5form;
            const path = shared ? '/monofillshared.php' : '/monofill.php';
            return form && form.venue_mandatory.value === expected &&
                /^[0-9]+$/.test(form.firstsaverwins.value) && /^[a-f0-9]{64}$/.test(form.u5cms_formversion?.value || '') &&
                Array.from(document.querySelectorAll('iframe')).some(frame => {
                    try { return frame.contentWindow.location.pathname === path &&
                        frame.contentDocument.querySelector('#u5transportcontainer'); } catch (_) { return false; }
                });
        },{expected,shared});
    }
    async function open(context, name, expected) {
        const page = await context.newPage();
        await page.goto(origin + '/index.php?c=' + name + '&l=en');
        await ready(page,expected,name!=='timepreference');
        const first = await page.locator('[name=u5cms_formversion]').inputValue();
        await page.waitForTimeout(900);
        const later = await page.locator('[name=u5cms_formversion]').inputValue();
        assert.equal(first,later);
        return page;
    }
    async function save(page, value) {
        await page.getByLabel('Preferred venue').fill(value);
        await Promise.all([
            page.waitForEvent('framenavigated',{predicate:frame=>frame===page.mainFrame()}),
            page.getByRole('button',{name:'Save preference'}).click()
        ]);
        await page.waitForLoadState('load');
        await ready(page,value,true);
    }
    async function reject(page, value) {
        await page.getByLabel('Preferred venue').fill(value);
        const waiting = page.waitForEvent('dialog');
        await page.getByRole('button',{name:'Save preference'}).click();
        const dialog = await waiting;
        assert.ok(dialog.message().includes('You cannot save your data'));
        await dialog.dismiss();
        assert.equal(await page.getByLabel('Preferred venue').inputValue(),value);
    }
    async function exportRows(editor, name, expected) {
        const page = await editor.newPage();
        await page.goto(origin+'/u5admin/formdata2.php?n='+name+'&f=&s=1');
        const [download] = await Promise.all([
            page.waitForEvent('download'),
            page.locator('a[href^="formdata2csv.php?"]').click()
        ]);
        assert.equal(await download.failure(),null);
        const csv = await fs.readFile(await download.path(),'utf8');
        for (const value of expected) assert.ok(csv.includes(value), csv);
        await page.close();
    }
    try {
        const bob = await account('bob@example.org');
        const charlie = await account('charlie@example.org');
        const editor = await account('alice@example.org');
        const first = await open(bob,'sharedpreference','');
        const stale = await open(charlie,'sharedpreference','');
        await save(first,'Ada Genève 東京');
        await reject(stale,'Ben draft 🙂');
        await stale.reload();
        await ready(stale,'Ada Genève 東京',true);
        await save(stale,'Ben Zürich 京都');
        await exportRows(editor,'sharedpreference',['Ben Zürich 京都','charlie@example.org']);
        console.log('PASS shared stale tab, preserved draft, refresh, new author and active CSV');
        const callback = await open(bob,'sharedcallback','');
        const old = await open(charlie,'sharedcallback','');
        for (let count=1;count<=2;count++) {
            const before = await callback.locator('[name=u5cms_formversion]').inputValue();
            await callback.getByLabel('Preferred venue').fill('Callback Genève '+count);
            await callback.getByRole('button',{name:'Save preference'}).click();
            await callback.waitForFunction(count=>window.auditSavedCount===count,count);
            const after = await callback.locator('[name=u5cms_formversion]').inputValue();
            assert.notEqual(before,after);
        }
        await reject(old,'Stale callback');
        await exportRows(editor,'sharedcallback',['Callback Genève 2','bob@example.org']);
        console.log('PASS two callback saves without navigation refresh the loaded version');
        const own = await open(bob,'timepreference','');
        const other = await open(charlie,'timepreference','');
        await own.getByLabel('Preferred venue').fill('Personal Ada');
        await Promise.all([
            own.waitForEvent('framenavigated',{predicate:frame=>frame===own.mainFrame()}),
            own.getByRole('button',{name:'Save preference'}).click()
        ]);
        await own.waitForLoadState('load');
        await ready(own,'Personal Ada',false);
        await other.getByLabel('Preferred venue').fill('Personal Ben');
        await Promise.all([
            other.waitForEvent('framenavigated',{predicate:frame=>frame===other.mainFrame()}),
            other.getByRole('button',{name:'Save preference'}).click()
        ]);
        await other.waitForLoadState('load');
        await ready(other,'Personal Ben',false);
        await exportRows(editor,'timepreference',['Personal Ada','Personal Ben','bob@example.org','charlie@example.org']);
        console.log('PASS independently opened personal forms preserve both owners');
        assert.deepEqual(errors,[]);
        console.log('COMPLETE',process.env.U5_AUDIT_REVISION,process.env.U5_TEST_ENGINE);
    } finally {
        for (const context of contexts) await context.close();
        await browser.close();
    }
})().catch(error=>{console.error(error);process.exitCode=1;});
