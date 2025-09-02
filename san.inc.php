<?php
function u5stz($that) {
    return htmlspecialchars($that, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
}
function u5map($v) {
    return is_string($v) ? u5stz($v) : $v;
}

$filterChars = array('\r', '\n', ';', '<', '>', '(', ')');
if (isset($_GET['q']) && is_string($_GET['q'])) {
    $_GET['q'] = str_replace($filterChars, '', $_GET['q']);
}
$_GET = array_map("u5map", $_GET);
$_COOKIE = array_map("u5map", $_COOKIE);

$skipPost = false;
foreach (get_included_files() as $f) {
    if (in_array(basename($f), ['formsave.php','delformsave.php','editformsave.php'])) {
        $skipPost = true;
        break;
    }
}

$keys = ['c','n','l','typ','name','id'];
foreach ($keys as $k) {
    if (isset($_GET[$k]) && is_string($_GET[$k])) {
        $_GET[$k] = preg_replace('/[^a-z0-9!]/i','',$_GET[$k]);
    }
    if (!$skipPost && isset($_POST[$k]) && is_string($_POST[$k])) {
        $_POST[$k] = preg_replace('/[^a-z0-9!]/i','',$_POST[$k]);
    }
}
