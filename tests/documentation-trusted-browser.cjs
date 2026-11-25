const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {execFileSync} = require('node:child_process');
const {launchBrowser} = require(process.env.U5_DOCUMENTATION_SOURCE + '/tests/browser-runtime.cjs');

(async () => {
    const origin = process.env.U5_DOCUMENTATION_ORIGIN;
    const site = process.env.U5_DOCUMENTATION_SITE;
    const configuration = fs.readFileSync(path.join(site, 'config.php'), 'latin1');
    const results = [];
    const browser = await launchBrowser();
    const contexts = [];
    const record = name => { results.push({case:name,passed:true}); console.log('PASS ' + name); };
    const management = '/u5admin/trusteddevice.php';
    async function context() {
        const c = await browser.newContext({ignoreHTTPSErrors:true, viewport:{width:1120,height:900}});
        await c.route('**/*', route => {
            const hostname = new URL(route.request().url()).hostname;
            return ['localhost','127.0.0.1'].includes(hostname) ? route.continue() : route.abort();
        });
        contexts.push(c); return c;
    }
    async function login(c, user='alice@example.org') {
        const response = await c.request.post(origin + '/loginsave.php?u=' + management,
            {form:{u:user,p:process.env.U5CMS_FIXTURE_PASSWORD}});
        assert.equal(response.status(),200);
        const p = await c.newPage(); await p.goto(origin + management);
        assert.match(await p.locator('body').innerText(),new RegExp(user.replaceAll('.','\\.')));
        return p;
    }
    async function clearSession(c) {
        await c.clearCookies({name:'u'}); await c.clearCookies({name:'p'});
    }
    async function registered(p,count,maximum) {
        assert.match(await p.locator('body').innerText(),new RegExp(count+' of '+maximum+' permitted browsers'));
    }
    function config(extra='') { fs.writeFileSync(path.join(site,'config.php'),configuration+'\n'+extra,'latin1'); }
    async function missingLogin(p) {
        assert.doesNotMatch(await p.locator('body').innerText(),/Account:\s+alice@example\.org/);
        assert.equal(await p.locator('input[name="p"]').count(),1);
    }
    try {
        const a=await context(), b=await context(), c=await context();
        let ap = await a.newPage();
        await ap.goto(origin + management);
        await ap.locator('input[name="u"]').fill('alice@example.org');
        await ap.locator('input[name="p"]').fill(process.env.U5CMS_FIXTURE_PASSWORD);
        await ap.getByRole('button', {name:'log in'}).click();
        await ap.getByRole('heading', {name:'Trusted browsers'}).waitFor();
        assert.equal(new URL(ap.url()).pathname, management);
        await registered(ap,0,2);
        record('Opening the management page while signed out returns here after a normal form login');
        await ap.getByRole('button',{name:'Trust this browser',exact:true}).click();
        assert.match(await ap.locator('body').innerText(),/This browser is now trusted/);
        await registered(ap,1,2); record('A normal login can register the intended browser');
        const token=(await a.cookies()).find(x=>x.name.startsWith('__Host-u5td-'));
        assert.ok(token && token.secure && token.httpOnly && token.sameSite==='Strict' && token.path==='/');
        assert.equal(token.domain,'localhost');
        assert.ok(token.expires>Date.now()/1000+399*86400);
        assert.ok(!(await ap.evaluate(()=>document.cookie)).includes(token.name));
        record('The real HTTPS cookie is host-only, HttpOnly, Secure and Strict, with a 400-day lifetime');
        await ap.reload();await registered(ap,1,2);record('Revisiting does not allocate another place');
        let bp=await login(b);await bp.getByRole('button',{name:'Trust this browser',exact:true}).click();
        await registered(bp,2,2);record('A second independent profile fills the two-place allowance');
        await bp.locator('body').screenshot({path:path.join(process.env.U5_DOCUMENTATION_OUTPUT,'trusted-browser-list.png')});
        let cp=await login(c);assert.match(await cp.locator('body').innerText(),/browser limit has been reached/);
        assert.equal(await cp.getByRole('button',{name:'Trust this browser',exact:true}).count(),0);
        record('A third profile is not silently substituted for an existing registration');
        await clearSession(a);await ap.reload();await registered(ap,2,2);
        assert.match(await ap.locator('body').innerText(),/Account:\s+alice@example\.org/);
        record('Removing ordinary session cookies still resumes through the trusted credential');
        await ap.close();ap=await a.newPage();await ap.goto(origin+management);await registered(ap,2,2);
        record('Closing and reopening a tab retains registration in the same profile');
        const alias=await a.newPage();await alias.goto(origin.replace('localhost','127.0.0.1')+management);
        await missingLogin(alias);await alias.close();record('A host alias does not receive the registered host cookie');
        await ap.goto(origin+'/logout.php');
        assert.equal((await a.cookies()).filter(x=>x.name.startsWith('__Host-u5td-')).length,0);
        await clearSession(a);await ap.goto(origin+management);await missingLogin(ap);
        await bp.reload();await registered(bp,1,2);assert.match(await bp.locator('body').innerText(),/this browser/);
        record('CMS logout revokes only the current browser, while another profile remains trusted');
        ap=await login(a);await ap.getByRole('button',{name:'Trust this browser',exact:true}).click();
        config("$u5trusteddeviceenabled='no';");
        const disabled=await cp.goto(origin+management);assert.equal(disabled.status(),404);
        await clearSession(a);await ap.goto(origin+management);await missingLogin(ap);
        config();await clearSession(a);await ap.reload();await registered(ap,2,2);
        record('Disabling prevents resumption, but re-enabling restores an unrevoked registration');
        config("$u5trusteddeviceusers=array('alice@example.org'=>0,'bob@example.org'=>1);");
        const noQuota=await cp.goto(origin+management);assert.equal(noQuota.status(),404);
        config();await cp.reload();await registered(cp,2,2);
        record('An eligible login with zero allowance receives 404; restoring the allowance retains records');
        await a.clearCookies();ap=await login(a);await registered(ap,2,2);
        assert.doesNotMatch(await ap.locator('body').innerText(),/this browser/);
        record('Clearing cookies loses the local credential without freeing the server place');
        await bp.reload();await bp.getByRole('row').filter({hasText:/Browser 1\b/}).getByRole('button',{name:'Revoke'}).click();
        await ap.reload();await ap.getByRole('button',{name:'Trust this browser',exact:true}).click();
        await registered(ap,2,2);record('Revoking the stale place permits a replacement profile');
        config("$u5trusteddeviceusers=array('alice@example.org'=>1,'bob@example.org'=>1);");
        await clearSession(b);await bp.goto(origin+management);await missingLogin(bp);
        await clearSession(a);await ap.reload();await registered(ap,1,1);
        record('Reducing the allowance rejects place 2 and preserves place 1');
        config();bp=await login(b);await bp.getByRole('button',{name:'Trust this browser',exact:true}).click();
        ap=await login(a,'bob@example.org');await registered(ap,0,1);
        await bp.reload();await registered(bp,1,2);
        record('An explicit account switch revokes the previous profile identity, not another profile');
        await ap.getByRole('button',{name:'Trust this browser',exact:true}).click();await registered(ap,1,1);
        record('The new account has its own registration allowance');
        await bp.getByRole('row').filter({hasText:/this browser/}).getByRole('button',{name:'Revoke'}).click();
        await registered(bp,0,2);assert.match(await bp.locator('body').innerText(),/Account:\s+alice@example\.org/);
        record('Individual revocation leaves the already established ordinary session usable');
        await bp.getByRole('button',{name:'Trust this browser',exact:true}).click();
        const csrf=await bp.locator('input[name="csrf"]').first().inputValue();
        const badCsrf=await b.request.post(origin+management,{form:{action:'enrol',csrf:'incorrect'}});
        assert.equal(badCsrf.status(),403);
        const cross=await b.request.post(origin+management,{headers:{Origin:'https://attacker.invalid','Sec-Fetch-Site':'cross-site'},form:{action:'enrol',csrf}});
        assert.equal(cross.status(),403);record('Bad CSRF tokens and cross-origin registration are rejected');
        config("$u5trusteddeviceusers=array('alice@example.org'=>'2','bob@example.org'=>1);");
        const invalid=await bp.goto(origin+management);assert.equal(invalid.status(),404);
        config();await bp.reload();record('A numeric string is not a valid configured browser limit');
        fs.writeFileSync(path.join(process.env.U5_DOCUMENTATION_OUTPUT,'trusted-browser-observations.json'),JSON.stringify({checks:results.length,cases:results},null,2));
    } finally {
        config();for (const c of contexts) await c.close();await browser.close();
    }
})().catch(error=>{console.error(error.stack);process.exitCode=1;});
