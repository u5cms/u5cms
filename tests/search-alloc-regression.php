<?php
/* Legacy alloc callers and the active UTF-8 renderer share literal matching. */
require_once dirname(__DIR__) . '/alloc.inc.php';
require_once dirname(__DIR__) . '/search-core.inc.php';
$checks = 0;
function allocCheck($condition, $message) {
    global $checks;
    $checks++;
    if (!$condition) throw new RuntimeException($message);
}
function allocLegacy($text) {
    return mb_encode_numericentity($text, array(0x80, 0x10ffff, 0, 0x1fffff), 'UTF-8');
}
foreach (array(
    array('"Wort (Wort Wort) Wort"', 'Davor Wort (Wort Wort) Wort danach', 'Wort (Wort Wort) Wort'),
    array('cafe', "Davor Caf\u{00e9} danach", "Caf\u{00e9}"),
    array('strasse', "Die Stra\u{00df}e ist hier.", "Stra\u{00df}e"),
    array('oeuvre', "Ein \u{0152}uvre", "\u{0152}uvre"),
    array('ecole', "Eine e\u{0301}cole", "e\u{0301}cole"),
    array('C++', 'Lernen Sie C++.', 'C++'),
    array('100%', 'Mit 100% Leistung', '100%'),
    array('foo_bar', 'foo_bar und foo bar', 'foo_bar'),
    array('a.b', 'a.b und axb', 'a.b'),
    array('[square]', 'Mit [square] Klammern', '[square]'),
    array('a&b', 'a&b', 'a&b'),
    array("\u{1f600}", "Emoji \u{1f600} im Text", "\u{1f600}"),
) as $case) {
    $expected = '<span class="hitshilite">' . u5SearchHtml($case[2]) . '</span>';
    $legacy = alloc(allocLegacy($case[0]), allocLegacy($case[1]));
    $canonical = alloc(u5SearchParseQuery($case[0]), $case[1]);
    allocCheck(strpos($legacy, $expected) !== false, 'Legacy caller marks literal source: ' . $case[0]);
    allocCheck($canonical === $legacy, 'Canonical/legacy excerpt parity: ' . $case[0]);
    allocCheck(substr_count($canonical, '<span') === substr_count($canonical, '</span>'), 'Balanced highlight spans');
}
allocCheck(strpos(alloc('cafe', "Caf\xe9"), '&#233;') !== false, 'Real Windows-1252 bytes retain their accent');
allocCheck(alloc('absent', 'No matching text') === '', 'No fabricated ellipsis for an absent term');
allocCheck(alloc('"unclosed', 'unclosed') === '', 'Invalid legacy phrase gives no partial quote');
allocCheck(alloc(u5SearchParseQuery('"unclosed'), 'unclosed') === '', 'Invalid canonical phrase gives no partial quote');
allocCheck(alloc('foo_bar', 'foo bar') === '', 'Literal underscore never becomes a space');
allocCheck(alloc('"Wort (Wort Wort) Wort"', 'Wort Wort Wort Wort') === '', 'Parentheses are required in a quoted phrase');
$literal = alloc(u5SearchParseQuery('&#128512;'), 'Literal &#128512; notation');
allocCheck(strpos($literal, '<span class="hitshilite">&amp;#128512;</span>') !== false, 'Canonical entity-looking text is not decoded twice');
$payload = '<img src=x onerror=alert(1)>';
$html = alloc(u5SearchParseQuery('"' . $payload . '"'), $payload);
allocCheck(strpos($html, '<img') === false && strpos($html, '&lt;img') !== false, 'Excerpt text cannot create markup');
$long = alloc(u5SearchParseQuery('alpha beta'), str_repeat('alpha ', 300) . str_repeat('filler ', 300) . 'beta');
allocCheck(strpos($long, '>alpha</span>') !== false && strpos($long, '>beta</span>') !== false, 'Representative excerpts include a late distinct term');
allocCheck(strlen($long) < 1500, 'Legacy entry point retains bounded excerpts');
echo 'Search alloc checks: ' . $checks . " passed.\n";
