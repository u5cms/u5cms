const assert = require('node:assert/strict');
const {launchBrowser} = require('./browser-runtime.cjs');
(async () => {
    const browser = await launchBrowser();
    const failures = [];
    try {
        for (const width of [1100, 640]) {
            const context = await browser.newContext({viewport:{width,height:760},ignoreHTTPSErrors:true});
            const origin = process.env.U5_DOCUMENTATION_ORIGIN;
            await context.route('**/*', route => new URL(route.request().url()).origin === origin ? route.continue() : route.abort());
            for (const [query, input, expected] of [
                ['FormerTerm,Seidenatlas', 'FormerTerm Seidenatlas', ['Seidenatlas']],
                ['Seidenatlas,FormerTerm', 'Seidenatlas FormerTerm', ['Seidenatlas']],
                ['FormerTerm,Gen%u00E8ve', 'FormerTerm Genève', ['Genève']],
                ['Gen%C3%A8ve,FormerTerm', 'Genève FormerTerm', ['Genève']],
                ['Gen%C3%A8ve,FormerTerm,Seidenatlas', 'Genève FormerTerm Seidenatlas', ['Genève', 'Seidenatlas']]
            ]) {
                const page = await context.newPage();
                const errors = [];
                page.on('pageerror', e => errors.push(e.message));
                const url = new URL('/index.php?c=auditvenue&l=en', origin);
                url.search += '&q=' + query;
                const response = await page.goto(url.href);
                await page.locator('#audit-target').waitFor({state:'attached'});
                const measure = () => page.locator('#audit-target').evaluate(target => {
                    const hits = Array.from(target.querySelectorAll('.hitshilite,[data-u5-search-hit]'));
                    return {text:target.textContent, marks:hits.map(h=>h.textContent), y:window.scrollY, top:target.getBoundingClientRect().top,
                        bottom:target.getBoundingClientRect().bottom, height:window.innerHeight, input:document.getElementById('search_Input')?.value};
                });
                let m;
                const deadline = Date.now()+4500;
                do {
                    m = await measure();
                    if ( m.y>400 && m.top>=0 && m.bottom<=m.height) break;
                    await page.waitForTimeout(100);
                } while (Date.now()<deadline);
                const result = {engine:process.env.U5_TEST_ENGINE,width,query,status:response.status(),...m,errors};
                console.log('MEASURE '+JSON.stringify(result));
                try {
                    assert.equal(response.status(),200);
                    assert.equal(m.text,'Genève und Seidenatlas');
                    assert.deepEqual(m.marks,expected);
                    assert.deepEqual(errors, []);
                    assert.equal(m.input,input);
                    assert.ok(m.y>400 && m.top>=0 && m.bottom<=m.height);
                } catch (e) {failures.push({width,query,message:e.message});}
                await page.close();
            }
            await context.close();
        }
    } finally {await browser.close();}
    assert.deepEqual(failures,[]);
})().catch(e=>{console.error(e);process.exitCode=1;});