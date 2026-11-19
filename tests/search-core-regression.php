<?php
/* Pure search regressions. Run with PHP 7.4+ and mbstring; no database needed. */
require_once dirname(__DIR__) . '/search-core.inc.php';
if (isset($argv[1], $argv[2]) && $argv[1] === '--parity-input') {
    $input = json_decode(file_get_contents($argv[2]), true);
    $output = array();
    foreach ($input['cases'] as $case) {
        $parsed = u5SearchParseQuery($case['query']);
        if ($parsed['error'] !== null) throw new RuntimeException($parsed['error']);
        $match = u5SearchMatch($case['text'], $parsed, true);
        $pieces = array();
        foreach ($match['ranges'] as $range) $pieces[] = substr($case['text'], $range[0], $range[1] - $range[0]);
        $output[] = array('normalized' => u5SearchNormalize($case['text']), 'terms' => $parsed['terms'], 'pieces' => $pieces);
    }
    echo json_encode(array('cases' => $output, 'tableText' => u5SearchNormalize($input['tableText'])));
    exit;
}
$checks = 0;
function searchCheck($condition, $message) {
    global $checks;
    $checks++;
    if (!$condition) throw new RuntimeException('Search check failed: ' . $message);
}
function searchSame($actual, $expected, $message) {
    searchCheck($actual === $expected, $message . ': ' . var_export($actual, true));
}
function searchTerms($query) {
    $parsed = u5SearchParseQuery($query);
    searchSame($parsed['error'], null, 'parse ' . $query);
    return $parsed['terms'];
}

$query = '"Wort (Wort Wort) Wort"';
$parsed = u5SearchParseQuery($query);
searchSame($parsed['raw'], $query, 'canonical display query stays literal');
searchSame(count($parsed['terms']), 1, 'one quoted phrase');
searchSame($parsed['terms'][0]['text'], 'Wort (Wort Wort) Wort', 'parentheses survive parsing');
searchSame($parsed['terms'][0]['phrase'], true, 'quote provenance');
searchCheck(u5SearchMatch('Davor Wort (Wort Wort) Wort danach', $parsed)['matched'], 'original parentheses match');
searchCheck(!u5SearchMatch('Davor Wort Wort Wort Wort danach', $parsed)['matched'], 'missing parentheses do not match');
searchCheck(!u5SearchMatch('Wort (Wort anderes Wort) Wort', $parsed)['matched'], 'phrase remains contiguous');
searchCheck(strpos(u5SearchSnippet('Davor Wort (Wort Wort) Wort danach', $parsed), '<span class="hitshilite">Wort (Wort Wort) Wort</span>') !== false, 'whole literal phrase is quoted and highlighted');
searchSame(u5SearchSnippet('Wort Wort Wort Wort', $parsed), '', 'no fabricated quotation');

$mixed = searchTerms('Anmeldung "roter Faden"');
searchCheck(u5SearchMatch('Ein roter Faden zur Anmeldung', $mixed)['matched'], 'mixed word and quoted phrase AND');
searchCheck(!u5SearchMatch('Anmeldung: roter sehr langer Faden', $mixed)['matched'], 'quoted words cannot drift apart');
searchCheck(u5SearchMatch('blau und danach rot', searchTerms('rot blau'))['matched'], 'unquoted terms may occur in either order');
searchSame(u5SearchParseQuery("\u{201e}Wort (Wort)\u{201c}")['terms'][0]['normalized'], 'wort (wort)', 'typographic quote pair');
searchSame(u5SearchParseQuery("\u{00ab}Wort (Wort)\u{00bb}")['terms'][0]['phrase'], true, 'French quote pair');
searchSame(u5SearchParseQuery('"not closed')['error'], 'unclosed_quote', 'unclosed phrase rejected');
searchSame(u5SearchParseQuery('""')['error'], 'empty_phrase', 'empty phrase rejected');
searchSame(u5SearchParseQuery('   ')['error'], 'empty', 'blank query rejected');
searchSame(u5SearchParseQuery("a\x00b")['error'], 'invalid_control', 'NUL rejected');
searchSame(u5SearchParseQuery(str_repeat("\u{1f600}", 513))['error'], 'too_long', 'query length is Unicode codepoints');
searchSame(u5SearchParseQuery(str_repeat('x ', 17))['error'], 'too_many_terms', 'term limit applies before deduplication');
searchSame(count(u5SearchParseQuery('word word "word"')['terms']), 2, 'deduplicate while preserving quote provenance');
searchSame(u5SearchParseQuery('"say \\"hello\\""')['terms'][0]['text'], 'say "hello"', 'escaped literal quotes inside phrase');
searchSame(u5SearchParseQuery('C:\\temp')['terms'][0]['text'], 'C:\\temp', 'ordinary path backslash remains literal');
searchSame(u5SearchParseQuery('"a\\\\b"')['terms'][0]['text'], 'a\\b', 'escaped backslash inside phrase');
$quotedLiteral = u5SearchParseQuery('"say \\"hello\\"" C:\\temp');
searchSame(u5SearchParseQuery(u5SearchSerializeTerms($quotedLiteral))['terms'], $quotedLiteral['terms'], 'serialized literal quotes and backslashes roundtrip');
$withoutQuotes = u5SearchParseQuery(u5SearchSerializeTerms($quotedLiteral, false));
searchSame($withoutQuotes['error'], null, 'without-phrase link keeps literal quotes escaped');
searchSame(count($withoutQuotes['terms']), 3, 'removing phrase constraint separates its words');
searchSame($withoutQuotes['terms'][1]['text'], '"hello"', 'literal quote data survives removing phrase constraint');

