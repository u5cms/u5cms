'use strict';
const fs=require('fs'),path=require('path'),assert=require('node:assert/strict'),{spawn}=require('child_process');
const {launchBrowser}=require('./browser-runtime.cjs');
const [base,site]=process.argv.slice(2);
let checks=0;
function equal(actual,expected,label){assert.deepEqual(actual,expected,label);checks++;}
function verify(value,label){assert.ok(value,label);checks++;}
async function locked(operation){
 const source='require '+JSON.stringify(path.join(site,'codeassets.inc.php'))+';$release=u5CodeMigrationLock('+JSON.stringify(site)+');echo "READY\\n";flush();fgets(STDIN);$release();';
 const worker=spawn(process.env.U5_TEST_PHP||'php',['-r',source],{stdio:['pipe','pipe','pipe']});
 let errors='';worker.stderr.on('data',data=>errors+=data);
 try{
  await new Promise((resolve,reject)=>{let data='';const timer=setTimeout(()=>reject(new Error('Lock readiness timed out: '+errors)),5000);worker.once('error',reject);worker.stdout.on('data',chunk=>{data+=chunk;if(data.includes('READY\n')){clearTimeout(timer);resolve();}});worker.once('exit',code=>{if(!data.includes('READY')){clearTimeout(timer);reject(new Error('Lock exited '+code+': '+errors));}});});
  await operation();
 }finally{
  const exited=new Promise((resolve,reject)=>{worker.once('exit',code=>code===0?resolve():reject(new Error('Lock exit '+code+': '+errors)));});
  worker.stdin.end('\n');await exited;
 }
}
(async()=>{
 const browser=await launchBrowser(),config=path.join(site,'config.php'),original=fs.readFileSync(config);
 try{
  for(const mode of ['basic','session']){
   fs.writeFileSync(config,Buffer.concat([original,Buffer.from("\n$usesessioninsteadofbasicauth='"+(mode==='basic'?'no':'yes')+"';\n")]));
   const context=await browser.newContext(mode==='basic'?{httpCredentials:{username:'auditadmin',password:'audit-password',send:'always',origin:base}}:{});
   const errors=[];
   context.on('page',page=>{page.on('dialog',dialog=>dialog.type()==='beforeunload'?dialog.accept():dialog.dismiss());page.on('pageerror',error=>errors.push(error.message));});
   await context.route('**/*',route=>new URL(route.request().url()).origin===new URL(base).origin?route.continue():route.abort());
   try{
    if(mode==='session')equal((await context.request.post(base+'/loginsave.php?u=u5admin/',{form:{u:'auditadmin',p:'audit-password'},maxRedirects:0})).status(),302,'actual cookie login');
    equal((await context.request.get(base+'/u5admin/zr.php')).status(),200,'actual upgrade completes');
    const tokenResponse=await context.request.get(base+'/u5admin/new.php?typ=c');
    const token=(await tokenResponse.text()).match(/name="u5scrtytknfrfrms"[^>]*value="([^"]+)"/)[1];
    const name='journey-'+mode+'!js',pname='journeyl'+mode,rname='journeyr'+mode;
    for(const [n,type] of [[name,'c'],[pname,'p'],[rname,'p']])equal((await context.request.post(base+'/u5admin/new2.php?typ='+type,{form:{name:n,u5scrtytknfrfrms:token}})).status(),200,'create '+type);
    const snapshot=async n=>(await context.request.get(base+'/fixture-snapshot.php?asset='+encodeURIComponent(n))).json();
    const openCode=async()=>{const p=await context.newPage();equal((await p.goto(base+'/u5admin/coding.php?name='+encodeURIComponent(name),{waitUntil:'domcontentloaded'})).status(),200,'open real code editor');await p.waitForFunction(()=>window.u5ResourceSave&&document.form1);return p;};
    const author=await openCode(),peer=await openCode();
    const first='window.journey="Über € 中文 🌷 &amp; [literal]"; // '+mode;
    const rival='window.peer="Unsaved competing source 中文"; // '+mode;
    const revision=(await snapshot(name)).rows[0].lastmut;
    await author.locator('#codeTextarea').fill(first);
    await peer.locator('#codeTextarea').fill(rival);
    await locked(async()=>{
     const response=author.waitForResponse(r=>r.url().includes('/u5admin/coding2.php'));
     await author.locator('#savebutton').click();equal((await response).status(),409,'actual busy code save');
     await author.locator('#u5resource_status').waitFor();
     equal(await author.locator('#u5resource_status').getAttribute('role'),'alert','failed save is announced as an alert');
     equal(await author.locator('#codeTextarea').inputValue(),first,'busy code save retains exact input');
     equal((await snapshot(name)).rows[0].lastmut,revision,'busy code save preserves revision');
     equal((await snapshot(name)).rows[0].content_1||'','','busy code save preserves original source');
     equal(await author.locator('#changes').inputValue(),'1','busy code save retains unload protection');
    });
    await author.locator('#savebutton').click();
    await author.waitForFunction(()=>document.getElementById('changes').value==='0');
    equal((await snapshot(name)).rows[0].content_1,first,'retry saves exact Unicode source');
    verify((await snapshot(name)).history.includes(first),'successful retry retained in history');
    const stale=peer.waitForResponse(r=>r.url().includes('/u5admin/coding2.php'));
    await peer.locator('#savebutton').click();equal((await stale).status(),200,'peer receives actual conflict document');
    await peer.waitForFunction(()=>document.getElementById('changes').value==='1');
    equal((await snapshot(name)).rows[0].content_1,first,'peer cannot overwrite successful retry');
    equal(await peer.locator('#codeTextarea').inputValue(),rival,'peer keeps unsaved competing source');
    verify((await snapshot(name)).history.includes(rival),'peer conflict retained in real history');
    const reopened=await openCode();equal(await reopened.locator('#codeTextarea').inputValue(),first,'reopen after conflict preserves published source');
    const exported=fs.readFileSync(path.join(site,'r',name.replace(/!js$/,'.js')));
    equal(exported.subarray(0,3),Buffer.from([239,187,191]),'exact UTF-8 BOM');
    equal(exported.subarray(3).toString('utf8'),first,'published file bytes equal successful editor input');
    // Two views of the SAME page intentionally synchronize their source. Use
    // distinct pages for independent panes; separate code windows test conflicts.
    await context.addCookies([{name:'ns',value:'true',url:base},{name:'i1_p',value:pname,url:base},{name:'i2_p',value:rname,url:base},{name:'i1_l',value:'1',url:base},{name:'i2_l',value:'1',url:base}]);
    const page=await context.newPage();equal((await page.goto(base+'/u5admin/index.php',{waitUntil:'domcontentloaded'})).status(),200,'open full editorial backend');
    await page.locator('iframe[name=i1]').waitFor({state:'attached'});
    await page.locator('iframe[name=i2]').waitFor({state:'attached'});
    const left=await(await page.locator('iframe[name=i1]').elementHandle()).contentFrame();
    const right=await(await page.locator('iframe[name=i2]').elementHandle()).contentFrame();
    await left.waitForFunction(n=>document.readyState!=='loading'&&new URL(location.href).searchParams.get('c')===n&&document.form1&&document.form1.page.value===n,pname);
    await right.waitForFunction(n=>document.readyState!=='loading'&&new URL(location.href).searchParams.get('c')===n&&document.form1&&document.form1.page.value===n,rname);
    const pageSource='Journey [b]Über €[/] '+mode,peerSource='Peer [i]unsaved[/] '+mode;
    await left.locator('textarea[name=content_1]').fill(pageSource);
    await right.locator('textarea[name=content_1]').fill(peerSource);
    const pageRevision=(await snapshot(pname)).rows[0].lastmut;
    const coordinatedPages=fs.readFileSync(path.join(site,'u5admin/savepage.php'),'utf8').includes('resource-mutation.inc.php');
    if(coordinatedPages)await locked(async()=>{
     const response=page.waitForResponse(r=>r.url().includes('/u5admin/savepage.php'));
     await left.locator('#savebutton').click();equal((await response).status(),409,'real page save conflicts with active operation');
     await page.waitForFunction(()=>!u5SavePending);
     equal(await left.locator('textarea[name=content_1]').inputValue(),pageSource,'page input survives actual failed save');
     equal(await right.locator('textarea[name=content_1]').inputValue(),peerSource,'other pane retains its independent input');
     verify(await left.evaluate(()=>changes>0),'failed page retains dirty state');
     equal((await snapshot(pname)).rows[0].lastmut,pageRevision,'failed page save keeps server revision');
    });
    await left.locator('#savebutton').click();await left.waitForFunction(()=>changes===0);
    equal((await snapshot(pname)).rows[0].content_1,pageSource,'page retry succeeds');
    const peerSave=page.waitForResponse(r=>r.url().includes('/u5admin/savepage.php'));
    await right.locator('#savebutton').click();equal((await peerSave).status(),200,'other-page pane saves its own resource');
    await page.waitForFunction(()=>!u5SavePending);
    equal((await snapshot(pname)).rows[0].content_1,pageSource,'other pane preserves the first published page');
    equal((await snapshot(rname)).rows[0].content_1,peerSource,'other page receives its correct source');
    verify((await snapshot(rname)).history.includes(peerSource),'other page save retained in history');
    await page.setViewportSize({width:375,height:667});
    verify(await left.locator('textarea[name=content_1]').isVisible(),'editor remains usable in small viewport');
    await page.reload({waitUntil:'domcontentloaded'});
    await page.waitForFunction(name=>{const frame=document.querySelector('iframe[name=i1]'),win=frame&&frame.contentWindow;const form=win&&win.document.form1;return win&&win.document.readyState!=='loading'&&new URL(win.location.href).searchParams.get('c')===name&&form&&form.page&&form.page.value===name&&form.content_1;},pname);
    const fresh=page.frame({name:'i1'});
    equal(await fresh.locator('textarea[name=content_1]').inputValue(),pageSource,'reopen full backend retains successful page');
    equal(errors,[],'no JavaScript errors during real failure/retry/conflict journeys');
    console.log(mode+': real lock, retained input, retry, peer conflict and reopen completed');
   }finally{await context.close();}
  }
 }finally{fs.writeFileSync(config,original);await browser.close();}
 console.log('Editorial journey browser checks: '+checks+' passed.');
})().catch(error=>{console.error(error);process.exit(1);});
