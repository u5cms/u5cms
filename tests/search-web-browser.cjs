/* End-to-end checks against an actual PHP frontend and MariaDB index. */
const assert = require('node:assert/strict');
const {launchBrowser} = require('./browser-runtime.cjs');
const base = process.argv[2];
const credentials = JSON.parse(process.argv[3]);
let checks = 0;
const equal = (actual, expected, message) => { assert.deepEqual(actual, expected, message); checks++; };
const verify = (condition, message) => { assert.ok(condition, message); checks++; };
const url = (q, language='de', scope='_search') => base + '/index.php?' + new URLSearchParams({c:scope,l:language,qv:'2',q});
const nestedTypoQuery = 'Adressen "Mueller (Bern Rathuas) Hilfe & Beratung"';
const nestedCorrectedQuery = 'Adressen "M\u00fcller (Bern Rathaus) Hilfe & Beratung"';
const offers = activePage => activePage.locator('.u5-search-suggestions a').evaluateAll(links => links.map(link => ({query:new URL(link.href).searchParams.get('q'),language:new URL(link.href).searchParams.get('l'),text:link.textContent,href:link.href})));

async function checkRevealedTarget(target, label) {
    await target.waitForFunction(() => window.u5SearchHighlight && window.u5SearchHighlight.ready);
    equal((await target.evaluate(() => window.u5SearchHighlight.ready)).count, 2, label + ': the word and intact quoted phrase are highlighted');
    equal(await target.locator('#corrected-target').textContent(), 'Adressen M\u00fcller (Bern Rathaus) Hilfe & Beratung', label + ': source accents, parentheses and ampersand remain intact');
    equal((await target.locator('#corrected-target [data-u5-search-hit]').allTextContents()).join(''), 'AdressenM\u00fcller (Bern Rathaus) Hilfe & Beratung', label + ': marking crosses inline elements without replacing the source');
    await target.waitForFunction(() => {
        const hit = document.querySelector('#corrected-target [data-u5-search-hit]');
        if (!hit || !document.getElementById('suggestion-details').open) return false;
        const rect = hit.getBoundingClientRect();
        return window.scrollY > 500 && rect.top >= 8 && rect.bottom < window.innerHeight;
    });
    equal(await target.locator('#suggestion-details').evaluate(node => node.open), true, label + ': native details opens');
    for (const level of ['outer','inner']) {
        equal(await target.locator('#suggestion-' + level).evaluate(node => getComputedStyle(node).display), 'block', label + ': ' + level + ' showblock opens');
        equal(await target.locator('#suggestion-' + level + '-toggle').getAttribute('aria-expanded'), 'true', label + ': ' + level + ' control is synchronized');
        verify(await target.locator('#suggestion-' + level).evaluate(node => node.scrollTop > 200), label + ': ' + level + ' scroll container reveals its hit');
        verify(await target.locator('#suggestion-' + level).evaluate(node => {
            const panel = node.getBoundingClientRect();
            const hit = node.querySelector('[data-u5-search-hit]').getBoundingClientRect();
            const top = panel.top + node.clientTop, left = panel.left + node.clientLeft;
            return hit.top >= top - 1 && hit.bottom <= top + node.clientHeight + 1 && hit.left >= left - 1 && hit.right <= left + node.clientWidth + 1;
        }), label + ': the hit lies inside the visible ' + level + ' panel, not merely inside the viewport');
    }
    equal(await target.locator('#unrelated-details').evaluate(node => node.open), false, label + ': unrelated container remains closed');
    equal(await target.locator('#search_Input').inputValue(), nestedCorrectedQuery, label + ': destination retains the corrected quoted query');
}

