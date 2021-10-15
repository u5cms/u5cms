<?php
@set_time_limit(0);
require('connect.inc.php');

use U5cms\Zipper;

if ($backupRqHIADRI != 'no') require('accadmin.inc.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
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

$zip->write();

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