searchSame(u5SearchDecodeRequest('"a,b"', '2'), '"a,b"', 'new query comma is literal');
searchSame(u5SearchDecodeRequest('"a,b"'), '"a b"', 'legacy comma remains separator');
searchSame(u5SearchDecodeRequest('%uD83D%uDE00'), "\u{1f600}", 'legacy surrogate pair');
searchSame(u5SearchDecodeRequest('%u20AC'), "\u{20ac}", 'legacy percent-u');
searchSame(u5SearchDecodeRequest("M\xfc nchen \x80", '2'), "M\u{fc} nchen \u{20ac}", 'CP1252 browser request decoded');
searchSame(u5SearchDecodeRequest('&#128512;', '2'), '&#128512;', 'new numeric-looking query stays literal');
searchSame(u5SearchDecodeRequest('%uD83D%uDE00', '2'), '%uD83D%uDE00', 'new percent-u stays literal');
searchSame(u5SearchDecodeRequest('&#128512;'), "\u{1f600}", 'legacy numeric entity supported');
searchSame(u5SearchDecodeRequest(array('bad')), '', 'array input is rejected safely');
searchSame(u5SearchFromLegacy("Preis \x80"), "Preis \u{20ac}", 'known DB CP1252 bytes');
searchSame(u5SearchFromLegacy('&#128512;'), "\u{1f600}", 'indexed nonlegacy character');
searchSame(u5SearchFromLegacy('&amp;#128512;'), '&#128512;', 'visible numeric entity decoded once');
searchSame(u5SearchFromLegacy('&amp;amp;'), '&amp;', 'visible named entity decoded once');
searchCheck(u5SearchMatch(u5SearchFromLegacy('&amp;#128512;'), searchTerms('"&#128512;"'))['matched'], 'literal numeric-looking entity can be searched');
searchCheck(!u5SearchMatch("\u{1f600}", searchTerms('"&#128512;"'))['matched'], 'literal entity does not silently become emoji');

$punctuation = 'A[B](C).+?^$|\\%_';
searchCheck(u5SearchMatch($punctuation, searchTerms('"' . $punctuation . '"'))['matched'], 'regex-looking punctuation remains literal');
searchCheck(!u5SearchMatch('1000 success', searchTerms('100%'))['matched'], 'percent is not a wildcard');
searchCheck(!u5SearchMatch('axb', searchTerms('a_b'))['matched'], 'underscore is not a wildcard');
searchCheck(u5SearchMatch('C++', searchTerms('C++'))['matched'], 'plus signs preserved');
searchCheck(u5SearchMatch('alpha beta', searchTerms('alpha-beta'))['matched'], 'established dash equivalence');
searchCheck(u5SearchMatch("alpha\u{2011}beta", searchTerms('"alpha beta"'))['matched'], 'Unicode dash equivalence');
searchSame(u5SearchNormalize("  \u{c9}cole\u{a0}\u{202f}STRA\u{1e9e}E  "), 'ecole strasse', 'case accents and whitespace');
searchSame(u5SearchNormalize("\u{152} \u{c6} \u{d8} \u{141} \u{130}"), 'oe ae o l i', 'ligature and base character folding');
searchSame(u5SearchNormalize("e\u{301}"), 'e', 'decomposed accent folding');
searchSame(u5SearchNormalize("ab\u{ad}c\u{200b}d"), 'abcd', 'soft hyphen and invisible spacing');
searchCheck(u5SearchMatch("\u{041f}\u{0440}\u{0438}\u{0432}\u{0435}\u{0442}", searchTerms("\u{043f}\u{0440}\u{0438}\u{0432}\u{0435}\u{0442}"))['matched'], 'Cyrillic case folding');
searchCheck(u5SearchNormalize("\u{30d1}") !== u5SearchNormalize("\u{30cf}"), 'Japanese voicing is preserved');

