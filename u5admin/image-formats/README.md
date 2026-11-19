# Additional image upload formats

Standard images and albums additionally accept HEIC, HEIF, HIF, TIF and TIFF.
Free images additionally convert these formats to JPEG for browser display.
The original is kept in `fileversions`; dimensions and aspect ratio are kept.
TIFF uses the first image/page and applies its orientation. Transparent pixels
in a JPEG receive a white background. Existing free SVG, PNG, GIF, WebP and
AVIF uploads retain their format and, where applicable, animation/transparency.

The new formats are decoded locally in the browser, using terminable workers
and a 60-megapixel limit. No public CDN or external conversion service is used.
They require JavaScript and a current browser. Standard images/albums also use
browser decoding for PNG, GIF, WebP, BMP or AVIF if that server's GD loader is
missing; working server loaders continue to use the previous conversion path.

The original and converted image are submitted together. Both count towards
PHP's file-count and POST limits. The form checks those limits before submission
and asks for a smaller batch where necessary. The server validates the original
signature and JPEG companion before publishing. `move_uploaded_file()` still
receives a genuine uploaded temporary file.

## Deployment and release compatibility

Deploy all changed PHP/JS files and the complete `image-formats` directory.
The source archives are included for the bundled LGPL decoder.
This change does not modify the u5CMS version number or reserve a release tag.

The upload publisher and upload-preservation changes are identical to those
already present in `12.10.0-Multi-Optimizations-by-AI`; no unrelated changes from
that development branch are included. Existing files are published atomically
before obsolete extensions are removed. The new decoding step precedes this
publisher, so missing conversions cannot remove the previous published file.
Originals of converted images are archived atomically before publication;
if the archive cannot be written, the existing image is retained.

## Regression checks

Run `python3 tests/image-upload-regression.py` with PHP/GD, Pillow, Node and
Playwright/Chromium installed. The test creates a temporary CMS filesystem and
uses the real upload forms, multipart handlers, CSRF checks and image decoders.
Only the database and login environment are stubbed; no real CMS data is used.
`U5_TEST_PHP`, `U5_TEST_PHP_ARGS` (a JSON array) and `U5_TEST_CHROMIUM` can select
alternate local test runtimes.

Checks cover existing formats, mixed albums, original bytes, eight TIFF
orientations, several TIFF compressions, missing/corrupt conversion parts,
decoder failures, upload limits, a full album, and failed publication. The
output images are also inspected for dimensions and expected colors.

## Bundled dependencies

The scripts and source archives in `vendor` are unmodified copies from the
photo-card implementation. Each script is independently replaceable.

| Component | Version/license | Upstream |
| --- | --- | --- |
| heic-to | 1.6.6, LGPL-3.0 | https://github.com/hoppergee/heic-to |
| libheif | 1.23.6, LGPL-3.0 | https://github.com/strukturag/libheif |
| libde265 | 1.0.16, LGPL-3.0 | https://github.com/strukturag/libde265 |
| UTIF.js | MIT | https://github.com/photopea/UTIF.js |
| pako | 2.1.0, MIT/Zlib | https://github.com/nodeca/pako |

License texts and corresponding source archives are supplied alongside the
scripts. The HEIC decoder includes its own worker and codec; TIFF uses UTIF.js
and pako in `image-upload-worker.js`.
