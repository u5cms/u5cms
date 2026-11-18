const playwright=require(process.env.U5_TEST_PLAYWRIGHT || 'playwright');
async function launchBrowser() {
 const engine=process.env.U5_TEST_ENGINE || 'chromium';
 if(!['chromium','firefox','webkit'].includes(engine))throw new Error('Unsupported test browser engine: '+engine);
 const executablePath=engine==='chromium' ? (process.env.U5_TEST_BROWSER || process.env.U5_TEST_CHROMIUM || undefined) : (process.env.U5_TEST_ENGINE_PATH || undefined);
 const constrained=process.env.U5_TEST_DISABLE_BROWSER_SANDBOX==='1';
 const extra=engine==='firefox' && constrained ? {firefoxUserPrefs:{'security.sandbox.content.level':0},env:{...process.env,MOZ_DISABLE_CONTENT_SANDBOX:'1',MOZ_DISABLE_RDD_SANDBOX:'1',MOZ_DISABLE_GMP_SANDBOX:'1'}} : {};
 return playwright[engine].launch({headless:true,executablePath,args:engine==='chromium' ? ['--no-sandbox','--disable-dev-shm-usage'] : [],...extra});
}
module.exports={launchBrowser};
