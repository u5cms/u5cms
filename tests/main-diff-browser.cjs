const fs=require('fs'),path=require('path'),os=require('os'),assert=require('node:assert/strict');
const {spawnSync}=require('child_process');
const {launchBrowser}=require('./browser-runtime.cjs');
(async()=>{
 const [, ,base,site]=process.argv;
 const browser=await launchBrowser();
 let checks=0;
 const equal=(a,b,label)=>{assert.equal(a,b,label);checks++;};
 const verify=(a,label)=>{assert.ok(a,label);checks++;};
 const config=path.join(site,'config.php');
 function setMode(mode){fs.writeFileSync(config,fs.readFileSync(config).toString('latin1').replace(/\$usesessioninsteadofbasicauth\s*=\s*'(?:yes|no)'/,"$usesessioninsteadofbasicauth='"+mode+"'"),'latin1');}
 const php=(args)=>spawnSync(process.env.U5_TEST_PHP || 'php',[...JSON.parse(process.env.U5_TEST_PHP_ARGS || '[]'),...args],{encoding:'utf8'});
 try {
  setMode('no');
  const context=await browser.newContext({httpCredentials:{username:'auditadmin',password:'audit-password',send:'always',origin:base}});
  // Custom P/web applications own their extra query/cookie fields. The shared
  // bootstrap must preserve collections instead of rejecting the whole page.
  fs.writeFileSync(path.join(site,'fixture-application-request.php'),'<?php require __DIR__."/connect.inc.php";header("Content-Type: application/json");echo json_encode(array("selection"=>$_GET["selection"],"preferences"=>$_COOKIE["application"]));');
  const applicationResponse=await context.request.get(base+'/fixture-application-request.php?selection[0]=first&selection[1]=second',{headers:{Cookie:'application[mode]=compact; application[language]=de'}});
  equal(applicationResponse.status(),200,'application query/cookie collections pass through the real CMS bootstrap');
  const applicationInput=await applicationResponse.json();
  equal(JSON.stringify(applicationInput.selection),JSON.stringify(['first','second']),'application query values and order are retained');
  equal(JSON.stringify(applicationInput.preferences),JSON.stringify({mode:'compact',language:'de'}),'application cookie structure is retained');
  const applicationPage=await context.request.get(base+'/?c=codefixture&selection[0]=first&selection[1]=second');
  equal(applicationPage.status(),200,'the real resource page accepts additional application collections');
  verify((await applicationPage.text()).includes('id="proof-form"'),'the resource renderer still reaches its P-object content');
  // Direct entry must load the stylesheet helper before its first use.
  // Forbid password orders in this fixture so this check cannot send mail.
  const passwordConfig=fs.readFileSync(config);
  const passwordStyle=path.join(site,'r/pworder.css');
  const legacyPasswordStyle=path.join(site,'r/csspworder.css');
  const passwordMarker=path.join(site,'fileversions/codeassets-12.11.0.done.php');
  const markerBytes=fs.readFileSync(passwordMarker);
  const savedStyles=[passwordStyle,legacyPasswordStyle].map(file=>fs.existsSync(file) ? fs.readFileSync(file) : null);
  try {
   fs.writeFileSync(config,passwordConfig.toString('latin1').replace(/\$orderingintranetpasswordsisforbidden\s*=\s*'no'/,"$orderingintranetpasswordsisforbidden='yes'"),'latin1');
   for(const migrated of [true,false]) {
    if(migrated)fs.writeFileSync(passwordStyle,'/* migrated password order style */');
    else {fs.unlinkSync(passwordMarker);fs.writeFileSync(legacyPasswordStyle,'/* legacy password order style */');}
    const response=await context.request.get(base+'/sendintranetlogin.php');
    const body=await response.text();
    equal(response.status(),200,'direct password order entry renders '+(migrated ? 'migrated' : 'legacy')+' installation');
    verify(!/Fatal error|undefined function/i.test(body),'password order entry has its helper before rendering');
    verify(body.includes('MESSAGE TO THE ADMINISTRATOR'),'password order policy still blocks the request before mail or salt changes');
    verify(body.includes('href="r/'+(migrated ? 'pworder.css' : 'csspworder.css')+'"'),'password order uses the appropriate stylesheet');
   }
  } finally {
   fs.writeFileSync(config,passwordConfig);fs.writeFileSync(passwordMarker,markerBytes);
   [passwordStyle,legacyPasswordStyle].forEach((file,index)=>{if(savedStyles[index]===null) {if(fs.existsSync(file))fs.unlinkSync(file);}else fs.writeFileSync(file,savedStyles[index]);});
  }
  const tokenResponse=await context.request.get(base+'/u5admin/new.php?typ=p');
  const match=(await tokenResponse.text()).match(/name="u5scrtytknfrfrms"[^>]*value="([^"]+)"/);verify(match,'real backend CSRF token');
  const token=match[1];
  const previewSource=fs.readFileSync(path.join(site,'preview.inc.php'),'latin1');
  const previewCopy=previewSource.slice(previewSource.indexOf('function u5CopyPreviewBody()'),previewSource.lastIndexOf('</script>'));
  const legacyCode=spawnSync('git',['show','16377863d62f0b9a53235fbfabf236d1b18a425c:preview.inc.php'],{cwd:path.join(__dirname,'..'),encoding:'latin1'});
  equal(legacyCode.status,0,'historical preview copier is available for its parser-race reproduction');
  const oldCopy=legacyCode.stdout.slice(legacyCode.stdout.lastIndexOf('if (parent.pframe2'),legacyCode.stdout.lastIndexOf('</script>'));
  for(const [helper,early] of [[oldCopy,true],[previewCopy,false]]) {
   const probe=await context.newPage();
   const result=await probe.evaluate(async helper=>{
    const target=document.createElement('iframe'),source=document.createElement('iframe');target.name='pframe2';source.name='pframe';document.body.append(target,source);
    const destination=target.contentDocument;destination.open();destination.write('<!doctype html><html><body id="body"><form name="fsearch">');
    source.contentDocument.open();source.contentDocument.write('<html><body id="body"><form name="fsearch"><input name="q" value="replacement"></form></body></html>');source.contentDocument.close();
    source.contentWindow.eval(helper);
    const copiedEarly=destination.body.innerHTML.includes('replacement');
    const ready=new Promise(resolve=>destination.addEventListener('DOMContentLoaded',resolve,{once:true}));
    destination.write('<input name="q" value="original"></form><script>window.parsedSearch=!!(document.fsearch && document.fsearch.q)</script></body></html>');destination.close();await ready;
    return {copiedEarly,parsedSearch:target.contentWindow.parsedSearch,forms:destination.querySelectorAll('form[name=fsearch]').length,value:destination.fsearch && destination.fsearch.q && destination.fsearch.q.value};
   },helper);
   equal(result.copiedEarly,early,'preview copy waits for its actual target parser: '+(early ? 'previous failure reproduced' : 'corrected'));
   if(!early) {verify(result.parsedSearch,'target parser keeps a valid search form');equal(result.forms,1,'preview synchronization leaves a single search form');equal(result.value,'replacement','latest preview HTML is published after parsing');}
   await probe.close();
  }
  if(process.env.U5CMS_BACKUP_STRESS)equal(php([path.join(__dirname,'main-diff-support.php'),site,'large']).status,0,'dedicated large-row backup fixture is created');
  let response=await context.request.get(base+'/u5admin/backupdb.php');
  equal(response.status(),200,'database backup endpoint succeeds');
  const backupBody=await response.text();verify(backupBody.includes('_structure_'),'database backup publishes completed files: '+backupBody.slice(-2000));
  const restored=php(['-d','memory_limit=384M',path.join(__dirname,'backup-restore-check.php'),site]);
  equal(restored.status,0,'actual dump restores data/schema exactly: '+restored.stdout+restored.stderr);
  checks+=JSON.parse(restored.stdout).checks;
  const dumpDirectory=path.join(site,'fileversions','_dbbackup');
  const previousDumps=new Map(fs.readdirSync(dumpDirectory).map(name=>[name,fs.readFileSync(path.join(dumpDirectory,name))]));
  fs.writeFileSync(config,fs.readFileSync(config).toString('latin1').replace(/\$backup_compress\s*=\s*true/,'$backup_compress=false'),'latin1');
  response=await context.request.get(base+'/u5admin/backupdb.php');equal(response.status(),200,'plain SQL backup endpoint succeeds');
  const plainName=(await response.text()).match(/fff\.php\?f=([^"&]+_structure_[^"&]+\.sql)&/);
  verify(plainName,'uncompressed SQL backup is published');
  const plainRestore=php(['-d','memory_limit=384M',path.join(__dirname,'backup-restore-check.php'),site,path.join(dumpDirectory,plainName[1])]);
  equal(plainRestore.status,0,'actual plain SQL restores exactly: '+plainRestore.stdout+plainRestore.stderr);checks+=JSON.parse(plainRestore.stdout).checks;
  for(const [name,bytes] of previousDumps)verify(fs.readFileSync(path.join(dumpDirectory,name)).equals(bytes),'a second backup retains its previous complete dump: '+name);
  const beforeFailure=fs.readdirSync(dumpDirectory).sort();
  equal(php([path.join(__dirname,'main-diff-support.php'),site,'view']).status,0,'custom view failure fixture is created');
  try {
   equal((await context.request.get(base+'/u5admin/backupdb.php')).status(),500,'unsupported custom view produces an explicit SQL backup failure');
   equal(JSON.stringify(fs.readdirSync(dumpDirectory).sort()),JSON.stringify(beforeFailure),'failed SQL backup publishes no incomplete files and retains prior dumps');
  }finally{php([path.join(__dirname,'main-diff-support.php'),site,'drop-view']);}
  // The 24 MiB single-row fixture belongs to the 64 MiB backup export test.
  // Migration memory is tested separately with paged data and retained history.
  if(process.env.U5CMS_BACKUP_STRESS)equal(php([path.join(__dirname,'main-diff-support.php'),site,'drop-large']).status,0,'large backup fixture is removed before testing unrelated editor workflows');
  fs.appendFileSync(path.join(site,'r/htmltemplate!html.tpl'),'\n<!-- main-audit custom reset baseline -->');
  response=await context.request.post(base+'/u5admin/rename2.php?name=htmltemplate!html',{form:{name:'audit-site!layout!html',ulinks:'yes',u5scrtytknfrfrms:token}});
  equal(response.status(),200,'custom template rename before backup succeeds');
  const pairBackup=await context.request.get(base+'/u5admin/backupdb.php');equal(pairBackup.status(),200,'database backup captures the renamed layout');
  const pairName=(await pairBackup.text()).match(/fff\.php\?f=([^"&]+_structure_[^"&]+\.sql)&/);verify(pairName,'paired database backup is complete');
  const pairBytes=php(['-d','memory_limit=384M',path.join(__dirname,'backup-restore-check.php'),site,path.join(dumpDirectory,pairName[1])]);
  equal(pairBytes.status,0,'renamed-layout dump restores all bytes/schema exactly: '+pairBytes.stdout+pairBytes.stderr);checks+=JSON.parse(pairBytes.stdout).checks;
  response=await context.request.get(base+'/u5admin/backupfiles.php');
  equal(response.status(),200,'file backup endpoint succeeds');
  const archives=fs.readdirSync(path.join(site,'fileversions')).filter(name=>name.endsWith('_FilesExclCMSsys.zip'));
  equal(archives.length,1,'file backup produces a complete archive');
  const archive=path.join(site,'fileversions',archives[0]);
  const listing=spawnSync('python',['-c','import json,zipfile,sys;print(json.dumps(zipfile.ZipFile(sys.argv[1]).namelist()))',archive],{encoding:'utf8'});
  equal(listing.status,0,'generated ZIP is readable');
  const names=JSON.parse(listing.stdout);
  verify(names.includes('fileversions/codeassets-12.11.0.done.php'),'backup includes active layout/role mapping');
  verify(names.some(name=>/fileversions\/codeassets-[0-9.]+-rows-/.test(name)),'backup includes guarded migration/rename rows');
  verify(names.includes('r/audit-site!layout.html') && names.includes('r/audit-site!layout!html.tpl'),'backup includes renamed active/reset layouts');
  const restoreRoot=fs.mkdtempSync(path.join(os.tmpdir(),'u5-restore-proof-'));
  try {
   const extracted=spawnSync('python',['-c','import zipfile,sys;zipfile.ZipFile(sys.argv[1]).extractall(sys.argv[2])',archive,restoreRoot],{encoding:'utf8'});
   equal(extracted.status,0,'actual ZIP extracts successfully');
   const selected=php(['-r',"require $argv[1].'/codeassets.inc.php';echo u5CodeTemplatePath($argv[2]);",site,restoreRoot]);
   equal(selected.status,0,'restored layout mapping is readable');
   verify(fs.existsSync(selected.stdout) && selected.stdout.endsWith('audit-site!layout.html'),'restored installation resolves its custom layout');
   const paired=php(['-d','memory_limit=384M',path.join(__dirname,'backup-restore-check.php'),site,path.join(restoreRoot,'fileversions/_dbbackup',pairName[1]),restoreRoot]);
   equal(paired.status,0,'actual extracted SQL plus ZIP restore, export and custom layout reset succeed: '+paired.stdout+paired.stderr);checks+=JSON.parse(paired.stdout).checks;
  }finally{fs.rmSync(restoreRoot,{recursive:true,force:true});}
  const prior=fs.readFileSync(archive);
  const cycle=path.join(site,'r','audit-backup-cycle');fs.symlinkSync(path.join(site,'r'),cycle);
  try {
   equal((await context.request.get(base+'/u5admin/backupfiles.php')).status(),500,'a directory cycle fails the file backup explicitly');
   verify(fs.readFileSync(archive).equals(prior),'failed ZIP backup preserves the complete previous archive');
   verify(!fs.readdirSync(path.join(site,'fileversions')).some(name=>name.startsWith('u5zip_')),'failed ZIP backup cleans its temporary archive');
  }finally{fs.unlinkSync(cycle);}
  await context.close();
  for(const mode of ['basic','session']) {
   setMode(mode==='basic' ? 'no' : 'yes');
   const ctx=await browser.newContext(mode==='basic' ? {httpCredentials:{username:'auditadmin',password:'audit-password',send:'always',origin:base}} : {});
   await ctx.route('**/*',route=>new URL(route.request().url()).origin===new URL(base).origin ? route.continue() : route.abort());
   if(mode==='session') {
    const login=await ctx.request.post(base+'/loginsave.php?u=u5admin',{form:{u:'auditadmin',p:'audit-password'},maxRedirects:0});
    equal(login.status(),302,'ordinary backend session login');
   }
   const form=await ctx.request.get(base+'/u5admin/new.php?typ=p');
   const csrf=(await form.text()).match(/name="u5scrtytknfrfrms"[^>]*value="([^"]+)"/)[1];
   const leftName='mainleft'+mode,rightName='mainright'+mode;
   for(const name of [leftName,rightName]) {
    const created=await ctx.request.post(base+'/u5admin/new2.php?typ=p',{form:{name,u5scrtytknfrfrms:csrf}});
    equal(created.status(),200,mode+': normal page creation');
   }
   await ctx.addCookies([{name:'ns',value:'true',url:base},{name:'i1_p',value:leftName,url:base},{name:'i2_p',value:rightName,url:base},{name:'i1_l',value:'1',url:base},{name:'i2_l',value:'1',url:base}]);
   const page=await ctx.newPage(),errors=[];
   page.on('dialog',dialog=>dialog.dismiss());page.on('pageerror',error=>errors.push(error.stack || String(error)));
   await ctx.addInitScript(()=>window.addEventListener('error',event=>{
    console.log('U5AUDIT_ERROR '+JSON.stringify({url:location.href,line:event.lineno,source:document.documentElement.outerHTML.split('\n').slice(Math.max(0,event.lineno-4),event.lineno+3).join('\n')}));
   }));
   page.on('console',message=>{if(message.text().startsWith('U5AUDIT_ERROR '))errors.push(message.text());});
   // Backend polling never becomes idle; wait for the frames and their actual forms.
   const updating=fs.existsSync(path.join(site,'r/runonce.php'));
   const loaded=await page.goto(base+'/u5admin/index.php',{waitUntil:'domcontentloaded'});
   equal(loaded.status(),200,mode+': full backend loads');
   if(updating) {
    await page.waitForEvent('framenavigated',{predicate:frame=>frame===page.mainFrame()});
    verify(!fs.existsSync(path.join(site,'r/runonce.php')),mode+': automatic update finishes before its scheduled backend reload');
   }
   await page.locator('iframe[name=i1]').waitFor({state:'attached'});
   await page.locator('iframe[name=i2]').waitFor({state:'attached'});
   const left=await (await page.locator('iframe[name=i1]').elementHandle()).contentFrame();
   const right=await (await page.locator('iframe[name=i2]').elementHandle()).contentFrame();
   await left.waitForFunction(name=>document.form1 && document.form1.page.value===name,leftName);
   await right.waitForFunction(name=>document.form1 && document.form1.page.value===name,rightName);
   const snapshot=async name=>(await ctx.request.get(base+'/fixture-snapshot.php?asset='+encodeURIComponent(name))).json();
   const first='Über € &amp; original '+mode,later='Über € &amp; unsaved later '+mode,other='Right pane '+mode;
   const leftRevision=(await snapshot(leftName)).rows[0].lastmut,rightRevision=(await snapshot(rightName)).rows[0].lastmut;
   await left.locator('textarea[name=content_1]').fill(first);
   let release,started;
   const gate=new Promise(resolve=>release=resolve),pending=new Promise(resolve=>started=resolve);
   await ctx.route('**/u5admin/savepage.php?i=i1',async route=>{
    const saved=await route.fetch();started();await gate;await route.fulfill({response:saved});
   });
   await left.locator('#savebutton').click();await pending;
   await left.locator('textarea[name=content_1]').fill(later);
   await right.locator('textarea[name=content_1]').fill(other);
   await right.locator('#savebutton').click();
   release();
   await right.waitForFunction(old=>document.form1.coco.value!==String(old),rightRevision);
   equal((await snapshot(leftName)).rows[0].content_1,first,mode+': first save keeps its submitted snapshot');
   equal(await left.locator('textarea[name=content_1]').inputValue(),later,mode+': edits during a save stay in the editor');
   verify(await left.evaluate(()=>changes>0),mode+': edits during a save remain marked dirty');
   equal((await snapshot(rightName)).rows[0].content_1,other,mode+': queued other-pane save uses the correct page');
   equal((await snapshot(rightName)).rows[0].typ,'p',mode+': ordinary page kind remains P');
   verify(Number(await left.locator('input[name=coco]').inputValue())>Number(leftRevision),mode+': originating pane gets its new revision');
   await ctx.unroute('**/u5admin/savepage.php?i=i1');
   await left.locator('#savebutton').click();
   await left.waitForFunction(()=>changes===0);
   equal((await snapshot(leftName)).rows[0].content_1,later,mode+': later input can then be saved');
   // Simulate an asynchronous request rejected before its save acknowledgement.
   await ctx.route('**/u5admin/savepage.php?i=i1',route=>route.fulfill({status:403,contentType:'text/html; charset=windows-1252',body:'Expired form token; input was kept.'}));
   await left.locator('textarea[name=content_1]').fill('retryable '+mode);
   await left.locator('#savebutton').click();
   await page.waitForFunction(()=>!u5SavePending);
   equal((await snapshot(leftName)).rows[0].content_1,later,mode+': rejected save cannot change the database');
   equal(await left.locator('textarea[name=content_1]').inputValue(),'retryable '+mode,mode+': failed save retains entered text');
   verify(await left.evaluate(()=>changes>0),mode+': failed save keeps unload protection');
   await ctx.unroute('**/u5admin/savepage.php?i=i1');
   await left.locator('#savebutton').click();await left.waitForFunction(()=>changes===0);
   equal((await snapshot(leftName)).rows[0].content_1,'retryable '+mode,mode+': a failed save can be retried');
   for(const size of [{width:1280,height:800},{width:800,height:600},{width:375,height:667}]) {
    await page.setViewportSize(size);
    await left.waitForTimeout(100);
    const dimensions=await left.locator('textarea[name=content_1]').evaluate(element=>({height:element.getBoundingClientRect().height,bottom:element.getBoundingClientRect().bottom,viewport:innerHeight}));
    verify(dimensions.height>0 && dimensions.bottom<=dimensions.viewport+2,mode+': editor viewport preserves text area at '+size.width);
   }
   await page.setViewportSize({width:1280,height:800});
   for(const item of [{type:'d',editor:'metad.php'},{type:'i',editor:'metai.php'},{type:'f',editor:'metaf.php'},{type:'e',editor:'metae.php'},{type:'y',editor:'metay.php'}]) {
    const name='mainmeta'+item.type+mode;
    await ctx.request.post(base+'/u5admin/new2.php?typ='+item.type,{form:{name,u5scrtytknfrfrms:csrf}});
    const meta=await ctx.newPage();meta.on('dialog',dialog=>dialog.dismiss());
    const shown=await meta.goto(base+'/u5admin/'+item.editor+'?typ='+item.type+'&name='+name,{waitUntil:'domcontentloaded'});
    equal(shown.status(),200,mode+': metadata form '+item.type+' loads');
    const before=(await snapshot(name)).rows[0].lastmut;
    await meta.locator('[name=content_1]').fill('Über € &amp; metadata '+item.type);
    await meta.locator('input[name=button2]').click();
    if(!meta.isClosed())await Promise.race([meta.waitForEvent('close'),meta.waitForFunction(old=>document.form1.coco.value!==String(old),before)]);
    equal((await snapshot(name)).rows[0].content_1,'Über € &amp; metadata '+item.type,mode+': metadata '+item.type+' saves without Latin1 corruption');
    equal((await snapshot(name)).rows[0].typ,item.type,mode+': metadata '+item.type+' preserves the object kind');
    if(!meta.isClosed())await meta.close();
   }
   // The normal page endpoint must reject actual code objects.
   const code='maintype'+mode+'!js';
   await ctx.request.post(base+'/u5admin/new2.php?typ=c',{form:{name:code,u5scrtytknfrfrms:csrf}});
   const old=(await snapshot(code)).rows[0],forged={page:code,coco:old.lastmut,ishomepage:'0',hidden:'0',logins:'',u5scrtytknfrfrms:csrf};
   for(let language=1;language<=5;language++)for(const field of ['content','title','desc','key'])forged[field+'_'+language]='unwanted page replacement';
   const rejected=await ctx.request.post(base+'/u5admin/savepage.php?i=i1',{form:forged});
   verify(rejected.status()>=400,mode+': page save rejects the wrong actual resource kind');
   equal((await snapshot(code)).rows[0].typ,'c',mode+': code survives a forged page save');
   equal((await snapshot(code)).rows[0].content_1,old.content_1,mode+': rejected page save retains the original code source');
   const stalePage={...forged,page:rightName,coco:rightRevision,content_1:'conflicting ordinary page text'};
   const stale=await ctx.request.post(base+'/u5admin/savepage.php?i=i2',{form:stalePage});
   verify((await stale.text()).includes('CONFLICT'),'stale normal page save reports its version conflict');
   const rightAfter=await snapshot(rightName);equal(rightAfter.rows[0].content_1,other,'stale normal page save keeps the newer page');
   verify(rightAfter.history.includes('conflicting ordinary page text'),'conflicting ordinary page input remains in text versions');
   // Independent sessions submit the same revision concurrently.
   const racingName='mainrace'+mode;
   await ctx.request.post(base+'/u5admin/new2.php?typ=p',{form:{name:racingName,u5scrtytknfrfrms:csrf}});
   const peer=await browser.newContext(mode==='basic' ? {httpCredentials:{username:'auditadmin',password:'audit-password',send:'always',origin:base}} : {});
   try {
    if(mode==='session')equal((await peer.request.post(base+'/loginsave.php?u=u5admin',{form:{u:'auditadmin',p:'audit-password'},maxRedirects:0})).status(),302,'independent second session logs in');
    const peerForm=await peer.request.get(base+'/u5admin/new.php?typ=p');
    const peerToken=(await peerForm.text()).match(/name="u5scrtytknfrfrms"[^>]*value="([^"]+)"/)[1];
    const revision=(await snapshot(racingName)).rows[0].lastmut;
    const bodies=['simultaneous first '+mode,'simultaneous second '+mode];
    const responses=await Promise.all([ctx,peer].map((session,index)=>session.request.post(base+'/u5admin/savepage.php?i=i1',{form:{...stalePage,page:racingName,coco:revision,content_1:bodies[index],u5scrtytknfrfrms:index===0 ? csrf : peerToken}})));
    const documents=await Promise.all(responses.map(response=>response.text()));
    equal(documents.filter(text=>text.includes('CONFLICT')).length,1,mode+': exactly one simultaneous stale revision conflicts');
    const raced=await snapshot(racingName);
    verify(bodies.includes(raced.rows[0].content_1),mode+': simultaneous save retains one complete source');
    verify(bodies.every(body=>raced.history.includes(body)),mode+': both simultaneous inputs remain recoverable in history');
   } finally { await peer.close(); }
   const expired=await browser.newContext();
   equal((await expired.request.post(base+'/u5admin/savepage.php?i=i2',{form:{...stalePage,u5saveid:'audit_expired'}})).status(),401,'an expired ordinary editor login is rejected without an acknowledgement');
   await expired.close();
   equal((await ctx.request.post(base+'/u5admin/savepage.php?i=i2',{form:{...stalePage,u5scrtytknfrfrms:'expired',u5saveid:'audit_token'}})).status(),403,'an expired ordinary editor CSRF token is rejected');
   equal(php([path.join(__dirname,'main-diff-support.php'),site,'nonadmin']).status,0,'limited backend user fixture is active');
   try {
    equal((await ctx.request.post(base+'/u5admin/coding2.php?typ=c',{form:{name:code,coco:old.lastmut,content_1:'forbidden replacement',u5resourceid:'audit_forbidden',u5scrtytknfrfrms:csrf}})).status(),403,'limited backend user cannot save protected code');
    equal((await ctx.request.post(base+'/u5admin/meta2.php?typ=c',{form:{name:code,coco:old.lastmut,content_1:'forbidden metadata replacement',u5resourceid:'audit_forbidden_meta',u5scrtytknfrfrms:csrf}})).status(),403,'limited backend user cannot replace code through metadata');
    equal((await ctx.request.get(base+'/u5admin/backupdb.php')).status(),403,'limited backend user cannot create an admin-only backup');
    equal((await snapshot(code)).rows[0].content_1,old.content_1,'permission failures preserve the original code bytes');
   }finally{php([path.join(__dirname,'main-diff-support.php'),site,'admin']);}
   const used='mainusage'+mode+'!extra!css',file='mainusage'+mode+'!extra.css';
   await ctx.request.post(base+'/u5admin/new2.php?typ=c',{form:{name:used,u5scrtytknfrfrms:csrf}});
   for(const content of ['<link href="r/'+file+'?v=1#x">','@import "./'+file+'";',JSON.stringify({url:'https://other.invalid/cms/r/'+file.replace('!','%21')}).replaceAll('/','\\/')]) {
    const current=(await snapshot(leftName)).rows[0];
    const dependencyForm={name:leftName,coco:current.lastmut,u5scrtytknfrfrms:csrf};
    for(let language=1;language<=5;language++)for(const field of ['content','title','desc','key'])dependencyForm[field+'_'+language]=language===1 && field==='content' ? content : '';
    equal((await ctx.request.post(base+'/u5admin/meta2.php?typ=p',{form:dependencyForm})).status(),200,mode+': complete dependency metadata form succeeds');
    equal((await snapshot(leftName)).rows[0].content_1,content,mode+': dependency source was actually saved');
    const localized=await ctx.request.get(base+'/u5admin/localize.php?name='+encodeURIComponent(used));
    const localizedText=await localized.text();
    verify(localizedText.includes('>'+leftName+'</a>'),mode+': localization recognizes flat code dependency '+content+'; output: '+localizedText.slice(0,1800));
    fs.writeFileSync(path.join(site,'fileversions/orphanrunning.txt'),'0');
    const scanned=await ctx.request.get(base+'/u5admin/orphan.write.php');
    equal(scanned.status(),200,mode+': orphan scan completes for typed names');
    verify(fs.readFileSync(path.join(site,'fileversions/orphan.js.php'),'latin1').includes("mark('"+used+"',false)"),mode+': used flat code export is not an orphan');
   }
   await left.locator('textarea[name=content_1]').fill('<link href="r/'+file+'">');
   // Inspect the actual iframe document: Firefox may retain the old frame
   // navigation identity after the legacy editor uses document.open/write.
   await page.evaluate(url=>{document.querySelector('iframe[name=i3]').src=url;},base+'/u5admin/c.php?f=*');
   await page.waitForFunction(name=>{
    const doc=document.querySelector('iframe[name=i3]').contentDocument;
    const row=doc && doc.getElementById('tdL_'+name);
    return row && row.style.background==='lightgreen';
   },used);
   verify(true,mode+': repository highlights code linked by its flat public filename');
   const cookieValues=Object.fromEntries((await ctx.cookies()).map(cookie=>[cookie.name,cookie.value]));
   const prepared=php([path.join(__dirname,'main-diff-support.php'),site,'cache',JSON.stringify(cookieValues)]);
   equal(prepared.status,0,mode+': private media/cache fixtures are prepared');
   const anonymous=await browser.newContext();
   for(const entry of [{url:'/f.php?f=r/auditprivate/auditsecret.txt&t=1700000000',file:'r/auditprivate/auditsecret.txt'},
     {url:'/ff.php?f=auditsecret.txt&t=1700000000',file:'fileversions/auditsecret.txt'},
     {url:'/fff.php?f=auditsecret.txt&t=1700000000',file:'fileversions/_dbbackup/auditsecret.txt'},
     {url:'/ffff.php?f=auditsecret.txt&t=1700000000',file:'fileversions/useruploads/auditsecret.txt'}]) {
    const firstMedia=await ctx.request.get(base+entry.url,{maxRedirects:0});
    if(mode==='basic') {equal(firstMedia.status(),302,'Basic media retains its Apache-protected static redirect');continue;}
    equal(firstMedia.status(),200,'session-protected media can be read after login');
    equal(await firstMedia.text(),'private fixture bytes','protected media returns the original bytes');
    equal(firstMedia.headers()['cache-control'],'private, no-cache','protected media is marked private and revalidated');
    const modified=firstMedia.headers()['last-modified'];
    equal((await ctx.request.get(base+entry.url,{headers:{'If-Modified-Since':modified}})).status(),304,'unchanged authorized media returns 304');
    const denied=await anonymous.request.get(base+entry.url,{headers:{'If-Modified-Since':modified},maxRedirects:0});
    verify(denied.status()!==304 && !(await denied.text()).includes('private fixture bytes'),'conditional media requests cannot skip session access checks');
    const real=path.join(site,entry.file);fs.writeFileSync(real,'changed private fixture bytes');fs.utimesSync(real,1700000010,1700000010);
    const changed=await ctx.request.get(base+entry.url,{headers:{'If-Modified-Since':modified}});
    equal(changed.status(),200,'a changed protected file is sent despite an older conditional date');
    equal(await changed.text(),'changed private fixture bytes','changed protected file content is fresh');
   }
   for(const shape of ['portrait','landscape','square'])for(const parameter of ['w=19&sq=1','h=17&sq=1','w=1','h=1']) {
    const thumb=await ctx.request.get(base+'/thumb.php?f=r/auditprivate/'+shape+'.jpg&'+parameter);
    equal(thumb.status(),200,mode+': protected thumbnail succeeds for '+shape+' '+parameter);
    const dimensions=await page.evaluate(url=>new Promise((resolve,reject)=>{const image=new Image();image.onload=()=>{const canvas=document.createElement('canvas');canvas.width=image.width;canvas.height=image.height;const c=canvas.getContext('2d');c.drawImage(image,0,0);resolve({w:image.width,h:image.height,color:[...c.getImageData(0,0,1,1).data]});};image.onerror=reject;image.src=url;}),'data:image/jpeg;base64,'+(await thumb.body()).toString('base64'));
    const size=parameter.startsWith('w=19') ? 19 : parameter.startsWith('h=17') ? 17 : 1;
    if(parameter.includes('sq=1'))verify(dimensions.w===size && dimensions.h===size,'square thumbnail uses the requested width/height for '+shape);
    else verify(dimensions.w>=1 && dimensions.h>=1,'extreme resize never creates a zero-sized image');
    verify(dimensions.color[1]>100 && dimensions.color[0]<100 && dimensions.color[2]<100,'thumbnail contains the resampled source pixels');
    const modified=thumb.headers()['last-modified'];
    equal((await ctx.request.get(base+'/thumb.php?f=r/auditprivate/'+shape+'.jpg&'+parameter,{headers:{'If-Modified-Since':modified}})).status(),304,'unchanged authorized thumbnail is revalidated');
   }
   const deniedThumb=await anonymous.request.get(base+'/thumb.php?f=r/auditprivate/portrait.jpg&w=19&sq=1',{headers:{'If-Modified-Since':new Date().toUTCString()}});
   equal(deniedThumb.status(),401,'thumbnail access is rejected before conditional caching');
   equal(deniedThumb.headers()['cache-control'],'no-store','thumbnail authentication failure cannot be reused from cache');
   verify(deniedThumb.headers()['content-type']!=='image/jpeg','thumbnail denial contains no image response');
   const download=base+'/df.php?f='+prepared.stdout;
   const direct=await ctx.request.get(download);equal(direct.status(),200,'private form upload hash permits its original session');
   equal((await ctx.request.get(download,{headers:{'If-Modified-Since':direct.headers()['last-modified']}})).status(),304,'private form upload uses correct conditional dates');
   if(mode==='session')equal((await anonymous.request.get(download,{headers:{'If-Modified-Since':direct.headers()['last-modified']}})).status(),404,'private form upload hash is not transferable to another cookie session');
   equal((await anonymous.request.get(base+'/df.php?f=invalid-hash',{headers:{'If-Modified-Since':direct.headers()['last-modified']}})).status(),404,'an invalid upload hash cannot obtain a conditional response');
   await anonymous.close();
   const albumName='mainalbum'+mode;
   equal((await ctx.request.post(base+'/u5admin/new2.php?typ=a',{form:{name:albumName,u5scrtytknfrfrms:csrf}})).status(),200,'album resource is created through the real backend');
   fs.mkdirSync(path.join(site,'r',albumName),{recursive:true});fs.copyFileSync(path.join(site,'r/auditprivate/portrait.jpg'),path.join(site,'r',albumName,albumName+'_1.jpg'));
   const album=await ctx.newPage();album.on('dialog',dialog=>dialog.dismiss());
   album.on('pageerror',error=>errors.push(error.stack || String(error)));
   await album.goto(base+'/u5admin/filesa.php?name='+albumName+'&typ=a',{waitUntil:'domcontentloaded'});
   const caption=album.locator('#tx11');await caption.waitFor();
   await album.frameLocator('iframe[name=ametai]').locator('input[name=coco]').waitFor({state:'attached'});
   const firstCaption='Über € &amp; caption',laterCaption='Über € &amp; later caption';
   const albumRevision=(await snapshot(albumName)).rows[0].lastmut;
   await caption.fill('');await caption.pressSequentially(firstCaption);await caption.blur();
   let releaseAlbum,albumStarted;
   const albumGate=new Promise(resolve=>releaseAlbum=resolve),albumPending=new Promise(resolve=>albumStarted=resolve);
   await ctx.route('**/u5admin/meta2.php?typ=a**',async route=>{const saved=await route.fetch();albumStarted();await albumGate;await route.fulfill({response:saved});});
   await album.locator('#sacabu button').click();await albumPending;
   await caption.fill('');await caption.pressSequentially(laterCaption);await caption.blur();releaseAlbum();
   const albumFrame=await (await album.locator('iframe[name=ametai]').elementHandle()).contentFrame();
   await albumFrame.waitForFunction(old=>document.form1.coco.value!==String(old),albumRevision);
   verify((await snapshot(albumName)).rows[0].content_1.includes(firstCaption),'album saves its submitted caption snapshot');
   equal(await caption.inputValue(),laterCaption,'album retains caption edits made during the save');
   verify(await album.evaluate(()=>Boolean(window.onbeforeunload())),'pending album caption edits retain unload protection');
   await ctx.unroute('**/u5admin/meta2.php?typ=a**');
   await Promise.all([album.waitForEvent('domcontentloaded'),album.locator('#sacabu button').click()]);
   equal(await album.locator('#tx11').inputValue(),laterCaption,'album retry reloads its latest caption after acknowledgement');
   verify((await snapshot(albumName)).rows[0].content_1.includes(laterCaption),'album latest caption reaches the database');
   await album.close();
   const scrolling=php([path.join(__dirname,'main-diff-support.php'),site,'scroll']);
   equal(scrolling.status,0,'real repository scroll fixtures are created');
   const scrollNames=JSON.parse(scrolling.stdout);
   const liveLeft=await (await page.locator('iframe[name=i1]').elementHandle()).contentFrame();
   await liveLeft.locator('textarea[name=content_1]').fill(scrollNames.map(name=>'['+name+']').join(' '));
   await liveLeft.locator('textarea[name=content_1]').press('End');await liveLeft.locator('textarea[name=content_1]').press('Space');
   verify(await liveLeft.evaluate(()=>changes>0),'live filter input is marked as an actual unsaved user edit');
   fs.writeFileSync(path.join(site,'fileversions/orphanrunning.txt'),'0');await ctx.request.get(base+'/u5admin/orphan.write.php');
   const list=await (await page.locator('iframe[name=i3]').elementHandle()).contentFrame();
   await list.goto(base+'/u5admin/p1.php?f=auditscroll*&pvs_f=here&pvs_p=off',{waitUntil:'domcontentloaded'});
   await list.waitForFunction(()=>window.u5FScroll && !u5FScroll.hold());
   const rowId='tr1_'+scrollNames[40];
   await list.evaluate(id=>{
    const row=document.getElementById(id);row.scrollIntoView({block:'center'});
    const middle=(document.getElementById('pidvesadivinc').getBoundingClientRect().bottom+document.getElementById('u5listfooter').getBoundingClientRect().top)/2;
    const rect=row.getBoundingClientRect();window.scrollBy(0,rect.top+rect.height/2-middle);
   },rowId);
   await list.waitForTimeout(150);
   const centerOffset=async()=>list.locator('#'+rowId).evaluate(element=>{
    const rect=element.getBoundingClientRect(),middle=(document.getElementById('pidvesadivinc').getBoundingClientRect().bottom+document.getElementById('u5listfooter').getBoundingClientRect().top)/2;
    return rect.top+rect.height/2-middle;
   });
   const anchored=await centerOffset();
   for(const preview of ['on','off']) {
    await list.locator('input[name=pvs_p][value='+preview+']').click();
    await list.waitForFunction(mode=>document.readyState!=='loading' && document.form1 && document.form1.pvs_p[1].checked===(mode==='on') && new URL(location.href).searchParams.get('pvs_p')===mode,preview);
    await list.waitForFunction(()=>window.u5FScroll && !u5FScroll.hold());
    verify((await liveLeft.locator('textarea[name=content_1]').inputValue()).includes('auditscroll041'),'preview navigation preserves the live editor links');
    const moved=await centerOffset();
    verify(Math.abs(moved-anchored)<=12,'filtered repository retains its centered object when preview is '+preview+': '+anchored+' / '+moved);
   }
   await liveLeft.locator('textarea[name=content_1]').fill(scrollNames.slice(30).map(name=>'['+name+']').join(' '));
   await list.waitForFunction(()=>document.getElementById('tr1_auditscroll001').style.display==='none' && document.getElementById('tr1_auditscroll041').style.display!=='none');
   verify(true,'live F-filter responds when editor links change after preview navigation');
   verify(errors.length===0,mode+': no full-backend JavaScript exceptions: '+errors.join('; '));
   await ctx.close();
  }
  console.log('Main-diff Browser checks: '+checks+' passed (SQL/ZIP restore, full page editor, queued/failing saves, viewports and metadata).');
 } finally{await browser.close();}
})().catch(error=>{console.error(error);process.exitCode=1;});
