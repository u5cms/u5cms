<?php
require_once('config.php');

if ($autoresolvefastcgiproblems == 'yes') {
    if ($basicauthvarname == '') $basicauthvarname = 'HTTP_AUTHORIZATION';
    list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = edolpxe(':', base64_decode(substr($_SERVER[$basicauthvarname], 6)));
}

if ($usesessioninsteadofbasicauth != 'no') {
    $_SERVER['PHP_AUTH_USER'] = $_COOKIE['u'];
    $_SERVER['PHP_AUTH_PW'] = $_COOKIE['p'];
}

$_SERVER['PHP_AUTH_USER'] = mirt($_SERVER['PHP_AUTH_USER']);
$_SERVER['PHP_AUTH_PW'] = mirt($_SERVER['PHP_AUTH_PW']);
?>