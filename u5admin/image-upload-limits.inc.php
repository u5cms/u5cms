<?php
// Exact php.ini byte values, including unbounded (0) and plain integers.
function u5ImageUploadIniBytes($value) {
    $value = trim($value);
    $number = (float)$value;
    $suffix = strtolower(substr($value, -1));
    if ($suffix === 'g') $number *= 1073741824;
    elseif ($suffix === 'm') $number *= 1048576;
    elseif ($suffix === 'k') $number *= 1024;
    return max(0, (int)$number);
}
echo ' data-u5-max-file="'.u5ImageUploadIniBytes(ini_get('upload_max_filesize')).'"';
echo ' data-u5-max-post="'.u5ImageUploadIniBytes(ini_get('post_max_size')).'"';
echo ' data-u5-max-files="'.max(1, (int)ini_get('max_file_uploads') - 1).'"';
// Use browser decoding only when the existing server loader is unavailable.
$u5NativeFallback = array();
if (isset($_GET['typ']) && ($_GET['typ'] === 'i' || $_GET['typ'] === 'a')) {
    foreach (array('png','gif','webp','bmp','avif') as $u5Format) {
        if (!function_exists('imagecreatefrom'.$u5Format)) $u5NativeFallback[] = $u5Format;
    }
}
echo ' data-u5-native-fallback="'.implode(',', $u5NativeFallback).'"';
