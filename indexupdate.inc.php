<?php
require_once __DIR__ . '/search-index.inc.php';
$u5SearchIndexRunning = u5SearchIndexIsRunning();
$u5IndexNoticeSave = u5SearchIndexFileTime(__DIR__.'/fileversions/lastsave.txt');
$u5IndexNoticeRaw = trim((string)@file_get_contents(__DIR__.'/fileversions/lastindex.txt'));
$u5LastIndex = preg_match('/\A[0-9]+\z/D', $u5IndexNoticeRaw) ? (int)$u5IndexNoticeRaw : 0;
if ($u5LastIndex > time()) $u5LastIndex = 0;
if ($u5SearchIndexRunning || u5SearchIndexNeedsUpdate() || $u5IndexNoticeSave > $u5LastIndex) {
    $u5SearchIndexMessage = $u5SearchIndexRunning
        ? 'Search index &amp; sitemap are currently being updated.'
        : 'Search index &amp; sitemap are not yet fully up to date.';
    if ($u5LastIndex > 0) $u5SearchIndexMessage .= ' Index state of the last completed full rebuild: '.date('Y-m-d H:i T', $u5LastIndex).'.';
    else $u5SearchIndexMessage .= ' No completed full index rebuild is recorded yet.';
    echo '<div lang="en" style="font-size:10px;line-height:177%;margin-top:12px">'.$u5SearchIndexMessage.'</div>';
}
?>
