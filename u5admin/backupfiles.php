<?php
@set_time_limit(3600);
ob_start();
require('connect.inc.php');
require_once __DIR__.'/resource-rename-support.inc.php';
try {
    register_shutdown_function(u5RenameStorageLock(dirname(__DIR__),null,true));
    $u5BackupGeneration=u5ResourceBackupStart(dirname(__DIR__));
} catch (RuntimeException $error) {
    http_response_code($error instanceof U5ResourceBusyException ? 409 : 500);
    exit(htmlspecialchars($error->getMessage(),ENT_QUOTES,'Windows-1252'));
}

use U5cms\Zipper;

if ($backupRqHIADRI != 'no') require('accadmin.inc.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=WINDOWS-1252"/>
    <title>backup files</title>
    <script src="shortcut.js"></script>
    <script>
        shortcut.add("Ctrl+S", function () {
            parent.close();
        })
    </script>
    <?php require('backendcss.php'); ?></head>

<body>
<?php

//http://campstamba.com/2010/12/create-your-own-php-backup-solution-complete-tutorial-with-source-files/
$backupDirs = array(
    U5ROOT_PATH . '/fileversions/useruploads',
    U5ROOT_PATH . '/fileversions/_dbbackup',
    U5ROOT_PATH . '/r'
);

$instName = preg_replace("/[^a-z0-9]/", "", strtolower($db));
$targetfile = U5ROOT_PATH . DIRECTORY_SEPARATOR . 'fileversions' . DIRECTORY_SEPARATOR . $instName . '_FilesExclCMSsys.zip';

try {
$zip = new Zipper($targetfile);
$zip->setStripPath(U5ROOT_PATH);

foreach ($backupDirs as $backupDir) {
    @mkdir($backupDir);
    $zip->addFolder($backupDir);
}

// write a backup config and add it to the zip
require_once('backupconfigwrite.php');
if (file_exists(U5ROOT_PATH . DIRECTORY_SEPARATOR . 'fileversions/configBACKUPED.php')) {
    $zip->addFile(U5ROOT_PATH . DIRECTORY_SEPARATOR . 'fileversions/configBACKUPED.php');
} else {
    $zip->addFile(U5ROOT_PATH . DIRECTORY_SEPARATOR . 'config.php');
}

foreach (glob(U5ROOT_PATH.'/fileversions/resource-rename-*.php') as $u5RenameBackup) $zip->addFile($u5RenameBackup);
foreach (glob(U5ROOT_PATH.'/fileversions/codeassets-*-rows-*',GLOB_ONLYDIR) as $u5RenameBackup) $zip->addFolder($u5RenameBackup);
$zip->write();
$u5BackupCreated=true;
u5ResourceBackupFinish(U5ROOT_PATH,$u5BackupGeneration);
} catch (Throwable $error) {
    if (!empty($u5BackupCreated) && is_file($targetfile)) @unlink($targetfile);
    unset($zip);
    echo '<p>Backup failed. The previous backup has been kept.</p>';
    echo '<script>parent.document.title=parent.document.title.replace(/\.\.\./,"?");</script>';
    exit;
}

if (file_exists($targetfile)) {
echo '<a href="../ff.php?f='.basename($targetfile).'&t='.filemtime($targetfile).'">' . basename($targetfile) . '</a> ' . ceil(filesize($targetfile) / 1024) . ' kB ' . date('Y-m-d H:i:s', filemtime($targetfile));
echo "
<script>
parent.document.title=parent.document.title.replace(/\.\.\./,'');
</script>
";
}
else {
echo 'The files could not be zipped. Backup them using FTP.';
echo "
<script>
parent.document.title=parent.document.title.replace(/\.\.\./,'?');
</script>
";
}
?>
</body>
</html>