async function checkPresentation(browser) {
    const screenshotDirectory = process.env.U5_SEARCH_SCREENSHOTS;
    if (screenshotDirectory) require('node:fs').mkdirSync(screenshotDirectory, {recursive:true});
    for (const width of [1440, 320]) {
        const context = await browser.newContext({viewport:{width,height:900},screen:{width,height:900},reducedMotion:'reduce'});
        await context.route('**/*', route => new URL(route.request().url()).origin === new URL(base).origin ? route.continue() : route.abort());
        const page = await context.newPage();
        const errors = [];
        page.on('pageerror', error => errors.push(String(error)));
        const open = async query => {
            await page.goto(url(query), {waitUntil:'networkidle'});
            if (width === 320) await page.waitForSelector('#contentinner #frm_Search2');
        };
        const fits = async message => {
            verify(await page.locator('#frm_Search2, .u5-search-results').evaluateAll(nodes => nodes.every(node => {
                const rect = node.getBoundingClientRect();
                return node.scrollWidth <= node.clientWidth + 1 && rect.width > 0 && (window.innerWidth > 420 || rect.left >= 0 && rect.right <= window.innerWidth + 1);
            })), message);
        };
        try {
            await open('"Wort (Wort Wort) Wort"');
            equal(await page.locator('link[href^="search-results.css?"]').count(), 1, 'Result stylesheet loads once with a content version');
            verify(await page.locator('#frm_Search2.u5-search-form').isVisible(), 'Search form is visible in the real default theme at ' + width);
            const excerptStyle = await page.locator('.u5-search-excerpt').evaluate(node => {
                const style = getComputedStyle(node);
                return {tag:node.tagName,border:parseFloat(style.borderInlineStartWidth),line:parseFloat(style.lineHeight),font:parseFloat(style.fontSize)};
            });
            equal(excerptStyle.tag, 'P', 'Excerpt remains a paragraph without inherited blockquote quotation ornaments');
            verify(excerptStyle.border > 0 && excerptStyle.line >= excerptStyle.font * 1.3, 'Excerpt has a visible boundary and readable line spacing');
            equal(await page.locator('.u5-search-excerpt .hitshilite').allTextContents(), ['Wort (Wort Wort) Wort'], 'Default-theme excerpt retains the complete literal phrase');
            await fits('Result and form fit their default-theme container at ' + width);
            if (screenshotDirectory) await page.screenshot({path:require('node:path').join(screenshotDirectory, 'search-phrase-' + width + '.png'),fullPage:true});

            const probeStable = await page.evaluate(() => {
                const probe = document.createElement('aside');
                probe.innerHTML = '<article><h3>Unrelated heading</h3><p>Unrelated paragraph <a href="#probe">link</a></p></article>';
                document.body.append(probe);
                const properties = ['color','backgroundColor','fontSize','lineHeight','marginTop','marginBottom','paddingLeft','borderLeftWidth','display'];
                const styles = () => Array.from(probe.querySelectorAll('*'), node => properties.map(property => getComputedStyle(node)[property]));
                const before = styles();
                const sheet = document.querySelector('link[href^="search-results.css?"]').sheet;
                sheet.disabled = true;
                const after = styles();
                sheet.disabled = false;
                probe.remove();
                return JSON.stringify(before) === JSON.stringify(after);
            });
            verify(probeStable, 'Search stylesheet leaves unrelated headings, paragraphs and links unchanged');

            await open('"' + 'LongLiteral'.repeat(10) + ' (quoted phrase)"');
            equal(await page.locator('.u5-search-result').count(), 1, 'Long literal phrase is searchable in the real layout');
            await fits('Long unbroken query and excerpt wrap without overflowing at ' + width);
            if (width === 320) {
                const controls = await page.locator('#searchlarge, #frm_Search2 input[type="submit"]').evaluateAll(nodes => nodes.map(node => node.getBoundingClientRect().toJSON()));
                verify(controls[1].top >= controls[0].bottom, 'Mobile search button wraps below the input');
            }
            if (screenshotDirectory) await page.screenshot({path:require('node:path').join(screenshotDirectory, 'search-long-' + width + '.png'),fullPage:true});

            await open('');
            equal(await page.locator('.u5-search-result, .u5-search-message[role="alert"]').count(), 0, 'An empty query displays the form without a misleading error');
            await fits('Empty search form fits its container');
            await open('qzzunfindable');
            verify(await page.locator('.u5-search-summary').isVisible(), 'A no-result heading is visible');
            await fits('No-result state fits its container');
            await open('green qzzmissing');
            verify(await page.locator('.u5-search-subtitle, .u5-search-message').count() === 2, 'Partial results have a heading and explanation');
            verify(await page.locator('.u5-search-match-count').evaluateAll(nodes => nodes.length > 0 && nodes.every(node => node.textContent.startsWith('1/2'))), 'Each partial result states the matched-term count');
            await fits('Partial-result state fits its container');
            await open('muler');
            verify(await page.locator('.u5-search-suggestions').isVisible(), 'Spelling suggestions have a visible separate presentation');
            equal(await page.locator('.u5-search-suggestions a').first().textContent(), 'M\u00fcller', 'Suggestion presentation retains the source accent and case');
            await fits('Suggestion state fits its container');
            if (screenshotDirectory) await page.screenshot({path:require('node:path').join(screenshotDirectory, 'search-suggestion-' + width + '.png'),fullPage:true});
            await open(nestedTypoQuery);
            verify((await offers(page)).some(offer => offer.query === nestedCorrectedQuery), 'A long mixed phrase correction is visible in the default theme');
            await fits('A multiword quoted suggestion wraps within the default theme at ' + width);
            if (screenshotDirectory) await page.screenshot({path:require('node:path').join(screenshotDirectory, 'search-multiword-suggestion-' + width + '.png'),fullPage:true});
            await page.goto(url(nestedCorrectedQuery), {waitUntil:'networkidle'});
            const correctedHref = await page.locator('.u5-search-result h3 a').getAttribute('href');
            await page.goto(new URL(correctedHref, base).href, {waitUntil:'networkidle'});
            await checkRevealedTarget(page, 'Default theme at ' + width);
            if (screenshotDirectory) await page.screenshot({path:require('node:path').join(screenshotDirectory, 'search-corrected-target-' + width + '.png')});
            await open('PaginateMarker');
            verify(await page.locator('.u5-search-pagination [rel="next"]').isVisible(), 'Pagination stays visible in the default theme');
            await fits('Pagination state fits its container');
            equal(errors, [], 'Default desktop/mobile layout has no uncaught JavaScript exceptions');
        } finally {
            await context.close();
        }
    }
}

