const assert=require('node:assert/strict');
const {execFileSync}=require('node:child_process');
const {launchBrowser}=require('./browser-runtime.cjs');
function snapshot(reset=false,kind='shared'){return JSON.parse(execFileSync('php',['-d','mysqli.default_port='+process.env.U5CMS_TEST_PORT,process.env.U5_PREFILL_SNAPSHOT,...(reset?['reset',kind]:[])],{cwd:process.env.U5_PREFILL_SITE,encoding:'utf8'}));}
async function main(){const defects=[];
for(const engine of ['chromium','firefox']){
 process.env.U5_TEST_ENGINE=engine;const browser=await launchBrowser();
 try{for(const kind of ['shared','personal','append','new'])for(const mode of kind==='new'?['loaded']:['loaded','paused','aborted'])for(const trigger of ['button','enter','script']){
  const before=snapshot(true,kind),origin=process.env.U5_PREFILL_ORIGIN;
  const context=await browser.newContext({httpCredentials:{username:'auditadmin',password:process.env.U5CMS_FIXTURE_PASSWORD}});
  await context.route('**/*',route=>new URL(route.request().url()).origin===origin?route.continue():route.abort());
  const page=await context.newPage();page.on('dialog',d=>d.dismiss());page.on('pageerror',e=>console.log('MONO_PAGEERROR '+e.message));
  let release;const gate=new Promise(r=>release=r);let seen=false,posts=0;
  await page.route('**/formsave.php?**',async route=>{posts++;const response=await route.fetch();await route.fulfill({response});});
  const loader=(kind==='personal'||kind==='append')?'**/monofill.php?**':'**/monofillshared.php?**';
  if(mode!=='loaded')await page.route(loader,async route=>{seen=true;if(mode==='aborted')return route.abort('failed');await gate;try{await route.continue();}catch{}});
  const docResponse=await page.goto(origin+'/index.php?c=prefillaudit&l=en',{waitUntil:'domcontentloaded'});
  const raw=await docResponse.text();const at=raw.indexOf('monofill');if(mode==='loaded'&&trigger==='button')console.log('MONO_RAW '+JSON.stringify({kind,mode,excerpt:raw.slice(Math.max(0,at-200),at+900),guard:raw.includes('var ready = false'),frames:await page.locator('iframe').evaluateAll(es=>es.map(e=>({id:e.id,src:e.getAttribute('src')})))}));
  if(mode==='loaded'){
   if(kind==='new')await page.waitForLoadState('load');
   else await page.waitForFunction(()=>document.forms.u5form.title_mandatory.value.startsWith('Saved Genève'));
  }else {for(let i=0;i<100&&!seen;i++)await page.waitForTimeout(20);assert.ok(seen);}
  await page.waitForFunction(()=>Number(document.forms.u5form.firstsaverwins.value)>0);
  const values=await page.evaluate(()=>{const f=document.forms.u5form;return{title:f.title_mandatory.value,optional:f.optional.value,version:f.u5cms_formversion?.value||'',action:f.action,ed2cu:f.ed2cu?.value};});
  async function submit(){if(trigger==='script')await page.evaluate(()=>document.forms.u5form.submit());else if(trigger==='enter')await page.locator('[name="title_mandatory"]').press('Enter');else await page.getByRole('button',{name:'Save',exact:true}).click({noWaitAfter:true});}
  if(mode==='loaded'){const response=page.waitForResponse(r=>new URL(r.url()).pathname==='/formsave.php');await submit();assert.equal((await response).status(),200);}
  else {await submit();await page.waitForTimeout(350);}
  const after=snapshot(),active=after.filter(r=>Number(r.status)===1),changed=JSON.stringify(after)!==JSON.stringify(before);
  console.log('MONO_PREFILL_OBSERVATION '+JSON.stringify({revision:process.env.U5_AUDIT_REVISION,engine,kind,mode,trigger,values,posts,changed,rows:after.length,active:active.length}));
  if(mode!=='loaded'){
   if(posts||changed)defects.push({engine,kind,mode,trigger});
   release();
   if(!changed){
    if(mode==='aborted'){await page.unroute(loader);await page.reload({waitUntil:'domcontentloaded'});}
    await page.waitForFunction(()=>document.forms.u5form.title_mandatory.value.startsWith('Saved Genève'));
    assert.equal(await page.locator('[name="optional"]').inputValue(),'');
    const response=page.waitForResponse(r=>new URL(r.url()).pathname==='/formsave.php');await submit();assert.equal((await response).status(),200);
    const resumed=snapshot();assert.equal(resumed.length,2);assert.equal(resumed.filter(r=>Number(r.status)===1).length,kind==='append'?2:1);
    console.log('MONO_PREFILL_RESUMED '+JSON.stringify({engine,kind,mode,trigger,posts,rows:resumed.length}));
   }
  }else{
   assert.equal(posts,1);assert.ok(active.every(row=>row.authuser==='auditadmin'),'Authenticated ownership was not preserved');assert.equal(after.length,kind==='new'?1:2);assert.equal(active.length,kind==='append'?2:1);
   const data=Buffer.from(active[active.length-1].data,'hex').toString('utf8');
   assert.ok(data.includes(kind==='new'?'Fresh initial title':'Saved Gen'));
   if(kind!=='new')assert.ok(!data.includes('Fresh initial optional'));
  }
  release();await page.waitForTimeout(80);await context.close();
 }}finally{await browser.close();}
}assert.equal(defects.length,0,'Pending MF/MFS prefill allowed a premature submission');}
main().catch(e=>{console.error(e);process.exitCode=1;});
