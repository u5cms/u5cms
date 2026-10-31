const assert=require('node:assert/strict'),fs=require('fs'),path=require('path');
const {launchBrowser}=require('./browser-runtime.cjs');
(async()=>{
 const [, ,base,site]=process.argv,browser=await launchBrowser();let checks=0;
 const equal=(a,b,label)=>{assert.equal(a,b,label);checks++;},ok=(a,label)=>{assert.ok(a,label);checks++;};
 const config=path.join(site,'config.php'),original=fs.readFileSync(config);
 const setMode=mode=>fs.writeFileSync(config,fs.readFileSync(config).toString('latin1').replace(/\$usesessioninsteadofbasicauth\s*=\s*'(?:yes|no)'/,"$usesessioninsteadofbasicauth='"+mode+"'"),'latin1');
 try {
  for(const mode of ['basic','session']){
   setMode(mode==='basic'?'no':'yes');
   const context=await browser.newContext(mode==='basic'?{httpCredentials:{username:'auditadmin',password:'audit-password',send:'always',origin:base}}:{});
   await context.route('**/*',r=>new URL(r.request().url()).origin===new URL(base).origin?r.continue():r.abort());
   if(mode==='session')equal((await context.request.post(base+'/loginsave.php?u=u5admin/',{form:{u:'auditadmin',p:'audit-password'},maxRedirects:0})).status(),302,'real session login');
   const page=await context.newPage(),errors=[];page.on('pageerror',e=>errors.push(e.message));page.on('dialog',d=>d.dismiss());
   await context.addCookies(['i1_p','i2_p','i1_l','i2_l'].map(name=>({name,value:name.endsWith('_p')?'textareatest':'1',url:base})));
   await page.goto(base+'/u5admin/index.php',{waitUntil:'domcontentloaded'});
   await page.waitForFunction(()=>['i1','i2'].every(name=>{const el=document.querySelector('iframe[name='+name+']');return el && el.contentWindow && el.contentWindow.u5TextareaActions;}));
   const left=page.frame({name:'i1'}),right=page.frame({name:'i2'});
   for(const f of [left,right])await f.waitForFunction(()=>window.u5TextareaActions && document.form1.page.value==='textareatest');
   const ta=left.locator('#ta1');
   ok(await left.locator('#u5-textarea-trigger').isVisible(),mode+': compact trigger is visible');
   ok(await left.evaluate(()=>u5TextareaConfig.formats.some(x=>x.open==='[b]' && x.close==='[/]')),mode+': formats use real insert definitions');
   const initial='Lead sample text [textareatarget] tail';await ta.fill(initial);
   await left.evaluate(()=>{ta1.focus();ta1.setSelectionRange(5,11);});await ta.press('Alt+Enter');
   await left.getByRole('button',{name:'Bold',exact:true}).click();
   equal(await ta.inputValue(),'Lead [b]sample[/] text [textareatarget] tail',mode+': selected text is formatted');
   equal(await right.locator('#ta1').inputValue(),await ta.inputValue(),mode+': formatting synchronizes both editors');
   ok(await left.locator('#star').isVisible(),mode+': formatting marks the source dirty');
   await ta.press('Control+z');equal(await ta.inputValue(),initial,mode+': format undo');
   await ta.press('Control+Shift+z');ok((await ta.inputValue()).includes('[b]sample[/]'),mode+': format redo');
   await ta.press('Control+z');
   // Mouse menu and compact toolbar keep the selected source unchanged until an action.
   await left.evaluate(()=>{ta1.focus();ta1.setSelectionRange(5,11);ta1.dispatchEvent(new MouseEvent('contextmenu',{bubbles:true,cancelable:true,clientX:20,clientY:100}));});
   await left.getByRole('dialog',{name:'Text and object actions'}).press('Escape');
   equal(await left.locator('.u5-textarea-menu').count(),0,mode+': Escape closes the keyboard dialog');
   await left.locator('#u5-textarea-trigger').click();await left.getByRole('button',{name:'Close text actions',exact:true}).click();
   equal(await ta.inputValue(),initial,mode+': mouse and toolbar actions leave the source intact');
   equal(await ta.evaluate(el=>el.selectionStart),5,mode+': closing returns the selection start');
   equal(await ta.evaluate(el=>el.selectionEnd),11,mode+': closing returns the selection end');
   await left.evaluate(()=>{ta1.focus();ta1.dispatchEvent(new CompositionEvent('compositionstart',{bubbles:true}));ta1.value='first composing value';ta1.dispatchEvent(new InputEvent('input',{bubbles:true,inputType:'insertCompositionText',isComposing:true}));ta1.value='completed composition';ta1.dispatchEvent(new InputEvent('input',{bubbles:true,inputType:'insertCompositionText',isComposing:true}));ta1.dispatchEvent(new CompositionEvent('compositionend',{bubbles:true}));});
   await ta.press('Control+z');equal(await ta.inputValue(),initial,mode+': an IME composition is one undo transaction');
   await ta.press('End');await ta.press('x');const typed=await ta.inputValue();
   await ta.press('Control+z');equal(await ta.inputValue(),initial,mode+': ordinary typing undo');
   await ta.press('Control+y');equal(await ta.inputValue(),typed,mode+': ordinary typing redo');
   await ta.fill(initial);
   for(const raw of ['[textareatarget]','[$$$:textareatarget]','[human:textareatarget]','[:human:textareatarget]','[Time: 12:30:textareatarget?l=fr&x=2]']){
    await ta.fill('before '+raw+' after');
    await left.evaluate(()=>{ta1.focus();ta1.setSelectionRange(10,10);});await ta.press('Alt+Enter');
    const edit=left.getByRole('link',{name:'Edit',exact:true});await edit.waitFor();
    const href=new URL(await edit.getAttribute('href'));
    equal(href.pathname,'/edit.php',mode+': every P syntax offers root Edit');equal(href.searchParams.get('n'),'textareatarget',mode+': Edit uses the exact object name');
    equal(await edit.getAttribute('target'),'_blank',mode+': Edit uses a new tab');ok((await edit.getAttribute('rel')).includes('noopener'),mode+': Edit blocks opener access');
    await left.getByRole('button',{name:'Replace target',exact:true}).click();
    await left.getByLabel('Object name',{exact:true}).fill('codefixture');await left.getByRole('button',{name:'Apply',exact:true}).click();
    await left.waitForFunction(()=>!document.querySelector('.u5-textarea-menu'));
    equal(await ta.inputValue(),'before '+raw.replace('textareatarget','codefixture')+' after',mode+': replacement preserves label, inclusion/button and query');
    await ta.press('Control+z');equal(await ta.inputValue(),'before '+raw+' after',mode+': replacement undo');
    await ta.press('Control+y');equal(await right.locator('#ta1').inputValue(),await ta.inputValue(),mode+': redo synchronizes both editors');
   }
   await ta.fill('[:old label:textareatarget?l=fr]');await left.evaluate(()=>{ta1.focus();ta1.setSelectionRange(5,5);});await ta.press('Alt+Enter');
   await left.getByRole('button',{name:'Link text',exact:true}).click();await left.getByLabel('Link text',{exact:true}).fill('New: label');await left.getByRole('button',{name:'Apply',exact:true}).click();
   equal(await ta.inputValue(),'[:New: label:textareatarget?l=fr]',mode+': label editing preserves button, target and query');
   await left.evaluate(()=>{ta1.focus();ta1.setSelectionRange(5,5);});await ta.press('Alt+Enter');await left.getByRole('button',{name:'Remove reference',exact:true}).click();
   equal(await ta.inputValue(),'New: label',mode+': remove reference retains an explicit visible label');await ta.press('Control+z');
   await ta.fill('[missingtextarea]');await left.evaluate(()=>{ta1.focus();ta1.setSelectionRange(4,4);});await ta.press('Alt+Enter');
   await left.getByText('Object not found: missingtextarea',{exact:true}).waitFor();
   await left.getByRole('button',{name:'Replace target',exact:true}).click();await left.getByLabel('Object name',{exact:true}).fill('also_missing');await left.getByRole('button',{name:'Apply',exact:true}).click();
   await left.getByRole('status').filter({hasText:'Object not found'}).waitFor();equal(await ta.inputValue(),'[missingtextarea]',mode+': invalid replacement leaves the source intact');
   await left.getByLabel('Object name',{exact:true}).fill('textareatarget');await left.getByRole('button',{name:'Apply',exact:true}).click();
   await left.waitForFunction(()=>!document.querySelector('.u5-textarea-menu'));equal(await ta.inputValue(),'[textareatarget]',mode+': typed valid names work without selecting a suggestion');
   await left.evaluate(()=>{ta1.focus();ta1.setSelectionRange(4,4);});await ta.press('Alt+Enter');await left.getByRole('button',{name:'Uses',exact:true}).click();
   await left.getByRole('link',{name:'textareatest',exact:true}).waitFor();ok(true,mode+': uses come from stored references');
   await left.getByRole('button',{name:'Close text actions',exact:true}).click();
   await ta.fill('[accent!js]');await left.evaluate(()=>{ta1.focus();ta1.setSelectionRange(4,4);});await ta.press('Alt+Enter');
   await left.getByRole('link',{name:'Edit',exact:true}).waitFor();ok((await left.getByRole('link',{name:'Edit',exact:true}).getAttribute('href')).includes('coding.php?name=accent%21js'),mode+': C object opens the code editor');
   await left.getByRole('button',{name:'Close text actions',exact:true}).click();
   // Delayed lookup must never overwrite source changed after opening the menu.
   await ta.fill('[textareatarget]');await left.evaluate(()=>{ta1.focus();ta1.setSelectionRange(4,4);});await ta.press('Alt+Enter');
   await left.getByRole('button',{name:'Replace target',exact:true}).click();await left.getByLabel('Object name',{exact:true}).fill('codefixture');
   await left.evaluate(()=>{ta1.value='Changed independently';});await left.getByRole('button',{name:'Apply',exact:true}).click();
   await left.getByRole('status').filter({hasText:'source changed'}).waitFor();equal(await ta.inputValue(),'Changed independently',mode+': stale action cannot overwrite a new value');
   await left.getByRole('button',{name:'Close text actions',exact:true}).click();
   // All five language slots share source actions, without altering the other slots.
   for(let language=1;language<=5;language++){
    await left.evaluate(n=>{lview(String(n));},language);
    const field=left.locator('#ta'+language);await field.fill('Language '+language);await field.evaluate(el=>{el.focus();el.setSelectionRange(0,8);});await field.press('Alt+Enter');
    await left.getByRole('button',{name:'Bold',exact:true}).click();equal(await field.inputValue(),'[b]Language[/] '+language,mode+': language '+language+' source format');
    equal(await right.locator('#ta'+language).inputValue(),await field.inputValue(),mode+': language '+language+' peer value');
   }
   await left.evaluate(()=>lview('1'));await ta.fill('Persist [b]sample[/] [textareatarget]');
   await left.locator('#savebutton').click();await left.waitForFunction(()=>!document.getElementById('star'),{timeout:15000});
   await page.reload({waitUntil:'domcontentloaded'});await page.waitForFunction(()=>document.querySelector('iframe[name=i1]') && document.querySelector('iframe[name=i1]').contentWindow.u5TextareaActions);const reloaded=page.frame({name:'i1'});await reloaded.waitForFunction(()=>window.u5TextareaActions);
   equal(await reloaded.locator('#ta1').inputValue(),'Persist [b]sample[/] [textareatarget]',mode+': real save and reopen preserve the new markup');
   for(const value of ['../config','bad/name','bad-name','a'.repeat(256)])equal((await context.request.get(base+'/u5admin/textarea-resource.php?object='+encodeURIComponent(value))).status(),400,mode+': reject unsafe object '+value.slice(0,20));
   equal((await context.request.get(base+'/u5admin/textarea-resource.php?object[]=x')).status(),400,mode+': reject array-valued names');
   const response=await context.request.get(base+'/u5admin/textarea-resource.php?object=textareadoc&language=de');const data=await response.json();
   ok(data.original && data.original.startsWith('../f.php?f='),mode+': original downloads retain the existing access proxy');equal(response.headers()['cache-control'],'no-store',mode+': no object-information cache');
   const search=await (await context.request.get(base+'/u5admin/textarea-resource.php?mode=search&term=textareatarget')).json();ok(search.results.some(x=>x.name==='textareatarget'),mode+': authenticated object search');
   const serious=errors.filter(x=>!/Cannot read properties.*(form1|page)|Blocked a frame/.test(x));equal(serious.length,0,mode+': no new uncaught editor error: '+serious.join('; '));
   await context.close();
  }
  setMode('no');
  const limited=await browser.newContext({httpCredentials:{username:'textareauser',password:'textarea-password',send:'always',origin:base}});
  for(const object of ['accent!js','textareaarchive']){
   const data=await (await limited.request.get(base+'/u5admin/textarea-resource.php?object='+encodeURIComponent(object))).json();equal(data.edit,null,'higher-admin edit actions are not offered for '+object);equal(data.metadata,null,'restricted metadata action is not offered');
  }
  await limited.close();const anonymous=await browser.newContext();const denied=await anonymous.request.get(base+'/u5admin/textarea-resource.php?object=textareatarget');
  equal(denied.status(),401,'unauthenticated object endpoint is denied');ok(!(denied.headers()['content-type']||'').includes('application/json'),'anonymous receives no object metadata');await anonymous.close();
  console.log('Textarea real editor/browser/HTTP checks: '+checks+' passed.');
 }finally{fs.writeFileSync(config,original);await browser.close();}
})().catch(error=>{console.error(error);process.exit(1);});
