<?php
require('../config.php');
require('connect.inc.php');
if ($backupRqHIADRI != 'no') require('accadmin.inc.php');
$src = preg_replace("/[^a-z0-9]/", "", strtolower($db));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>...<?php echo $src ?> backup</title>
    <script src="shortcut.js"></script>
    <script>
        shortcut.add("Ctrl+S", function () {
            parent.close();
        })
    </script>
    <?php require('backendcss.php'); ?></head>

<body>
<h1>Backup content</h1>

<p>
    Store these 3 files (database structure (sql), database data (sql) and files (zip*)) in a safe place.
    <br>
    *<span style="font-size:60%">If the zip-file containing your uploaded files is missing on the list below (after waiting 1 minute or more and getting an error), there are too many or heavy files. In that case you have to backup your uploaded files by (s)ftp-ing the folders 'r' and 'fileversions/useruploads' in the CMS's filesystem. The zip-file containing your uploaded files does not contain the CMS system files; the latter you get at <a
            href="http://www.yuba.ch/u5cms/">http://www.yuba.ch/u5cms/</a>.</span>
</p>
<iframe src="backupdb.php" width="100%" height="100" frameborder="0"></iframe>
<iframe id="backupfiles" src="../upload/spinner.gif" width="100%" height="100" frameborder="0"></iframe>
<?php include('selfclose.inc.php')?>
</body>
</html>