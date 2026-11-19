<?php
require_once __DIR__ . '/search-core.inc.php';

/* Called before the legacy global sanitizer. Its legacy q remains untouched. */
function u5SearchCaptureRequest() {
    if (!array_key_exists('u5SearchRequest', $GLOBALS)) {
        $raw = isset($_GET['q']) && is_string($_GET['q']) ? $_GET['q'] : '';
        $version = isset($_GET['qv']) && is_string($_GET['qv']) ? $_GET['qv'] : null;
        $GLOBALS['u5SearchRequest'] = u5SearchParseQuery(u5SearchDecodeRequest($raw, $version));
    }
    return $GLOBALS['u5SearchRequest'];
}

function u5SearchRequest() {
    return u5SearchCaptureRequest();
}
