<?php
/* PHP 7.4+ and mbstring. Isolated SQL stubs; no database or repository writes. */
$serviceFixture = sys_get_temp_dir() . '/u5-search-service-' . bin2hex(random_bytes(8));
mkdir($serviceFixture, 0700, true);
mkdir($serviceFixture . '/r');
foreach (array('search-service.inc.php', 'search-core.inc.php', 'search-normalization.json') as $file) {
    copy(dirname(__DIR__) . '/' . $file, $serviceFixture . '/' . $file);
}
register_shutdown_function(function () use ($serviceFixture) {
    $entries = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($serviceFixture, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($entries as $entry) {
        if ($entry->isDir()) rmdir($entry->getPathname());
        else unlink($entry->getPathname());
    }
    rmdir($serviceFixture);
});
require $serviceFixture . '/search-service.inc.php';
if (!defined('MYSQL_ASSOC')) define('MYSQL_ASSOC', 1);

$serviceChecks = 0;
$serviceSteps = array();
$serviceCalls = array();
$serviceResults = array();
function serviceCheck($condition, $message) {
    global $serviceChecks;
    $serviceChecks++;
    if (!$condition) throw new RuntimeException('Service check failed: ' . $message);
}
function serviceSame($actual, $expected, $message) {
    serviceCheck($actual === $expected, $message . ': ' . var_export($actual, true));
}
class ServiceResult {
    public $rows;
    public $offset = 0;
    public $fetches = 0;
    public $freed = false;
    public function __construct($rows) { $this->rows = $rows; }
}
class ServiceUnreadableBody {
    public function __toString() { throw new RuntimeException('A forbidden protected body was read.'); }
}
function serviceDatabase($steps) {
    global $serviceSteps, $serviceCalls, $serviceResults;
    serviceSame(count($serviceSteps), 0, 'Previous SQL scenario was fully consumed');
    foreach ($serviceResults as $result) serviceCheck($result->freed, 'Every successful SQL result was released');
    $serviceSteps = $steps;
    $serviceCalls = array();
    $serviceResults = array();
}
function serviceSql($kind, $sql) {
    global $serviceSteps, $serviceCalls, $serviceResults;
    $serviceCalls[] = array($kind, $sql);
    if (!$serviceSteps) throw new RuntimeException('Unexpected SQL call: ' . $sql);
    $step = array_shift($serviceSteps);
    serviceSame($kind, $step['kind'], 'Expected SQL operation');
    if (isset($step['before'])) $step['before']($sql);
    if (!empty($step['fail'])) return false;
    $rows = isset($step['rows']) && is_callable($step['rows']) ? $step['rows']($sql) : ($step['rows'] ?? array());
    $result = new ServiceResult($rows);
    $serviceResults[] = $result;
    return $result;
}
function mysql_unbuffered_query($sql) { return serviceSql('scan', $sql); }
function mysql_query($sql) { return serviceSql('detail', $sql); }
function mysql_fetch_array($result, $mode = MYSQL_ASSOC) {
    $result->fetches++;
    return $result->offset < count($result->rows) ? $result->rows[$result->offset++] : false;
}
function mysql_free_result($result) { $result->freed = true; return true; }
function mysql_real_escape_string($value) { return str_replace(array('\\', "'"), array('\\\\', "\\'"), $value); }
function serviceRow($name, $title, $body, $extra = array()) {
    return array_merge(array('name'=>$name, 'typ'=>'p', 'logins'=>'', 'lastmut'=>100,
        'title_1'=>$title, 'title_2'=>'', 'title_3'=>'', 'title_4'=>'', 'title_5'=>'', 'search_text'=>$body), $extra);
}
function serviceNames($result) { return array_column($result['rows'], 'name'); }
function serviceLegacy($text) { return mb_encode_numericentity($text, array(0x80, 0x10ffff, 0, 0x1fffff), 'UTF-8'); }
function serviceScan($rows, $before = null) { return array('kind'=>'scan', 'rows'=>$rows, 'before'=>$before); }
function serviceDetails($rows, $before = null) { return array('kind'=>'detail', 'rows'=>$rows, 'before'=>$before); }
function serviceSuggestions($query, $rows, $policy) {
    serviceDatabase(array(serviceScan($rows)));
    return u5SearchSuggest(u5SearchParseQuery($query), 1, $policy, u5SearchWhere(1, $policy));
}

// Configuration remains authoritative; the intranet opt-out is scoped to ! names.
foreach (array('doesfindpasswordprotectedcontent', 'doesshowpreviewofsuchcontent', 'doeshideintranetcontenttopublic', 'automaticallyskipintranetlogin', 'excludedobjecttypesinsearchresults', 'searchenginesqladditionalbooland') as $setting) unset($GLOBALS[$setting]);
$public = u5SearchPolicy();
serviceSame($public, array('findProtected'=>false, 'previewProtected'=>false, 'hideIntranet'=>true, 'intranet'=>false, 'openIntranet'=>false), 'Safe default policy');
$GLOBALS['doesfindpasswordprotectedcontent'] = 'yes';
$GLOBALS['doesshowpreviewofsuchcontent'] = 'no';
$GLOBALS['doeshideintranetcontenttopublic'] = 'no';
$GLOBALS['automaticallyskipintranetlogin'] = 'yes';
$findProtected = u5SearchPolicy();
$open = u5SearchPolicy(true);
serviceCheck($findProtected['findProtected'] && !$findProtected['previewProtected'], 'Finding a title does not grant a protected preview');
serviceCheck(!$findProtected['openIntranet'] && $open['openIntranet'], 'Login opt-out applies only to intranet scope');
serviceCheck(u5SearchRowProtected(array('name'=>'ordinary', 'logins'=>'person:secret'), $open), 'Ordinary protected resource remains protected during intranet opt-out');
serviceCheck(!u5SearchRowProtected(array('name'=>'!intranet', 'logins'=>'person:secret'), $open), 'Explicit opt-out opens intranet-prefixed resource');
serviceCheck(u5SearchRowProtected(array('name'=>'!intranet', 'logins'=>'person:secret'), $public), 'Public scope does not inherit intranet opt-out');
$where = u5SearchWhere(5, $public);
foreach (array('deleted!=1', 'hidden=0', "typ!='c'", 'search_5 IS NOT NULL', "name NOT LIKE '!%'", "logins NOT LIKE '%:%'") as $clause) serviceCheck(strpos($where, $clause) !== false, 'Eligibility includes ' . $clause);
serviceCheck(strpos($where, "search_5!=''") === false, 'An indexed empty body remains eligible for a title-only match');
$open['findProtected'] = false;
serviceCheck(strpos(u5SearchWhere(1, $open), "(logins NOT LIKE '%:%' OR name LIKE '!%')") !== false, 'SQL opt-out permits only intranet-prefixed protected rows');
$GLOBALS['excludedobjecttypesinsearchresults'] = 'iyq';
$GLOBALS['searchenginesqladditionalbooland'] = 'AND archived=0';
$where = u5SearchWhere(2, $public);
serviceCheck(strpos($where, "typ!='i'") !== false && strpos($where, "typ!='y'") !== false && strpos($where, "typ!='q'") === false, 'Only supported configured object types become SQL exclusions');
serviceCheck(substr($where, -14) === 'AND archived=0', 'Trusted administrator eligibility condition remains available');
unset($GLOBALS['excludedobjecttypesinsearchresults'], $GLOBALS['searchenginesqladditionalbooland']);

// Language fallback and decoding are shared by ranking and suggestions.
$fallback = serviceRow('fallback', 'First title', '', array('title_3'=>'Third title', 'title_4'=>'   '));
serviceSame(u5SearchRowTitle($fallback, 3), 'Third title', 'Requested language title wins');
serviceSame(u5SearchRowTitle($fallback, 4), 'First title', 'Blank requested language falls back to first nonempty title');
serviceSame(u5SearchRowTitle(serviceRow('name-fallback', '', ''), 5), 'name-fallback', 'Resource name is the final title fallback');
serviceSame(u5SearchRowTitle(serviceRow('encoded', "Caf\xe9 &#128512;", ''), 1), "Caf\u{e9} \u{1f600}", 'Title decoding handles CP1252 and Unicode references');
serviceSame(u5SearchRowTitle(serviceRow('literal-entity', '&euro;', ''), 1), '&euro;', 'A named entity in a title remains visible literal text like the CMS title renderer');
serviceSame(u5SearchRowTitle(serviceRow('currency', "\x80 &#128512;", ''), 1), "\u{20ac} \u{1f600}", 'CP1252 euro and numeric-reference emoji remain real title characters');

// Edit distance operates on Unicode characters and counts a swapped pair once.
foreach (array(
    array('same', 'same', 2, 0), array('', 'ab', 2, 2), array('ab', '', 2, 2),
    array("ma\u{f1}ana", 'manana', 1, 1), array("\u{732b}", "\u{732b}\u{72ac}", 1, 1),
    array("\u{1f600}x", "\u{1f601}x", 1, 1), array("e\u{301}cole", 'ecole', 1, 1),
    array('bren', 'bern', 1, 1), array('bern', 'bren', 1, 1),
    array('ab', 'ba', 1, 1), array('abcd', 'badc', 2, 2),
    array("\u{732b}\u{72ac}", "\u{72ac}\u{732b}", 1, 1),
    array('bren', 'bernx', 2, 2),
    array('x', 'enormous', 2, 3)
) as $case) serviceSame(u5SearchEditDistance($case[0], $case[1], $case[2]), $case[3], 'Unicode edit distance ' . json_encode(array($case[0], $case[1])));
serviceCheck(u5SearchEditDistance('abcdef', 'uvwxyz', 2) > 2, 'Clearly distant words stop outside the edit limit');
serviceCheck(u5SearchEditDistance('abcd', 'badc', 1) > 1, 'Two transpositions still exceed a one-error budget');

// Streaming tokens retain combining marks and charge repeated tokens to budgets.
$bytes = 1000; $tokens = 20;
serviceSame(iterator_to_array(u5SearchSuggestionWords("e\u{301}cole Stra\u{df}e _underscore word_2 42", $bytes, $tokens), false), array("e\u{301}cole", "Stra\u{df}e", 'underscore', 'word_2', '42'), 'Unicode token boundaries preserve original spelling');
serviceSame($tokens, 15, 'Each emitted token consumes one token');
$bytes = 100; $tokens = 3;
serviceSame(iterator_to_array(u5SearchSuggestionWords('word word word word novel', $bytes, $tokens), false), array('word', 'word', 'word'), 'Repeated words consume the finite token budget');
serviceSame($tokens, 0, 'Repeated tokens exhaust the shared budget');
serviceSame(iterator_to_array(u5SearchSuggestionWords('later', $bytes, $tokens), false), array(), 'Later resources cannot reset an exhausted token budget');
$bytes = 8; $tokens = 10;
serviceSame(iterator_to_array(u5SearchSuggestionWords('alpha beta', $bytes, $tokens), false), array('alpha'), 'Byte truncation never invents the partial word be');
serviceSame($bytes, 0, 'Long input consumes the remaining byte budget');
$bytes = 7; $tokens = 10;
serviceSame(iterator_to_array(u5SearchSuggestionWords("cat e\u{301}cole", $bytes, $tokens), false), array('cat'), 'A cut combining-mark word is not a candidate');
$bytes = 5; $tokens = 10;
serviceSame(iterator_to_array(u5SearchSuggestionWords("cat \u{1f600}tail", $bytes, $tokens), false), array('cat'), 'A byte cut inside a multibyte character remains valid UTF8');
$bytes = 4; $tokens = 10;
serviceSame(iterator_to_array(u5SearchSuggestionWords('word', $bytes, $tokens), false), array('word'), 'An entire word fitting exactly is retained');
$bytes = 9; $tokens = 10;
serviceSame(iterator_to_array(u5SearchSuggestionWords('one ', $bytes, $tokens), false), array('one'), 'First resource uses a shared byte budget');
serviceSame(iterator_to_array(u5SearchSuggestionWords('three more', $bytes, $tokens), false), array(), 'Second resource cannot turn a byte-boundary prefix into a word');
serviceSame($bytes, 0, 'Byte budget is shared across resources');

// Ranking prioritizes term coverage over title strength, then stable tie-breaks.
$partialBody = serviceRow('two-body-terms', 'Neutral', 'alpha beta');
$partialTitle = serviceRow('one-title-term', 'alpha', 'unrelated');
serviceDatabase(array(serviceScan(array($partialTitle, $partialBody)), serviceDetails(array($partialTitle, $partialBody))));
$result = u5SearchFind(u5SearchParseQuery('alpha beta omega'), 1, $public);
serviceSame(serviceNames($result), array('two-body-terms', 'one-title-term'), 'More matched terms outrank a stronger single title match');
serviceCheck($result['partial'] && $result['exactTotal'] === 0, 'Incomplete results remain explicitly partial');
serviceCheck($result['rows'][0]['score'] < $result['rows'][1]['score'], 'Coverage wins even when title relevance score is higher');
$exact = serviceRow('complete', 'Neutral', 'alpha beta omega');
serviceDatabase(array(serviceScan(array($partialTitle, $exact, $partialBody)), serviceDetails(array($exact))));
$result = u5SearchFind(u5SearchParseQuery('alpha beta omega'), 1, $public);
serviceSame(serviceNames($result), array('complete'), 'Complete matches suppress partial results');
serviceCheck(!$result['partial'] && $result['total'] === 1 && $result['exactTotal'] === 1, 'Complete counts reflect only full matches');
$emptyBody = serviceRow('empty-body', 'TitleNeedle', '');
serviceDatabase(array(serviceScan(array($emptyBody)), serviceDetails(array($emptyBody))));
$result = u5SearchFind(u5SearchParseQuery('TitleNeedle'), 1, $public);
serviceSame(serviceNames($result), array('empty-body'), 'An already indexed empty body can match its title');
serviceSame($result['rows'][0]['body'], '', 'Title-only result does not manufacture body content');

$pageRows = array();
for ($i = 1; $i <= 12; $i++) $pageRows[] = serviceRow('page-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT), 'Equal title', 'needle');
foreach (array($pageRows, array_reverse($pageRows)) as $scanOrder) {
    serviceDatabase(array(serviceScan($scanOrder), serviceDetails(array_slice($pageRows, 5, 5))));
    $result = u5SearchFind(u5SearchParseQuery('needle'), 1, $public, 2, 5);
    serviceSame(serviceNames($result), array('page-06', 'page-07', 'page-08', 'page-09', 'page-10'), 'Pagination is independent of database scan order');
    serviceSame(array($result['total'], $result['pages'], $result['page']), array(12, 3, 2), 'Pagination counts are stable');
}
serviceDatabase(array(serviceScan(array_reverse($pageRows)), serviceDetails(array_slice($pageRows, 10))));
$result = u5SearchFind(u5SearchParseQuery('needle'), 1, $public, 999, 1);
serviceSame(serviceNames($result), array('page-11', 'page-12'), 'Page and page-size lower bounds clamp deterministically');
serviceSame($result['page'], 3, 'An excessive page number resolves to the last page');
$sortRows = array(
    array('name'=>'b','matchedTerms'=>1,'score'=>10,'lastmut'=>10),
    array('name'=>'a','matchedTerms'=>1,'score'=>10,'lastmut'=>10),
    array('name'=>'newer','matchedTerms'=>1,'score'=>10,'lastmut'=>20),
    array('name'=>'title','matchedTerms'=>1,'score'=>20,'lastmut'=>1),
    array('name'=>'coverage','matchedTerms'=>2,'score'=>1,'lastmut'=>1)
);
u5SearchSortResults($sortRows);
serviceSame(array_column($sortRows, 'name'), array('coverage', 'title', 'newer', 'a', 'b'), 'Coverage, relevance, modification time, then name define stable order');

// Protected previews must not decode or return a body even when it matches.
$protected = serviceRow('protected', 'Protected title', 'needle secret-body', array('logins'=>'person:secret'));
$protectedDetail = array_merge($protected, array('search_text'=>new ServiceUnreadableBody()));
serviceDatabase(array(serviceScan(array($protected)), serviceDetails(array($protectedDetail))));
$result = u5SearchFind(u5SearchParseQuery('needle'), 1, $findProtected);
serviceSame(serviceNames($result), array('protected'), 'Configured protected title result is retained');
serviceCheck($result['rows'][0]['protected'] && !$result['rows'][0]['preview'], 'Protected result carries the lock and no-preview state');
serviceSame($result['rows'][0]['body'], '', 'Protected preview cannot leak the matching body');

// The file appears between the scan and selected-page fetch, in the copied site.
$raceName = 'race-' . bin2hex(random_bytes(6));
mkdir($serviceFixture . '/r/' . $raceName);
$raceFile = $serviceFixture . '/r/' . $raceName . '/.htaccess';
$race = serviceRow($raceName, 'Race title', 'needle confidential-body');
$raceUnreadable = array_merge($race, array('search_text'=>new ServiceUnreadableBody()));
$noProtected = $public; $noProtected['previewProtected'] = true;
serviceDatabase(array(
    serviceScan(array($race)),
    serviceDetails(array($raceUnreadable), function () use ($raceFile) { file_put_contents($raceFile, 'Require valid-user'); clearstatcache(true, $raceFile); }),
    serviceScan(array($raceUnreadable)),
    serviceScan(array($raceUnreadable))
));
$result = u5SearchFind(u5SearchParseQuery('needle'), 1, $noProtected);
serviceSame(serviceNames($result), array(), 'New file protection removes a previously selected resource');
serviceSame(array($result['total'], $result['exactTotal'], $result['suggestions']), array(0, 0, array()), 'Permission refresh removes counts and suggestion vocabulary');
serviceSame(count($serviceCalls), 4, 'Permission race performs only one refreshed search and bounded suggestion scan');
serviceCheck(strpos(json_encode($result), 'confidential-body') === false, 'Permission race returns no forbidden body');

// Further concurrent removals cannot cause recursive re-search forever.
serviceDatabase(array(serviceScan(array($emptyBody)), serviceDetails(array()), serviceScan(array($emptyBody)), serviceDetails(array())));
$result = u5SearchFind(u5SearchParseQuery('TitleNeedle'), 1, $public);
serviceSame(array($result['total'], $result['exactTotal'], $result['rows']), array(0, 0, array()), 'A second eligibility change drops the stale hit');
serviceSame(count($serviceCalls), 4, 'Eligibility retries are capped at one');

// Query text remains data: SQL selects candidates and literal matching is in PHP.
$literalQuery = "x' OR fixture_canary=1 --";
serviceDatabase(array(serviceScan(array())));
$result = u5SearchFind(u5SearchParseQuery($literalQuery), 1, $public);
serviceSame($result['rows'], array(), 'SQL-looking query does not alter eligibility');
serviceCheck(strpos($serviceCalls[0][1], 'fixture_canary') === false, 'Literal search text is never interpolated into SQL');
serviceDatabase(array(array('kind'=>'scan', 'fail'=>true)));
$result = u5SearchFind(u5SearchParseQuery('needle'), 1, $public);
serviceSame(array($result['error'], $result['rows']), array('unavailable', array()), 'Candidate SQL failure is an explicit unavailable result');
serviceDatabase(array(serviceScan(array($emptyBody)), array('kind'=>'detail', 'fail'=>true)));
$result = u5SearchFind(u5SearchParseQuery('TitleNeedle'), 1, $public);
serviceSame(array($result['error'], $result['rows']), array('unavailable', array()), 'Detail SQL failure cannot expose unverified metadata');

// Suggestions are finite alternatives, retain display spelling, and honor access.
serviceSame(serviceSuggestions('cat', array(serviceRow('words', 'Words', 'cat bat BAT cats cot cut bot')), $public), array('bat', 'cats', 'cot'), 'Suggestions are unique, distance-sorted, deterministic, and limited to three');
serviceSame(serviceSuggestions('Bren', array(serviceRow('swapped', 'Bern', 'Willkommen')), $public), array('Bern'), 'A short place name with swapped letters receives the original-spelling suggestion');
serviceSame(serviceSuggestions('muler', array(serviceRow('accented', serviceLegacy("M\u{fc}ller"), 'Name')), $public), array("M\u{fc}ller"), 'Spelling corrections use accent folding while retaining the original umlaut');
$decomposed = "e\u{301}cole";
serviceSame(serviceSuggestions('ecle', array(serviceRow('unicode', 'Words', serviceLegacy($decomposed . ' Ecole'))), $public), array($decomposed), 'Combining marks stay attached to the displayed suggestion');
serviceSame(serviceSuggestions('sunflwer', array(serviceRow('first', 'Words', 'sun'), serviceRow('second', 'Words', 'flower')), $public), array(), 'Vocabulary never concatenates adjacent resources into a new word');
serviceSame(serviceSuggestions('sunflwer', array(serviceRow('fields', 'sun', 'flower')), $public), array(), 'Vocabulary keeps title/body word boundaries');
serviceSame(serviceSuggestions('secretw', array(serviceRow('private', 'Protected title', new ServiceUnreadableBody(), array('logins'=>'person:secret'))), $findProtected), array(), 'Disabled protected previews exclude body vocabulary before reading it');
serviceSame(serviceSuggestions('secretw', array(serviceRow('private', 'Protected title', new ServiceUnreadableBody(), array('logins'=>'person:secret'))), $noProtected), array(), 'Preview permission cannot override disabled protected search');
serviceSame(serviceSuggestions('needle', array($raceUnreadable), $noProtected), array(), 'File-protected vocabulary follows the same exclusion');
$open['previewProtected'] = false;
serviceSame(serviceSuggestions('sunflwer', array(serviceRow('!open', 'Words', 'sunflower', array('logins'=>'person:secret'))), $open), array('sunflower'), 'Explicitly open intranet vocabulary can be suggested');
foreach (array('"quoted phrase"', 'two words', 'ab', str_repeat('x', 41)) as $invalidSuggestion) {
    serviceDatabase(array());
    serviceSame(u5SearchSuggest(u5SearchParseQuery($invalidSuggestion), 1, $public, u5SearchWhere(1, $public)), array(), 'Ineligible suggestion query never starts a scan');
    serviceSame($serviceCalls, array(), 'No SQL for unsupported suggestion shape');
}
serviceDatabase(array(array('kind'=>'scan', 'fail'=>true)));
serviceSame(u5SearchSuggest(u5SearchParseQuery('sunflwer'), 1, $public, u5SearchWhere(1, $public)), array(), 'Suggestion SQL failure returns no invented alternative');

$repeated = serviceRow('budget-first', 'Budget', str_repeat('x ', 100000) . 'sunflower');
serviceSame(serviceSuggestions('sunflwer', array($repeated, serviceRow('budget-later', 'Words', 'sunflower')), $public), array(), 'Repeated input cannot bypass the global suggestion token budget');
serviceSame($serviceResults[0]->fetches, 1, 'Token exhaustion stops before fetching later resources');
unset($repeated);
$prefix = 4 * 1024 * 1024 - strlen('Budget ') - strlen('sunflower');
$cut = serviceRow('byte-budget', 'Budget', str_repeat(' ', $prefix) . 'sunflowerSuffix');
serviceSame(serviceSuggestions('sunflwer', array($cut, serviceRow('byte-later', 'Words', 'sunflower')), $public), array(), 'Byte truncation cannot invent sunflower from a longer cut-off word');
serviceSame($serviceResults[0]->fetches, 1, 'Byte exhaustion stops before fetching later resources');
unset($cut);
serviceDatabase(array());

echo 'Search service regression checks: ' . $serviceChecks . " passed.\n";
