<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

function u5SearchExtractDecoder($path, $name) {
    $source = file_get_contents($path);
    if (!is_string($source)) throw new RuntimeException('Cannot read ' . $path);
    $start = strpos($source, 'function u5SearchUnicodeEntities');
    if ($start === false) throw new RuntimeException('Decoder missing from ' . $path);
    $brace = strpos($source, '{', $start);
    $depth = 0;
    $end = null;
    for ($i = $brace, $length = strlen($source); $i < $length; $i++) {
        if ($source[$i] === '{') $depth++;
        elseif ($source[$i] === '}' && --$depth === 0) { $end = $i + 1; break; }
    }
    if ($end === null) throw new RuntimeException('Incomplete decoder in ' . $path);
    $function = substr($source, $start, $end - $start);
    $function = preg_replace('/function\\s+u5SearchUnicodeEntities/', 'function ' . $name, $function, 1);
    eval($function);
}

$root = dirname(__DIR__);
$files = array('u5sys._search.php', 'u5sys._searchsi.php');
$checks = 0;
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
foreach ($files as $position => $file) {
    $name = 'u5SearchUnicodeEntitiesRegression' . $position;
    u5SearchExtractDecoder($root . '/' . $file, $name);
    $decode = $name;
    $fixed = array(
        '%u00E9' => '&#233;',
        '%u0153' => '&#339;',
        '%u4E2D%u6587' => '&#20013;&#25991;',
        '%uD83D%uDE00' => '&#128512;',
        'plain %uZZZZ text' => 'plain %uZZZZ text',
        '' => '',
    );
    foreach ($fixed as $input => $expected) {
        if ($decode($input) !== $expected) throw new RuntimeException('Unexpected decode for ' . $input . ' in ' . $file);
        $checks++;
    }
    mt_srand(129511 + $position);
    for ($i = 0; $i < 1024; $i++) {
        do { $point = mt_rand(1, 0xffff); } while ($point >= 0xd800 && $point <= 0xdfff);
        $input = sprintf('%%u%04X', $point);
        if ($decode($input) !== '&#' . $point . ';') throw new RuntimeException('BMP mismatch in ' . $file);
        $checks++;
    }
    for ($i = 0; $i < 512; $i++) {
        $point = mt_rand(0x10000, 0x10ffff);
        $offset = $point - 0x10000;
        $input = sprintf('%%u%04X%%u%04X', 0xd800 + ($offset >> 10), 0xdc00 + ($offset & 0x3ff));
        if ($decode($input) !== '&#' . $point . ';') throw new RuntimeException('Surrogate mismatch in ' . $file);
        $checks++;
    }
}
restore_error_handler();
echo 'Legacy Unicode search decoder checks: ' . $checks . " passed.\n";

