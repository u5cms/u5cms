const assert=require('node:assert/strict');
const fs=require('node:fs'),path=require('node:path');
const {launchBrowser}=require('./browser-runtime.cjs');
(async()=>{
 const [,,base,seed,site]=process.argv,browser=await launchBrowser();
 let checks=0;const check=(v,m)=>{assert.ok(v,m);checks++;};
 const shots=process.env.U5_RESPONSIVE_SCREENSHOTS;if(shots)fs.mkdirSync(shots,{recursive:true});
 try {
  for(const javascript of [true,false]) {
   const context=await browser.newContext({viewport:{width:1440,height:1000},screen:{width:1920,height:1080},javaScriptEnabled:javascript});
   const requests=[],errors=[];
   await context.route('**/*',r=>{requests.push(r.request().url());return new URL(r.request().url()).origin===new URL(base).origin?r.continue():r.abort();});
   const page=await context.newPage();page.on('pageerror',e=>errors.push(String(e)));
   await context.addCookies([{url:base,name:'u5cmsfrcthsvw',value:'desktop'}]);
   let response=await page.goto(base+'/?c=responsivefixture&l=en',{waitUntil:'networkidle'});
   check(response.status()===200,'real CMS responsive page');
   if(shots){fs.writeFileSync(path.join(shots,seed+'-initial.html'),await page.content());await page.screenshot({path:path.join(shots,seed+'-initial.png'),fullPage:true});}
   check(await page.locator('#responsive-name').count()===1,'fixture form exists: '+(await page.locator('#content').innerText()).slice(-1200));
   if(javascript){
    await page.locator('#responsive-name').fill('Anna');await page.locator('#responsive-comment').fill('Keep my text');await page.locator('#responsive-checkbox').check();
    await page.evaluate(()=>{window.keptFrame=document.getElementById('responsive-save');window.keptMedia=document.querySelector('.u5-video-frame');window.keptNavigation=document.getElementById('navLeft');});
   }
   for(const width of [1440,1024,800,768,375,320,1920,320]) {
    await page.setViewportSize({width,height:1000});
    const geometry=await page.evaluate(()=>({vw:innerWidth,doc:document.documentElement.scrollWidth,main:document.getElementById('main').getBoundingClientRect().width,columns:getComputedStyle(document.getElementById('outer')).gridTemplateColumns.split(' ').length,content:document.getElementById('content').getBoundingClientRect(),video:(()=>{const r=document.querySelector('.u5-video-frame').getBoundingClientRect();return {w:r.width,h:r.height,ratio:Number(document.querySelector('.u5-video-frame').getAttribute('width'))/Number(document.querySelector('.u5-video-frame').getAttribute('height'))}})()}));
    check(geometry.doc<=width+1,'page fits '+width+' JS='+javascript+' '+JSON.stringify(geometry));
    check(geometry.main<=1330&&geometry.main<=width,'fluid capped main '+width);
    check(geometry.columns===(width>1088?3:width>768?2:1),'column breakpoint '+width);
    check(geometry.video.w>0&&Math.abs(geometry.video.w/geometry.video.h-geometry.video.ratio)<.02,'configured YouTube aspect ratio preserved '+width+' '+JSON.stringify(geometry.video));
    check(await page.locator('#dat-grid tbody').innerText()==='Anna\t42','dat table values/structure preserved');
    check(await page.locator('#wide-grid td').last().innerText()==='End','wide table end retained');
    check(await page.locator('.albumhorizontal img').first().evaluate(n=>Math.abs(n.getBoundingClientRect().height-Number(n.getAttribute('height')))<1),'horizontal album keeps configured height '+width);
    check(await page.locator('.dlAlbumLarge > dt > div').first().evaluate(n=>n.getBoundingClientRect().width<=document.getElementById('content').clientWidth),'album scrolls within content '+width);
    if(javascript){
     check(await page.evaluate(()=>window.responsiveForm===document.getElementById('responsive-form')&&window.keptFrame===document.getElementById('responsive-save')&&window.keptMedia===document.querySelector('.u5-video-frame')&&window.keptNavigation===document.getElementById('navLeft')),'nodes unchanged on resize '+width);
     check(await page.locator('#responsive-name').inputValue()==='Anna'&&await page.locator('#responsive-comment').inputValue()==='Keep my text'&&await page.locator('#responsive-checkbox').isChecked(),'form values unchanged '+width);
    } else check(await page.locator('#navigation').isVisible(),'navigation available without JS');
    if(shots&&[1440,375].includes(width)){
     // Fixed off-screen skip links must be captured relative to the page top.
     await page.evaluate(()=>scrollTo(0,0));
     await page.screenshot({path:path.join(shots,seed+'-'+width+'-'+javascript+'.png'),fullPage:true});
    }
   }
   if(javascript){
    await page.locator('#responsive-form').dispatchEvent('proof');check(await page.evaluate(()=>window.responsiveEvents===1),'original event handler retained');
    const toggle=page.locator('#u5-navigation-toggle');await toggle.click();
    check(await page.locator('#navigation').isVisible()&&await toggle.getAttribute('aria-expanded')==='true','navigation disclosure');
    await page.locator('#navLeft a').first().focus();await page.keyboard.press('Escape');
    check(await toggle.getAttribute('aria-expanded')==='false'&&await toggle.evaluate(n=>document.activeElement===n),'Escape closes and restores focus');
    await toggle.focus();await page.keyboard.press('Space');check(await toggle.getAttribute('aria-expanded')==='true','native Space activation');
    await toggle.click();await page.evaluate(()=>location.hash='navigation');
    await page.waitForFunction(()=>document.getElementById('u5-navigation-toggle').getAttribute('aria-expanded')==='true');checks++;
    await page.locator('#responsive-submit').click();await page.waitForFunction(()=>window.responsiveWasSaved===true);checks++;
    check(!requests.some(u=>/\/(mobile|mobilebase)\.js|jsmobile/.test(u)),'no legacy mobile script requested');
    check(!await page.locator('#contentinner,#webapp_mona').count(),'no mobile conversion DOM');
    // The four real file-upload frames survive repeated layout changes.
    await page.goto(base+'/?c=codefixture&l=en',{waitUntil:'networkidle'});
    await page.waitForFunction(()=>document.querySelectorAll('iframe[name^="iuserupload"]').length===4);
    await page.evaluate(()=>window.uploadNodes=Array.from(document.querySelectorAll('iframe[name^="iuserupload"]')));
    const upload=page.frames().find(f=>/\/upload\/upload.php/.test(f.url()));await upload.waitForSelector('input[type=file]');
    await upload.locator('input[type=file]').setInputFiles({name:'responsive.pdf',mimeType:'application/pdf',buffer:Buffer.from('%PDF-1.4\nfixture\n%%EOF')});
    await page.waitForFunction(()=>Array.from(document.querySelectorAll('input[id^="userupload"]')).some(n=>n.value));
    const values=await page.locator('input[id^="userupload"]').evaluateAll(ns=>ns.map(n=>n.value));
    for(const width of [1440,375,800,320])await page.setViewportSize({width,height:1000});
    check(await page.evaluate(()=>window.uploadNodes.every((n,i)=>n===document.querySelectorAll('iframe[name^="iuserupload"]')[i])),'upload iframe identity preserved');
    assert.deepEqual(await page.locator('input[id^="userupload"]').evaluateAll(ns=>ns.map(n=>n.value)),values);checks++;
    for(const [lang,label] of [['de','Navigation'],['fr','Navigation'],['it','Navigazione'],['rm','Navigaziun'],['en','Navigation']]){
     await page.goto(base+'/?c=responsivefixture&l='+lang,{waitUntil:'domcontentloaded'});check(await toggle.textContent()===label,'navigation language '+lang);
    }
    await page.goto(base+'/?c=_search&l=en&q=Responsive',{waitUntil:'networkidle'});check(await page.locator('#frm_Search2').isVisible(),'responsive actual search page');
    check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),'search fits phone');
    await page.emulateMedia({media:'print'});check(!await toggle.isVisible(),'print hides disclosure');await page.emulateMedia({media:'screen'});
   }
   check(errors.length===0,'no JS errors '+errors.join(';'));await context.close();
  }
  const context=await browser.newContext({viewport:{width:375,height:1000}}),page=await context.newPage();
  await context.route('**/*',r=>new URL(r.request().url()).origin===new URL(base).origin?r.continue():r.abort());
  await page.goto(base+'/fixture-split.php');await page.goto(base+'/?c=responsivefixture&l=en',{waitUntil:'networkidle'});
  check(await page.locator('#navTop').isVisible(),'split top navigation visible');
  await page.locator('#u5-navigation-toggle').click();check(await page.locator('#navLeftSubTop > ul > li > a').first().isVisible(),'split root navigation available on phone');
  check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),'split navigation fits');
  await context.close();console.log(seed+': '+checks+' responsive browser checks passed.');
 } finally{await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
