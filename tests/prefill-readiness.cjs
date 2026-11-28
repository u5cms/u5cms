const assert=require('node:assert/strict');
const {execFileSync}=require('node:child_process');
const {launchBrowser}=require('./browser-runtime.cjs');
const php=['-d','mysqli.default_port='+process.env.U5CMS_TEST_PORT,process.env.U5_PREFILL_SNAPSHOT];
function snapshot(reset=false){return JSON.parse(execFileSync('php',[...php,...(reset?['reset']:[])],{cwd:process.env.U5_PREFILL_SITE,encoding:'utf8'}));}
async function main(){
 const defects=[];
 for(const engine of ['chromium','firefox']){
  process.env.U5_TEST_ENGINE=engine;
  const browser=await launchBrowser();
  try{
   for(const action of ['edit','delete'])for(const mode of ['loaded','paused','aborted'])for(const trigger of ['button','enter','script']){
    const before=snapshot(true);
    const origin=process.env.U5_PREFILL_ORIGIN;
    const context=await browser.newContext({httpCredentials:{username:'auditadmin',password:process.env.U5CMS_FIXTURE_PASSWORD}});
    const page=await context.newPage();
    page.on('dialog',d=>{console.log('PREFILL_DIALOG '+JSON.stringify({action,mode,trigger,message:d.message()}));return d.dismiss();});
    page.on('pageerror',e=>console.log('PREFILL_PAGEERROR '+e.message));
    page.on('request',r=>{if(r.method()==='POST')console.log('PREFILL_POST '+new URL(r.url()).pathname);});
    let posts=0, responseBody='';
    const endpoint=action==='edit'?'/editformsave.php':'/delformsave.php';
    await page.route('**'+endpoint+'?**',async route=>{posts++;const response=await route.fetch();responseBody=await response.text();await route.fulfill({response});});
    let release;
    const gate=new Promise(r=>release=r);
    let seen=false;
    if(mode!=='loaded')await page.route('**/formdataedit2.php?**',async route=>{
      seen=true;
      if(mode==='aborted')return route.abort('failed');
      await gate;try{await route.continue();}catch(error){if(!page.isClosed()&&!page.url().includes('/u5admin/'))throw error;}
    });
    const url=origin+'/'+(action==='edit'?'formdataedit.php':'formdatadel.php')+'?n=prefillaudit&id=900001&a=1&l=en';
    await page.goto(url,{waitUntil:'domcontentloaded'});
    if(action==='delete')await page.locator('#genericformdataeditorlinkdiv').getByText('×',{exact:true}).click();
    if(mode==='loaded')await page.waitForFunction(()=>document.forms.u5form.title_mandatory.value.startsWith('Saved Genève'));
    else {for(let i=0;i<100&&!seen;i++)await page.waitForTimeout(20);assert.ok(seen,'Actual loader request was not intercepted');}
    await page.waitForFunction(()=>Number(document.forms.u5form.firstsaverwins.value)>0);
    const values=await page.evaluate(()=>{let f=document.forms.u5form;return{title:f.title_mandatory.value,optional:f.optional.value,version:f.u5cms_formversion?.value||'',action:new URL(f.action).pathname};});
    assert.equal(values.action,endpoint);
    if(mode==='loaded'){assert.equal(values.optional,'');await page.locator('[name="title_mandatory"]').fill('Edited after complete prefill');}
    else assert.equal(values.title,'Fresh initial title');
    async function submit(){
      if(trigger==='script')await page.evaluate(()=>document.forms.u5form.submit());
      else if(trigger==='enter')await page.locator('[name="title_mandatory"]').press('Enter');
      else await page.getByRole('button',{name:action==='edit'?'Save':'delete',exact:true}).click({noWaitAfter:true});
    }
    if(mode==='loaded'){
      const response=page.waitForResponse(r=>new URL(r.url()).pathname===endpoint);await submit();assert.equal((await response).status(),200);
    }else {await submit();await page.waitForTimeout(350);}
    const after=snapshot();
    const changed=JSON.stringify(before)!==JSON.stringify(after);
    const active=after.filter(r=>Number(r.status)===1);
    const data=active.length?Buffer.from(active[0].data,'hex').toString('utf8'):'';
    const observed={revision:process.env.U5_AUDIT_REVISION,engine,action,mode,trigger,values,posts,changed,rows:after.length,active:active.length,stored:data,conflict:responseBody.includes('You cannot save your data')};
    console.log('PREFILL_OBSERVATION '+JSON.stringify(observed));
    if(mode==='loaded'){
      assert.equal(posts,1);assert.ok(changed);assert.equal(after.length,2);
      if(action==='edit'){assert.equal(active.length,1);assert.ok(data.includes('Edited after complete prefill'));assert.ok(!data.includes('Fresh initial optional'));}
      else {assert.equal(active.length,0);assert.equal(after.filter(r=>Number(r.status)===5).length,1);}
    }else{
      if(changed||posts)defects.push(observed);
      release();
      if(!changed){
        if(mode==='aborted'){await page.unroute('**/formdataedit2.php?**');await page.reload({waitUntil:'domcontentloaded'});if(action==='delete')await page.locator('#genericformdataeditorlinkdiv').getByText('×',{exact:true}).click();}
        await page.waitForFunction(()=>document.forms.u5form.title_mandatory.value.startsWith('Saved Genève'));
        assert.equal(await page.locator('[name="optional"]').inputValue(),'');
        const response=page.waitForResponse(r=>new URL(r.url()).pathname===endpoint);await submit();assert.equal((await response).status(),200);
        const resumed=snapshot();assert.equal(resumed.length,2);assert.equal(resumed.filter(r=>Number(r.status)===1).length,action==='edit'?1:0);
        console.log('PREFILL_RESUMED '+JSON.stringify({revision:process.env.U5_AUDIT_REVISION,engine,action,mode,trigger,posts,rows:resumed.length}));
      }
    }
    release();await page.waitForTimeout(80);await context.close();
   }
  }finally{await browser.close();}
 }
 assert.equal(defects.length,0,'A response was changed before its prefill completed');
}
main().catch(e=>{console.error(e);process.exitCode=1;});
