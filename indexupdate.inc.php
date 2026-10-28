<?php
require_once __DIR__ . '/search-index.inc.php';
$u5SearchIndexRunning = u5SearchIndexIsRunning();
if ($u5SearchIndexRunning || u5SearchIndexNeedsUpdate()) {
    $u5SearchIndexMessage = $u5SearchIndexRunning
        ? 'Search index &amp; sitemap are currently being updated.'
        : 'Search index &amp; sitemap are not yet fully up to date.';
    $u5LastIndex = u5SearchIndexFileTime(__DIR__ . '/fileversions/lastindex.txt');
    if (!$u5SearchIndexRunning) {
        if ($u5LastIndex > 0) $u5SearchIndexMessage .= ' A few entries are still as of ' . date('Y-m-d H:i T', $u5LastIndex) . '.';
        $u5SearchIndexMessage .= ' Auto-update will run soon.';
    }
    echo '<div lang="en" style="font-size:10px;line-height:177%;margin-top:12px">' . $u5SearchIndexMessage . '</div>';
}