foreach (array(
    array('x ' . "Stra\u{df}e" . ' y', 'strasse', "Stra\u{df}e"),
    array('x ' . "\u{153}" . ' y', 'oe', "\u{153}"),
    array('x ' . "e\u{301}" . ' y', 'e', "e\u{301}"),
    array("\u{1f600} x Ziel", 'ziel', 'Ziel'),
    array('x ' . "a\u{a0}\u{a0}b" . ' y', '"a b"', "a\u{a0}\u{a0}b")
) as $case) {
    $matched = u5SearchMatch($case[0], searchTerms($case[1]), true);
    searchSame(count($matched['ranges']), 1, 'one original offset range');
    $range = $matched['ranges'][0];
    searchSame(substr($case[0], $range[0], $range[1] - $range[0]), $case[2], 'original bytes survive normalization');
}
$overlap = u5SearchSnippet('foobar', searchTerms('foo foobar'));
searchSame($overlap, '<span class="hitshilite">foobar</span>', 'overlapping matches merge without nested spans');
$safeSnippet = u5SearchSnippet('<script>unsafe & "text"</script>', searchTerms('unsafe'));
searchCheck(strpos($safeSnippet, '<script>') === false && strpos($safeSnippet, '&lt;script&gt;') !== false, 'quotes cannot inject HTML');
searchSame(substr_count($safeSnippet, '<span'), substr_count($safeSnippet, '</span>'), 'snippet spans balanced');
searchSame(u5SearchHtml("\u{1f600}<>&\"'"), '&#128512;&lt;&gt;&amp;&quot;&apos;', 'ASCII-safe output on legacy pages');
$safeJson = u5SearchJson(array('text' => '</script>"\'&' . "\u{2028}"));
searchCheck(strpos($safeJson, '<') === false && strpos($safeJson, '>') === false && strpos($safeJson, '&') === false, 'JSON cannot close script element');
searchSame(json_decode($safeJson, true)['text'], '</script>"\'&' . "\u{2028}", 'safe JSON preserves exact text');

$terms = searchTerms('rot blau');
$row = u5SearchEvaluate('rot', 'blau', $terms);
searchCheck($row['matched'], 'AND may span title and body for separate terms');
searchSame($row['matchedTerms'], 2, 'matched term count');
searchCheck(!u5SearchEvaluate('rot', 'blau', searchTerms('"rot blau"'))['matched'], 'a phrase cannot span title and body');
searchCheck(u5SearchEvaluate('rot blau', 'other', $terms)['score'] > u5SearchEvaluate('other', 'rot blau', $terms)['score'], 'all-title match ranks higher');
searchSame(u5SearchEvaluate('rot', '', $terms)['matchedTerms'], 1, 'partial result count available without rewriting query');
searchCheck(!u5SearchMatch('', $terms)['matched'], 'empty corpus returns false immediately');
searchSame(u5SearchSnippet('', $terms), '', 'empty corpus produces no fabricated quote');
searchCheck(!u5SearchMatch('Alpha', searchTerms('AlphaBeta'))['matched'], 'no cross-document synthetic word');

