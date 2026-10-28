/* End-to-end checks against an actual PHP frontend and MariaDB index. */
const assert = require('node:assert/strict');
const {launchBrowser} = require('./browser-runtime.cjs');
const base = process.argv[2];
const credentials = JSON.parse(process.argv[3]);
let checks = 0;
const equal = (actual, expected, message) => { assert.deepEqual(actual, expected, message); checks++; };
const verify = (condition, message) => { assert.ok(condition, message); checks++; };
const url = (q, language='de', scope='_search') => base + '/index.php?' + new URLSearchParams({c:scope,l:language,qv:'2',q});

(async () => {
    const browser = await launchBrowser();
    const context = await browser.newContext({viewport:{width:1000,height:700},reducedMotion:'reduce'});
    const page = await context.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(String(error)));
    const names = async () => page.locator('.u5-search-result h3 a').evaluateAll(links => links.map(link => new URL(link.href).searchParams.get('c')).sort());
    const search = async (query, language='de') => {
        await page.goto(url(query, language), {waitUntil:'domcontentloaded'});
        equal(await page.locator('#searchlarge').inputValue(), query, 'Search field preserves the literal query');
        return names();
    };
    try {
        equal(await search('"Wort (Wort Wort) Wort"'), ['phrase'], 'Quoted parentheses select the exact phrase');
        equal(await page.locator('.u5-search-excerpt .hitshilite').allTextContents(), ['Wort (Wort Wort) Wort'], 'Excerpt marks the original phrase including parentheses');
        equal(await search('"alpha beta gamma"'), ['ordered'], 'A quoted phrase retains word order');
        equal(await search('alpha beta gamma'), ['ordered','reordered'], 'Unquoted terms use AND independent of word order');
        equal(await search('red blue'), ['andall'], 'All unquoted terms are required when complete matches exist');
        equal(await search('"red blue"'), [], 'Phrase matching does not skip an intervening word');
        verify(await page.locator('.u5-search-results a').filter({hasText:'Ohne Anf'}).count() === 1, 'Relaxing a phrase is an explicit link');
        for (const query of ['C++','100%','foo_bar','a.b','[square]','x+y','a&b']) {
            equal(await search(query), ['punctuation'], 'Literal punctuation is not a wildcard or query operator: ' + query);
            verify((await page.locator('.u5-search-excerpt .hitshilite').allTextContents()).includes(query), 'Punctuation appears intact in its real excerpt');
        }
        equal(await search('&#128512;'), ['punctuation'], 'A literal numeric entity string remains literal');
        for (const query of ['cafe','strasse','oeuvre','\u20ac','\ud83d\ude00','ecole']) {
            equal(await search(query), ['unicode'], 'Unicode, accents and legacy codepages share the same matching: ' + query);
        }
        equal(await search('Suchbegriff'), ['inline'], 'A word split by inline formatting is indexed as one word');
        equal(await search('TitleNeedle ordinary'), ['titleonly'], 'Separate words may match title and body');
        equal(await search('"TitleNeedle ordinary"'), [], 'A phrase cannot cross title/body boundaries');
        equal(await search('TitleNeedle'), ['titleonly'], 'Title-only match remains available');
        verify((await page.locator('.u5-search-excerpt').textContent()).includes('Fundstelle im Seitentitel:'), 'A title match is labelled honestly instead of inventing a body quote');
        equal(await search('EmptyBodyNeedle'), ['emptybody'], 'A title-only resource with an indexed empty body is searchable');
        verify((await page.locator('.u5-search-excerpt').textContent()).includes('Fundstelle im Seitentitel:'), 'An empty body produces an honest title excerpt');
        await search('RealDownloadFixture');
        const downloadHref = await page.locator('.u5-search-result h3 a').getAttribute('href');
        equal(new URL(downloadHref, base).pathname, '/f.php', 'A document result links through the normal download endpoint');
        equal(new URL(downloadHref, base).searchParams.get('f'), 'r/downloadfixture/downloadfixture_de.txt', 'Document link names the existing localized file');
        const downloadResponse = await page.request.get(new URL(downloadHref, base).href);
        equal(await downloadResponse.text(), 'Actual download fixture payload.', 'Following the document result retrieves the actual file');
        equal(await search('AbsentDownloadFixture'), ['missingdownload'], 'A missing document retains its resource link');
        const missingHref = await page.locator('.u5-search-result h3 a').getAttribute('href');
        equal(new URL(missingHref, base).pathname, '/index.php', 'A dotfile fallback is never offered as a download');
        equal(new URL(missingHref, base).searchParams.get('f'), null, 'Missing document does not expose its protection file');
        for (const [language, query] of [['de','deutschmarker'],['fr','francaismarker'],['it','italianomarker'],['rm','rumantschmarker'],['en','englishmarker']]) {
            equal(await search(query, language), ['languages'], 'Correct language slot: ' + language);
            equal(await page.locator('.u5-search-excerpt .hitshilite').allTextContents(), [query], 'Language-specific excerpt matches its indexed field');
        }
        equal(await search('HiddenNeverDisplayed'), [], 'Hidden resources are excluded');
        equal(await search('DeletedNeverDisplayed'), [], 'Deleted resources are excluded');
        equal(await search('CodeNeverDisplayed'), [], 'Code assets are excluded');
        equal(await search('green qzzmissing'), ['andall','andpartial'], 'Partial matches appear only after no complete match exists');
        verify((await page.locator('.u5-search-results').textContent()).includes('Treffer mit einem Teil der Suchbegriffe'), 'Partial matches have an explicit label');
        equal(await search('sunflwer'), [], 'Misspelling does not silently replace the original query');
        const suggestions = await page.locator('.u5-search-results a').evaluateAll(links => links.map(link => new URL(link.href).searchParams.get('q')));
        verify(suggestions.includes('sunflower'), 'A bounded spelling suggestion is offered as a link');
        equal(new URL(page.url()).searchParams.get('q'), 'sunflwer', 'No automatic recursive navigation changes the misspelling');

        equal((await search('PaginateMarker')).length, 5, 'First result page respects the configured page size');
        const firstPageNames = await names();
        const next = await page.locator('.u5-search-pagination [rel=next]').getAttribute('href');
        equal(new URL(next, base).searchParams.get('q'), 'PaginateMarker', 'Pagination carries the query');
        await page.locator('.u5-search-pagination [rel=next]').click();
        equal((await names()).length, 2, 'Second page contains the remaining results');
        equal(new Set([...firstPageNames,...await names()]).size, 7, 'Pagination neither duplicates nor loses results');

        await page.goto(base + '/index.php?c=phrase&l=de', {waitUntil:'domcontentloaded'});
        const original = '"Wort (Wort Wort) Wort"';
        await page.locator('#search_Input').fill(original);
        await Promise.all([page.waitForURL(u => u.searchParams.get('c') === '_search'), page.locator('#search_Input').press('Enter')]);
        equal(new URL(page.url()).searchParams.get('q'), original, 'The actual GET form submits the unchanged phrase');
        equal(new URL(page.url()).searchParams.get('qv'), '2', 'New forms use the explicit literal-query protocol');
        const resultLink = page.locator('.u5-search-result h3 a').first();
        equal(await resultLink.getAttribute('onclick'), null, 'Result links do not need a click-time query mutation');
        const href = await resultLink.getAttribute('href');
        equal(new URL(href, base).searchParams.get('q'), original, 'Copied result href contains the complete original query');
        const target = await context.newPage();
        target.on('pageerror', error => errors.push(String(error)));
        await target.goto(new URL(href, base).href, {waitUntil:'domcontentloaded'});
        await target.waitForFunction(() => window.u5SearchHighlight && window.u5SearchHighlight.ready);
        const marked = await target.evaluate(() => window.u5SearchHighlight.ready);
        equal(marked.count, 1, 'Opening the copied link in a new tab highlights the phrase');
        equal(await target.locator('#phrase-text').textContent(), 'Wort (Wort Wort) Wort', 'Target highlighting preserves original source text');
        equal((await target.locator('#phrase-text [data-u5-search-hit]').allTextContents()).join(''), 'Wort (Wort Wort) Wort', 'Highlight spans cross inline formatting without losing characters');
        await target.waitForFunction(() => window.scrollY > 500);
        verify(await target.evaluate(() => window.scrollY > 500), 'Target automatically scrolls to the real match');
        equal(await target.locator('#search_Input').inputValue(), original, 'Target search field retains quoted parentheses');
        await target.close();

        const canary = '"\\\')|globalThis.__searchInjected=1//"';
        await page.goto(url(canary, 'de', 'phrase'), {waitUntil:'domcontentloaded'});
        await page.waitForFunction(() => window.u5SearchHighlight && window.u5SearchHighlight.ready);
        await page.evaluate(() => window.u5SearchHighlight.ready);
        equal(await page.evaluate(() => window.__searchInjected), undefined, 'Quotes and backslashes remain data in inline JavaScript');
        equal(await page.locator('#search_Input').inputValue(), canary, 'Potential syntax characters are not silently rewritten');

        const authenticated = await browser.newContext({httpCredentials:{username:credentials.username,password:credentials.password}});
        const authPage = await authenticated.newPage();
        await authPage.goto(url('SecretNeedle','de','_searchsi'), {waitUntil:'domcontentloaded'});
        verify((await authPage.locator('.u5-search-excerpt').textContent()).includes('privatepayload'), 'Real browser Basic authentication enables protected search preview');
        await authenticated.close();
        equal(errors, [], 'Search and destination pages have no uncaught JavaScript errors');
        console.log(`Search web browser checks: ${checks} passed.`);
    } finally {
        await context.close();
        await browser.close();
    }
})().catch(error => { console.error(error); process.exitCode=1; });
