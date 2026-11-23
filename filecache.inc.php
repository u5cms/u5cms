<?php
// Call only after the request has passed the file's access checks.
function u5FileCacheHeaders($file, $private=false) {
    clearstatcache(true,$file);
    $modified=filemtime($file);
    if ($modified===false) { http_response_code(404); exit; }
    // A formerly public asset can acquire a narrower audience without a URL change.
    header('Cache-Control: '.($private ? 'private, no-cache' : 'public, no-cache'));
    header('Last-Modified: '.gmdate(DATE_RFC1123,$modified));
    $method=$_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (($method==='GET' || $method==='HEAD') && !isset($_SERVER['HTTP_IF_NONE_MATCH']) && isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        $since=strtotime(($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? ''));
        if ($since!==false && $modified<=$since) { http_response_code(304); exit; }
    }
}
?>
