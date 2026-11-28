const assert = require('node:assert/strict');
const fs = require('node:fs/promises');
const {launchBrowser} = require('./browser-runtime.cjs');

async function main() {
    const origin = process.env.U5_DOCUMENTATION_ORIGIN;
    const browser = await launchBrowser();
    async function account(name) {
        const context = await browser.newContext({ignoreHTTPSErrors: true, acceptDownloads: true});
        context.setDefaultTimeout(20000);
        context.setDefaultNavigationTimeout(30000);
        context.on('page', page => {
            page.on('dialog', async dialog => {
                console.log('Dialog', dialog.type(), dialog.message().slice(0, 240));
                await dialog.dismiss();
            });
            page.on('response', response => {
                const url = new URL(response.url());
                if (['/formsave.php', '/formsav.php', '/fv.php', '/monofill.php', '/monofillshared.php', '/u5admin/formdata2csv.php'].includes(url.pathname))
                    console.log('HTTP', response.status(), url.pathname, url.searchParams.get('n') || '');
            });
            page.on('pageerror', error => console.log('Page error', error.message));
        });
        await context.route('**/*', route => new URL(route.request().url()).origin === origin ? route.continue() : route.abort());
        const login = await context.request.post(origin + '/loginsave.php?u=/index.php', {
            form: {u: name, p: process.env.U5CMS_FIXTURE_PASSWORD},
        });
        assert.equal(login.status(), 200);
        return context;
    }
    try {
        const bob = await account('bob@example.org');
        const charlie = await account('charlie@example.org');
        const editor = await (await account('alice@example.org')).newPage();
        for (const shared of [false, true]) {
            const name = shared ? 'sharedpreference' : 'timepreference';
            const framePath = shared ? '/monofillshared.php' : '/monofill.php';
            async function open(context, expected) {
                console.log('Opening', name, JSON.stringify(expected));
                const page = await context.newPage();
                await page.goto(origin + '/index.php?c=' + name + '&l=en');
                const frame = page.frames().find(frame => new URL(frame.url()).pathname === framePath);
                assert.ok(frame, 'Expected the shipped ' + framePath + ' frame on ' + name);
                await frame.locator('#u5transportcontainer').waitFor({state: 'attached'});
                if (!shared) await page.waitForFunction(() => new URL(document.forms.u5form.action).searchParams.has('o'));
                const field = page.getByLabel('Preferred venue');
                assert.equal(await field.inputValue(), expected);
                return page;
            }
            async function save(context, before, after) {
                const page = await open(context, before);
                await page.getByLabel('Preferred venue').fill(after);
                console.log('Submitting', name, JSON.stringify(after));
                await Promise.all([
                    page.waitForEvent('framenavigated', {predicate: frame => frame === page.mainFrame()}),
                    page.getByRole('button', {name: 'Save preference'}).click(),
                ]);
                await page.close();
            }
            async function csv(status, expected) {
                console.log('Downloading', name, status);
                await editor.goto(origin + '/u5admin/formdata2.php?n=' + name + '&f=&s=' + status);
                const link = editor.locator('a[href^="formdata2csv.php?"]');
                const [file] = await Promise.all([editor.waitForEvent('download'), link.click()]);
                assert.equal(await file.failure(), null);
                const raw = await fs.readFile(await file.path(), 'utf8');
                const lines = raw.replace(/^\uFEFF/, '').trimEnd().split(/\r?\n/);
                const header = lines.shift().split(';');
                const venue = header.indexOf('venue*');
                const author = header.indexOf('Authuser');
                assert.ok(venue >= 0 && author >= 0, JSON.stringify(header));
                const actual = lines.map(line => {
                    const cells = line.split(';');
                    assert.equal(cells[0], String(status));
                    return [cells[author], cells[venue]];
                });
                assert.deepEqual(actual.sort(), expected.sort());
            }
            const first = 'Genève – 東京';
            const other = 'München 🙂';
            const last = 'Zürich – 京都 🙂';
            await save(bob, '', first);
            const abandoned = await open(bob, first);
            await abandoned.getByLabel('Preferred venue').fill('Unsubmitted change');
            await abandoned.close();
            await save(charlie, shared ? first : '', other);
            await save(bob, shared ? other : first, last);
            await (await open(bob, last)).close();
            await (await open(charlie, shared ? last : other)).close();
            const bobRow = value => ['bob@example.org', value];
            const charlieRow = value => ['charlie@example.org', value];
            await csv(1, shared ? [bobRow(last)] : [bobRow(last), charlieRow(other)]);
            await csv(6, shared ? [bobRow(first), charlieRow(other)] : [bobRow(first)]);
            console.log('PASS', process.env.U5_AUDIT_REVISION, process.env.U5_TEST_ENGINE, name,
                'three submissions, one abandoned change, both readers and both CSV downloads');
        }
    } finally {
        await browser.close();
    }
}
main().catch(error => {console.error(error); process.exitCode = 1;});
