// Real Chromium loader state against the actual maintenance launcher and runner.
const assert=require('node:assert/strict');
const fs=require('node:fs');
const os=require('node:os');
const path=require('node:path');
const http=require('node:http');
const {execFileSync}=require('node:child_process');
const {launchBrowser}=require('./browser-runtime.cjs');
const root=path.resolve(__dirname,'..');
const temp=fs.mkdtempSync(path.join(os.tmpdir(),'u5-background-browser-'));
fs.mkdirSync(path.join(temp,'fileversions'));
for(const [name,value] of Object.entries({lastsave:'200',lastindex:'100',indexerrunning:'0'}))fs.writeFileSync(path.join(temp,'fileversions',name+'.txt'),value);
const launcher=execFileSync(process.env.U5_TEST_PHP||'php',[path.join(__dirname,'background-launcher-fixture.php'),temp],{encoding:'utf8'});
let indexRequests=0,provisionRequests=0,active=0,maximumActive=0,foreignRequests=0;
const seen=[];
const server=http.createServer((req,res)=>{
 const url=new URL(req.url,'http://localhost');
 if(url.pathname==='/page') {
  res.setHeader('Content-Type','text/html');
  res.end('<!doctype html><title>Maintenance test</title><p id="content">The page is usable.</p>'+launcher);
 } else if(url.pathname==='/js/u5-background-work.js') {
  const file=path.join(root,'js/u5-background-work.js');
  if(!fs.existsSync(file)){res.writeHead(404);res.end();return;}
  res.setHeader('Content-Type','application/javascript');res.end(fs.readFileSync(file));
 } else if(url.pathname==='/indexer.php'||url.pathname==='/htaccess.php') {
  seen.push({path:req.url,mode:req.headers['x-u5-background-work']});
  active++;maximumActive=Math.max(maximumActive,active);
  const kind=url.pathname==='/indexer.php'?'index':'provision';
  const number=kind==='index'?++indexRequests:++provisionRequests;
  setTimeout(()=>{
   active--;
   if(req.headers['x-u5-background-work']!=='1') {res.end('<!doctype html><p>Legacy iframe work</p>');return;}
   res.setHeader('Content-Type','application/json');
   res.end(JSON.stringify({u5work:1,kind,state:kind==='index'&&number===1?'wait':'done',next:'?u5indexcontinue=1',delay:1000}));
  },1000);
 } else if(url.pathname==='/favicon.ico') {res.writeHead(204);res.end();}
 else {foreignRequests++;res.writeHead(404);res.end();}
});
(async()=>{
 let browser;
 try {
  await new Promise(resolve=>server.listen(0,'127.0.0.1',resolve));
  const origin='http://127.0.0.1:'+server.address().port;
  browser=await launchBrowser();const page=await browser.newPage();const workRequests=[];
  page.on('request',request=>{if(/\/(?:indexer|htaccess)\.php(?:[?]|$)/.test(request.url()))workRequests.push({navigation:request.isNavigationRequest(),type:request.resourceType()});});
  await page.goto(origin+'/page',{waitUntil:'load'});
  assert.equal(page.frames().length,1,'Maintenance must not create hidden browsing contexts.');
  for(let attempt=0;!active&&attempt<100;attempt++)await new Promise(resolve=>setTimeout(resolve,10));
  assert.equal(await page.evaluate(()=>document.readyState),'complete');
  assert.ok(active>0,'Background work must still run after the page has finished loading.');
  let navigations=0;page.on('framenavigated',()=>navigations++);
  await page.waitForFunction(()=>window.u5BackgroundWork&&window.u5BackgroundWork.pending()===0);
  assert.equal(navigations,0,'Index continuation must not navigate any frame.');
  assert.ok(workRequests.length>0&&workRequests.every(request=>!request.navigation&&request.type==='fetch'),'Maintenance must use fetch requests, not document loads.');
  assert.equal(indexRequests,2,'Full indexing must continue after its server-requested rest.');
  assert.equal(provisionRequests,1,'Provisioning must use the same quiet transport.');
  assert.equal(maximumActive,1,'A document must not send parallel maintenance requests.');
  assert.ok(seen.every(x=>x.mode==='1'),'Every worker request must select the structured response.');
  assert.equal(foreignRequests,0);
  await require('./turnstile-browser.cjs')(browser);
  console.log('background-browser: passed (no child frames or navigations, complete document during pending work, sequential requests, continuation)');
 } finally {
  if(browser)await browser.close();server.closeAllConnections();await new Promise(resolve=>server.close(resolve));fs.rmSync(temp,{recursive:true,force:true});
 }
})().catch(error=>{console.error(error);process.exitCode=1;});
