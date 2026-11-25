const assert=require('node:assert/strict');
const fs=require('node:fs');
const path=require('node:path');
const {execFileSync}=require('node:child_process');
const {launchBrowser}=require(process.env.U5_DOCUMENTATION_SOURCE+'/tests/browser-runtime.cjs');
(async()=>{
    const origin=process.env.U5_DOCUMENTATION_ORIGIN,site=process.env.U5_DOCUMENTATION_SITE;
    const observations=[];
    const passed=name=>{observations.push({case:name,passed:true});console.log('PASS '+name);};
    const sql=expression=>JSON.parse(execFileSync(process.env.U5_TEST_PHP||'php',[
        '-d','mysqli.default_port='+process.env.U5CMS_TEST_PORT,'-r',
        "$_SERVER['REQUEST_URI']='/';$_SERVER['HTTP_HOST']='localhost';require 'connect.inc.php';"+expression,
    ],{cwd:site,encoding:'utf8'}));
    const count=()=>sql("$r=mysql_fetch_row(mysql_query(\"SELECT COUNT(*) FROM formdata WHERE formname='docform'\"));echo json_encode((int)$r[0]);");
    const browser=await launchBrowser();
    const context=await browser.newContext({ignoreHTTPSErrors:true});
    await context.route('**/*',route=>['localhost','127.0.0.1'].includes(new URL(route.request().url()).hostname)?route.continue():route.abort());
    const page=await context.newPage();page.on('dialog',dialog=>dialog.dismiss());
    try {
        await page.goto(origin+'/index.php?c=doclesson&l=en');
        assert.equal(await page.getByRole('heading',{name:'A small workshop',level:1,exact:true}).count(),1);
        assert.equal(await page.getByRole('heading',{name:'Reusable content',level:2,exact:true}).count(),1);
        assert.ok(Number(await page.getByText('precise words',{exact:true}).evaluate(element=>getComputedStyle(element).fontWeight))>=600);
        passed('The worked source produces the intended heading levels and inline emphasis');
        assert.equal(await page.locator('#literal-markers').innerText(),'[b]Keep these markers[/]');
        passed('Encoded brackets display CMS markers verbatim inside an HTML section');
        assert.match(await page.locator('#included-html').innerText(),/A reusable opening\./);
        assert.equal(await page.getByRole('link',{name:'Open the fragment',exact:true}).count(),1);
        passed('An inclusion renders content, while a labelled reference renders a link');
        await page.goto(origin+'/index.php?c=docfallback&l=en');
        assert.match(await page.locator('body').innerText(),/Fallback text from the first language\./);
        passed('An empty language field can display fallback content rather than an empty page');
        await page.goto(origin+'/index.php?c=docform&l=en');
        assert.equal(await page.getByLabel('Your name',{exact:true}).count(),1);
        assert.equal(await page.getByRole('button',{name:'Register for the workshop',exact:true}).count(),1);
        passed('The form markers produce a usable labelled HTML field and submit control');
        const form=await page.locator('form[name="u5form"]').evaluate(f=>({action:f.action,values:Object.fromEntries(new FormData(f))}));
        const missing=await context.request.post(form.action,{form:{...form.values,participant_mandatory:''}});
        assert.equal(count(),0);assert.match(await missing.text(),/mandatory|Please|fill|Required/i);
        passed('An empty mandatory field is rejected before a record is inserted');
        const foreign=await context.request.post(form.action,{form:{...form.values,participant_mandatory:'A visitor',unexpected_field:'outside the declared form'}});
        assert.match(await foreign.text(),/Data cannot be saved/);assert.equal(count(),0);
        passed('A field absent from the stored form is rejected without writing a record');
        const participant='Zürich – 東京 🙂';await page.getByLabel('Your name',{exact:true}).fill(participant);
        await page.getByRole('button',{name:'Register for the workshop',exact:true}).click();
        await page.waitForFunction(()=>document.getElementById('submission-status').textContent==='Your registration was saved.');
        assert.equal(count(),1);
        passed('A successful browser submission invokes its callback and inserts exactly one record');
        await context.request.post(origin+'/loginsave.php?u=/u5admin/formdata2.php?n=docform',{form:{u:'alice@example.org',p:process.env.U5CMS_FIXTURE_PASSWORD}});
        const exported=await context.request.get(origin+'/u5admin/formdata2csv.php?n=docform&f=&s=1');
        assert.equal(exported.status(),200);assert.ok((await exported.text()).includes(participant));
        passed('The actual CSV export preserves accented, CJK and supplementary Unicode characters');
        await page.goto(origin+'/u5admin/formdata2.php?n=docform&f=&s=1');
        const displayed=await page.locator('body').evaluate(body=>body.innerText+'\n'+Array.from(body.querySelectorAll('input,textarea')).map(field=>field.value).join('\n'));
        assert.ok(displayed.includes(participant));passed('The actual backend data view renders the submitted Unicode value');
        const watermark=path.join(site,'fileversions/lastindex.txt');
        const before=fs.existsSync(watermark)?fs.readFileSync(watermark,'utf8'):null;
        const response=await context.request.get(origin+'/indexer.php?n=docsearch',{headers:{'X-U5-Background-Work':'1','Accept':'application/json'}});
        assert.equal(response.status(),200);const state=await response.json();assert.equal(state.state,'done');
        await page.goto(origin+'/index.php?c=_search&l=en&q=atlasquartz');
        assert.ok(await page.locator('a[href*="c=docsearch"]').count()>0);
        passed('A named index update makes the exercise page discoverable by its unique text');
        assert.equal(fs.existsSync(watermark)?fs.readFileSync(watermark,'utf8'):null,before);
        passed('A named update leaves the last completed full-rebuild watermark unchanged');
        fs.writeFileSync(path.join(process.env.U5_DOCUMENTATION_OUTPUT,'content-observations.json'),JSON.stringify({checks:observations.length,cases:observations},null,2));
    } finally {await context.close();await browser.close();}
})().catch(error=>{console.error(error.stack);process.exitCode=1;});
