/* Add HEIC/HEIF/HIF and TIFF without changing existing upload formats. */
(function () {
    'use strict';
    var root = new URL('.', document.currentScript.src).href;
    var maxPixels = 60000000;

    function extension(file) { return (file.name.split('.').pop() || '').toLowerCase(); }
    function extended(file) { return /^(heic|heif|hif|tif|tiff)$/.test(extension(file)); }
    function jpeg(canvas) {
        return new Promise(function (resolve, reject) {
            canvas.toBlob(function (blob) {
                canvas.width = canvas.height = 1;
                if (blob && blob.type === 'image/jpeg') resolve(blob);
                else reject(new Error('This image is too large for this browser. Please use a smaller image.'));
            }, 'image/jpeg', 0.98);
        });
    }
    function canvasFor(width, height) {
        if (!width || !height || width * height > maxPixels) throw new Error('Image exceeds 60 megapixels.');
        var canvas = document.createElement('canvas');
        canvas.width = width; canvas.height = height;
        if (!canvas.getContext('2d')) throw new Error('This browser cannot convert the image.');
        return canvas;
    }
    function nativeImage(file) {
        return new Promise(function (resolve, reject) {
            var url = URL.createObjectURL(file), image = new Image();
            var timer = setTimeout(function () { finish(new Error('The image could not be opened.')); }, 30000);
            function finish(error, blob) {
                clearTimeout(timer); image.onload = image.onerror = null;
                URL.revokeObjectURL(url); image.src = '';
                if (error) reject(error); else resolve(blob);
            }
            image.onerror = function () { finish(new Error('Native image decoder unavailable.')); };
            image.onload = function () {
                var canvas;
                try {
                    canvas = canvasFor(image.naturalWidth, image.naturalHeight);
                    var ctx = canvas.getContext('2d');
                    ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(image, 0, 0);
                    jpeg(canvas).then(function (blob) { finish(null, blob); }, finish);
                }
                catch (error) { if (canvas) canvas.width = canvas.height = 1; finish(error); }
            };
            image.src = url;
        });
    }
    async function decodeInWorker(file, kind) {
        var buffer = kind === 'tiff' ? await file.arrayBuffer() : null;
        return new Promise(function (resolve, reject) {
            var worker = new Worker(root + 'image-upload-worker.js');
            var timer = setTimeout(function () { worker.terminate(); reject(new Error('Image conversion timed out. Please use a smaller image or try JPEG/PNG.')); }, 45000);
            worker.onerror = function () { clearTimeout(timer); worker.terminate(); reject(new Error('The image decoder could not be loaded.')); };
            worker.onmessage = function (event) {
                clearTimeout(timer); worker.terminate();
                if (event.data.error) reject(new Error(event.data.error)); else resolve(event.data);
            };
            if (buffer) worker.postMessage({kind:kind, buffer:buffer}, [buffer]);
            else worker.postMessage({kind:kind, file:file});
        });
    }
    async function tiff(file) {
        var decoded = await decodeInWorker(file, 'tiff');
        var swapped = decoded.orientation >= 5 && decoded.orientation <= 8;
        var canvas = canvasFor(swapped ? decoded.height : decoded.width, swapped ? decoded.width : decoded.height);
        var pixels = canvasFor(decoded.width, decoded.height);
        pixels.getContext('2d').putImageData(new ImageData(new Uint8ClampedArray(decoded.rgba), decoded.width, decoded.height), 0, 0);
        var w = decoded.width, h = decoded.height, ctx = canvas.getContext('2d');
        var transforms = {2:[-1,0,0,1,w,0],3:[-1,0,0,-1,w,h],4:[1,0,0,-1,0,h],5:[0,1,1,0,0,0],6:[0,1,-1,0,h,0],7:[0,-1,-1,0,h,w],8:[0,-1,1,0,0,w]};
        ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, canvas.width, canvas.height);
        if (transforms[decoded.orientation]) ctx.setTransform.apply(ctx, transforms[decoded.orientation]);
        ctx.drawImage(pixels, 0, 0); pixels.width = pixels.height = 1;
        return jpeg(canvas);
    }
    async function convert(file) {
        if (/^tiff?$/.test(extension(file))) return tiff(file);
        if (!/^(heic|heif|hif)$/.test(extension(file))) return nativeImage(file);
        // Test the actual <img>/canvas path. ImageBitmap support alone does not
        // imply that a HEIC can be displayed or encoded by the current browser.
        try { return await nativeImage(file); }
        catch (error) {
            if (error.message.indexOf('60 megapixels') >= 0) throw error;
            var decoded = await decodeInWorker(file, 'heic');
            var bitmap = decoded.bitmap;
            var canvas;
            try {
                canvas = canvasFor(bitmap.width, bitmap.height);
                var ctx = canvas.getContext('2d');
                ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(bitmap, 0, 0);
                return await jpeg(canvas);
            }
            finally { if (bitmap.close) bitmap.close(); if (canvas) canvas.width = canvas.height = 1; }
        }
    }
    function removeCompanions(form) {
        Array.prototype.forEach.call(form.querySelectorAll('[data-u5-converted]'), function (element) { element.remove(); });
    }
    window.u5ImageUpload = async function (input) {
        var form = input.form;
        if (form.dataset.u5Busy === '1' || !input.files.length) return;
        var files = Array.prototype.slice.call(input.files);
        var fallback = (form.dataset.u5NativeFallback || '').split(',');
        function needsConversion(file) { return extended(file) || fallback.indexOf(extension(file)) >= 0; }
        var count = files.filter(needsConversion).length;
        var spinner = document.getElementById('spinner');
        var started = false, submitted = false, current = '';
        form.dataset.u5Busy = '1';
        try {
            removeCompanions(form);
            parent.upstart(); started = true;
            spinner.style.display = 'inline';
            if (!count) { form.submit(); submitted = true; return; }
            if (!window.DataTransfer || !window.Worker || !window.File || !Blob.prototype.arrayBuffer) throw new Error('Please use a current browser to upload HEIC or TIFF images.');
            var maxFiles = Number(form.dataset.u5MaxFiles), maxFile = Number(form.dataset.u5MaxFile), maxPost = Number(form.dataset.u5MaxPost);
            if (maxFiles > 0 && files.length + count > maxFiles) throw new Error('Too many files at once: HEIC/TIFF images also need a converted JPEG. Please upload fewer images together.');
            var total = files.reduce(function (sum, file) { return sum + file.size + 2048; }, 65536);
            for (var i = 0; i < files.length; i++) {
                if (maxFile > 0 && files[i].size > maxFile) throw new Error('A selected file exceeds the server upload limit. Please use a smaller file.');
            }
            if (maxPost > 0 && total > maxPost) throw new Error('The selected files exceed the total server upload limit. Please select fewer or smaller images.');
            input.disabled = true;
            for (var index = 0; index < files.length; index++) {
                if (!needsConversion(files[index])) continue;
                current = files[index].name;
                var blob = await convert(files[index]);
                if (maxFile > 0 && blob.size > maxFile) throw new Error('The converted JPEG exceeds the server upload limit. Please use a smaller image.');
                total += blob.size + 2048;
                if (maxPost > 0 && total > maxPost) throw new Error('Original images and converted JPEGs exceed the total server upload limit. Please upload fewer images together.');
                var companion = document.createElement('input');
                companion.type = 'file'; companion.hidden = true; companion.setAttribute('data-u5-converted', '1');
                companion.name = input.multiple ? 'u5converted[' + index + ']' : 'u5converted';
                var transfer = new DataTransfer();
                transfer.items.add(new File([blob], 'converted.jpg', {type:'image/jpeg'}));
                companion.files = transfer.files;
                if (companion.files.length !== 1) throw new Error('This browser cannot submit the converted image.');
                form.appendChild(companion);
            }
            input.disabled = false;
            form.submit(); submitted = true;
        }
        catch (error) {
            removeCompanions(form);
            alert((current ? current + ': ' : '') + (error.message || 'This image could not be converted. Please try JPEG or PNG.'));
        }
        finally {
            input.disabled = false;
            if (!submitted) {
                if (started) parent.upend();
                spinner.style.display = 'none'; input.value = ''; form.dataset.u5Busy = '0';
            }
        }
    };
})();
