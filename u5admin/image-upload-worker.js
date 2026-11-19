/* Decoders are isolated and can be terminated on errors or timeouts. */
self.onmessage = async function (event) {
    try {
        if (event.data.kind === 'heic') {
            importScripts('image-formats/vendor/heic-to.js');
            var bitmap = await HeicTo({blob:event.data.file, type:'bitmap'});
            if (!bitmap.width || !bitmap.height || bitmap.width * bitmap.height > 60000000) {
                bitmap.close(); throw new Error('Image exceeds 60 megapixels.');
            }
            self.postMessage({bitmap:bitmap}, [bitmap]);
            return;
        }
        importScripts('image-formats/vendor/pako.min.js', 'image-formats/vendor/UTIF.js');
        var buffer = event.data.buffer;
        var pages = UTIF.decode(buffer);
        var page = pages.filter(function (item) { return item.t256 && item.t257; })[0];
        if (!page) throw new Error('No image found in this TIFF.');
        var width = Number(page.t256[0]), height = Number(page.t257[0]);
        if (!width || !height || width * height > 60000000) throw new Error('Image exceeds 60 megapixels.');
        UTIF.decodeImage(buffer, page);
        var rgba = UTIF.toRGBA8(page);
        if (rgba.length !== width * height * 4) throw new Error('Invalid TIFF image data.');
        self.postMessage({width:width, height:height, orientation:Number((page.t274 || [1])[0]), rgba:rgba.buffer}, [rgba.buffer]);
    }
    catch (error) { self.postMessage({error:error && error.message ? error.message : 'This image could not be decoded.'}); }
};
