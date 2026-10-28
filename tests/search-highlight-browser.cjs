/* Real-browser regression coverage for the target-page search highlighter. */
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const {launchBrowser} = require('./browser-runtime.cjs');
const normalizer = require('../js/search-highlight.js');

const site = path.resolve(__dirname, '..');
const script = fs.readFileSync(path.join(site, 'js/search-highlight.js'), 'utf8');
const mappingText = fs.readFileSync(path.join(site, 'search-normalization.json'), 'utf8');
const mapping = JSON.parse(mappingText);
const origin = 'https://u5-search.test';
let checks = 0;
const verify = (value, message) => { assert.ok(value, message); checks++; };
const equal = (actual, expected, message) => { assert.deepEqual(actual, expected, message); checks++; };
const term = text => ({text, normalized: normalizer.normalizeText(text, mapping), phrase: text.includes(' ')});
const data = value => JSON.stringify(value).replace(/[<>&\u2028\u2029]/g, ch => '\\u' + ch.charCodeAt(0).toString(16).padStart(4, '0'));

async function fixture(browser, body, terms, options = {}) {
    const context = await browser.newContext({viewport: {width: 1000, height: 700}, reducedMotion: options.motion || 'reduce'});
    const errors = [];
    const config = {terms: terms.map(term), normalizationUrl: '/search-normalization.json', rootSelector: '#content', autoScroll: Boolean(options.scroll)};
    const html = '<!doctype html><html><head><meta charset="utf-8"><style>' +
        'html{overflow-anchor:none}body{margin:0;font:16px/24px Arial}.hitshilite{background:#ffe67b}' +
        '#fixed-header{position:fixed;top:0;left:0;right:0;height:72px;background:white;z-index:10}' +
        '.showblockdivinner,.showblock2divinner{display:none}main{display:block}p{margin:0 0 12px}' +
        '</style>' + (options.head || '') + '</head><body>' + body +
        '<script>window.scrollCalls=[];window.savedScrollTo=window.scrollTo.bind(window);window.scrollTo=function(){window.scrollCalls.push(arguments[0]);return window.savedScrollTo.apply(window,arguments)};' +
        (options.before || '') + 'window.u5SearchConfig=' + data(config) + ';</script>' +
        '<script src="/js/search-highlight.js"></script></body></html>';
    await context.route('**/*', async route => {
        const url = new URL(route.request().url());
        if (url.origin !== origin) return route.abort();
        if (url.pathname === '/js/search-highlight.js') return route.fulfill({contentType: 'text/javascript; charset=utf-8', body: script});
        if (url.pathname === '/search-normalization.json') {
            if (options.mappingGate) await options.mappingGate;
            return route.fulfill({status: options.failMapping ? 503 : 200, contentType: 'application/json', body: mappingText});
        }
        return route.fulfill({contentType: 'text/html; charset=utf-8', body: html});
    });
    const page = await context.newPage();
    page.on('pageerror', error => errors.push(String(error)));
    await page.goto(origin + '/' + (options.hash || ''), {waitUntil: 'domcontentloaded'});
    const ready = () => page.evaluate(() => window.u5SearchHighlight.ready);
    const finish = async () => { equal(errors, [], 'No uncaught browser exception'); await context.close(); };
    return {context, page, ready, finish};
}

