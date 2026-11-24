const assert=require('node:assert/strict');
const vm=require('node:vm');
const fs=require('node:fs');
const path=require('node:path');
const code=fs.readFileSync(path.join(__dirname,'../js/u5-background-work.js'),'utf8');
async function flush(){for(let i=0;i<20;i++)await Promise.resolve();}
function fixture(handler,inputs=[{url:'indexer.php?u5indexauto=1',kind:'index'}],loading=false){
 const timers=new Map(),calls=[],listeners={};let clock=0,serial=0;
 const document={baseURI:'https://cms.example.test/site/',readyState:loading?'loading':'complete',getElementById:()=>null,title:'CMS',write(){throw new Error('Document writes are forbidden.');},createElement(){throw new Error('Browsing contexts are forbidden.');}};
 const window={document,location:{origin:'https://cms.example.test',replace(){throw new Error('Navigation is forbidden.');}},u5BackgroundWorkQueue:inputs,AbortController,Audio:function(){this.play=()=>Promise.resolve();},addEventListener:(type,callback)=>{listeners[type]=callback;},setTimeout:(callback,delay)=>{const id=++serial;timers.set(id,{callback,at:clock+delay,delay});return id;},clearTimeout:id=>timers.delete(id),fetch:(url,options)=>{calls.push({url,options});return handler(url,options,calls.length);}};
 window.top=window;window.parent=window;
 const context=vm.createContext({window,document,URL,Number,Promise,Object,Error});vm.runInContext(code,context);
 return {window,calls,document,listeners,timers,async tick(maxDelay=10000){await flush();const due=[...timers.entries()].filter(([,t])=>t.delay<=maxDelay).sort((a,b)=>a[1].at-b[1].at)[0];if(due){timers.delete(due[0]);clock=due[1].at;due[1].callback();await flush();return true;}return false;}};
}
function response(reply,status=200,type='application/json'){return Promise.resolve({ok:status>=200&&status<300,status,headers:{get:()=>type},json:()=>Promise.resolve(reply)});}
(async()=>{
 let held;
 const sequential=fixture((url,options,count)=>{
  assert.equal(options.credentials,'same-origin');assert.equal(options.redirect,'error');assert.equal(options.cache,'no-store');assert.equal(options.headers['X-U5-Background-Work'],'1');
  if(count===1)return new Promise(resolve=>{held=resolve;});
  return response({u5work:1,kind:url.includes('htaccess')?'provision':'index',state:'done'});
 },[{url:'indexer.php',kind:'index'},{url:'indexer.php',kind:'index'},{url:'htaccess.php',kind:'provision'}],true);
 assert.equal(sequential.calls.length,0,'Work must wait for initial page load.');
 sequential.document.readyState='complete';sequential.listeners.load();await flush();
 assert.equal(sequential.calls.length,1,'One document must run only one worker at a time.');
 held({ok:true,status:200,headers:{get:()=> 'application/json'},json:()=>Promise.resolve({u5work:1,kind:'index',state:'wait',next:'?u5indexcontinue=1',delay:11000})});await flush();
 assert.equal(sequential.calls.length,2,'Another queued job may use the index rest interval.');
 assert.equal(sequential.window.u5BackgroundWork.pending(),1,'Waiting index work must remain scheduled.');
 assert.equal(await sequential.tick(10000),false,'The production rest must not be shortened.');
 assert.equal(await sequential.tick(11000),true);assert.equal(sequential.calls.length,3);assert.equal(sequential.window.u5BackgroundWork.pending(),0);
 const foreign=fixture(()=>response({u5work:1,kind:'index',state:'wait',next:'https://attacker.example/indexer.php',delay:1000}));await flush();
 assert.equal(foreign.calls.length,1);assert.equal(foreign.window.u5BackgroundWork.pending(),0,'A foreign continuation must stop.');
 const wrongPath=fixture(()=>response({u5work:1,kind:'index',state:'wait',next:'/delete.php',delay:1000}));await flush();assert.equal(wrongPath.window.u5BackgroundWork.pending(),0);
 for(const bad of ['html','network','wrong-kind']){
  const f=fixture(()=>bad==='network'?Promise.reject(new Error('offline')):response({u5work:1,kind:bad==='wrong-kind'?'provision':'index',state:'done'},200,bad==='html'?'text/html':'application/json'));
  await flush();await f.tick();await f.tick();await f.tick();
  assert.equal(f.calls.length,3,'Failed transport retries must be bounded: '+bad);assert.equal(f.window.u5BackgroundWork.pending(),0);
 }
 for (const [state, message] of [['busy', 'An update is already running.'], ['idle', 'Already up to date.']]) {
  const view = {textContent: ''};
  const f = fixture(() => response({u5work: 1, kind: 'index', state}),
   [{url: 'indexer.php', kind: 'index', status: 'target'}]);
  f.document.getElementById = () => view;
  await flush();
  assert.equal(view.textContent, message);
  assert.equal(f.calls.length, 1);
 }
 const failed=fixture(()=>response({u5work:1,kind:'index',state:'error'},500));await flush();assert.equal(failed.calls.length,1);assert.equal(failed.window.u5BackgroundWork.pending(),0,'Server failures must retain work for a later visit.');
 const pending=fixture(()=>response({u5work:1,kind:'index',state:'pending'},409));await flush();assert.equal(pending.window.u5BackgroundWork.pending(),0,'Exhausted named retries must stop.');
 const notify=fixture(()=>response({u5work:1,kind:'index',state:'done',completed:true}));notify.window.Audio=function(){throw new Error('Media unavailable');};await flush();assert.equal(notify.calls.length,1,'A notification failure repeated successful indexing.');assert.equal(notify.window.u5BackgroundWork.pending(),0);
 const unsupported=fixture(()=>Promise.reject(new Error('must not run')),[],true);unsupported.window.fetch=undefined;unsupported.window.u5BackgroundWorkQueue=[{url:'indexer.php',kind:'index'}];unsupported.window.u5BackgroundWork.drain();unsupported.listeners.load();await flush();assert.equal(unsupported.calls.length,0);assert.equal(unsupported.window.u5BackgroundWork.pending(),0);
 console.log('background-runner-regression: ok (page load, sequential work, duplicates, rest, continuation validation, bounded retries, login HTML, unsupported browser, notification failure)');
})().catch(error=>{console.error(error);process.exitCode=1;});
