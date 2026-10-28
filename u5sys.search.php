<?php
require_once __DIR__ . '/connect.inc.php';
require_once __DIR__ . '/search-ui.inc.php';
$u5SearchScope = isset($_GET['c']) && (substr($_GET['c'], 0, 1) === '!' || $_GET['c'] === '_searchsi') ? '_searchsi' : '_search';
u5SearchForm(false, $u5SearchScope);
