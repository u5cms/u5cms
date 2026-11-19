'use strict';
const assert=require('node:assert/strict');
const {launchBrowser}=require('./browser-runtime.cjs');
let input='';process.stdin.setEncoding('utf8');process.stdin.on('data',s=>input+=s);
process.stdin.on('end',async()=>{
 const f=JSON.parse(input),browser=await launchBrowser();let checks=0;
 const equal=(a,b,label)=>{assert.equal(a,b,label);checks++;};
 const truth=(v,label)=>{assert.ok(v,label);checks++;};
 try {
  const context=await browser.newContext();
  await context.route('**/*',route=>new URL(route.request().url()).origin===new URL(f.base).origin ? route.continue() : route.abort());
  const postedBodies=[];
  // Retain the exact actual response before its script closes/navigates the
  // popup. Fulfil unchanged bytes; checking after navigation loses the body.
  await context.route('**/formsave.php*',async route=>{
   if(new URL(route.request().url()).origin!==new URL(f.base).origin)return route.abort();
   const response=await route.fetch({maxRedirects:0}),body=await response.body();
   postedBodies.push(body.toString('latin1'));await route.fulfill({response,body});
  });
  const login=async user=>{
   const response=await context.request.post(f.base+'/loginsave.php?u='+encodeURIComponent('index.php?c=!reviewprocess&l='+f.language),{form:{u:user,p:f.password},maxRedirects:0});
   equal(response.status(),302,'session login succeeds');
  };
  const list=await context.newPage(),dialogs=[];
  const open=async(id,title,absent)=>{
   await list.goto(f.base+'/index.php?c=!reviewprocess&l='+f.language,{waitUntil:'domcontentloaded'});
   const text=await list.locator('body').innerText();truth(text.includes(title),'assigned title is visible');truth(!text.includes(absent),'other assignment is hidden');
   const link=list.locator('a[href*="!reviewscore"][href*="id='+id+'"]');equal(await link.count(),1,'one real review link');
   const opened=context.waitForEvent('page');await link.click();const review=await opened;
   review.on('dialog',async d=>{dialogs.push(d.message());await d.dismiss();});
   await review.waitForLoadState('domcontentloaded');await review.waitForFunction(()=>document.u5form && new URL(document.u5form.action).searchParams.has('o'));
   equal(await review.locator('[name=id_mandatory]').inputValue(),id,'loaded review binds the assigned record');
   return review;
  };
  const fill=async(page,comment)=>{
   for(const [name,value] of [['content_relevance_mandatory','5'],['content_methodic_mandatory','4'],['content_conclusion_mandatory','3']])await page.locator('[name='+name+']').selectOption(value);
   await page.locator('[name=comments]').fill(comment);
  };
  const save=async page=>{const closed=page.waitForEvent('close');await page.locator('form[name=u5form] input[type=submit]').click();await closed;checks++;};
  await login(f.userA);const old=await open(f.idA,'TRANSITION-ALPHA','TRANSITION-BETA');await fill(old,'UNSAVED-BEFORE-ACCOUNT-SWITCH');
  const originalAction=await old.locator('form[name=u5form]').getAttribute('action');
  await login(f.userB);
  const rejected=old.waitForResponse(r=>r.request().method()==='POST' && r.url().includes('/formsave.php'));
  await old.locator('form[name=u5form] input[type=submit]').click();await rejected;const body=postedBodies.at(-1);
  truth(body.includes('Login Error.'),'old identity action is explicitly rejected after account switch');truth(!body.includes('Fatal error'),'identity rejection completes without a PHP abort');
  equal(await old.locator('form[name=u5form]').getAttribute('action'),originalAction,'old tab retains its original identity-bound action');await old.close();
  const b=await open(f.idB,'TRANSITION-BETA','TRANSITION-ALPHA');equal(await b.locator('[name=comments]').inputValue(),'','second reviewer does not inherit the unsaved first review');await fill(b,'CONFIRMED-B');await save(b);
  await login(f.userA);const a=await open(f.idA,'TRANSITION-ALPHA','TRANSITION-BETA');equal(await a.locator('[name=comments]').inputValue(),'','rejected review was not persisted');await fill(a,'RECOVERED-A');await save(a);
  // A second page is deliberately kept open across actual cookie removal.
  const expired=await open(f.idA,'TRANSITION-ALPHA','TRANSITION-BETA');equal(await expired.locator('[name=comments]').inputValue(),'RECOVERED-A','review can be loaded after identity recovery');await fill(expired,'MUST-NOT-SAVE-AFTER-LOGOUT');
  await context.clearCookies();const denied=expired.waitForResponse(r=>r.request().method()==='POST' && r.url().includes('/formsave.php'));
  await expired.locator('form[name=u5form] input[type=submit]').click();const deniedResponse=await denied;
  truth(deniedResponse.status()<500,'logged-out submission does not return a server error');
  await expired.waitForURL(url=>url.searchParams.get('c')==='_login');checks++;
  truth(!(await expired.locator('body').innerText()).includes('Fatal error'),'actual authentication page loads after logged-out submission');
  await context.close();process.stdout.write(JSON.stringify({checks,language:f.language,identity_denial:true,logout_denial:true}));
 }finally{await browser.close();}
});
