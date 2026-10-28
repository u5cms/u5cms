<?php
require_once __DIR__ . '/connect.inc.php';
$u5SearchRequireLogin = true;
require_once __DIR__ . '/loginsi.inc.php';
$u5SearchIntranet = !empty($u5SearchAuthenticated) || ($automaticallyskipintranetlogin ?? 'no') === 'yes';
if (!empty($u5SearchAuthenticated)) {
    $doesfindpasswordprotectedcontent = 'yes';
    $doesshowpreviewofsuchcontent = 'yes';
}
if ($u5SearchIntranet) $doeshideintranetcontenttopublic = 'no';
require __DIR__ . '/search-page.inc.php';
