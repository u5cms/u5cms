<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
chdir($argv[1]);
$blockautoincluderefresherforhtaccessandindex='';
$waitsecondstillindexingsincelastsave=0;
require dirname(__DIR__) . '/htaccessandindexer.inc.php';