(async () => {
    const browser = await launchBrowser();
    if (process.argv[4] === 'presentation') {
        try {
            await checkPresentation(browser);
            console.log(`Search default-theme presentation checks: ${checks} passed.`);
        } finally {
            await browser.close();
        }
        return;
    }
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
        const destination = await context.newPage();
        destination.on('pageerror', error => errors.push(String(error)));
        for (const [query, resource, original] of [
            ['C++','punctuation','C++'], ['100%','punctuation','100%'], ['foo_bar','punctuation','foo_bar'],
            ['[square]','punctuation','[square]'], ['a&b','punctuation','a&b'],
            ['cafe','unicode','Caf\u00e9'], ['strasse','unicode','Stra\u00dfe'],
            ['oeuvre','unicode','\u0152uvre'], ['ecole','unicode','e\u0301cole'], ['\ud83d\ude00','unicode','\ud83d\ude00'],
        ]) {
            await destination.goto(url(query,'de',resource), {waitUntil:'domcontentloaded'});
            await destination.waitForFunction(() => window.u5SearchHighlight && window.u5SearchHighlight.ready);
            equal((await destination.evaluate(() => window.u5SearchHighlight.ready)).count, 1, 'Real target page matches the literal/normalized query: ' + query);
            equal(await destination.locator('#content [data-u5-search-hit]').allTextContents(), [original], 'Real target highlights original characters: ' + original);
        }
        await destination.close();
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
        for (const [query, suggestion] of [['Bren', 'Bern'], ['muler', 'M\u00fcller']]) {
            equal(await search(query), [], 'A misspelling remains an explicit no-result query: ' + query);
            const suggestionLinks = await page.locator('.u5-search-results a').evaluateAll(links => links.map(link => ({query: new URL(link.href).searchParams.get('q'), text: link.textContent})));
            verify(suggestionLinks.some(link => link.query === suggestion && link.text.trim() === suggestion), 'Suggestion preserves the source spelling: ' + suggestion);
        }

        const proposedQueries = new Set();
        for (const [query, expected] of [
            ['Bern Rathuas','Bern Rathaus'], ['Bren Rathuas','Bern Rathaus'],
            ['"Bern Rathuas"','"Bern Rathaus"'], ['Mueller','M\u00fcller'],
            ['Mueller Reiseplanugn','M\u00fcller Reiseplanung'], [nestedTypoQuery,nestedCorrectedQuery],
        ]) {
            const rows = await search(query);
            const offered = await offers(page);
            verify(offered.some(offer => offer.query === expected && offer.text === expected), 'A complete correction retains phrase and source spelling: ' + query);
            verify(offered.length <= 3, 'The number of whole-query suggestions remains bounded');
            equal(new URL(page.url()).searchParams.get('q'), query, 'The unaccepted correction does not change the current URL');
            if (query === 'Bern Rathuas') {
                verify(rows.includes('suggestionbern') && rows.includes('suggestionbernrathaus'), 'Correction is offered alongside the original partial Bern matches');
                verify(await page.locator('.u5-search-subtitle').isVisible(), 'Partial matches remain labelled while a better complete query is offered');
            }
            for (const offer of offered) proposedQueries.add(offer.query);
        }
        for (const corrected of proposedQueries) {
            verify((await search(corrected)).length > 0, 'An offered correction leads to an actual indexed result: ' + corrected);
            equal(await page.locator('.u5-search-subtitle').count(), 0, 'An offered correction satisfies the complete query, not only part of it');
        }
        for (const language of ['de','fr','it','rm','en']) {
            await search('"Bern Rathuas"', language);
            verify((await offers(page)).some(offer => offer.query === '"Bern Rathaus"' && offer.language === language), 'Quoted correction preserves the selected language: ' + language);
        }
        for (const [query, fabricated] of [['Harbor Gazebo','Harbour Gazebo'], ['"Harbor Gazebo"','"Harbour Gazebo"'], ['"Lrahc Maple"','"Larch Maple"']]) {
            await search(query);
            verify(!(await offers(page)).some(offer => offer.query === fabricated), 'Suggestions cannot combine separate pages or title/body into a nonexistent match: ' + query);
        }
        await search('SecretNeedle privatepaylod');
        verify(!(await offers(page)).some(offer => offer.query.includes('privatepayload')), 'A partial protected match cannot reveal private words through a multiword suggestion');
        verify(!(await page.locator('.u5-search-results').textContent()).includes('privatepayload'), 'Protected correction vocabulary is absent from the result output');

        await search(nestedTypoQuery);
        const journeyOffers = await offers(page);
        const correctionIndex = journeyOffers.findIndex(offer => offer.query === nestedCorrectedQuery);
        verify(correctionIndex >= 0, 'The mixed quoted correction is selectable');
        await Promise.all([
            page.waitForURL(current => current.searchParams.get('q') === nestedCorrectedQuery),
            page.locator('.u5-search-suggestions a').nth(correctionIndex).click(),
        ]);
        equal(await page.locator('#searchlarge').inputValue(), nestedCorrectedQuery, 'Selecting a suggestion explicitly changes the search field to the complete corrected query');
        equal(await names(), ['suggestionnested'], 'The selected correction leads to its verified resource');
        equal(await page.locator('.u5-search-excerpt .hitshilite').allTextContents(), ['Adressen','M\u00fcller (Bern Rathaus) Hilfe & Beratung'], 'Corrected result excerpts preserve the word and literal quoted phrase');
        const correctedLink = await page.locator('.u5-search-result h3 a').getAttribute('href');
        equal(new URL(correctedLink, base).searchParams.get('q'), nestedCorrectedQuery, 'Copied result URL contains the entire corrected phrase');
        const correctedTarget = await context.newPage();
        correctedTarget.on('pageerror', error => errors.push(String(error)));
        await correctedTarget.goto(new URL(correctedLink, base).href, {waitUntil:'domcontentloaded'});
        await checkRevealedTarget(correctedTarget, 'Copied corrected result');
        const hitBox = await correctedTarget.locator('#corrected-target [data-u5-search-hit]').first().boundingBox();
        const headerBox = await correctedTarget.locator('header').boundingBox();
        verify(hitBox.y >= headerBox.y + headerBox.height + 8, 'Corrected destination scroll clears the real sticky header');
        equal(await correctedTarget.evaluate(() => document.activeElement.tagName), 'BODY', 'Corrected destination scrolling does not steal keyboard focus');
        await correctedTarget.close();

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
        await authPage.goto(url('SecretNeedle privatepaylod','de','_searchsi'), {waitUntil:'domcontentloaded'});
        verify((await offers(authPage)).some(offer => offer.query === 'SecretNeedle privatepayload'), 'Authenticated intranet corrections may use the now permitted protected vocabulary');
        await authenticated.close();
        equal(errors, [], 'Search and destination pages have no uncaught JavaScript errors');
        console.log(`Search web browser checks: ${checks} passed.`);
    } finally {
        await context.close();
        await browser.close();
    }
})().catch(error => { console.error(error); process.exitCode=1; });
