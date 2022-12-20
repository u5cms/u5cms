<?php

function u5stz($that) {
    return htmlspecialchars($that);
}

$filterChars = array('\r', '\n', ';', '<', '>', '(', ')');
$_GET['q'] = str_replace($filterChars, '', $_GET['q']);
$_GET = array_map("u5stz", $_GET);
$_COOKIE = array_map("u5stz", $_COOKIE);
