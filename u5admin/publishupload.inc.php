<?php
// Keep the current file until the complete upload is ready in its directory.
function u5PublishUploadedFile($uploadedFile, $target) {
    if (!is_uploaded_file($uploadedFile)) return false;
    $directory=realpath(dirname($target));
    if ($directory===false) return false;
    $temporary=tempnam($directory,'u5upload_');
    if ($temporary===false || dirname($temporary)!==$directory) {
        if ($temporary!==false) @unlink($temporary);
        return false;
    }
    $mode=is_file($target) ? (fileperms($target) & 0777) : (0666 & ~umask());
    if (!move_uploaded_file($uploadedFile,$temporary) || !chmod($temporary,$mode) || !rename($temporary,$target)) {
        @unlink($temporary);
        return false;
    }
    clearstatcache(true,$target);
    return true;
}
?>
