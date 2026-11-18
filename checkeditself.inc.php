<?php
require_once __DIR__ . '/onlymine.inc.php';
if (u5onlymineHasMarker($cmdstring, 'SELF_TOEDITITEMS_LOGINS_SAME_AS_[') && !u5onlymineOwnsRequestedRecord()) {
    if (($_GET['a'] ?? '') != 1) {
        if (u5onlymineUsesDomain()) http_response_code(403);
        die('<script>alert("forbidden editing (you do not own this record)");parent.history.go(-1)</script>');
    }
    require_once('u5admin/usercheck.inc.php');
    if ($formdataRqHIADRI != 'no') require('u5admin/accadmin.inc.php');
}
