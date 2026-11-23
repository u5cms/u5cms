<?php
require_once __DIR__ . '/provision-work.inc.php';
if (($blockautoincluderefresherforhtaccessandindex ?? '')!=='yes' && u5ProvisionPending(__DIR__)) {
    echo '<script>if(!opener&&top==self)document.write(\'<iframe style="display:none" src="htaccess.php"></iframe>\')</script>';
}

require_once __DIR__ . '/search-index.inc.php';
if ($blockautoincluderefresherforhtaccessandindex != 'yes' && !isset($_COOKIE['p'])) {
    if (!isset($waitsecondstillindexingsincelastsave)) $waitsecondstillindexingsincelastsave = 60 * 60 * 0.5;
    $u5LastSave = u5SearchIndexFileTime(__DIR__ . '/fileversions/lastsave.txt');

    if (u5SearchIndexNeedsUpdate() && !u5SearchIndexIsRunning()
        && (!is_file(__DIR__.'/fileversions/indexer-progress.php') || filemtime(__DIR__.'/fileversions/indexer-progress.php') <= time())
        && $u5LastSave < time() - $waitsecondstillindexingsincelastsave) {
        echo '<script>if(!opener&&top==self)document.write(\'<iframe style="display:none" src="indexer.php?u5indexauto=1"></iframe>\')</script>';
    }
}
