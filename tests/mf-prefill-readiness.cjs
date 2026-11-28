const assert=require('node:assert/strict');
const {execFileSync}=require('node:child_process');
const {launchBrowser}=require('./browser-runtime.cjs');
function snapshot(reset=false){return JSON.parse(execFileSync('php',['-d','mysqli.default_port='+process.env.U5CMS_TEST_PORT,process.env.U5_PREFILL_SNAPSHOT,...(reset?['reset']:[])],{cwd:process.env.U5_PREFILL_SITE,encoding:'utf8'}));}
async function main(){const defects=[];
for(const engine of ['chromium','firefox']){
 process.env.U5_TEST_ENGINE=engine;const browser=await launchBrowser();
 try{for(const mode of ['loaded','paused','aborted']){
  const before=snapshot(true),origin=process.env.U5_PREFILL_ORIGIN;
  const context=await browser.newContext({httpCredentials:{username:'auditadmin',password:process.env.U5CMS_FIXTURE_PASSWORD}});
  await context.route('**/*',route=>new URL(route.request().url()).origin===origin?route.continue():route.abort());
  const page=await context.newPage();page.on('dialog',d=>d.dismiss());
  let release;const gate=new Promise(r=>release=r);let seen=false,posts=0;
  await page.route('**/formsave.php?**',async route=>{posts++;const response=await route.fetch();await route.fulfill({response});});
  if(mode!=='loaded')await page.route('**/monofillshared.php?**',async route=>{seen=true;if(mode==='aborted')return route.abort('failed');await gate;try{await route.continue();}catch{}});
  await page.goto(origin+'/index.php?c=prefillaudit&l=en',{waitUntil:'domcontentloaded'});
  if(mode==='loaded')await page.waitForFunction(()=>document.forms.u5form.title_mandatory.value.startsWith('Saved Genève'));
  else {for(let i=0;i<100&&!seen;i++)await page.waitForTimeout(20);assert.ok(seen);}
  await page.waitForFunction(()=>Number(document.forms.u5form.firstsaverwins.value)>0);
  const values=await page.evaluate(()=>{const f=document.forms.u5form;return{title:f.title_mandatory.value,optional:f.optional.value,version:f.u5cms_formversion?.value||'',action:f.action,ed2cu:f.ed2cu?.value};});
  const response=page.waitForResponse(r=>new URL(r.url()).pathname==='/formsave.php');await page.getByRole('button',{name:'Save',exact:true}).click({noWaitAfter:true});assert.equal((await response).status(),200);
  const after=snapshot();const active=after.filter(r=>Number(r.status)===1),data=Buffer.from(active[0].data,'hex').toString('utf8');
  console.log('MFS_PREFILL_OBSERVATION '+JSON.stringify({engine,mode,values,posts,rows:after.length,active:active.length,data}));
  if(mode!=='loaded'&&data.includes('Fresh initial title'))defects.push({engine,mode});
  if(mode==='loaded')assert.ok(data.includes('Saved Gen'));
  release();await page.waitForTimeout(80);await context.close();
 }}finally{await browser.close();}
}assert.equal(defects.length,0,'Pending MFS prefill allowed initial values to replace the existing response');}
main().catch(e=>{console.error(e);process.exitCode=1;});