// Every shared fold must be final and map/range normalizers must agree.
foreach (u5SearchNormalizationMap() as $character => $folded) {
    searchSame(u5SearchNormalize($folded, false), preg_replace('/ +/', ' ', $folded), 'final shared fold ' . bin2hex($character));
}
$mappingFixture = " \u{c4}\u{a0}Stra\u{df}e e\u{301} \u{153} \u{1f600}  ";
searchSame(u5SearchNormalizeWithMap($mappingFixture)['text'], u5SearchNormalize($mappingFixture, false), 'offset normalization agrees with fast matching');
$longText = str_repeat('word ', 210000) . 'late (unique) hit';
$memoryBefore = memory_get_usage(true);
$lateSnippet = u5SearchSnippet($longText, searchTerms('"late (unique) hit"'));
searchCheck(strpos($lateSnippet, '<span class="hitshilite">late (unique) hit</span>') !== false, 'late hit in megabyte document survives bounded mapping');
searchCheck(memory_get_usage(true) - $memoryBefore < 16 * 1024 * 1024, 'offset mapping does not allocate per-character arrays for long document');
$denseSnippet = u5SearchSnippet(str_repeat('word ', 30000), searchTerms('word'));
searchCheck(strlen(strip_tags(html_entity_decode($denseSnippet, ENT_QUOTES | ENT_HTML5, 'UTF-8'))) < 600, 'dense matches cannot expand snippets to whole document');
// Overlapping occurrences must not merge into one document-sized anchor.
$period = str_repeat('a', 508) . 'b';
$overlapPhrase = $period . 'a';
$overlapQuery = searchTerms('"' . $overlapPhrase . '"');
$overlapSnippet = u5SearchSnippet(str_repeat($period, 1000) . 'a', $overlapQuery);
searchCheck(strlen($overlapSnippet) < 1000, 'long overlapping phrases keep excerpts bounded');
searchCheck(strpos($overlapSnippet, '<span class="hitshilite">' . $overlapPhrase . '</span>') !== false, 'bounded overlapping phrase remains complete');
searchSame(substr_count($overlapSnippet, '<span'), substr_count($overlapSnippet, '</span>'), 'overlapping phrase excerpt markup is balanced');
$representative = u5SearchSnippet(str_repeat('alpha ' . str_repeat('x', 150) . ' ', 1000) . 'beta', searchTerms('alpha beta'));
searchCheck(strpos($representative, '<span class="hitshilite">alpha</span>') !== false, 'representative excerpts include early frequent term');
searchCheck(strpos($representative, '<span class="hitshilite">beta</span>') !== false, 'representative excerpts include late distinct term');
searchCheck(strlen($representative) < 1000, 'representative selection remains bounded');
$wordEdges = u5SearchSnippet('prefix abcdefg TARGET uvwxy suffix', searchTerms('target'), 3);
searchSame($wordEdges, '&hellip;abcdefg <span class="hitshilite">TARGET</span> uvwxy&hellip;', 'Short context expands to nearby complete word edges');
$accentedEdges = u5SearchSnippet("prefix d\u{e9}compos\u{e9} TARGET re\u{301}ponse suffix", searchTerms('target'), 3);
searchSame($accentedEdges, '&hellip;' . u5SearchHtml("d\u{e9}compos\u{e9}") . ' <span class="hitshilite">TARGET</span> ' . u5SearchHtml("re\u{301}ponse") . '&hellip;', 'Word-edge context preserves Unicode and attached diacritics');
searchSame(u5SearchSnippet('prefixTARGETsuffix', searchTerms('target'), 0), '&hellip;<span class="hitshilite">TARGET</span>&hellip;', 'Zero context still requests only the complete match');
$longWords = u5SearchSnippet(str_repeat('a', 500) . ' TARGET ' . str_repeat('b', 500), searchTerms('target'), 3);
searchSame($longWords, '&hellip; <span class="hitshilite">TARGET</span> &hellip;', 'An oversized context word is trimmed without expanding the excerpt');
searchSame(u5SearchSnippet(str_repeat('a', 500) . 'TARGET' . str_repeat('b', 500), searchTerms('target'), 3), '&hellip;aaa<span class="hitshilite">TARGET</span>bbb&hellip;', 'A literal substring inside a giant word stays complete and highlighted');
$wideAnchor = 'a' . str_repeat(' ', 248) . 'b';
$boundedEdges = u5SearchSnippet('abcdefghij ' . $wideAnchor . ' klmnopqrst', searchTerms('"a b"'), 65);
searchSame($boundedEdges, '&hellip; <span class="hitshilite">' . $wideAnchor . '</span> &hellip;', 'Word-edge refinement keeps the hard fragment bound and complete original phrase');
$fullWidthAnchor = 'a' . str_repeat(' ', 254) . 'b';
searchSame(u5SearchSnippet('prefix' . $fullWidthAnchor . 'suffix', searchTerms('"a b"'), 65), '&hellip;<span class="hitshilite">' . $fullWidthAnchor . '</span>&hellip;', 'An anchor filling the whole fragment cannot be clipped to tidy word edges');
$hugeWhitespace = 'a' . str_repeat(' ', 1000000) . 'b';
searchCheck(u5SearchMatch($hugeWhitespace, searchTerms('"a b"'))['matched'], 'huge original whitespace still obeys matching semantics');
searchSame(u5SearchSnippet($hugeWhitespace, searchTerms('"a b"')), '', 'oversized original quote is omitted instead of clipped or emitted whole');
unset($hugeWhitespace);
$hugeInvisible = 'a' . str_repeat("\u{200b}", 1000000) . 'b';
searchSame(u5SearchSnippet($hugeInvisible, searchTerms('"ab"')), '', 'oversized invisible source span cannot bypass excerpt limit');
unset($hugeInvisible);
foreach (array(
    array("x Stra\u{df}e y", 'strasse', "Stra\u{df}e"),
    array("x \u{153} y", 'oe', "\u{153}"),
    array("x e\u{301} y", 'e', "e\u{301}"),
    array("\u{1f600} x Ziel", 'ziel', 'Ziel'),
    array("x a\u{a0}\u{a0}b y", '"a b"', "a\u{a0}\u{a0}b")
) as $case) {
    $snippet = u5SearchSnippet($case[0], searchTerms($case[1]), 0);
    searchCheck(strpos($snippet, '<span class="hitshilite">' . u5SearchHtml($case[2]) . '</span>') !== false, 'individual anchor offsets preserve original Unicode text');
}
echo 'Search core: ' . $checks . " checks passed.\n";
