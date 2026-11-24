<?php
require_once __DIR__ . '/provision-work.inc.php';
require_once __DIR__ . '/background-work.inc.php';
if (($blockautoincluderefresherforhtaccessandindex ?? '')!=='yes' && u5ProvisionPending(__DIR__)) {
    u5BackgroundWorkLaunch('htaccess.php','provision','js/u5-background-work.js',true);
}

if (($blockautoincluderefresherforhtaccessandindex ?? '') !== 'yes' && !isset($_COOKIE['p'])) {
    $directory = __DIR__.'/fileversions';
    $progress = $directory.'/indexer-progress.php';
    $now = time();
    $quietPeriod = $waitsecondstillindexingsincelastsave ?? 1800;
    $lastSave = (int)@file_get_contents($directory.'/lastsave.txt');
    $needsNewRun = $lastSave > (int)@file_get_contents($directory.'/lastindex.txt');
    $running = (int)@file_get_contents($directory.'/indexerrunning.txt');
    if (($running === 0 || $running <= $now - 300 || $running > $now)) {
        if (is_file($progress)) {
            $due = filemtime($progress);
            // Resume only an existing pass. A concurrent finisher makes this a no-op.
            if ($due <= $now || $due > $now + 86400) {
                u5BackgroundWorkLaunch('indexer.php?u5indexcontinue=1','index','js/u5-background-work.js',true);
            }
        } elseif ($needsNewRun && $lastSave < $now - $quietPeriod) {
            u5BackgroundWorkLaunch('indexer.php?u5indexauto=1','index','js/u5-background-work.js',true);
        }
    }
}
