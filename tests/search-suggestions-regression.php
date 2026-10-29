<?php
/* Pure resource-local suggestion tests, including full phrases and budgets. */
require_once dirname(__DIR__) . '/search-service.inc.php';
require_once dirname(__DIR__) . '/search-suggestions.inc.php';
$checks = 0;
function suggestionCheck($condition, $message) {
    global $checks;
    $checks++;
    if (!$condition) throw new RuntimeException($message);
}
function suggestionRows($query, $rows, $options = array()) {
    $state = u5SearchSuggestionState(u5SearchParseQuery($query));
    foreach ($options as $key => $value) $state[$key] = $value;
    foreach ($rows as $i => $row) u5SearchSuggestionCollect($state, $row[0], $row[1], 'resource' . $i);
    $results = u5SearchSuggestionResults($state);
    foreach ($results as $text) {
        $parsed = u5SearchParseQuery($text);
        suggestionCheck($parsed['error'] === null, 'Suggested query parses: ' . $text);
        $found = false;
        foreach ($rows as $row) $found = $found || u5SearchEvaluate($row[0], $row[1], $parsed)['matched'];
        suggestionCheck($found, 'Suggested query has a complete same-resource match: ' . $text);
    }
    suggestionCheck(count($results) <= 3, 'At most three display suggestions');
    suggestionCheck(count(u5SearchSuggestionCandidates($state)) <= 12, 'Bounded ranked candidate reserve');
    return array($results, $state);
}
foreach (array(
    array('Bren', '', 'Bern Rathaus', 'Bern'),
    array('muler', '', "M\u{fc}ller", "M\u{fc}ller"),
    array('Mueller', '', "M\u{fc}ller", "M\u{fc}ller"),
    array('Bern Rathuas', '', 'Bern Rathaus', 'Bern Rathaus'),
    array('Bren Rathuas', '', 'Bern Rathaus', 'Bern Rathaus'),
    array('BERN Rathuas', '', 'Bern Rathaus', 'BERN Rathaus'),
    array('"Bern Rathuas"', '', 'Bern Rathaus', '"Bern Rathaus"'),
    array("\u{ab}Bern Rathuas\u{bb}", '', 'Bern Rathaus', '"Bern Rathaus"'),
    array('Adressen "Mueller (Bern Rathuas) Hilfe & Beratung"', '', "Adressen M\u{fc}ller (Bern Rathaus) Hilfe & Beratung", "Adressen \"M\u{fc}ller (Bern Rathaus) Hilfe & Beratung\""),
    array('"New Yolk"', '', 'New York has eggs and yolk.', '"New York"'),
    array('foo_bra', '', 'foo_bar', 'foo_bar'),
    array('foo1bra', '', 'foo1bar', 'foo1bar'),
    array('[squre]', '', '[square]', '[square]'),
    array('100% sunflwer C++', '', '100% sunflower C++', '100% sunflower C++'),
    array('"C++ (Wrot) 100% foo_bra"', '', "C++ (W\u{f6}rt) 100% foo_bar", "\"C++ (W\u{f6}rt) 100% foo_bar\""),
    array('muler', '', "M\u{fc}l\u{ad}ler", "M\u{fc}l\u{ad}ler"),
    array('muler', '', "M\u{fc}l\u{200b}ler", "M\u{fc}l\u{200b}ler"),
    array('ecle', '', "e\u{301}cole Ecole", "e\u{301}cole"),
    array('Bren Rathuas', 'Bern', 'Rathaus', 'Bern Rathaus'),
) as $case) {
    list($results) = suggestionRows($case[0], array(array($case[1], $case[2])));
    suggestionCheck(in_array($case[3], $results, true), 'Expected complete correction: ' . $case[0] . ' -> ' . $case[3]);
}
list($escaped) = suggestionRows('"say \\"helllo\\" \\\\path"', array(array('', 'say "hello" \\path')));
suggestionCheck(count($escaped) === 1, 'Quoted literal quote/backslash phrase is correctable');
$parsed = u5SearchParseQuery($escaped[0]);
suggestionCheck($parsed['terms'][0]['phrase'] && $parsed['terms'][0]['text'] === 'say "hello" \\path', 'Literal quotes/backslash survive serialization');
foreach (array(
    array('"Bren Rathuas"', array(array('Bern', 'Rathaus'))),
    array('Harbor Gazeboo', array(array('', 'Harbour'), array('', 'Gazebo'))),
    array('100% sunflwer', array(array('', '100 sunflower'))),
    array('C++ sunflwer', array(array('', 'C sunflower'))),
    array('foo1bra', array(array('', 'foo2bar'))),
    array('[squre]', array(array('', 'square'))),
    array('"Bern (Rathuas)"', array(array('', 'Bern Rathaus'))),
    array('"Bern Rathuas"', array(array('', 'Rathaus Bern'))),
) as $case) {
    list($results) = suggestionRows($case[0], $case[1]);
    suggestionCheck($results === array(), 'Never invent a punctuation/order/field/resource match: ' . $case[0]);
}
list($partial) = suggestionRows('Bern Rathuas', array(array('Bern', 'Museum'), array('', 'Bern Rathaus')));
suggestionCheck($partial === array('Bern Rathaus'), 'A partial original match does not block correction');
list($exact, $exactState) = suggestionRows('Bren', array(array('', 'Bern'), array('', 'Bren')));
suggestionCheck($exact === array() && $exactState['exact'], 'A newly observed exact query clears alternatives');

