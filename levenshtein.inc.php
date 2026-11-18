<?php
require_once __DIR__ . '/search-legacy-suggestions.inc.php';
$aclosest = u5LegacySearchClosest($sfor ?? array(), 'SELECT text FROM search_1', 'text');
if (is_string($aclosest) && trim((string) ($sfora ?? '')) !== trim($aclosest)) {
    echo '<span id="levenshtein"><b><big>Meinten Sie: <a href="index.php?s='.
        htmlspecialchars(urlencode($aclosest), ENT_QUOTES, 'WINDOWS-1252').'">'.
        htmlspecialchars($aclosest, ENT_QUOTES, 'WINDOWS-1252').'</a></big></b>&nbsp;</span> ';
}
