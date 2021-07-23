<?php
@set_time_limit(0);
require('../config.php');
require('connect.inc.php');
if ($backupRqHIADRI != 'no') require('accadmin.inc.php');
$src = preg_replace("/[^a-z0-9]/", "", strtolower($db));
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
require_once('backupconfigwrite.php');

if ($backupRqHIADRI != 'no') require('accadmin.inc.php');

//http://campstamba.com/2010/12/create-your-own-php-backup-solution-complete-tutorial-with-source-files/
@mkdir('../fileversions/useruploads');
@mkdir('../fileversions/_dbbackup');
$f = '../fileversions/' . $src . '_FilesExclCMSsys.zip';
unlink($f);

// Include the PclZip library http://www.phpconcept.net/pclzip
require_once('pclzip.lib.php');

// Set the arhive filename
$archive = new PclZip($f);

// Set the dir to archive
$v_dir = dirname(getcwd()); // or dirname(__FILE__);
$v_dir = '../r/';
$v_dir2 = '../fileversions/useruploads/';
$v_dir3 = '../fileversions/_dbbackup/';
$v_remove = '../';

// Create the archive
$v_list = $archive->create($v_dir, PCLZIP_OPT_REMOVE_PATH, $v_remove);
if ($v_list == 0) {
    die("Error : " . $archive->errorInfo(true));
}
$v_list = $archive->add($v_dir2, PCLZIP_OPT_REMOVE_PATH, $v_remove);
if ($v_list == 0) {
    die("Error : " . $archive->errorInfo(true));
}
$v_list = $archive->add($v_dir3, PCLZIP_OPT_REMOVE_PATH, $v_remove);
if ($v_list == 0) {
    die("Error : " . $archive->errorInfo(true));
}

if(file_exists('../fileversions/configBACKUPED.php')){
$v_list = $archive->add('../fileversions/configBACKUPED.php', PCLZIP_OPT_REMOVE_PATH, $v_remove.'fileversions');
if ($v_list == 0) {
    die("Error : " . $archive->errorInfo(true));
}
}

else {
$v_list = $archive->add('../config.php', PCLZIP_OPT_REMOVE_PATH, $v_remove);
if ($v_list == 0) {
    die("Error : " . $archive->errorInfo(true));
}
}
if (file_exists($f)) {
echo '<a href="../ff.php?f='.basename($f).'&t='.filemtime('../fileversions/'.basename($f)).'">' . basename($f) . '</a> ' . ceil(filesize($f) / 1024) . ' kB ' . date('Y-m-d H:i:s', filemtime($f));
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