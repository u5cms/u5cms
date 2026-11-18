<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
/* No CMS/database changes. SQL doubles expose only a lazily generated row. */
require_once dirname(__DIR__) . '/search-legacy-suggestions.inc.php';
$checks = 0;
$factory = function () { if (false) yield array(); };
$calls = 0;
$released = 0;
$fail = false;
$active = false;
function checkLegacyMemory($condition, $message) {
    global $checks;
    $checks++;
    if (!$condition) throw new RuntimeException($message);
}
function mysql_unbuffered_query($sql) {
    global $factory, $calls, $fail, $active;
    checkLegacyMemory(!$active, 'Previous streaming result must be freed before another query');
    $calls++;
    if ($fail) return false;
    $active = true;
    return $factory();
}
function mysql_fetch_assoc($result) {
    if (!$result->valid()) return false;
    $row = $result->current();
    $result->next();
    return $row;
}
function mysql_free_result($result) { $GLOBALS['released']++; $GLOBALS['active'] = false; }
function legacyRows($texts) {
    $GLOBALS['factory'] = function () use ($texts) {
        foreach ($texts as $text) yield array('search_1' => $text, 'text' => $text);
    };
}
function closestLegacy($terms) { return u5LegacySearchClosest($terms, 'SELECT search_1 FROM resources WHERE hidden=0', 'search_1'); }

legacyRows(array('Bundeskanzlei Informatik Sicherheit '));
checkLegacyMemory(closestLegacy(array('Informtik')) === 'Informatik', 'Correct an ordinary typo');
checkLegacyMemory(closestLegacy(array('Informtik', 'Sicherhet')) === 'Informatik Sicherheit', 'All terms use a single vocabulary pass');
checkLegacyMemory(closestLegacy(array('INFORMATIK')) === 'Informatik', 'Preserve indexed spelling on an exact folded match');
legacyRows(array("(alpha),beta\ngamma\tdelta/epsilon[theta]!zeta?"));
foreach (array('alpha','beta','gamma','delta','epsilon','theta','zeta') as $word) {
    checkLegacyMemory(closestLegacy(array($word)) === $word, 'Keep legacy delimiter: ' . $word);
}
legacyRows(array('alpha', 'beta'));
checkLegacyMemory(closestLegacy(array('alphabeta')) !== 'alphabeta', 'Never join unrelated page boundaries into a fictitious word');
legacyRows(array('cat bat'));
checkLegacyMemory(closestLegacy(array('hat')) === 'bat', 'Keep the legacy last-equal-distance tie rule');
legacyRows(array('', " \n\r\t"));
checkLegacyMemory(closestLegacy(array('missing')) === null, 'Empty vocabulary terminates');
checkLegacyMemory(closestLegacy(array()) === null, 'Empty term list terminates');
checkLegacyMemory(closestLegacy(array('')) === null, 'Blank query terminates');
checkLegacyMemory(closestLegacy(array(str_repeat('x', 256))) === null, 'Do not compute unbounded input distances');
checkLegacyMemory(closestLegacy(array_fill(0, 17, 'alpha')) === null, 'Excessive correction terms do not allocate unbounded state');
legacyRows(array(str_repeat('z', 2 * 1024 * 1024) . ' Informatik'));
checkLegacyMemory(closestLegacy(array('Informtik')) === 'Informatik', 'Skip an oversized token without hiding the next valid word');
legacyRows(array("M\xfcller &#339;uvre"));
checkLegacyMemory(closestLegacy(array("M\xfcler")) === "M\xfcller", 'Keep Windows-1252 source bytes');
checkLegacyMemory(closestLegacy(array('&#339;uvr')) === '&#339;uvre', 'Keep legacy numeric-entity tokens intact');
$fail = true;
checkLegacyMemory(closestLegacy(array('anything')) === false, 'A failed query returns explicitly and terminates');
$fail = false;
$factory = function () { throw new RuntimeException('Simulated interrupted fetch'); if (false) yield array(); };
try {
    closestLegacy(array('anything'));
    checkLegacyMemory(false, 'Interrupted fetch must report its failure');
} catch (RuntimeException $error) {
    checkLegacyMemory($error->getMessage() === 'Simulated interrupted fetch' && !$active, 'Release the stream on an interrupted fetch');
}
$beforeCalls = $calls;
$factory = function () {
    for ($i = 0; $i < 50000; $i++) yield array('search_1' => str_repeat('Administration ', 100));
    yield array('search_1' => 'Informatik Sicherheit');
};
$started = microtime(true);
checkLegacyMemory(closestLegacy(array('Informtik', 'Sicherhet')) === 'Informatik Sicherheit', 'Find both corrections after 75 MB of repeated site text');
checkLegacyMemory($calls === $beforeCalls + 1, 'A multiword correction performs exactly one source query');
checkLegacyMemory(!$active && $released === $calls - 1, 'Release every successful result, including early exact matches');
checkLegacyMemory(memory_get_peak_usage(true) < 16 * 1024 * 1024, 'Streaming stress stays below 16 MiB PHP memory');
echo json_encode(array('status' => 'PASS', 'checks' => $checks, 'stress_source_bytes' => 75000000,
    'peak_bytes' => memory_get_peak_usage(true), 'stress_seconds' => round(microtime(true) - $started, 3)), JSON_UNESCAPED_SLASHES) . "\n";
