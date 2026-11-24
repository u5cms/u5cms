const assert = require('node:assert/strict');
const {execFileSync} = require('node:child_process');
const path = require('node:path');

module.exports = async function verifyTurnstile(browser) {
    const root = path.resolve(__dirname, '..');
    const php = process.env.U5_TEST_PHP || 'php';
    const widget = execFileSync(php, ['-r', "include $argv[1];", path.join(root, 'grc.php')], {encoding:'utf8'});
    // Exercise an actual PHP rejection response without using provider credentials.
    const rejection = execFileSync(php, ['-r', "$_POST['u5-turnstile-widget']='2'; include $argv[1];", path.join(root, 'grc2.php')], {encoding:'utf8'});
    const page = await browser.newPage();
    try {
        let downloads = 0;
        await page.route('https://challenges.cloudflare.com/**', async route => {
            ++downloads;
            await route.fulfill({contentType:'application/javascript', body:`
                window.callbacks = [];
                window.resets = [];
                window.turnstile = {
                    ready: fn => fn(),
                    render: (el, options) => { callbacks.push(options); return 'widget-' + callbacks.length; },
                    reset: id => resets.push(id)
                };`});
        });
        const forms = ['A','B'].map(name => '<form id="'+name+'">'+widget+'<button>Save</button></form>').join('');
        await page.setContent(forms);
        await page.waitForFunction(() => window.callbacks && callbacks.length === 2);
        assert.equal(downloads, 1);
        const buttons = () => page.locator('button').evaluateAll(items => items.map(b => getComputedStyle(b).visibility));
        const submits = () => page.locator('form').evaluateAll(items => items.map(f => f.dispatchEvent(new Event('submit', {cancelable:true}))));
        assert.deepEqual(await buttons(), ['hidden','hidden']);
        assert.deepEqual(await submits(), [false,false]);
        await page.evaluate(() => callbacks[1].callback('local-B'));
        assert.deepEqual(await buttons(), ['hidden','visible']);
        assert.deepEqual(await submits(), [false,true]);
        await page.evaluate(() => { callbacks[0].callback('local-A'); callbacks[1]['expired-callback'](); });
        assert.deepEqual(await buttons(), ['visible','hidden']);
        assert.deepEqual(await page.locator('form').evaluateAll(items => items.map(f => new FormData(f).get('u5-turnstile-widget'))), ['1','2']);
        await page.evaluate(() => { callbacks[1].callback('local-B2'); window.alert = () => {}; });
        // Use the returned script in its own frame, as the production POST does.
        await page.evaluate(body => {
            const frame = document.createElement('iframe');
            frame.srcdoc = '<script>window.alert=function(){};<\/script>' + body;
            document.body.appendChild(frame);
        }, rejection);
        await page.waitForFunction(() => resets.length === 1);
        assert.deepEqual(await page.evaluate(() => resets), ['widget-2']);
        assert.deepEqual(await buttons(), ['visible','hidden']);
        await page.evaluate(() => window.u5turnstileReset());
        assert.deepEqual(await buttons(), ['visible','hidden']);
        console.log('Turnstile: independent forms, expiry, submit and iframe reset passed.');
    } finally { await page.close(); }
};
