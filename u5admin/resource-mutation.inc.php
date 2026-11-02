<?php
// Coordinate page, metadata and file mutations with rename, publication and backups.
require_once dirname(__DIR__).'/codeassets.inc.php';
try {
    $u5ResourceMutationRelease=u5CodeMigrationLock(dirname(__DIR__));
    register_shutdown_function($u5ResourceMutationRelease);
} catch (RuntimeException $error) {
    http_response_code(409);
    header('Retry-After: 2');
    echo '<p>'.htmlspecialchars($error->getMessage(),ENT_QUOTES,'Windows-1252').'</p>';
    echo '<p>This request did not change the resource. Your editor contents have been kept. Retry after the active operation finishes; resume an interrupted rename using its original Retry rename button.</p>';
    exit;
}
