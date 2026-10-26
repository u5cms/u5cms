const fs = require('fs');
const path = require('path');
const assert = require('node:assert/strict');
const {launchBrowser}=require('./browser-runtime.cjs');

(async () => {
  const [, , base, site] = process.argv;
  const browser = await launchBrowser();
  const sharedOrigin=process.env.U5_TEST_SHARED_ORIGIN || 'https://info.agov.ch';
  let checks = 0;
  const verify = (value, message) => { assert.ok(value, message); checks++; };
  try {
    for (const scenario of [
      { name: 'desktop', width: 1280, mobile: false },
      { name: 'mobile', width: 375, mobile: true },
      { name: 'shared domain', width: 375, mobile: true, shared: true },
    ]) {
      if (scenario.shared) {
        const request = await browser.newContext();
        const response = await request.request.get(base + '/fixture-mode.php');
        assert.equal(response.status(), 200, await response.text());
        await request.close();
      }
      const context = await browser.newContext({ viewport: { width: scenario.width, height: 850 }, screen: { width: scenario.width, height: 850 } });
      const requests = [], errors = [], mimeErrors = [];
      await context.route('**/*', async route => {
        const url = new URL(route.request().url()); requests.push(url.href);
        if ((process.env.U5_TEST_SHARED_ORIGIN && url.origin===sharedOrigin) || url.origin === new URL(base).origin || url.protocol === 'data:') return route.continue();
        if (url.hostname === 'info.agov.ch' && ['/r/mobile.js','/r/mobilebase.js'].includes(url.pathname)) {
          return route.fulfill({ status: 200, headers: { 'content-type': 'text/javascript; charset=utf-8', 'x-content-type-options': 'nosniff' }, body: fs.readFileSync(path.join(site, url.pathname)) });
        }
        return route.abort();
      });
      const page = await context.newPage();
      page.on('pageerror', error => errors.push(String(error)));
      page.on('console', message => { if (message.type() === 'error' && /MIME type|Refused to execute script/.test(message.text())) mimeErrors.push(message.text()); });
      const response = await page.goto(base + '/?c=codefixture&l=en&mobile=' + (scenario.mobile ? 'on' : 'off'), { waitUntil: 'networkidle' });
      verify(response.status() === 200, scenario.name + ': real CMS page renders');
      await page.waitForSelector('#proof-form');
      await page.waitForFunction(() => document.querySelectorAll('iframe[name^="iuserupload"]').length === 4);
      if (scenario.mobile) await page.waitForFunction(() => Boolean(document.getElementById('contentinner')));
      verify(await page.evaluate(() => document.getElementById('token-proof') === window.proofToken), scenario.name + ': original input node retained');
      assert.deepEqual(await page.locator('#token-proof, #level-proof, #answer-proof').evaluateAll(elements => elements.map(element => element.value)), ['kept-token','kept-level','99']); checks++;
      await page.locator('#proof-form').dispatchEvent('proof');
      verify(await page.evaluate(() => window.proofEvents === 1), scenario.name + ': event listeners retained');
      const greeting = await page.evaluate(() => window.assetGreeting);
      verify(greeting === 'Über € &amp;', scenario.name + ': UTF-8 JavaScript and literal entities: ' + JSON.stringify(greeting) + '; ' + errors.join('; '));
      const frames = page.frames().filter(frame => /\/(?:Pupload|upload)(?:_mandatory)?\/upload\.php/.test(frame.url()));
      verify(frames.length === 4, scenario.name + ': four upload controls load');
      for (const [index, frame] of frames.entries()) {
        await frame.waitForSelector('input[type=file]');
        verify(await frame.locator('body').evaluate(element => getComputedStyle(element).color === 'rgb(1, 2, 3)'), scenario.name + ': correct relative upload stylesheet');
        await frame.locator('input[type=file]').setInputFiles({ name: 'proof-' + index + '.pdf', mimeType: 'application/pdf', buffer: Buffer.from('%PDF-1.4\n% regression upload\n%%EOF\n') });
      }
      await page.waitForFunction(() => Array.from(document.querySelectorAll('input[id^="userupload"]')).filter(input => input.value).length === 4);
      verify(await page.locator('input[id^="userupload"]').count() === 4, scenario.name + ': upload result fields retained');
      verify(await page.locator('#token-proof').inputValue() === 'kept-token', scenario.name + ': uploads preserve form token');
      if (scenario.mobile) {
        await page.locator('#webapp_monalinka').evaluate(element => element.click());
        verify(await page.locator('#webapp_mona').evaluate(element => getComputedStyle(element).display !== 'none'), scenario.name + ': mobile navigation opens');
        await page.locator('#webapp_monaclose').evaluate(element => element.click());
        verify(await page.locator('#webapp_mona').evaluate(element => getComputedStyle(element).display === 'none'), scenario.name + ': mobile navigation closes');
      }
      verify(!requests.some(url => /\/\/(?:www\.)?yuba\.ch\//i.test(url)), scenario.name + ': no yuba requests');
      verify(mimeErrors.length === 0, scenario.name + ': no MIME errors: ' + mimeErrors.join('\n'));
      verify(errors.length === 0, scenario.name + ': no JavaScript exceptions: ' + errors.join('\n'));
      if (scenario.shared) {
        verify(requests.some(url => url.startsWith(sharedOrigin+'/r/mobile.js')), 'shared script keeps source domain');
        verify(requests.some(url => url.startsWith(sharedOrigin+'/r/mobilebase.js')), 'shared common script uses updated extension');
      }
      await context.close();
    }
    console.log('Browser checks: ' + checks + ' passed (real CMS, desktop/mobile, shared domain, 12 file uploads, nosniff enabled).');
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
