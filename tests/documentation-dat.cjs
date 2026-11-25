const assert = require('node:assert/strict');
const {launchBrowser} = require('./browser-runtime.cjs');

async function main() {
    const origin = process.env.U5_DOCUMENTATION_ORIGIN;
    const browser = await launchBrowser();

    async function reader(account) {
        const context = await browser.newContext({ignoreHTTPSErrors: true});
        await context.route('**/*', route => {
            const url = new URL(route.request().url());
            return url.origin === origin ? route.continue() : route.abort();
        });
        if (account) {
            const login = await context.request.post(origin + '/loginsave.php?u=/index.php', {
                form: {u: account, p: process.env.U5CMS_FIXTURE_PASSWORD},
            });
            assert.equal(login.status(), 200);
        }
        const page = await context.newPage();
        page.on('dialog', dialog => dialog.dismiss());
        return page;
    }

    async function open(page, object) {
        await page.goto(origin + '/index.php?c=' + object + '&l=en');
    }

    async function addCourse(page, title, town) {
        await open(page, 'courseentry');
        await page.getByLabel('Course title').fill(title);
        await page.getByLabel('Town').fill(town);
        await page.getByRole('button', {name: 'Add course'}).click();
        await page.getByRole('link', {name: title + ' / ' + town, exact: true}).waitFor();
    }

    function links(page, target) {
        return page.locator('li a[href*="c=' + target + '&"]');
    }

    try {
        const alice = await reader('alice@example.org');
        const bob = await reader('bob@example.org');
        await addCourse(alice, 'Map reading', 'Bern');
        await addCourse(bob, 'Bookbinding', 'Biel');
        const shared = links(bob, 'coursedetail');
        assert.deepEqual(await shared.allTextContents(), ['Map reading / Bern', 'Bookbinding / Biel']);
        await shared.last().click();
        assert.match(new URL(bob.url()).searchParams.get('id'), /^[1-9][0-9]*$/);
        await bob.getByText('Bookbinding in Biel', {exact: true}).waitFor();
        assert.equal(await bob.getByText('Map reading in Bern', {exact: true}).count(), 0);

        await open(alice, 'mycourses');
        const personal = links(alice, 'mycourse');
        assert.deepEqual(await personal.allTextContents(), ['Map reading / Bern']);
        await personal.click();
        await alice.getByText('Map reading in Bern', {exact: true}).waitFor();
        const aliceDetail = alice.url();

        await open(bob, 'mycourses');
        assert.deepEqual(await links(bob, 'mycourse').allTextContents(), ['Bookbinding / Biel']);
        await links(bob, 'mycourse').click();
        await bob.getByText('Bookbinding in Biel', {exact: true}).waitFor();
        await bob.goto(aliceDetail);
        assert.doesNotMatch(await bob.locator('body').innerText(), /Map reading|Bookbinding/);

        await open(alice, 'datcheck');
        assert.equal(await alice.locator('#latest-course').innerText(), 'Bookbinding');
        assert.equal(await alice.locator('#local-label').innerText(), 'Local course');

        await open(alice, 'coursedetail');
        await alice.getByText(/Map reading in Bern|Bookbinding in Biel/).first().waitFor();

        const guest = await reader();
        await guest.goto(aliceDetail);
        await guest.waitForURL(url => url.searchParams.get('c') === '_login');
        assert.doesNotMatch(await guest.locator('body').innerText(), /Map reading|Bookbinding/);
        console.log('PASS DAT handbook: two real submissions, ordered list, direct details, both personal views, copied link, ordering and limit, field substitution, missing ID and signed-out access');
    } finally {
        await browser.close();
    }
}

main().catch(error => { console.error(error); process.exitCode = 1; });
