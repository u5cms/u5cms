const assert = require('node:assert/strict');
const {launchBrowser} = require('./browser-runtime.cjs');

const origin = process.env.U5_DOCUMENTATION_ORIGIN;
const historical = process.env.U5_AUDIT_LINE === 'origin';
const translations = {
    en: {text: 'Find Genève and Seidenatlas here.', hits: ['Genève', 'Seidenatlas']},
    de: {text: 'Nur Seidenatlas ist geblieben.', hits: ['Seidenatlas']}
};

async function visit(page, url, language, input, stage) {
    const response = await page.goto(url);
    assert.equal(response.status(), 200);
    const target = page.locator('#audit-target');
    assert.equal(await target.textContent(), translations[language].text);
    assert.equal(await page.locator('html').getAttribute('lang'), language);
    assert.equal(await page.locator('#search_Input').inputValue(), input);
    await page.waitForFunction(() => {
        const rect = document.querySelector('#audit-target').getBoundingClientRect();
        return window.scrollY > 400 && rect.top >= 0 && rect.bottom <= window.innerHeight;
    }, undefined, {timeout: 8000});
    const marks = await target.locator('.hitshilite,[data-u5-search-hit]').allTextContents();
    assert.deepEqual(marks, translations[language].hits);
    const geometry = await target.evaluate(p => [scrollY,p.getBoundingClientRect().top,p.getBoundingClientRect().bottom,innerHeight]);
    assert.ok(geometry[0] > 400 && geometry[1] >= 0 && geometry[2] <= geometry[3]);
    console.log('VISIT', JSON.stringify({language, stage, url, marks, geometry}));
}

async function next(page, language, first) {
    const links = page.locator('link[rel="alternate"][hreflang]');
    if (historical) {
        assert.equal(await links.count(), 0);
        return first.href.replace('l=en', 'l=' + language);
    }
    const link = links.and(page.locator('[hreflang="' + language + '"]'));
    assert.equal(await link.count(), 1);
    const href = new URL(await link.getAttribute('href'), page.url());
    assert.equal(href.origin, origin);
    for (const name of ['q', 'view', 'c']) {
        assert.deepEqual(href.searchParams.getAll(name), first.searchParams.getAll(name));
    }
    assert.deepEqual(href.searchParams.getAll('l'), [language]);
    return href.href;
}

(async () => {
    const browser = await launchBrowser();
    try {
        for (const width of [640, 1100]) {
            const context = await browser.newContext({viewport:{width,height:760},ignoreHTTPSErrors:true});
            await context.route('**/*', route => new URL(route.request().url()).origin === origin ? route.continue() : route.abort());
            for (const [encoded, input] of [
                ['Seidenatlas,Gen%u00E8ve','Seidenatlas Genève'],
                ['Gen%C3%A8ve,Seidenatlas','Genève Seidenatlas']
            ]) {
                const page = await context.newPage();
                const errors = [];
                page.on('pageerror', error => errors.push(error.message));
                const first = new URL('/index.php?c=auditvenue&l=en&q=' + encoded + '&view=Gen%C3%A8ve', origin);
                await visit(page, first.href, 'en', input, 'saved');
                await visit(page, await next(page, 'de', first), 'de', input, historical ? 'direct-control' : 'alternate');
                await visit(page, await next(page, 'en', first), 'en', input, historical ? 'direct-return' : 'return-alternate');
                assert.deepEqual(errors, []);
                console.log('ROUNDTRIP', JSON.stringify({line:process.env.U5_AUDIT_LINE,engine:process.env.U5_TEST_ENGINE,width,encoded,historical}));
                await page.close();
            }
            await context.close();
        }
    } finally {
        await browser.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
