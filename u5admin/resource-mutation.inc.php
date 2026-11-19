<?php
// Only a live Rename excludes ordinary writes. Indexing, backups and saved
// recovery journals never reserve this gate exclusively.
require_once __DIR__.'/resource-rename-support.inc.php';
try {
    $u5ResourceMutationRelease=u5ResourceActivityLock(dirname(__DIR__));
} catch (U5ResourceBusyException $error) {
    http_response_code(409);
    header('Retry-After: 2');
    echo '<p>'.htmlspecialchars($error->getMessage(),ENT_QUOTES,'Windows-1252').'</p>';
    echo '<p>This request did not change the resource. <a href="renaming.php" target="_blank" rel="noopener">Show Rename progress</a>.</p>';
    exit;
}
require_once dirname(__DIR__).'/index-work.inc.php';
$u5ResourceWriteRelease=u5ResourceWriteActivity(dirname(__DIR__));
// Ancillary search markers must not turn a writable page into a refused save.
$u5ResourceDirty=function() {
    try {u5IndexMarkDirty(dirname(__DIR__));}
    catch (RuntimeException $error) {error_log('u5CMS search update marker: '.$error->getMessage());}
};
$u5ResourceDirty();
register_shutdown_function(function() use($u5ResourceDirty,$u5ResourceWriteRelease,$u5ResourceMutationRelease) {
    try {$u5ResourceDirty();}
    finally {$u5ResourceWriteRelease();$u5ResourceMutationRelease();}
});
