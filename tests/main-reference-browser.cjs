const {launchBrowser}=require('./browser-runtime.cjs');
const fs=require('fs');
(async()=>{
 const input=JSON.parse(fs.readFileSync(0,'utf8'));
 const browser=await launchBrowser();
 try{
  const page=await browser.newPage();const errors=[];
  page.on('pageerror',error=>errors.push(error.message));
  const response=await page.goto(input.base+'/index.php?c=refform&l=de');
  if(response.status()!==200)throw new Error('Original/target form is not reachable.');
  if((await page.evaluate(()=>document.characterSet)).toLowerCase()!=='windows-1252')throw new Error('Fixture did not exercise the original browser encoding.');
  for(const [name,value] of Object.entries(input.fields))await page.locator('textarea[name="'+name+'"]').fill(value);
  const saved=page.waitForResponse(response=>response.url().includes('/formsave.php?'));
  await page.locator('#reference-submit').click();
  const result=await saved;
  if(result.status()!==200)throw new Error('Browser form submission failed: '+result.status());
  await page.waitForFunction(()=>window.referenceSaved===true);
  if(errors.length)throw new Error('Browser error: '+errors.join('; '));
  process.stdout.write(JSON.stringify({charset:await page.evaluate(()=>document.characterSet),callback:true,errors}));
 }finally{await browser.close();}
})().catch(error=>{console.error(error);process.exit(1);});
