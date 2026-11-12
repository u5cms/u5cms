const assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path');
const {launchBrowser}=require('./browser-runtime.cjs');
(async()=>{
 const browser=await launchBrowser();let checks=0;const errors=[];
 const check=(v,m)=>{assert.ok(v,m);checks++;};
 try{
  const page=await browser.newPage({viewport:{width:375,height:800}});page.on('pageerror',e=>errors.push(String(e)));
  const css=fs.readFileSync(path.join(__dirname,'../css/u5-responsive.css'),'utf8'),js=fs.readFileSync(path.join(__dirname,'../js/u5-responsive.js'),'utf8');
  await page.route('http://fixture.invalid/**',r=>r.fulfill({contentType:'text/html',body:'<!doctype html><html><head><style>'+css+'</style></head><body class="u5-responsive" data-u5-layout="responsive"><a id="skip" href="#navtarget">Skip to navigation</a><button id="u5-navigation-toggle" aria-controls="navigation" hidden>Navigation</button><div id="navigation"><a id="navtarget" name="namedtarget" href="#content">Link</a></div><main id="content" tabindex="-1">Content</main><a id="broken" href="http://[">Invalid authored link</a><a id="badfragment" href="#%zz">Malformed fragment</a><script>'+js+'</script></body></html>'}));
  await page.goto('http://fixture.invalid/');
  const toggle=page.locator('#u5-navigation-toggle'),nav=page.locator('#navigation');
  check(!await nav.isVisible()&&await toggle.getAttribute('aria-expanded')==='false','phone starts collapsed');
  await toggle.focus();await page.keyboard.press('Enter');check(await nav.isVisible(),'Enter opens');
  await page.locator('#navtarget').focus();await page.setViewportSize({width:1440,height:800});await page.setViewportSize({width:375,height:800});
  check(await nav.isVisible()&&await page.locator('#navtarget').evaluate(n=>n===document.activeElement),'focused navigation survives resize');
  await page.keyboard.press('Escape');check(await toggle.evaluate(n=>n===document.activeElement)&&!await nav.isVisible(),'Escape returns focus');
  await page.locator('#skip').click();check(await nav.isVisible(),'skip target reveals navigation before default jump');
  await toggle.click();await page.evaluate(()=>location.hash='namedtarget');await page.waitForFunction(()=>document.getElementById('u5-navigation-toggle').getAttribute('aria-expanded')==='true');checks++;
  await page.evaluate(js);await toggle.click();check(!await nav.isVisible(),'duplicate initialization does not duplicate handlers');
  await page.locator('#badfragment').click();check(errors.length===0,'malformed fragment is contained');
  await page.locator('#broken').evaluate(n=>n.addEventListener('click',e=>e.preventDefault()));await page.locator('#broken').click();
  check(errors.length===0,'invalid authored URL must not break the navigation handler: '+errors.join(';'));
  await page.goto('http://fixture.invalid/#navtarget');check(await nav.isVisible(),'initial fragment reveals navigation');
  await page.setViewportSize({width:1440,height:800});check(await nav.isVisible()&&await toggle.getAttribute('aria-expanded')==='true'&&!await toggle.isVisible(),'desktop navigation is visible with consistent aria state');
  console.log('Responsive navigation: '+checks+' checks passed on '+(process.env.U5_TEST_ENGINE||'chromium')+'.');
 }finally{await browser.close()}
})().catch(e=>{console.error(e);process.exitCode=1});
