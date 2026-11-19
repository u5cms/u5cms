const {launchBrowser}=require('./browser-runtime.cjs');
const fs=require('node:fs');
const path=require('node:path');
const {execFileSync}=require('node:child_process');
const [base,issuer,site]=process.argv.slice(2);
const configPath=path.join(site,'config.php');
const originalConfig=fs.readFileSync(configPath);
let checks=0;
function check(value,message){if(!value)throw new Error(message);checks++;}
function config(append=''){fs.writeFileSync(configPath,Buffer.concat([originalConfig,Buffer.from('\n'+append+'\n')]));}
function fixture(action){return execFileSync(process.env.U5_TEST_PHP||'php',[...JSON.parse(process.env.U5_TEST_PHP_ARGS||'[]'),path.join(__dirname,'oidc-web-fixture.php'),action,process.env.U5_OIDC_TEST_DATABASE],{encoding:'utf8'});}
(async()=>{
 const browser=await launchBrowser();
 const contexts=[];
 async function context(){const c=await browser.newContext({ignoreHTTPSErrors:true});contexts.push(c);await c.route('**/*',route=>{const u=route.request().url();return u.startsWith(new URL(base).origin)||u.startsWith(new URL(issuer).origin)?route.continue():route.abort();});return c;}
 async function mode(value='normal',rotate=false){const c=await context();await c.request.get(new URL('/control',issuer).href+'?mode='+value+(rotate?'&rotate=1':''));await c.close();}
 async function begin(c,target='u5admin/'){const p=await c.newPage();await p.goto(base+'/oidc/login.php?u='+encodeURIComponent('/cms/'+target));await p.locator('button[value="admin-sub"]').waitFor();return p;}
 async function login(c,subject,target='u5admin/'){
  const page=await begin(c,target);let callbackURL,callbackStatus;
  page.on('response',r=>{if(r.url().includes('/oidc/callback.php?')){callbackURL=r.url();callbackStatus=r.status();}});
  await page.locator('button[value="'+subject+'"]').click();await page.waitForLoadState('domcontentloaded');
  return {page,callbackURL,callbackStatus};
 }
 try{
  const before=fixture('snapshot');
  const anonymous=await context();
  let r=await anonymous.request.get(base+'/index.php?c=!oidcprivate');check(r.status()===200||r.status()===403,'anonymous login response');check(!(await r.text()).includes('PRIVATE-INTRANET'),'anonymous intranet denied');
  r=await anonymous.request.get(base+'/f.php?f=r/oidc-files/private.txt');check(r.status()===401,'anonymous download denied');check(r.headers()['cache-control']==='no-store','failed download not cacheable');
  await anonymous.addCookies([{name:'u',value:'admin@example.org',url:base},{name:'p',value:process.env.U5_OIDC_LEGACY_COOKIE,url:base}]);
  r=await anonymous.request.get(base+'/u5admin/');check(!(await r.text()).includes('Welcome admin@example.org'),'valid legacy cookie cannot bypass OIDC');
  const admin=await context();const a=await login(admin,'admin-sub');
  check(a.callbackStatus===303,'real authorization code callback succeeded');
  check((await a.page.title()).includes('admin@example.org'),'CMS backend recognizes mapped admin');
  const cookie=(await admin.cookies()).find(c=>c.name.startsWith('u5oidc_'));
  check(cookie&&cookie.secure&&cookie.httpOnly&&cookie.sameSite==='Lax'&&cookie.path==='/cms/','isolated secure session cookie');
  check(!(await admin.cookies()).some(c=>c.name==='u'||c.name==='p'),'no legacy credential cookies issued');
  r=await admin.request.get(a.callbackURL,{maxRedirects:0});check(r.status()===400,'used callback cannot be replayed');
  r=await admin.request.get(base+'/u5admin/acc0.php');check((await r.text()).includes('Current backend users'),'higher admin right preserved');
  for(const endpoint of ['reset.php','reset2.php','sendintranetlogin.php','u5admin/pwd.php','u5admin/pwd2.php']){
   r=await admin.request.get(base+'/'+endpoint);check((await r.text()).includes('OIDC identity provider'),'password lifecycle delegated: '+endpoint);
  }
  check(fixture('snapshot')===before,'OIDC login and password screens preserve all account passwords and rights');
  const editor=await context();await login(editor,'editor-sub');
  r=await editor.request.get(base+'/u5admin/acc0.php');check(!(await r.text()).includes('Current backend users'),'OIDC does not grant higher admin rights');
  fixture('revoke-backend');r=await editor.request.get(base+'/u5admin/');check(r.status()===403,'backend account deletion revokes access on next request');
  const member=await context();const m=await login(member,'member-sub','index.php?c=!oidcprivate');
  check((await m.page.content()).includes('PRIVATE-INTRANET'),'intranet membership works');
  r=await member.request.get(base+'/u5admin/');check(r.status()===403,'intranet member gets no backend rights');
  r=await member.request.get(base+'/index.php?c=oidcgroup');check(!(await r.text()).includes('PRIVATE-GROUP'),'intranet member gets no page-group rights');
  r=await member.request.get(base+'/f.php?f=r/oidc-files/private.txt');check(r.status()===200&&(await r.text())==='PRIVATE-DOWNLOAD','real protected download works');
  r=await member.request.get(base+'/thumb.php?f=r/oidc-files/private.png&w=1&h=1');check(r.status()===200,'protected thumbnail gate works');
  const passwdPath=path.join(site,'r/oidc-files/.htpasswd');const passwd=fs.readFileSync(passwdPath);
  fs.writeFileSync(passwdPath,'other@example.org:{SHA}unusable\n');
  r=await member.request.get(base+'/f.php?f=r/oidc-files/private.txt');check(r.status()===401,'file-specific revocation takes effect immediately');
  fs.writeFileSync(passwdPath,passwd);
  fixture('revoke-member');r=await member.request.get(base+'/index.php?c=!oidcprivate');check(!(await r.text()).includes('PRIVATE-INTRANET'),'intranet revocation takes effect immediately');fixture('restore-member');
  const pageUser=await context();const p=await login(pageUser,'page-sub','index.php?c=oidcgroup');
  check((await p.page.content()).includes('PRIVATE-GROUP'),'page-specific login works');
  r=await pageUser.request.post(base+'/oidc-form-probe.php?n=oidcgroup',{form:{answer:'kept'}});check((await r.text()).includes('FORM-GATE-PASSED'),'real frontend form authorisation accepts mapped page member');
  r=await member.request.post(base+'/oidc-form-probe.php?n=oidcgroup',{form:{answer:'kept'}});check(!(await r.text()).includes('FORM-GATE-PASSED'),'frontend form gate refuses other mapped identity');
  const unknown=await context();const un=await login(unknown,'unknown-sub');check(un.callbackStatus===400,'unmapped subject with administrator email claim rejected');
  check(!(await un.page.content()).includes('unknown-sub'),'error does not expose subject');
  const noRights=await context();const nr=await login(noRights,'no-rights-sub');check(nr.callbackStatus===303,'known subject authenticates');check((await nr.page.content()).includes('no access'),'authentication does not create CMS account');
  for(const failure of ['bad_nonce','bad_audience','bad_token_issuer','expired_token','missing_id_token','bad_json','token_redirect','missing_response_issuer','wrong_response_issuer']){
   await mode(failure);const c=await context();const bad=await login(c,'admin-sub');check(bad.callbackStatus===400,'callback rejects '+failure);
   r=await c.request.get(base+'/u5admin/');check(!(await r.text()).includes('Welcome admin@example.org'),'failed callback grants no rights: '+failure);await c.close();
  }
  await mode('normal');const cancelled=await context();const can=await login(cancelled,'cancel');check(can.callbackStatus===400,'provider cancellation handled');
  for(const failure of ['wrong_discovery_issuer','untrusted_keys','discovery_redirect','oversized_metadata']){
   await mode(failure);const c=await context();r=await c.request.get(base+'/oidc/login.php',{maxRedirects:0});check(r.status()===400,'discovery rejects '+failure);await c.close();
  }
  await mode('normal',true);const rotated=await context();const rot=await login(rotated,'admin-sub');check(rot.callbackStatus===303,'provider signing-key rotation works');
  const parallel=await context();const tab1=await begin(parallel,'index.php?c=oidcpublic');const tab2=await begin(parallel,'index.php?c=oidcpublic');
  await tab1.locator('button[value="admin-sub"]').click();await tab1.waitForURL(base+'/index.php?c=oidcpublic');
  await tab2.locator('button[value="admin-sub"]').click();await tab2.waitForURL(base+'/index.php?c=oidcpublic');check(true,'parallel login tabs survive session ID rotation');
  const pending=await begin(admin,'index.php?c=oidcpublic');
  await a.page.goto(base+'/logout.php');await a.page.waitForURL(base+'/index.php?c=loggedout');
  check(!(await admin.cookies()).some(c=>c.name.startsWith('u5oidc_')),'RP-initiated logout clears local session');
  await pending.locator('button[value="admin-sub"]').click();await pending.waitForLoadState('domcontentloaded');
  check((await pending.content()).includes('could not be completed'),'logout cancels pending authorisation');
  r=await admin.request.get(base+'/u5admin/');check(!(await r.text()).includes('Welcome admin@example.org'),'logged-out user denied');
  config("$sessioncookiehashsalt='rotated-test-salt';");
  r=await member.request.get(base+'/index.php?c=!oidcprivate');check(!(await r.text()).includes('PRIVATE-INTRANET'),'installation secret rotation revokes OIDC session');config();
  config("$u5oidc['ca_bundle']='';");
  r=await anonymous.request.get(base+'/oidc/login.php',{maxRedirects:0});check(r.status()===400,'untrusted TLS certificate is rejected');config();
  config("$u5oidc['client_secret']='';");
  r=await anonymous.request.get(base+'/u5admin/');check(r.status()===503,'broken OIDC configuration fails closed');config();
  for(const attack of ['//evil.invalid','https://evil.invalid/cms/','/cms/../outside','/cms/%252e%252e/outside','/cms/%0d%0aLocation:evil']){
   r=await anonymous.request.get(base+'/oidc/login.php?u='+encodeURIComponent(attack),{maxRedirects:0});check(r.status()===400&&!r.headers().location,'return URL attack rejected');
  }
  config("$u5oidc['enabled']=false;");
  const local=await context();r=await local.request.get(base+'/oidc/login.php');check(r.status()===404,'disabled OIDC endpoint is unavailable');
  r=await local.request.post(base+'/loginsave.php?u=u5admin/',{form:{u:'admin@example.org',p:'fixture-password'},maxRedirects:0});check(r.status()===302,'local cookie login preserved when OIDC disabled');
  r=await local.request.get(base+'/u5admin/');check((await r.text()).includes('Welcome admin@example.org'),'disabled OIDC preserves local backend authentication');
  config("$u5oidc['enabled']=false;\n$u5samlsalt='saml-test-salt';\n$u5samlnonce='saml-test-nonce';\n$u5samlmockauth='yes';\n$u5samlmockauthusername='member@example.org';\n$u5samlinfrontend='yes';\n$u5samlautointranetenrollment='no';");
  const saml=await context();const sp=await saml.newPage();await sp.goto(base+'/saml/login.php?u='+encodeURIComponent(base+'/index.php?c=!oidcprivate'));await sp.waitForURL(base+'/index.php?c=!oidcprivate&*');
  check((await sp.content()).includes('PRIVATE-INTRANET'),'existing SAML mock-login path remains functional');
  config();
  console.log('OIDC browser ('+(process.env.U5_TEST_ENGINE||'chromium')+'): '+checks+' checks passed');
 }finally{config();await Promise.all(contexts.map(c=>c.close().catch(()=>{})));await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
