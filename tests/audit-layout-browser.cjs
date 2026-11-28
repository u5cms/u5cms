const { chromium, firefox } = require(process.env.U5_TEST_PLAYWRIGHT);
const { createServer } = require('node:http');
const { readFile } = require('node:fs/promises');
const { join } = require('node:path');
const assert = require('node:assert/strict');

async function main() {
  const [site, revision, stage, paths] = process.argv.slice(2);
  const { base, layout } = JSON.parse(paths);
  const server = createServer(async (req, res) => {
    const path = new URL(req.url, 'http://localhost').pathname;
    if (path === '/') {
      res.writeHead(200, { 'Content-Type':'text/html; charset=utf-8' });
      return res.end('<!doctype html><link rel="stylesheet" href="/'+base+'"><div id="u5auditcss">Layout</div>');
    }
    if (!/^\/r\/[\w!-]+\.css$/.test(path)) { res.writeHead(404); return res.end(); }
    try {
      const data = await readFile(join(site, path));
      res.writeHead(200, { 'Content-Type':'text/css' });
      res.end(data);
    } catch { res.writeHead(404); res.end(); }
  });
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
  const origin = 'http://127.0.0.1:'+server.address().port;
  try {
    for (const engine of [chromium, firefox]) {
      const browser = await engine.launch();
      try {
        const page = await browser.newPage();
        await page.route('**/*', route => new URL(route.request().url()).origin === origin ? route.continue() : route.abort());
        const loaded = new Set();
        page.on('response', r => { if (r.status()===200) loaded.add(new URL(r.url()).pathname); });
        await page.goto(origin);
        for (const width of [1100,640,700,701]) {
          await page.setViewportSize({width,height:760});
          await page.waitForFunction(w => innerWidth===w, width);
          const styles = await page.locator('#u5auditcss').evaluate(e => [
            getComputedStyle(e).width, getComputedStyle(e).color, getComputedStyle(e,'::after').content
          ]);
          assert.deepEqual(styles, [width<=700?'87px':'193px', stage==='edit'?'rgb(44, 55, 66)':'rgb(11, 22, 33)', '"Genève"']);
          assert(loaded.has('/'+base) && loaded.has('/'+layout), JSON.stringify([...loaded]));
          console.log('PASS',revision,stage,engine.name(),width,base,layout,JSON.stringify(styles));
        }
      } finally { await browser.close(); }
    }
  } finally { server.close(); }
}
main().catch(error => { console.error(error); process.exitCode = 1; });
