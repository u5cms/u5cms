<?php
function u5stz($that) {
    return htmlspecialchars($that, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
}

$filterChars = array('\r', '\n', ';', '<', '>', '(', ')');
$_GET['q'] = str_replace($filterChars, '', $_GET['q']);
$_GET = array_map("u5stz", $_GET);
$_COOKIE = array_map("u5stz", $_COOKIE);

$keys = ['c','n','l','typ','name','id'];
foreach ($keys as $k) {
    $_GET[$k]  = isset($_GET[$k])  ? preg_replace('/[^a-z0-9!]/i','',$_GET[$k])  : '';
    $_POST[$k] = isset($_POST[$k]) ? preg_replace('/[^a-z0-9!]/i','',$_POST[$k]) : '';
}

