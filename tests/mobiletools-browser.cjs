const fs = require('fs');
const assert = require('node:assert/strict');
const {chromium} = require(process.env.U5_TEST_PLAYWRIGHT || 'playwright');
(async () => {
 const base=process.argv[2], shot=process.env.U5_MOBILE_SCREENSHOT;
 const browser=await chromium.launch({headless:true,executablePath:process.env.U5_TEST_BROWSER,args:['--no-sandbox','--disable-dev-shm-usage']});
 let checks=0;
 const check=(actual,message)=>{assert.ok(actual,message);checks++;};
 try {
  for(const [lang,label] of [['de','Navigation schliessen'],['fr','Fermer la navigation'],['it','Chiudi la navigazione'],['rm','Serrar la navigaziun'],['en','Close navigation']]) {
   const context=await browser.newContext({viewport:{width:375,height:850},screen:{width:375,height:850}});
   const page=await context.newPage(),errors=[];page.on('pageerror',e=>errors.push(String(e)));
   await page.goto(base+'/?c=codefixture&l='+lang+'&mobile=on');
   // SQL defaults have only three configured languages; exercise all five DOM language contracts.
   await page.evaluate(l=>document.documentElement.lang=l,lang);
   await page.waitForSelector('#webapp_monaclose.u5-mobile-tool',{state:'attached'});
   await page.locator('#webapp_monalinka').click();
   await page.waitForSelector('#webapp_monafloat a.u5-mobile-tool');
   const controls=page.locator('#webapp_monafloat .u5-mobile-tool');
   check(await controls.count()===3,lang+': three controls');
   check(await page.locator('#webapp_monaclose').getAttribute('aria-label')===label,lang+': translated accessible label');
   const geometry=await controls.evaluateAll(nodes=>nodes.map(n=>({x:n.getBoundingClientRect().x,w:n.getBoundingClientRect().width,h:n.getBoundingClientRect().height,r:getComputedStyle(n).borderRadius,svg:n.querySelectorAll('svg').length,handler:n.getAttribute('onclick')})));
   for(const g of geometry) check(g.w===44&&g.h===44&&g.r==='9px'&&g.svg===1,lang+': real dimensions, corners and SVG');
   check(geometry.every(g=>g.x>=5&&g.x+g.w<=375),lang+': targets and focus outlines fit the viewport');
   check(geometry[0].handler.includes('webapp_mona'),lang+': original close handler retained');
   const close=page.locator('#webapp_monaclose');await close.hover();
   check(await close.evaluate(e=>getComputedStyle(e).backgroundColor)==='rgb(201, 44, 28)',lang+': close hover');
   await close.focus();await page.keyboard.press('Tab');await page.keyboard.press('Shift+Tab');
   check(await close.evaluate(e=>getComputedStyle(e).outlineWidth)==='3px',lang+': visible keyboard focus');
   if(shot&&lang==='de')await page.screenshot({path:shot,fullPage:true});
   await page.keyboard.press('Space');
   check(await page.locator('#webapp_mona').evaluate(e=>getComputedStyle(e).display)==='none',lang+': Space closes navigation');
   await page.waitForFunction(()=>document.activeElement.id==='webapp_monalinka');checks++;
   await page.locator('#webapp_monalinka').click();await close.focus();await page.keyboard.press('Enter');
   check(await page.locator('#webapp_mona').evaluate(e=>getComputedStyle(e).display)==='none',lang+': Enter closes navigation');
   await page.locator('#webapp_monalinkabtm').click();
   check(await page.locator('#webapp_mona').evaluate(e=>getComputedStyle(e).display)!=='none',lang+': bottom opener still works');
   check(await page.locator('#webapp_monafloat a').getAttribute('href')==='?c=_search',lang+': search URL retained');
   // The legacy timer rebuilds innerHTML, destroying listeners on individual control nodes.
   await page.evaluate(()=>{const b=document.getElementById('webapp_monafloat');b.innerHTML=b.innerHTML;});
   await page.waitForFunction(()=>document.querySelectorAll('#webapp_monafloat .u5-mobile-tool svg').length===3);checks++;
   if(lang==='en') {
    await page.waitForTimeout(10500);
    await page.evaluate(()=>{const b=document.getElementById('webapp_monafloat');b.querySelector('a').parentElement.remove();b.insertAdjacentHTML('beforeend','<div><a href="?c=_search&q=late">late</a></div>');});
    await page.waitForSelector('#webapp_monafloat a[data-u5-mobile-icon="search"]');checks++;
    const desktop=page.locator('#webapp_monafloat #webapp_monadesktop');await desktop.focus();
    await Promise.all([page.waitForNavigation(),page.keyboard.press('Enter')]);
    check((await context.cookies()).some(c=>c.name==='u5cmsfrcthsvw'&&c.value==='desktop'),'desktop cookie retained');
    check(await page.locator('#webapp_monafloat').count()===0,'desktop navigation retained');
   }
   check(errors.length===0,lang+': no JavaScript exceptions: '+errors.join('; '));
   await context.close();
  }
  const context=await browser.newContext({viewport:{width:1280,height:850},screen:{width:1280,height:850}});
  const page=await context.newPage();await page.goto(base+'/?c=codefixture&mobile=off');
  check(await page.locator('#u5-mobile-tools-enhancement-style').count()===0,'desktop is not styled');
  await context.close();
  console.log('Mobile tools browser: '+checks+' checks passed (real CMS, five languages, keyboard, delayed search, toolbar replacement, desktop switch).');
 } finally {await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
