<?php
require_once __DIR__ . '/provision-work.inc.php';
require_once __DIR__ . '/background-work.inc.php';
if (($blockautoincluderefresherforhtaccessandindex ?? '')!=='yes' && u5ProvisionPending(__DIR__)) {
    u5BackgroundWorkLaunch('htaccess.php','provision','js/u5-background-work.js',true);
}

if($blockautoincluderefresherforhtaccessandindex!='yes') {
if(!isset($_COOKIE['p'])) {
if(!isset($waitsecondstillindexingsincelastsave))$waitsecondstillindexingsincelastsave=60*60*0.5;

if(file_get_contents('fileversions/lastsave.txt')>file_get_contents('fileversions/lastindex.txt') && !(file_exists('fileversions/indexerrunning.txt') && file_get_contents('fileversions/indexerrunning.txt')!=0 && file_get_contents('fileversions/indexerrunning.txt')>time()-60*5) && (!is_file('fileversions/indexer-progress.php') || filemtime('fileversions/indexer-progress.php')<=time()) && file_get_contents('fileversions/lastsave.txt')<time()-$waitsecondstillindexingsincelastsave)u5BackgroundWorkLaunch('indexer.php?u5indexauto=1','index','js/u5-background-work.js',true);
}
}
?>