(async () => {
    const browser = await launchBrowser();
    try {
        {
            const f = await fixture(browser, '<main id="content"><p id="hit">Wort <em>(Wort</em> Wort) Wort</p></main>', ['Wort (Wort Wort) Wort']);
            const result = await f.ready();
            equal(result.count, 1, 'Parenthesized phrase crosses inline elements as one occurrence');
            equal(result.parts, 3, 'Original inline structure retained in three highlight portions');
            equal(await f.page.locator('#hit').textContent(), 'Wort (Wort Wort) Wort', 'Matched source spelling is unchanged');
            equal(await f.page.locator('#hit em').count(), 1, 'Inline element remains intact');
            equal(await f.page.locator('.hitshilite .hitshilite').count(), 0, 'No nested marks');
            await f.finish();
        }
        {
            const f = await fixture(browser, '<main id="content"><p>Alpha Beta</p><p>Alpha\u00a0Beta</p><p>Alpha\n  Beta</p></main>', ['Alpha Beta']);
            equal((await f.ready()).count, 3, 'Space, NBSP, line break and repeated whitespace use the shared normalization');
            await f.finish();
        }
        {
            const f = await fixture(browser, '<main id="content"><p>Alpha</p><p>Beta</p><p><b>Al</b><i>pha</i></p></main>', ['Alpha Beta', 'Alpha']);
            equal((await f.ready()).count, 2, 'Block boundary inserts a separator, inline boundary does not');
            equal(await f.page.locator('.hitshilite').allTextContents(), ['Alpha', 'Beta', 'Al', 'pha'], 'Cross-node ranges wrap the correct text');
            const joined = await f.page.evaluate(() => window.u5SearchHighlight.start({...window.u5SearchConfig, terms: [{normalized: 'alphabeta'}]}));
            equal(joined.count, 0, 'Words from adjacent blocks never become AlphaBeta');
            await f.finish();
        }
        {
            const f = await fixture(browser, '<main id="content"><p style="display:inline">Alpha</p><p style="display:inline">Beta</p></main>', ['Alpha Beta']);
            equal((await f.ready()).count, 1, 'Inline-styled paragraphs retain the same structural boundary as the index');
            equal(await f.page.locator('[data-u5-search-hit]').allTextContents(), ['Alpha', 'Beta'], 'Both structurally separated words are highlighted');
            await f.finish();
        }
        {
            const f = await fixture(browser, '<main id="content"><span style="display:block">Al</span><span style="display:block">pha</span></main>', ['Alpha']);
            equal((await f.ready()).count, 1, 'Block-styled spans retain the same inline adjacency as the index');
            equal(await f.page.locator('[data-u5-search-hit]').allTextContents(), ['Al', 'pha'], 'Presentation CSS cannot change the indexed phrase semantics');
            await f.finish();
        }
        for (const [query, text] of [
            ['strasse', 'Stra\u1e9ee'], ['oeuvre', '\u0152UVRE'], ['cafe', 'cafe\u0301'],
            ['ae', '\u00c6'], ['\u043c\u043e\u0441\u043a\u0432\u0430', '\u041c\u041e\u0421\u041a\u0412\u0410'],
            ['\ud83d\ude00', 'a\ud83d\ude00b'], ['foo_bar', 'foo_bar'], ['C++', 'C++'], ['100%', '100%'],
        ]) {
            const f = await fixture(browser, '<main id="content"><p id="source">' + text + '</p></main>', [query]);
            equal((await f.ready()).count, 1, 'Shared Unicode/literal matching: ' + JSON.stringify(query));
            const actual = await f.page.locator('.hitshilite').textContent();
            equal(actual, query === '\ud83d\ude00' ? query : text, 'Complete original grapheme is highlighted: ' + JSON.stringify(text));
            equal(await f.page.locator('#source').textContent(), text, 'Source UTF-16 text remains intact');
            await f.finish();
        }
        {
            const f = await fixture(browser, '<main id="content"><p>Alpha Beta</p></main>', ['Alpha', 'Beta', 'Alpha Beta', 'Alpha Beta']);
            equal((await f.ready()).count, 1, 'Duplicate and overlapping terms produce one merged occurrence');
            equal(await f.page.locator('.hitshilite').count(), 1, 'One mark for merged literal range');
            const rerun = await f.page.evaluate(() => window.u5SearchHighlight.start(window.u5SearchConfig));
            equal(rerun.count, 1, 'Repeated initialization is idempotent');
            equal(await f.page.locator('.hitshilite .hitshilite').count(), 0, 'Reinitialization never nests marks');
            await f.finish();
        }
        {
            const body = '<nav>Alpha</nav><main id="content"><p id="only">Alpha</p>' +
                '<form>Alpha<input id="state" value="initial"><textarea>Alpha</textarea><button>Alpha</button></form>' +
                '<div contenteditable="true">Alpha</div><mark id="foreign">Alpha</mark><span class="hitshilite">Alpha</span>' +
                '<a id="listener" href="#">unrelated</a><p>Alpha <mark>ignored</mark> Beta</p>' +
                '<div hidden>Alpha</div><script>window.literal="Alpha";</script></main>';
            const f = await fixture(browser, body, ['Alpha Beta'], {before: 'document.getElementById("state").value="kept";window.clicks=0;document.getElementById("listener").addEventListener("click",function(e){e.preventDefault();window.clicks++});'});
            equal((await f.ready()).count, 0, 'Ignored visible content is a phrase barrier');
            const next = await f.page.evaluate(() => window.u5SearchHighlight.start({...window.u5SearchConfig, terms: [{normalized: 'alpha'}]}));
            equal(next.count, 2, 'Only eligible content text is marked, excluding navigation/forms/editors/foreign marks/hidden content');
            equal(await f.page.locator('#state').inputValue(), 'kept', 'Form state survives DOM highlighting');
            equal(await f.page.locator('#foreign').textContent(), 'Alpha', 'Unrelated mark is preserved');
            await f.page.locator('#listener').click();
            equal(await f.page.evaluate(() => window.clicks), 1, 'Existing node event listener survives highlighting');
            await f.finish();
        }
        {
            const payload = '<img src=x onerror=window.pwned=1>';
            const f = await fixture(browser, '<main id="content"><p>&lt;img src=x onerror=window.pwned=1&gt;</p></main>', [payload]);
            equal((await f.ready()).count, 1, 'Markup-like query is treated as literal text');
            equal(await f.page.locator('img').count(), 0, 'Highlighting never creates query markup');
            equal(await f.page.evaluate(() => window.pwned), undefined, 'Query text never executes');
            await f.finish();
        }
        {
            const f = await fixture(browser, '<main id="content">' + 'a '.repeat(1100) + '</main>', ['a']);
            const result = await f.ready();
            equal(result.count, 1000, 'Maximum occurrence count bounds DOM work');
            verify(result.truncated, 'Result reports the highlight limit');
            equal(await f.page.locator('[data-u5-search-hit]').count(), 1000, 'Bound is applied to actual marks');
            await f.finish();
        }
        {
            const f = await fixture(browser, '<main id="content"><p>Unrelated text</p></main>', ['absent'], {scroll: true});
            equal((await f.ready()).count, 0, 'No-match page completes normally');
            await f.page.waitForTimeout(1150);
            equal(await f.page.evaluate(() => window.scrollCalls.length), 0, 'No-match page never attempts to scroll');
            await f.finish();
        }
        {
            const body = '<div id="fixed-header">Fixed header</div><main id="content"><div style="height:1000px"></div>' +
                '<details id="details"><summary>Open details</summary>' +
                '<a id="outer-toggle" class="showblockinactivetab">Open outer</a>\n<div id="outer-panel" class="showblockdivinner">' +
                '<a id="inner-toggle" class="showblockinactivetab">Open inner</a>\n<div id="inner-panel" class="showblockdivinner">' +
                '<a id="second-toggle" class="showblock2inactivetab">Open second</a>\n<div id="second-panel" class="showblock2divinner"><p id="target">Needle</p></div>' +
                '</div></div></details><a class="showblockinactivetab">Other</a><div id="untouched" class="showblockdivinner">Needle</div>' +
                '<div style="height:1600px"></div></main>';
            const f = await fixture(browser, body, ['Needle'], {scroll: true});
            equal((await f.ready()).count, 2, 'Closed collapsible text is available for matching');
            await f.page.waitForFunction(() => window.scrollY > 500);
            equal(await f.page.locator('#details').evaluate(el => el.open), true, 'Native details ancestor opens');
            for (const id of ['outer', 'inner', 'second']) {
                equal(await f.page.locator('#' + id + '-panel').evaluate(el => getComputedStyle(el).display), 'block', 'Nested panel opens: ' + id);
                equal(await f.page.locator('#' + id + '-toggle').getAttribute('aria-expanded'), 'true', 'Correct previous element toggle is synchronized: ' + id);
            }
            equal(await f.page.locator('#untouched').evaluate(el => getComputedStyle(el).display), 'none', 'Unrelated closed hit panel remains closed');
            const rect = await f.page.locator('#target').boundingBox();
            verify(rect.y >= 86 && rect.y < 130, 'First hit clears the 72px fixed header with reading margin');
            equal(await f.page.evaluate(() => window.scrollCalls[0].behavior), 'auto', 'Reduced-motion preference uses instant scrolling');
            equal(await f.page.evaluate(() => document.activeElement.tagName), 'BODY', 'Automatic search scrolling does not steal keyboard focus');
            await f.finish();
        }
        {
            const body = '<div id="fixed-header">Header</div><main id="content"><div id="spacer" style="height:1000px"></div><p id="target">Needle</p><div style="height:1700px"></div></main>';
            const f = await fixture(browser, body, ['Needle'], {scroll: true});
            await f.ready();
            await f.page.waitForFunction(() => window.scrollY > 500);
            await f.page.locator('#spacer').evaluate(el => el.style.height = '1250px');
            await f.page.waitForFunction(() => Math.abs(document.getElementById('target').getBoundingClientRect().top - 88) < 3);
            verify(await f.page.evaluate(() => window.scrollCalls.length >= 2), 'Bounded layout observer corrects an image/font-like height change');
            await f.page.mouse.wheel(0, 150);
            const calls = await f.page.evaluate(() => window.scrollCalls.length);
            await f.page.locator('#spacer').evaluate(el => el.style.height = '1450px');
            await f.page.waitForTimeout(150);
            equal(await f.page.evaluate(() => window.scrollCalls.length), calls, 'User scrolling cancels subsequent automatic correction');
            await f.finish();
        }
        {
            const body = '<div id="fixed-header">Header</div><main id="content"><div style="height:1000px"></div><p id="target">Needle</p><div style="height:1500px"></div></main>';
            const f = await fixture(browser, body, ['Needle'], {scroll: true, motion: 'no-preference'});
            await f.ready();
            await f.page.waitForFunction(() => Math.abs(document.getElementById('target').getBoundingClientRect().top - 88) < 3);
            equal(await f.page.evaluate(() => window.scrollCalls[0].behavior), 'smooth', 'Default motion scroll reaches the same header-safe position');
            equal(await f.page.evaluate(() => document.activeElement.tagName), 'BODY', 'Smooth scrolling also preserves focus');
            await f.finish();
        }
        {
            const body = '<div id="fixed-header">Header</div><main id="content"><div style="height:1000px"></div><p id="target">Needle</p><div style="height:1700px"></div></main>';
            const f = await fixture(browser, body, ['Needle'], {scroll: true, head: '<style>html{opacity:0}</style>'});
            equal((await f.ready()).count, 1, 'Highlighting runs before the mobile layout becomes visible');
            await f.page.evaluate(() => {
                const content = document.getElementById('content'), inner = document.createElement('div');
                inner.id = 'contentinner';
                while (content.firstChild) inner.appendChild(content.firstChild);
                content.appendChild(inner);
                content.style.marginTop = '130px';
                document.documentElement.style.opacity = '1';
                setTimeout(() => window.scrollTo(0, 7), 0);
            });
            await f.page.waitForFunction(() => window.scrollY > 800 && Math.abs(document.getElementById('target').getBoundingClientRect().top - 88) < 3);
            equal(await f.page.locator('#contentinner [data-u5-search-hit]').count(), 1, 'Mobile node moves preserve the original mark');
            await f.finish();
        }
        {
            const f = await fixture(browser, '<main id="content"><div style="height:300px"></div><p id="anchor">Anchor</p><div style="height:1000px"></div><p>Needle</p></main>', ['Needle'], {scroll: true, hash: '#anchor'});
            equal((await f.ready()).count, 1, 'An existing anchor still permits highlighting');
            await f.page.waitForTimeout(120);
            equal(await f.page.evaluate(() => window.scrollCalls.length), 0, 'Meaningful URL anchor takes precedence over search scrolling');
            await f.finish();
        }
        {
            let release;
            const mappingGate = new Promise(resolve => { release = resolve; });
            const f = await fixture(browser, '<main id="content"><div style="height:1200px"></div><p>Needle</p><div style="height:1200px"></div></main>', ['Needle'], {scroll: true, mappingGate});
            await f.page.mouse.wheel(0, 200);
            release();
            equal((await f.ready()).count, 1, 'A late normalization response still highlights');
            await f.page.waitForTimeout(120);
            equal(await f.page.evaluate(() => window.scrollCalls.length), 0, 'Late initialization respects prior user interaction');
            await f.finish();
        }
        {
            const f = await fixture(browser, '<main id="content"><p>Needle</p></main>', ['Needle'], {failMapping: true});
            equal((await f.ready()).status, 'normalization-unavailable', 'Unavailable shared mapping is handled without guessing different semantics');
            equal(await f.page.locator('[data-u5-search-hit]').count(), 0, 'Mapping failure leaves page content intact');
            await f.finish();
        }
        console.log('Search highlighter browser checks: ' + checks + ' passed.');
    } finally {
        await browser.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
