<?php
/* Test-only prepend: fail on deprecations even when legacy bootstraps mask them. */
set_error_handler(function($severity,$message,$file,$line) {
    if ($severity===E_DEPRECATED || $severity===E_USER_DEPRECATED) {
        error_log('PHP readiness deprecation: '.$message.' at '.$file.':'.$line);
        throw new ErrorException($message,0,$severity,$file,$line);
    }
    return false;
});
