const assert=require('node:assert/strict');
const path=require('path');
const {chromium}=require(process.env.U5_TEST_PLAYWRIGHT||'playwright');
(async()=>{
 const base=process.argv[2],seed=process.argv[3],screens=process.env.U5_DESIGN_SCREENSHOTS;
 const browser=await chromium.launch({headless:true,executablePath:process.env.U5_TEST_BROWSER,args:['--no-sandbox','--disable-dev-shm-usage']});
 let checks=0;const check=(v,m)=>{assert.ok(v,m);checks++;};
 try {
  for(const scenario of [{width:1440,mobile:false},{width:800,mobile:false},{width:375,mobile:true}]) {
   const context=await browser.newContext({viewport:{width:scenario.width,height:1000},screen:{width:scenario.width,height:1000}});
   if(!scenario.mobile&&scenario.width<1000)await context.addCookies([{url:base,name:'u5cmsfrcthsvw',value:'desktop'}]);
   const page=await context.newPage(),errors=[],external=[];
   page.on('pageerror',e=>errors.push(String(e)));
   await context.route('**/*',route=>{
    const url=new URL(route.request().url());
    if(url.origin===new URL(base).origin||url.protocol==='data:')return route.continue();
    external.push(url.href);return route.abort();
   });
   const response=await page.goto(base+'/?l=en&mobile='+(scenario.mobile?'on':'off'),{waitUntil:'networkidle'});
   check(response.status()===200,'seeded home renders');
   if(scenario.mobile)await page.waitForSelector('#contentinner');
   const dimensions=await page.evaluate(()=>({main:document.getElementById('main').getBoundingClientRect().width,content:document.getElementById('content').getBoundingClientRect().width,nav:document.getElementById('navigation').getBoundingClientRect().width,font:getComputedStyle(document.body).fontFamily,background:getComputedStyle(document.body).backgroundColor}));
   check(dimensions.font.includes('system-ui'),'local system typography');
   check(dimensions.background==='rgb(241, 243, 245)','quiet page background');
   if(!scenario.mobile){check(dimensions.main===1330,'fixed desktop width at '+scenario.width);check(dimensions.nav===190&&dimensions.content===811,'original desktop column geometry retained');}
   else {check(dimensions.main<=375&&dimensions.content<=375,'existing mobile script still sets widths');await page.locator('#webapp_monalinka').click();check(await page.locator('#webapp_monaclose.u5-mobile-tool').isVisible(),'inherited polished controls retained');await page.waitForSelector('#webapp_monafloat a.u5-mobile-tool');}
   if(screens&&scenario.width===1440)await page.screenshot({path:path.join(screens,seed+'-desktop.png'),fullPage:true});
   if(screens&&scenario.mobile)await page.screenshot({path:path.join(screens,seed+'-mobile.png'),fullPage:true});
   check(!external.some(u=>u.includes('fonts.googleapis.com')||u.includes('fonts.gstatic.com')),'default typography needs no external font service');
   const cssRules=await page.evaluate(()=>{
    const out=[];function walk(sheet){for(const rule of sheet.cssRules||[]){if(rule.styleSheet)walk(rule.styleSheet);if(rule.type===CSSRule.MEDIA_RULE)out.push(rule.conditionText);}}
    for(const sheet of document.styleSheets)try{if(sheet.href&&/\/r\/(?:base|layout|style|navleft|navtop|navleftsubtop|form|table)\.css/.test(sheet.href))walk(sheet);}catch(e){}
    return out;
   });
   check(cssRules.length===0,'no CSS media queries or responsive breakpoints');
   await page.goto(base+'/?c=codefixture&l=en&mobile='+(scenario.mobile?'on':'off'),{waitUntil:'networkidle'});
   await page.waitForSelector('#proof-form');
   await page.locator('#answer-proof').focus();await page.keyboard.press('Tab');await page.keyboard.press('Shift+Tab');
   check(await page.locator('#answer-proof').evaluate(e=>getComputedStyle(e).outlineWidth)==='3px','visible input keyboard focus');
   await page.locator('#answer-proof').fill('unchanged form workflow');
   check(await page.locator('#answer-proof').inputValue()==='unchanged form workflow','input remains editable');
   if(seed==='u5standard') {
    await page.goto(base+'/?c=formular&l=en&mobile='+(scenario.mobile?'on':'off'),{waitUntil:'networkidle'});
    const fieldset=page.locator('fieldset').first();await fieldset.waitFor();
    check(await fieldset.evaluate(e=>getComputedStyle(e).borderRadius)==='6px','real installed form has refined fieldset');
    const submit=page.locator('input[type=submit]').filter({visible:true}).last();
    check(await submit.evaluate(e=>getComputedStyle(e).backgroundColor)==='rgb(179, 38, 30)','real installed form has consistent submit accent');
    if(screens&&scenario.width===1440)await page.screenshot({path:path.join(screens,seed+'-form.png'),fullPage:true});
   }
   check(errors.length===0,'no script errors: '+errors.join('; '));
   await context.close();
  }
  console.log(seed+': default design '+checks+' browser checks passed (desktop 1440/800, script-mobile 375).');
 } finally{await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
