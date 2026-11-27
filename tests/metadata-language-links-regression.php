<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

function metadataAlternateBlock($path) {
    $source = file_get_contents($path);
    if (!is_string($source)) throw new RuntimeException('Cannot read metadata renderer');
    $start = strpos($source, "if(strpos(\$canonicalprotocolanddomain");
    $end = strpos($source, "\nelse if(\$canonicalprotocolanddomain!='')", $start);
    if ($start === false || $end === false) throw new RuntimeException('Canonical metadata block not found');
    return substr($source, $start, $end - $start);
}

function metadataRenderAlternates($block, $languages, $requestLanguage, $requestUri) {
    $canonicalprotocolanddomain = 'https://cms.example';
    $num_a = 1;
    $row_a = array('ishomepage' => 0);
    $_GET = array('c' => 'article', 'l' => $requestLanguage);
    $_SERVER['REQUEST_URI'] = $requestUri;
    for ($i = 1; $i <= 5; $i++) ${'lan' . $i . 'na'} = $languages[$i - 1] ?? '';
    $lancode = $requestLanguage;
    ob_start();
    eval($block);
    return ob_get_clean();
}

function metadataCheck($condition, $message) {
    $GLOBALS['metadataChecks']++;
    if (!$condition) throw new RuntimeException($message);
}

$metadataChecks = 0;
$block = metadataAlternateBlock(dirname(__DIR__) . '/u5sys.meta.php');

metadataCheck(
    ('de' != '' && 'de' < 1) === false,
    'PHP 8 comparison semantics must reproduce the legacy language-condition failure'
);

$html = metadataRenderAlternates(
    $block,
    array('de', 'fr', 'it', 'rm', 'en'),
    'fr',
    '/index.php?c=article&l=fr&view=old%20data'
);
metadataCheck(substr_count($html, 'rel="alternate"') === 5, 'Every configured language receives an alternate link');
metadataCheck(strpos($html, 'rel="canonical" href="https://cms.example/?c=article&l=fr&view=old%20data"') !== false, 'Canonical URL retains the selected language and encoded query');
foreach (array('de', 'fr', 'it', 'rm', 'en') as $language) {
    metadataCheck(strpos($html, 'hreflang="' . $language . '"') !== false, 'Missing hreflang ' . $language);
    metadataCheck(strpos($html, 'l=' . $language . '&view=old%20data') !== false, 'Alternate URL lost query data for ' . $language);
}

$html = metadataRenderAlternates(
    $block,
    array('de', 'fr', 'it', '', ''),
    'de',
    '/?c=article&l=de'
);
metadataCheck(substr_count($html, 'rel="alternate"') === 3, 'Empty language slots stay absent');
metadataCheck(strpos($html, 'hreflang=""') === false, 'Empty hreflang must not be emitted');

echo 'Metadata language links: ' . $metadataChecks . " passed.\n";

