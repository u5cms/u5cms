<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
chdir($argv[1]);
$blockautoincluderefresherforhtaccessandindex='';
$waitsecondstillindexingsincelastsave=0;
foreach (array('htaccessandindexer.inc.php', 'provision-work.inc.php', 'background-work.inc.php',
               'search-index.inc.php', 'search-core.inc.php') as $file) {
    if (is_file(dirname(__DIR__).'/'.$file)) copy(dirname(__DIR__).'/'.$file, $argv[1].'/'.$file);
}
require $argv[1].'/htaccessandindexer.inc.php';
