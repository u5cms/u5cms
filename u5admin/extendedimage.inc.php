<?php
// Only the new raster formats use a browser-generated JPEG companion.
// Keep the actual uploaded temporary path so move_uploaded_file() and the
// optional atomic publisher in newer u5CMS versions retain their guarantees.
function u5IsExtendedImageExtension($extension) {
    return in_array(strtolower($extension), array('heic','heif','hif','tif','tiff'), true);
}

function u5PrepareExtendedImage(&$file, $converted, &$extension, &$original, &$originalExtension) {
    $nativeFallback = in_array($extension, array('png','gif','webp','bmp','avif'), true) && is_array($converted);
    if (!u5IsExtendedImageExtension($extension) && !$nativeFallback) return '';
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return 'The original image upload is incomplete. Please select the file again.';
    }
    if (!is_array($converted) || !isset($converted['error']) || $converted['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($converted['tmp_name'])) {
        return 'Image conversion is missing or incomplete. Please enable JavaScript and select the image again in a current browser.';
    }
    $header = (string)@file_get_contents($file['tmp_name'], false, null, 0, 256);
    $valid = false;
    if ($extension === 'tif' || $extension === 'tiff') {
        $valid = substr($header, 0, 4) === "II\x2a\x00" || substr($header, 0, 4) === "MM\x00\x2a";
    }
    elseif ($extension === 'png') $valid = substr($header, 0, 8) === "\x89PNG\x0d\x0a\x1a\x0a";
    elseif ($extension === 'gif') $valid = substr($header, 0, 6) === 'GIF87a' || substr($header, 0, 6) === 'GIF89a';
    elseif ($extension === 'webp') $valid = substr($header, 0, 4) === 'RIFF' && substr($header, 8, 4) === 'WEBP';
    elseif ($extension === 'bmp') $valid = substr($header, 0, 2) === 'BM';
    elseif (strlen($header) >= 16 && substr($header, 4, 4) === 'ftyp') {
        $length = unpack('Nlength', substr($header, 0, 4));
        $end = min(strlen($header), $length['length']);
        $brands = $extension === 'avif' ? array('avif','avis') : array('heic','heix','hevc','hevx','heim','heis','hevm','hevs','mif1','msf1');
        for ($offset = 8; $offset + 4 <= $end; $offset += 4) {
            if ($offset !== 12 && in_array(substr($header, $offset, 4), $brands, true)) $valid = true;
        }
    }
    if (!$valid) return 'The original file is not a supported image of the selected format.';
    $info = @getimagesize($converted['tmp_name']);
    $size = @filesize($converted['tmp_name']);
    $ending = $size >= 2 ? @file_get_contents($converted['tmp_name'], false, null, $size - 2, 2) : '';
    if (!$info || $info[2] !== IMAGETYPE_JPEG || $info[0] < 1 || $info[1] < 1 || $info[0] * $info[1] > 60000000 || $ending !== "\xff\xd9") {
        return 'The converted image is incomplete, invalid or larger than 60 megapixels. No image has been replaced.';
    }
    $temporary = @tempnam(dirname($file['tmp_name']), 'u5original_');
    if ($temporary === false) return 'The original image could not be preserved.';
    // Also remove the preserved original if a later operation aborts the request.
    register_shutdown_function(function () use ($temporary) { if (is_file($temporary)) @unlink($temporary); });
    if (!@copy($file['tmp_name'], $temporary) || !@copy($converted['tmp_name'], $file['tmp_name'])) {
        @unlink($temporary);
        return 'The image could not be prepared. No image has been replaced.';
    }
    $original = $temporary;
    $originalExtension = $extension;
    $extension = 'jpg';
    return '';
}

function u5ConvertedAlbumImage($index) {
    if (!isset($_FILES['u5converted']['error'][$index])) return null;
    $file = array();
    foreach (array('name','type','tmp_name','error','size') as $key) {
        $file[$key] = isset($_FILES['u5converted'][$key][$index]) ? $_FILES['u5converted'][$key][$index] : null;
    }
    return $file;
}

// Stage the original in the archive before replacing a published image.
// A failed copy/rename must neither corrupt an older archive nor publish a
// conversion for which the original could not be retained.
function u5ArchiveImageOriginal($source, $target) {
    if (!is_file($source)) return false;
    $directory = realpath(dirname($target));
    if ($directory === false) return false;
    $temporary = @tempnam($directory, 'u5original_');
    if ($temporary === false || dirname($temporary) !== $directory) {
        if ($temporary !== false) @unlink($temporary);
        return false;
    }
    $mode = is_file($target) ? (fileperms($target) & 0777) : (0666 & ~umask());
    if (!@copy($source, $temporary)) {
        @unlink($temporary);
        return false;
    }
    clearstatcache(true, $temporary);
    if (@filesize($temporary) !== @filesize($source) || !@chmod($temporary, $mode) || !@rename($temporary, $target)) {
        @unlink($temporary);
        return false;
    }
    return true;
}
