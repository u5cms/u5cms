const fs=require('fs'),path=require('path'),assert=require('node:assert/strict');
const {spawnSync}=require('node:child_process');const {launchBrowser}=require('./browser-runtime.cjs');
(async()=>{
 const [,,base,site]=process.argv;let checks=0;
 const equal=(actual,expected,message)=>{assert.equal(actual,expected,message);checks++;};
 const verify=(value,message)=>{assert.ok(value,message);checks++;};
 const config=path.join(site,'config.php'),original=fs.readFileSync(config);
 const support=(action,mode)=>{const p=spawnSync(process.env.U5_TEST_PHP||'php',[...JSON.parse(process.env.U5_TEST_PHP_ARGS||'[]'),path.join(__dirname,'rename-web-support.php'),site,action,mode],{encoding:'utf8'});equal(p.status,0,'fixture '+action+': '+p.stderr+p.stdout);return action==='snapshot'?JSON.parse(p.stdout):null;};
 const browser=await launchBrowser();
 try {
  for(const mode of ['basic','session']) {
   const old='renameold'+mode,newName='renamenew'+mode,ref='renameref'+mode;
   fs.writeFileSync(config,original.toString('latin1').replace(/\$usesessioninsteadofbasicauth\s*=\s*'(?:yes|no)'/,"$usesessioninsteadofbasicauth='"+(mode==='basic'?'no':'yes')+"'"),'latin1');
   support('seed',mode);
   const context=await browser.newContext({httpCredentials:{username:'auditadmin',password:'audit-password',send:'always',origin:base}});
   if(mode==='session')await context.request.post(base+'/loginsave.php?u=u5admin/rename.php',{form:{u:'auditadmin',p:'audit-password'}});
   const page=await context.newPage();await page.goto(base+'/u5admin/rename.php?name='+old);
   const token=await page.locator('input[name=u5scrtytknfrfrms]').inputValue();
   let response=await context.request.post(base+'/u5admin/rename2.php?name='+old,{form:{name:newName,ulinks:'yes'}});
   equal(response.status(),403,'rename checks CSRF '+mode);
   let state=support('snapshot',mode);verify(state.resources.some(x=>x.name===old),'failed CSRF keeps source');
   response=await context.request.get(base+'/u5admin/rename3.php?name='+old+'&newname='+newName+'&ulinks=yes');equal(response.status(),410,'separate history GET cannot mutate data');
   equal(JSON.stringify(support('snapshot',mode)),JSON.stringify(state),'history endpoint changes no rows');
   await page.locator('input[name=name]').fill(newName);
   await page.waitForFunction(()=>window.nc&&window.nc.document.form1&&window.nc.document.form1.ok.value==='ok');
   const result=page.waitForEvent('response',r=>r.url().includes('/rename2.php?'));
   await page.locator('input[type=submit]').click({noWaitAfter:true});equal((await result).status(),200,'actual rename form succeeds '+mode);
   state=support('snapshot',mode);const renamed=state.resources.find(x=>x.name===newName);
   verify(renamed,'renamed object exists');equal(renamed.operator,'R original','one operator marker');
   for(let l=1;l<=5;l++) {
    verify(renamed['content_'+l].includes('[[[['+old+']]]]'),'heading is literal in language '+l);
    verify(renamed['content_'+l].includes('[Go to page:'+newName+']'),'links updated in language '+l);
    verify(state.resources_log.find(x=>x.name===newName)['content_'+l].includes('[Go to page:'+newName+']'),'history complete before response '+l);
   }
   equal(state.formdata[0].formname,newName,'form ownership follows');equal(state.formdata[0].datacsv,'submitted [label:'+old+']','form data remains opt-in by default');
   equal(fs.readFileSync(path.join(site,'r',newName,newName+'_en.txt'),'utf8'),'original language bytes','language file follows');
   equal(fs.readFileSync(path.join(site,'r',newName,newName+'_100.jpg'),'utf8'),'original album bytes','album file follows');
   equal(fs.readFileSync(path.join(site,'r',newName,'.htpasswd'),'utf8'),'private-member:{SHA}opaque','memberships remain exact');
   equal(fs.readFileSync(path.join(site,'r',newName,'.htaccess'),'utf8'),'Deny from all\n','access protection remains exact');
   const front=await context.newPage();await front.goto(base+'/?c='+ref+'&l=en');equal(await front.locator('h2').filter({hasText:old}).count(),1,'real renderer preserves title brackets');
   verify(await front.locator('a[href*="c='+newName+'"]').count()>0,'real renderer resolves renamed dependency');await front.close();
   const snapshot=JSON.stringify(state);response=await context.request.post(base+'/u5admin/rename2.php?name='+old,{form:{name:newName,ulinks:'yes',u5scrtytknfrfrms:token}});equal(response.status(),200,'replayed completed request succeeds without a second mutation');equal(JSON.stringify(support('snapshot',mode)),snapshot,'repeat leaves database unchanged');
   await context.close();
  }
  // Denied HIADRI and an actual file-publication failure, then retry after source ownership changed.
  fs.writeFileSync(config,original.toString('latin1').replace(/\$usesessioninsteadofbasicauth\s*=\s*'(?:yes|no)'/,"$usesessioninsteadofbasicauth='no'"),'latin1');
  support('seed','failure');support('archive','failure');
  const writer=await browser.newContext({httpCredentials:{username:'renamewriter',password:'writer-password',send:'always',origin:base}});
  let form=await writer.request.get(base+'/u5admin/rename.php?name=renameoldfailure');let token=(await form.text()).match(/name="u5scrtytknfrfrms"[^>]*value="([^"]+)"/)[1];
  let response=await writer.request.post(base+'/u5admin/rename2.php?name=renameoldfailure',{form:{name:'renamenewfailure',ulinks:'yes',u5scrtytknfrfrms:token}});equal(response.status(),403,'archived object requires HIADRI');verify(support('snapshot','failure').resources.some(x=>x.name==='renameoldfailure'),'denied rename keeps archived object');await writer.close();
  const admin=await browser.newContext({httpCredentials:{username:'auditadmin',password:'audit-password',send:'always',origin:base}});
  const conflict=await admin.request.get(base+'/u5admin/checkco.php?name=renameoldfailure&coco=100&changes=1');
  verify(!(await conflict.text()).includes('var form=parent.document.form1'),'archived editor conflict poll ignores a newer deleted tombstone');
  form=await admin.request.get(base+'/u5admin/rename.php?name=renameoldfailure');token=(await form.text()).match(/name="u5scrtytknfrfrms"[^>]*value="([^"]+)"/)[1];
  const exportPath=path.join(site,'r','htmltemplate.html'),savedExport=exportPath+'.fixture';fs.renameSync(exportPath,savedExport);fs.mkdirSync(exportPath);
  try {
   response=await admin.request.post(base+'/u5admin/rename2.php?name=renameoldfailure',{form:{name:'renamenewfailure',ulinks:'yes',u5scrtytknfrfrms:token}});equal(response.status(),500,'publication failure is visible');verify((await response.text()).includes('Retry rename'),'failure offers the exact resumable operation');
   verify(support('snapshot','failure').resources.some(x=>x.name==='renamenewfailure'),'publication failure checkpoint covers a moved source');
   const blocked=await admin.request.post(base+'/u5admin/meta2.php?typ=p',{form:{name:'renamenewfailure',u5scrtytknfrfrms:token}});equal(blocked.status(),409,'metadata writes cannot race an interrupted rename');
   verify((await blocked.text()).includes('did not change the resource'),'blocked mutation explains preserved input');
  } finally {fs.rmdirSync(exportPath);fs.renameSync(savedExport,exportPath);}
  response=await admin.request.post(base+'/u5admin/rename2.php?name=renameoldfailure',{form:{name:'renamenewfailure',ulinks:'yes',u5scrtytknfrfrms:token}});equal(response.status(),200,'administrator resumes publication after failure');
  verify(!(await response.text()).includes('Rename has not completed'),'completed response has no error');equal(support('snapshot','failure').resources.find(x=>x.name==='renamenewfailure').deleted,'2','archive state survives retry');
  support('codepending','failure');
  const policyConfig=fs.readFileSync(config);fs.appendFileSync(config,"\n$cssRqHIADRI='no';$archiveRqHIADRI='yes';\n");
  const lower=await browser.newContext({httpCredentials:{username:'renamewriter',password:'writer-password',send:'always',origin:base}});
  try {
   form=await lower.request.get(base+'/u5admin/rename.php?name=renamearchived!css');token=(await form.text()).match(/name="u5scrtytknfrfrms"[^>]*value="([^"]+)"/)[1];
   response=await lower.request.post(base+'/u5admin/rename2.php?name=renamearchived!css',{form:{name:'renamedarchived!css',ulinks:'yes',u5scrtytknfrfrms:token}});
   equal(response.status(),403,'resumed archived code rename still requires archive HIADRI after source ownership moved');
  } finally {await lower.close();fs.writeFileSync(config,policyConfig);}
  await admin.close();
  console.log('Rename browser checks: '+checks+' passed (forms, all languages, rendering, history, Basic/session, CSRF, HIADRI, failure/retry).');
 } finally {fs.writeFileSync(config,original);await browser.close();}
})().catch(error=>{console.error(error);process.exitCode=1;});
