'use strict';
const assert=require('node:assert/strict');
const {launchBrowser}=require('./browser-runtime.cjs');
let input='';process.stdin.setEncoding('utf8');process.stdin.on('data',s=>input+=s);
process.stdin.on('end',async()=>{
 const fixture=JSON.parse(input),browser=await launchBrowser();let checks=0;
 const equal=(a,b,label)=>{assert.equal(a,b,label);checks++;};
 try {
  const context=await browser.newContext(fixture.mode==='basic' ? {httpCredentials:{username:fixture.user,password:fixture.password,send:'always',origin:fixture.base}} : {});
  await context.route('**/*',route=>new URL(route.request().url()).origin===new URL(fixture.base).origin ? route.continue() : route.abort());
  if(fixture.mode==='session'){
   const login=await context.request.post(fixture.base+'/loginsave.php?u='+encodeURIComponent('index.php?c=!reviewprocess&l=en'),{form:{u:fixture.user,p:fixture.password},maxRedirects:0});
   equal(login.status(),302,'reviewer session login');
  }
  const page=await context.newPage(),errors=[];page.on('pageerror',e=>errors.push(String(e)));
  await page.goto(fixture.base+'/index.php?c=!reviewprocess&l=en',{waitUntil:'domcontentloaded'});
  const link=page.locator('a[href*="!reviewscore"][href*="id='+fixture.id+'"]');
  equal(await link.count(),1,'assigned pending abstract has one real review link');
  for(const [round,comment] of ['BROWSER-FIRST-'+fixture.mode,'BROWSER-EDITED-'+fixture.mode].entries()){
   const opened=context.waitForEvent('page');await link.click();const review=await opened;
   review.on('pageerror',e=>errors.push(String(e)));review.on('dialog',d=>d.dismiss());
   await review.waitForLoadState('domcontentloaded');
   await review.waitForFunction(()=>document.u5form && new URL(document.u5form.action).searchParams.has('o'));
   equal(await review.locator('[name=id_mandatory]').inputValue(),fixture.id,'real form binds the requested abstract');
   equal((await review.locator('[name=title_mandatory]').inputValue()).trim(),'Conference Beta','review form loads the assigned title');
   if(round===1)equal(await review.locator('[name=comments]').inputValue(),'BROWSER-FIRST-'+fixture.mode,'monofill restores the saved review for editing');
   await review.locator('[name=content_relevance_mandatory]').selectOption('5');
   await review.locator('[name=content_methodic_mandatory]').selectOption('4');
   await review.locator('[name=content_conclusion_mandatory]').selectOption('3');
   await review.locator('[name=comments]').fill(comment);
   const closed=review.waitForEvent('close');await review.locator('form[name=u5form] input[type=submit]').click();await closed;checks++;
  }
  equal(errors.length,0,'conference review pages have no browser exceptions: '+errors.join('; '));
  await context.close();process.stdout.write(JSON.stringify({checks}));
 } finally {await browser.close();}
});