$longWord = str_repeat('a', 100);
list($long) = suggestionRows('"' . $longWord . ' Rathuas"', array(array('', $longWord . ' Rathaus')));
suggestionCheck($long === array('"' . $longWord . ' Rathaus"'), 'A long unchanged word does not disable another correction in its phrase');
$original = 'alpha bravo charlie delta echo foxtrot golf hotel india juliet kilo lima mike november oscar papa';
// Sixteen independent typo terms, within the existing parser limit.
$words = explode(' ', $original);
$typos = array();
foreach ($words as $word) $typos[] = $word[1] . $word[0] . substr($word, 2);
list($many) = suggestionRows(implode(' ', $typos), array(array('', $original)));
suggestionCheck(in_array($original, $many, true), 'All sixteen terms may receive a correction');

list($ranked) = suggestionRows('sunflwer', array(array('sunflowerz', ''), array('', 'sunflower')));
suggestionCheck($ranked[0] === 'sunflower', 'Lower edit cost outranks a more prominent but poorer title alternative');
list($duplicates, $witnesses) = suggestionRows('Bren', array_fill(0, 6, array('', 'Bern')));
suggestionCheck($duplicates === array('Bern'), 'Equivalent complete queries are deduplicated');
suggestionCheck(count(u5SearchSuggestionCandidates($witnesses)[0]['sources']) === 3, 'Witness resources have a bounded reserve');

list($cut, $cutState) = suggestionRows('sunflwer', array(array('', 'sunflowerSuffix')), array('remainingBytes' => 9));
suggestionCheck($cut === array() && $cutState['truncated'], 'Byte clipping cannot invent a completed sunflower word');
list($tokenCut, $tokenState) = suggestionRows('sunflwer', array(array('', str_repeat('x ', 100001) . 'sunflower')));
suggestionCheck($tokenCut === array() && $tokenState['remainingTokens'] === 0, 'Repeated words consume the source token budget');
$giant = 'M' . str_repeat("\u{ad}", 600) . "\u{fc}ller";
list($recover) = suggestionRows('muler', array(array('', $giant . " M\u{fc}ller " . $giant)));
suggestionCheck($recover === array("M\u{fc}ller"), 'An oversized spelling cannot shadow a later valid same-fold word');
$nearLimit = 'M' . str_repeat("\u{ad}", 500) . "\u{fc}ller";
list($quotedRecover) = suggestionRows('"muler"', array(array('', $nearLimit . " M\u{fc}ller")));
suggestionCheck($quotedRecover === array('"Müller"'), 'An excessive invisible run does not shadow a concise equivalent quoted correction');
unset($giant, $nearLimit);
list($expanded, $expandedState) = suggestionRows('sunflwer', array(array('', str_repeat("\u{fdfa}", 200000) . ' sunflower')));
suggestionCheck($expanded === array() && $expandedState['truncated'], 'Compatibility expansion respects a separate normalized-output bound');

// The monotone source mapper must agree with the core mapper for every folded
// letter token, including expansions, ignored characters and combining marks.
$table = u5SearchNormalizationMap();
$source = implode(' | ', array_keys($table)) . " M\u{fc}l\u{ad}ler e\u{301}cole";
$folded = u5SearchNormalize($source, false);
preg_match_all('/\p{L}[\p{L}\p{M}]*/u', $folded, $tokens, PREG_OFFSET_CAPTURE);
$anchors = array();
foreach ($tokens[0] as $i => $token) $anchors[] = array($token[1], $token[1] + strlen($token[0]), $i);
$expected = u5SearchResolveOccurrences($source, $anchors);
$cursor = array('source' => 0, 'characters' => 0, 'normalized' => 0, 'space' => false, 'segment' => null);
foreach ($expected as $range) {
    $token = $tokens[0][$range[4]];
    $actual = u5SearchSuggestionSourceRange($source, $token[1], $token[1] + strlen($token[0]), $cursor);
    suggestionCheck($actual === array_slice($range, 0, 4), 'Streaming source range equals core source range');
}
echo 'Search suggestion checks: ' . $checks . " passed.\n";
