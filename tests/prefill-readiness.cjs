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
   for(const mode of ['loaded','paused','aborted']){
    const before=snapshot(true);
    const origin=process.env.U5_PREFILL_ORIGIN;
    const context=await browser.newContext({httpCredentials:{username:'auditadmin',password:process.env.U5CMS_FIXTURE_PASSWORD}});
    const page=await context.newPage();
    page.on('dialog',d=>d.dismiss());
    let release;
    const gate=new Promise(r=>release=r);
    let seen=false;
    if(mode!=='loaded')await page.route('**/formdataedit2.php?**',async route=>{
      seen=true;
      if(mode==='aborted')return route.abort('failed');
      await gate; await route.continue();
    });
    await page.goto(origin+'/formdataedit.php?n=prefillaudit&id=900001&a=1&l=en',{waitUntil:'domcontentloaded'});
    if(mode==='loaded')await page.waitForFunction(()=>document.forms.u5form.title_mandatory.value.startsWith('Saved Genève'));
    else {for(let i=0;i<100&&!seen;i++)await page.waitForTimeout(20);assert.ok(seen,'Actual loader request was not intercepted');}
    await page.waitForFunction(()=>Number(document.forms.u5form.firstsaverwins.value)>0);
    const values=await page.evaluate(()=>{let f=document.forms.u5form;return{title:f.title_mandatory.value,optional:f.optional.value,version:f.u5cms_formversion?.value||'',action:new URL(f.action).pathname,disabled:f.querySelector('button').disabled};});
    assert.equal(values.action,'/editformsave.php');
    if(mode==='loaded'){assert.equal(values.optional,'');await page.locator('[name="title_mandatory"]').fill('Edited after complete prefill');}
    else assert.equal(values.title,'Fresh initial title');
    const response=page.waitForResponse(r=>new URL(r.url()).pathname==='/editformsave.php');
    await page.getByRole('button',{name:'Save',exact:true}).click({noWaitAfter:true});
    const saved=await response;const body=await saved.text();
    const after=snapshot();
    const changed=JSON.stringify(before)!==JSON.stringify(after);
    const active=after.filter(r=>Number(r.status)===1);
    assert.equal(active.length,1);
    const data=Buffer.from(active[0].data,'hex').toString('utf8');
    const observed={revision:process.env.U5_AUDIT_REVISION,engine,mode,values,http:saved.status(),changed,rows:after.length,stored:data,conflict:body.includes('You cannot save your data')};
    console.log('PREFILL_OBSERVATION '+JSON.stringify(observed));
    if(mode==='loaded'){assert.ok(changed);assert.ok(data.includes('Edited after complete prefill'));assert.ok(!data.includes('Fresh initial optional'));}
    else if(changed&&data.includes('Fresh initial title'))defects.push(observed);
    release();await page.waitForTimeout(100);await context.close();
   }
  }finally{await browser.close();}
 }
 assert.equal(defects.length,0,'Initial values were stored before the existing response had loaded');
}
main().catch(e=>{console.error(e);process.exitCode=1;});
