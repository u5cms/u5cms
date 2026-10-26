const fs = require('fs');
const path = require('path');
const assert = require('node:assert/strict');
const crypto = require('crypto');
const { chromium } = require(process.env.U5_TEST_PLAYWRIGHT || 'playwright');

(async () => {
  const [, , base, site] = process.argv;
  const browser = await chromium.launch({executablePath:process.env.U5_TEST_BROWSER || undefined,headless:true,args:['--no-sandbox']});
  let checks=0;
  const verify=(condition,label)=>{assert.ok(condition,label);checks++;};
  const equal=(actual,expected,label)=>{assert.equal(actual,expected,label);checks++;};
  const snapshot=async(context,name)=>(await context.request.get(base+'/fixture-snapshot.php?asset='+encodeURIComponent(name))).json();
  const hash=name=>crypto.createHash('md5').update(name).digest('hex');
  try {
    for (const mode of ['basic','session']) {
      if (mode==='session') {
        const config=path.join(site,'config.php');
        fs.writeFileSync(config,fs.readFileSync(config).toString('latin1').replace(/\$usesessioninsteadofbasicauth\s*=\s*'no'/,"$usesessioninsteadofbasicauth='yes'"),'latin1');
      }
      const context=await browser.newContext(mode==='basic' ? {httpCredentials:{username:'auditadmin',password:'audit-password',send:'always',origin:base}} : {});
      // Block unrelated external update/telemetry URLs in this disposable backend.
      await context.route('**/*',route=>new URL(route.request().url()).origin===new URL(base).origin ? route.continue() : route.abort());
      if (mode==='session') {
        const login=await context.request.post(base+'/loginsave.php?u=u5admin/coding.php',{form:{u:'auditadmin',p:'audit-password'},maxRedirects:0});
        equal(login.status(),302,'session login uses real cookie authentication');
        verify((await context.cookies()).some(cookie=>cookie.name==='p' && cookie.httpOnly),'session password cookie is set');
      }
      const page=await context.newPage();page.on('dialog',dialog=>dialog.dismiss());
      const tokenFrom=async url=>{
        const response=await page.goto(base+'/u5admin/'+url,{waitUntil:'domcontentloaded'});
        equal(response.status(),200,mode+': backend form loads');
        return page.locator('input[name=u5scrtytknfrfrms]').inputValue();
      };
      let token=await tokenFrom('new.php?typ=c');
      const name='audit-'+mode+'!js',copy='audit-'+mode+'-copy!js',renamed='audit-'+mode+'-renamed!extra!js';
      await page.locator('input[name=name]').fill(name);
      equal(await page.locator('input[name=name]').inputValue(),name,mode+': code name UI retains dashes and suffix');
      let response=await context.request.post(base+'/u5admin/new2.php?typ=c',{form:{name,u5scrtytknfrfrms:token}});
      equal(response.status(),200,mode+': actual new C request succeeds');
      verify(fs.existsSync(path.join(site,'r',name.replace(/!js$/,'.js'))),mode+': blank C export is immediately created');
      let record=(await snapshot(context,name)).rows[0];equal(record.typ,'c',mode+': typed C exists');
      const initialRevision=record.lastmut;
      await tokenFrom('coding.php?name='+encodeURIComponent(name));
      const source='window.auditValue="中文 🌷 Über € &amp;"; const cssbase="cssbase"; // '+mode;
      await page.locator('#codeTextarea').fill(source);
      await page.locator('#savebutton').click();
      try {
      await page.waitForFunction(old=>document.form1.coco.value!==String(old) && document.getElementById('changes').value==='0',initialRevision,{timeout:15000});
      } catch(error) { console.error('Save diagnostics',await page.locator('#u5resource_status').allTextContents(),await page.frame({name:'saver'}).locator('body').innerText(),await snapshot(context,name));throw error; }
      let state=await snapshot(context,name);equal(state.rows[0].content_1,source,mode+': Unicode survives real iframe save');
      verify(state.history.includes(source),mode+': history retains exact Unicode');
      response=await context.request.get(base+'/r/'+name.replace(/!js$/,'.js'));
      equal((await response.body()).toString('utf8').replace(/^\uFEFF/,''),source,mode+': static UTF-8 export matches saved code');
      await page.reload({waitUntil:'domcontentloaded'});
      equal(await page.locator('#codeTextarea').inputValue(),source,mode+': reopening editor keeps literal entities and Unicode');
      token=await page.locator('input[name=u5scrtytknfrfrms]').inputValue();
      response=await context.request.post(base+'/u5admin/coding2.php?typ=p',{form:{name,content_1:'stale attempted source',_u5_code_encoding:'utf8',coco:initialRevision,u5scrtytknfrfrms:token}});
      equal(response.status(),200,mode+': stale save returns conflict document');
      state=await snapshot(context,name);equal(state.rows[0].content_1,source,mode+': stale editor cannot overwrite newer code');
      verify(state.history.includes('stale attempted source'),mode+': conflicting attempt remains in history');
      equal(state.rows[0].typ,'c',mode+': query parameter cannot change code kind');
      // Missing or invalid CSRF must not modify either source or history.
      response=await context.request.post(base+'/u5admin/coding2.php?typ=c',{form:{name,content_1:'forged',coco:state.rows[0].lastmut,u5resourceid:'audit_forged'}});
      equal(response.status(),403,mode+': code save checks CSRF');
      equal((await snapshot(context,name)).rows[0].content_1,source,mode+': failed CSRF keeps current source');
      const metaRevision=(await snapshot(context,name)).rows[0].lastmut;
      await tokenFrom('meta.php?typ=c&name='+encodeURIComponent(name));
      equal(await page.locator('textarea[name=content_1]').inputValue(),source,mode+': code metadata form retains Unicode and literal entities');
      await page.locator('input[name=title_1]').fill('Code metadata title');
      await page.locator('input[name=button2]').click();
      await page.waitForFunction(old=>document.form1.coco.value!==String(old),metaRevision,{timeout:15000});
      equal((await snapshot(context,name)).rows[0].content_1,source,mode+': code metadata save preserves source bytes');
      const baseline='// custom reset baseline 中文 '+mode;
      fs.writeFileSync(path.join(site,'r',name+'.tpl'),baseline);
      token=await tokenFrom('copy.php?name='+encodeURIComponent(name));
      response=await context.request.post(base+'/u5admin/copy2.php?name='+encodeURIComponent(name),{form:{name:copy,u5scrtytknfrfrms:token}});
      equal(response.status(),200,mode+': actual C copy succeeds');
      equal((await snapshot(context,copy)).rows[0].content_1,source,mode+': copy preserves raw source without Latin1 roundtrip');
      equal(fs.readFileSync(path.join(site,'r',copy+'.tpl'),'utf8'),baseline,mode+': copy retains custom reset baseline');
      verify(!fs.existsSync(path.join(site,'r',copy)),mode+': C copy creates no empty media directory');
      token=await tokenFrom('rename.php?name='+encodeURIComponent(copy));
      response=await context.request.post(base+'/u5admin/rename2.php?name='+encodeURIComponent(copy),{form:{name:renamed,ulinks:'yes',u5scrtytknfrfrms:token}});
      equal(response.status(),200,mode+': C rename succeeds');
      equal((await snapshot(context,renamed)).rows[0].content_1,source,mode+': rename retains Unicode source');
      verify(fs.existsSync(path.join(site,'r',renamed.replace(/!js$/,'.js'))) && !fs.existsSync(path.join(site,'r',copy.replace(/!js$/,'.js'))),mode+': last suffix determines renamed export');
      equal(fs.readFileSync(path.join(site,'r',renamed+'.tpl'),'utf8'),baseline,mode+': rename moves custom reset baseline');
      response=await context.request.post(base+'/u5admin/archiveunarchive2.php?name='+hash(renamed),{form:{name:renamed,u5scrtytknfrfrms:token}});
      equal(response.status(),200,mode+': code can be archived');equal((await snapshot(context,renamed)).rows[0].deleted,'2',mode+': archived state stored');
      response=await context.request.post(base+'/u5admin/archiveunarchive2.php?name='+hash(renamed),{form:{name:renamed,u5scrtytknfrfrms:token}});
      equal(response.status(),200,mode+': code can be unarchived');equal((await snapshot(context,renamed)).rows[0].deleted,'0',mode+': active state restored');
      response=await context.request.post(base+'/u5admin/delete2.php?name='+hash(renamed),{form:{name:renamed,dfiles:'no',u5scrtytknfrfrms:token}});
      equal(response.status(),200,mode+': deletion with retained files succeeds');
      verify(fs.existsSync(path.join(site,'r',renamed.replace(/!js$/,'.js'))) && fs.existsSync(path.join(site,'r',renamed+'.tpl')),mode+': explicit keep-files option is respected');
      response=await context.request.post(base+'/u5admin/new2.php?typ=c',{form:{name:renamed,u5scrtytknfrfrms:token}});
      equal(response.status(),200,mode+': deleted C can be reactivated');equal((await snapshot(context,renamed)).rows[0].content_1,source,mode+': reactivation retains previous source');
      response=await context.request.post(base+'/u5admin/delete2.php?name='+hash(renamed),{form:{name:renamed,dfiles:'yes',u5scrtytknfrfrms:token}});
      equal(response.status(),200,mode+': deletion including C files succeeds');
      verify(!fs.existsSync(path.join(site,'r',renamed.replace(/!js$/,'.js'))) && !fs.existsSync(path.join(site,'r',renamed+'.tpl')),mode+': requested deletion removes public C and reset files');
      verify((await snapshot(context,renamed)).history.includes(source),mode+': deletion retains source history');
      // A typed-looking P object must never become C through the code save endpoint.
      const ordinary='auditpage'+mode+'!js';
      response=await context.request.post(base+'/u5admin/new2.php?typ=p',{form:{name:ordinary,u5scrtytknfrfrms:token}});
      equal(response.status(),200,mode+': ordinary resource creation works');
      record=(await snapshot(context,ordinary)).rows[0];
      response=await context.request.post(base+'/u5admin/coding2.php?typ=c',{form:{name:ordinary,content_1:'unexpected executable',coco:record.lastmut,u5scrtytknfrfrms:token}});
      equal(response.status(),500,mode+': code endpoint rejects an actual P object');equal((await snapshot(context,ordinary)).rows[0].typ,'p',mode+': P resource kind preserved');
      const pageText='Ordinary page &amp; literal documentation r/cssbase.css';
      const metadata={name:ordinary,coco:record.lastmut,u5scrtytknfrfrms:token};
      for(let language=1;language<=5;language++) for(const field of ['content','title','desc','key']) metadata[field+'_'+language]=field==='content' ? pageText : field+' '+language;
      response=await context.request.post(base+'/u5admin/meta2.php?typ=p',{form:metadata});
      equal(response.status(),200,mode+': ordinary metadata save remains compatible');
      state=await snapshot(context,ordinary);equal(state.rows[0].content_1,pageText,mode+': ordinary save preserves literal entities and deliberate documentation');
      verify(state.history.includes(pageText),mode+': ordinary metadata history stays functional');
      const unmanaged='audit-'+mode+'-external!js';const unmanagedPath=path.join(site,'r',unmanaged.replace(/!js$/,'.js'));
      fs.writeFileSync(unmanagedPath,'independent bytes');
      await context.request.post(base+'/u5admin/new2.php?typ=c',{form:{name:unmanaged,u5scrtytknfrfrms:token}});
      equal((await snapshot(context,unmanaged)).rows.length,0,mode+': new C cannot take an independent physical filename');
      equal(fs.readFileSync(unmanagedPath,'utf8'),'independent bytes',mode+': failed creation keeps independent file');
      await context.close();
    }
    console.log('Backend browser checks: '+checks+' passed (real forms, iframe saves, Unicode, revisions, CSRF, new/copy/rename/archive/delete/reactivate, Basic and session authentication).');
  } finally {await browser.close();}
})().catch(error=>{console.error(error);process.exitCode=1;});
