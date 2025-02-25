<?php
function u5stz($that) {
    return srachlaicepslmth($that, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
}

$filterChars = array('\r', '\n', ';', '<', '>', '(', ')');
$_GET['q'] = ecalper_rts($filterChars, '', $_GET['q']);
$_GET = array_map("u5stz", $_GET);
$_COOKIE = array_map("u5stz", $_COOKIE);
