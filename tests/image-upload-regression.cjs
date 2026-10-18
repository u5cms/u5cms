/* Invoked by image-upload-regression.py against an isolated PHP server. */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {chromium} = require('playwright');
const [work, base] = process.argv.slice(2);
const site = path.join(work, 'site'), fixtures = path.join(work, 'fixtures');
const results = [];
let sequence = 0;

(async function () {
    if (process.env.U5_TEST_CHROMIUM) fs.chmodSync(process.env.U5_TEST_CHROMIUM, 0o755);
    const browser = await chromium.launch({headless:true,
        ...(process.env.U5_TEST_CHROMIUM ? {executablePath:process.env.U5_TEST_CHROMIUM} : {}),
        args:['--no-sandbox','--disable-dev-shm-usage','--disable-gpu']});
    let checks = 0;
    try {
        async function open(typ='i', album=false) {
            const name = 'case' + (++sequence), page = await browser.newPage();
            const dialogs = [], errors = [];
            page.on('dialog', async dialog => { dialogs.push(dialog.message()); await dialog.accept(); });
            page.on('pageerror', error => errors.push(error.message));
            await page.goto(base+'/testparent.php?name='+name+'&typ='+typ+(album?'&album=1':''));
            const frame = page.frameLocator('#upload');
            await frame.locator('input[type=file]').first().waitFor();
            return {name,page,frame,dialogs,errors,typ,album};
        }
        function directory(c) { return path.join(site,'r',c.name); }
        function target(c,extension='jpg') { return path.join(directory(c),c.name+'_de.'+extension); }
        function sentinel(c) { fs.mkdirSync(directory(c),{recursive:true});fs.copyFileSync(path.join(fixtures,'sample.jpg'),target(c));return fs.readFileSync(target(c)); }
        async function upload(c,names,success=true) {
            // The harness records the response before its scripts can reload
            // the parent; do not intercept binary multipart browser requests.
            const response = c.page.waitForResponse(r=>r.url().includes(c.album?'/sendfilea2.php?':'/sendfile2.php?'),{timeout:60000});
            await c.frame.locator('input[type=file]').first().setInputFiles(names.map(n=>path.join(fixtures,n)));
            await response;
            await c.page.waitForFunction(()=>window.pending===0);
            const body = fs.readFileSync(path.join(site,'responses',c.name+'.html'),'utf8');
            assert.equal(body.includes('<b>ok</b>'),success,body.slice(-1600));
            if (success) assert.equal(c.dialogs.length,0,c.dialogs.join('\n'));
            assert.deepEqual(c.errors,[]);
            checks++;
        }
        function archived(c,extension,original,albumFilename=null) {
            const suffix = '-'+(albumFilename?albumFilename.replace(/\.jpg$/,''):c.name+'_de')+'.'+extension;
            const names = fs.readdirSync(path.join(site,'fileversions')).filter(n=>n.endsWith(suffix));
            assert.equal(names.length,1,'Original archive missing: '+suffix);
            assert.deepEqual(fs.readFileSync(path.join(site,'fileversions',names[0])),fs.readFileSync(path.join(fixtures,original)));
            checks++;
        }
        for (const file of ['sample.jpg','sample.png','sample.gif','sample.webp','sample.bmp','sample.avif',
                            'example.heic','example.heif','example.HIF','sample-raw.tif','sample-tiff_lzw.tif',
                            'sample-tiff_adobe_deflate.tif','sample-packbits.tif','sample-jpeg.tif',
                            'multiple.tiff','big-endian.tif','transparent.png','transparent.tif',
                            ...Array.from({length:8},(_,i)=>'orientation-'+(i+1)+'.tiff')]) {
            const c = await open();
            try {
                await upload(c,[file]);
                const extension = file.split('.').pop().toLowerCase();
                archived(c,extension,file);
                results.push({path:target(c),heic:/^(heic|heif|hif)$/.test(extension),orientation:Number((file.match(/orientation-(\d)/)||[])[1])||1});
            } finally {await c.page.close();}
        }
        // Browser-visible free formats remain byte-identical, including SVG.
        for (const file of ['transparent.png','sample.gif','sample.svg','sample.avif']) {
            const c = await open('f');
            try {await upload(c,[file]);assert.deepEqual(fs.readFileSync(target(c,file.split('.').pop())),fs.readFileSync(path.join(fixtures,file)));checks++;}
            finally {await c.page.close();}
        }
        for (const file of ['example.heic','sample-raw.tif']) {
            const c = await open('f');
            try {await upload(c,[file]);archived(c,file.split('.').pop(),file);results.push({path:target(c),heic:file.endsWith('.heic')});}
            finally {await c.page.close();}
        }
        // Generic document uploads are not raster-converted.
        {
            const c=await open('d');
            try {await upload(c,['example.heic']);assert.deepEqual(fs.readFileSync(target(c,'heic')),fs.readFileSync(path.join(fixtures,'example.heic')));checks++;}
            finally {await c.page.close();}
        }
        // Companion indices must match a mixed batch, even with gaps.
        {
            const files=['sample.jpg','sample-raw.tif','example.heic','sample.png','sample.avif'];
            const c=await open('a',true);
            try {
                await upload(c,files);
                const names=fs.readdirSync(directory(c)).sort();assert.equal(names.length,files.length);
                names.forEach((name,i)=>{archived(c,files[i].split('.').pop(),files[i],name);results.push({path:path.join(directory(c),name),heic:files[i].endsWith('.heic')});});
            } finally {await c.page.close();}
        }
        // Existing JPG-only batches retain their original 19-file capacity.
        {
            const c=await open('a',true);
            try {await upload(c,Array(19).fill('sample.jpg'));assert.equal(fs.readdirSync(directory(c)).length,19);checks++;}
            finally {await c.page.close();}
        }
        // A failed conversion submits no part of a batch and releases the guard.
        for (const file of ['broken.heic','broken.tif','too-many-pixels.tif']) {
            const c=await open();const old=sentinel(c);let posts=0;
            c.page.on('request',r=>{if(r.method()==='POST')posts++;});
            try {
                await c.frame.locator('input[type=file]').setInputFiles(path.join(fixtures,file));
                await c.frame.locator('form').evaluate(form=>new Promise(resolve=>{const check=()=>form.dataset.u5Busy==='0'?resolve():setTimeout(check,25);check();}));
                assert(c.dialogs.length>0);assert.equal(posts,0);assert.deepEqual(fs.readFileSync(target(c)),old);assert.equal(await c.page.evaluate(()=>pending),0);checks++;
            } finally {await c.page.close();}
        }
        for (const [attribute,value,names,album] of [
            ['u5MaxFile','1',['example.heic'],false],
            ['u5MaxPost','65536',['example.heic'],false],
            ['u5MaxFiles','3',['sample-raw.tif','sample-raw.tif'],true]]) {
            const c=await open(album?'a':'i',album);const old=sentinel(c);let posts=0;
            c.page.on('request',r=>{if(r.method()==='POST')posts++;});
            try {
                await c.frame.locator('form').evaluate((form,pair)=>{form.dataset[pair[0]]=pair[1]},[attribute,value]);
                await c.frame.locator('input[type=file]').setInputFiles(names.map(n=>path.join(fixtures,n)));
                assert(c.dialogs.length>0);assert.equal(posts,0);assert.deepEqual(fs.readFileSync(target(c)),old);assert.equal(await c.page.evaluate(()=>pending),0);checks++;
            } finally {await c.page.close();}
        }
        {
            const c=await open('a',true);const old=sentinel(c);let posts=0;
            c.page.on('request',r=>{if(r.method()==='POST')posts++;});
            try {
                await c.page.route('**/image-upload-worker.js',route=>route.abort());
                await c.frame.locator('input[type=file]').setInputFiles(['sample.jpg','sample-raw.tif'].map(n=>path.join(fixtures,n)));
                await c.frame.locator('form').evaluate(form=>new Promise(resolve=>{const check=()=>form.dataset.u5Busy==='0'?resolve():setTimeout(check,25);check();}));
                assert(c.dialogs.length>0);assert.equal(posts,0);assert.deepEqual(fs.readFileSync(target(c)),old);assert.equal(await c.page.evaluate(()=>pending),0);checks++;
            } finally {await c.page.close();}
        }
        // Server-side validation is also exercised with forged/missing parts.
        for (const scenario of ['missing','bad-original','bad-jpeg','truncated-jpeg','csrf']) {
            const c=await open();const old=sentinel(c);
            try {
                const frame=c.page.frames().find(f=>f.url().includes('/sendfile.php?'));
                const action=await frame.locator('form').getAttribute('action');
                const token=await frame.locator('[name=u5scrtytknfrfrms]').inputValue();
                const multipart={name:c.name,u5scrtytknfrfrms:scenario==='csrf'?'bad':token,
                    userfile:{name:'original.heic',mimeType:'image/heic',buffer:fs.readFileSync(path.join(fixtures,scenario==='bad-original'?'broken.heic':'example.heic'))}};
                if(scenario!=='missing') {
                    let jpeg=fs.readFileSync(path.join(fixtures,'sample.jpg'));
                    if(scenario==='bad-jpeg')jpeg=Buffer.from('not jpeg');
                    if(scenario==='truncated-jpeg')jpeg=jpeg.subarray(0,jpeg.length-2);
                    multipart.u5converted={name:'converted.jpg',mimeType:'image/jpeg',buffer:jpeg};
                }
                const response=await c.page.request.post(new URL(action,frame.url()).href,{multipart});
                assert(!(await response.text()).includes('<b>ok</b>'));assert.deepEqual(fs.readFileSync(target(c)),old);checks++;
            } finally {await c.page.close();}
        }
        // Archive failure must prevent both replacement and album insertion.
        for (const album of [false,true]) {
            const c=await open(album?'a':'i',album);const old=sentinel(c);
            const archiveStem=album?c.name+'_100':c.name+'_de';
            const date=new Date().toISOString().slice(0,10).replace(/-/g,'');
            fs.mkdirSync(path.join(site,'fileversions',date+'-'+archiveStem+'.tif'));
            try {
                await upload(c,['sample-raw.tif'],false);
                assert.deepEqual(fs.readFileSync(target(c)),old);
                if(album)assert(!fs.existsSync(path.join(directory(c),c.name+'_100.jpg')));
                checks++;
            } finally {await c.page.close();}
        }
        // Publication failure must preserve a previous file with another suffix.
        {
            const c=await open();fs.mkdirSync(target(c),{recursive:true});
            const old=Buffer.from('previous image');fs.writeFileSync(target(c,'png'),old);
            try {await upload(c,['sample.jpg'],false);assert.deepEqual(fs.readFileSync(target(c,'png')),old);checks++;}
            finally {await c.page.close();}
        }
        // A full album must not reuse a stale filename and overwrite an image.
        {
            const c=await open('a',true);fs.mkdirSync(directory(c),{recursive:true});
            for(let i=100;i<=1000;i++)fs.writeFileSync(path.join(directory(c),c.name+'_'+i+'.jpg'),'previous-'+i);
            try {await upload(c,['sample.jpg'],false);assert.equal(fs.readdirSync(directory(c)).length,901);assert.equal(fs.readFileSync(path.join(directory(c),c.name+'_100.jpg'),'utf8'),'previous-100');checks++;}
            finally {await c.page.close();}
        }
        fs.writeFileSync(path.join(work,'results.json'),JSON.stringify(results));
        console.log('PASS: '+checks+' upload/archive/failure checks in Chromium '+browser.version());
    } finally {await browser.close();}
})().catch(error=>{console.error(error);process.exit(1)});
