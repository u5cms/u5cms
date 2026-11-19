<?php
require_once __DIR__ . '/search-index.inc.php';
if ($blockautoincluderefresherforhtaccessandindex != 'yes' && !isset($_COOKIE['p'])) {
    if (!isset($waitsecondstillindexingsincelastsave)) $waitsecondstillindexingsincelastsave = 60 * 60 * 0.5;
    $u5LastSave = u5SearchIndexFileTime(__DIR__ . '/fileversions/lastsave.txt');
    $u5HtaccessRunning = u5SearchIndexFileTime(__DIR__ . '/fileversions/htarunning.txt');
    if ($u5LastSave > u5SearchIndexFileTime(__DIR__ . '/fileversions/lasthtaccess.txt')
        && !($u5HtaccessRunning > 0 && $u5HtaccessRunning > time() - 60 * 5)) {
        echo '<script>if(!opener&&top==self)document.write(\'<iframe style="display:none" src="htaccess.php"></iframe>\')</script>';
    } elseif (u5SearchIndexNeedsUpdate() && !u5SearchIndexIsRunning()
        && (!is_file(__DIR__.'/fileversions/indexer-progress.php') || filemtime(__DIR__.'/fileversions/indexer-progress.php') <= time())
        && $u5LastSave < time() - $waitsecondstillindexingsincelastsave) {
        echo '<script>if(!opener&&top==self)document.write(\'<iframe style="display:none" src="indexer.php?u5indexauto=1"></iframe>\')</script>';
    }
}
