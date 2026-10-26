<?php
@set_time_limit(3600);

require('connect.inc.php');
require_once('u5idn.inc.php');
if ($backupRqHIADRI != 'no') require('accadmin.inc.php');

// Pfad zum Backup
$backup_dir=U5ROOT_PATH . DIRECTORY_SEPARATOR . 'fileversions' . DIRECTORY_SEPARATOR . '_dbbackup';
@mkdir($backup_dir);
require_once('databasebackup.inc.php');
$backup_files=array();$cur_date=date('Y-m-d');$cur_time=date('d.m.Y H:i');
$backup_compress=$backup_compress && extension_loaded('zlib');
$u5BackupRelease=null;$u5BackupError=null;
$link=null;
try {
    $u5BackupRelease=u5CodeMigrationLock(U5ROOT_PATH);
    $link=mysql_connect($host,$username,$password,true);
    if (!$link) throw new RuntimeException('Cannot open a separate database backup connection.');
    $backup_files=u5CreateDatabaseBackup($db,$backup_dir,$backup_compress,$link);
} catch (Throwable $error) {
    http_response_code(500);
    $u5BackupError=$error;
} finally {
    if ($link) mysql_close($link);
    if ($u5BackupRelease) $u5BackupRelease();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=WINDOWS-1252"/>
    <title>backup</title>
    <script src="shortcut.js"></script>
    <script>
        shortcut.add("Ctrl+S", function () {
            parent.close();
        })
    </script>
    <?php require('backendcss.php'); ?></head>

    <body>
<?php

if ($u5BackupError) { echo '<p>'.htmlspecialchars($u5BackupError->getMessage(),ENT_QUOTES,'Windows-1252').'</p>';exit; }

//Backup per Email verschicken
function mail_att($to, $from, $subject, $message, $attachments = array())
{
    if (is_array($attachments) AND count($attachments) > 0) {
        $mime_boundary = "-----=" . md5(uniqid(rand(), 1));

        $header = "From: " . $from . "\r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-Type: multipart/mixed;\r\n";
        $header .= " boundary=\"" . $mime_boundary . "\"\r\n";

        $content = "This is a multi-part message in MIME format.\r\n\r\n";
        $content .= "--" . $mime_boundary . "\r\n";
        $content .= "Content-Type: text/plain charset=\"WINDOWS-1252\"\r\n";
        $content .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $content .= $message . "\r\n\r\n\r\n";

        //Dateien anhaengen
        foreach ($attachments AS $file) {
            $name = basename($file);
            $data = chunk_split(base64_encode(implode("", file($file))));
            $len = filesize($file);
            $content .= "--" . $mime_boundary . "\r\n";
            $content .= "Content-Disposition: attachment;\r\n";
            $content .= "\tfilename=\"$name\";\r\n";
            $content .= "Content-Length: .$len;\r\n";
            $content .= "Content-Type: application/x-gzip; name=\"" . $file . "\"\r\n";
            $content .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $content .= $data . "\r\n";
        }

        if (mail(u5toidn($to), $subject, $content, $header)) return true;
        else return false;
    }

    return false;
}


//Backup per Email senden
if ($backup_send_mail) {
    $from = $mymail;
    $to = $_SERVER['PHP_AUTH_USER'];
    $subject = sprintf("%s db backup %s", preg_replace("/[^a-z0-9]/", "", strtolower($db)), $cur_date);
    $text = sprintf("db backup (dump) generated on: %s\n\n\nFor a full restore or a new installation you further need the u5CMS source (get it at http://www.yuba.ch/u5cms/) and the folders 'r' and 'fileversions/useruploads' containing your and your customers uploaded files (you got them zipped via the backup function of your u5CMS or backuped by (s)ftp).", $cur_date . ' ' . $cur_time);
    if (!mail_att($to, $from, $subject, $text, $backup_files))
        echo "-<br>";

}

foreach ($backup_files as $file) {
    if (file_exists($file)) echo '<a href="../fff.php?f='.basename($file).'&t='.filemtime('../fileversions/_dbbackup/'.basename($file)).'">' . basename($file) . '</a> ' . ceil(filesize($file) / 1024) . ' kB ' . date('Y-m-d H:i:s', filemtime($file));
    echo '<br /><br />';
}

trxlog('backup '.$src);
?>
<script>
parent.document.getElementById('backupfiles').src='backupfiles.php';
</script>
</body>
</html>
