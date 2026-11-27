<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
/* Real legacy suchen()/leven(), real SQL, isolated disposable database.
 * Run each entry point in a separate PHP process. No installed CMS is changed.
 */
require_once dirname(__DIR__) . '/mysql.php';
require_once dirname(__DIR__) . '/search-legacy-suggestions.inc.php';
$entry = $argv[1] ?? 'u5sys._search.php';
if (!in_array($entry, array('u5sys._search.php', 'u5sys._searchsi.php'), true)) throw new RuntimeException('Unknown entry point');
function legacyLoadFunction($path, $name) {
    $tokens = token_get_all(file_get_contents($path));
    for ($i = 0; $i < count($tokens); $i++) {
        if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_FUNCTION) continue;
        $j = $i + 1;
        while (is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
        if (!is_array($tokens[$j]) || $tokens[$j][1] !== $name) continue;
        $code = '';
        $depth = 0;
        $opened = false;
        for (; $i < count($tokens); $i++) {
            $token = $tokens[$i];
            $text = is_array($token) ? $token[1] : $token;
            $code .= $text;
            if ($token === '{') { $depth++; $opened = true; }
            elseif ($token === '}' && --$depth === 0 && $opened) { eval($code); return; }
        }
    }
    throw new RuntimeException('Function not found: ' . $name);
}
foreach (array('u5SearchUnicodeEntities', 'suchen', 'leven') as $function) legacyLoadFunction(dirname(__DIR__) . '/' . $entry, $function);
legacyLoadFunction(dirname(__DIR__) . '/connect.inc.php', 'def');
function tnuoc($value) { return count($value); }
function mkltgt($value) { return htmlspecialchars($value, ENT_QUOTES, 'WINDOWS-1252'); }
function ausgabe($hits, $result, $count, $terms, $alloc) {
    $GLOBALS['foundNames'] = array();
    while ($row = mysql_fetch_assoc($result)) $GLOBALS['foundNames'][] = $row['name'];
    $GLOBALS['foundTerms'] = $terms;
    mysql_free_result($result);
    echo $hits;
}
function legacyAssert($condition, $message) {
    $GLOBALS['checks']++;
    if (!$condition) throw new RuntimeException($message);
}
$checks = 0;
$database = 'u5_legacy_search_' . bin2hex(random_bytes(6));
$link = mysql_connect('127.0.0.1:' . (getenv('U5CMS_TEST_PORT') ?: '33361'), getenv('U5CMS_TEST_USER') ?: 'root', getenv('U5CMS_TEST_PASSWORD') ?: '');
if (!$link) throw new RuntimeException('Disposable database server unavailable');
if (!mysql_query('CREATE DATABASE `' . $database . '` CHARACTER SET latin1')) throw new RuntimeException(mysql_error());
register_shutdown_function(function () use ($database) { mysql_query('DROP DATABASE IF EXISTS `' . $database . '`'); });
mysql_select_db($database);
mysql_query('SET NAMES latin1');
$columns = array('name VARCHAR(100) PRIMARY KEY', 'typ CHAR(1)', 'deleted INT', 'hidden INT', 'logins TEXT', 'lastmut INT');
for ($i = 1; $i <= 5; $i++) { $columns[] = 'title_' . $i . ' TEXT'; $columns[] = 'search_' . $i . ' LONGTEXT'; }
mysql_query('CREATE TABLE resources (' . implode(',', $columns) . ')');
function legacyInsert($name, $texts, $extra = array()) {
    $row = array_merge(array('name'=>$name, 'typ'=>'p', 'deleted'=>0, 'hidden'=>0, 'logins'=>'', 'lastmut'=>100), $extra);
    for ($i = 1; $i <= 5; $i++) { $row['title_' . $i] = $texts[$i - 1] ?? ''; $row['search_' . $i] = $texts[$i - 1] ?? ''; }
    $values = array();
    foreach ($row as $value) $values[] = "'" . mysql_real_escape_string((string) $value) . "'";
    if (!mysql_query('INSERT INTO resources (`' . implode('`,`', array_keys($row)) . '`) VALUES (' . implode(',', $values) . ')')) throw new RuntimeException(mysql_error());
}
function legacyFind($query) {
    $_GET['q'] = str_replace(' ', ',', $query);
    $GLOBALS['leven'] = '';
    $GLOBALS['foundNames'] = array();
    $GLOBALS['foundTerms'] = '';
    ob_start();
    suchen($query);
    return ob_get_clean();
}
$lan1na='de';$lan2na='fr';$lan3na='it';$lan4na='rm';$lan5na='en';
foreach (array('nohit','andhit','andhits','orhit','orhits') as $label) for ($i=1;$i<=5;$i++) $GLOBALS[$label . '_' . $i] = 'No/partial results';
define('PIDVESAexcludes', " AND typ!='i'");
$nointranet = "AND name NOT LIKE '!%'";
$doesfindpasswordprotectedcontent = 'no';
legacyInsert('ordinary', array('Informatik Sicherheit', 'Informatique securite', 'Informatica sicurezza', 'Informatica segirezza', 'Information security'));
legacyInsert('!intranet', array('Interngeheim'));
legacyInsert('protected', array('Passwortgeheim'), array('logins'=>'person:secret'));
legacyInsert('hidden', array('Unsichtbar'), array('hidden'=>1));
legacyInsert('deleted', array('Geloescht'), array('deleted'=>1));
legacyInsert('code', array('Codegeheim'), array('typ'=>'c'));
legacyInsert('excluded', array('Bildgeheim'), array('typ'=>'i'));
foreach (array('de'=>'Informtik', 'fr'=>'Informatiqe', 'it'=>'Informtica', 'rm'=>'Informtica', 'en'=>'Informaton') as $language=>$query) {
    $_GET['l']=$language;
    legacyFind($query);
    legacyAssert($foundNames === array('ordinary'), 'Correct typo in language ' . $language);
}
$_GET['l']='de';
legacyFind('Informatik');
legacyAssert($foundNames === array('ordinary') && $foundTerms === 'Informatik', 'Exact search keeps original terms');
legacyFind('Informtik Sicherhet');
legacyAssert($foundNames === array('ordinary') && $foundTerms === 'Informatik Sicherheit', 'Correct both terms without recursive scans');

// Exercise the old JavaScript escape() links through the shipped decoder, SQL
// construction and the actual latin1 storage profile.  Keep the historical
// Windows-1252 byte for the oe ligature because the endpoint maps its numeric entity back to
// that byte before querying the legacy index.
legacyInsert('unicode-links', array("caf&#233; \x9cuvre &#20013;&#25991; &#128512;"));
foreach (array(
    '%u00E9' => '&#233;',
    '%u0153' => '&#339;',
    '%u4E2D%u6587' => '&#20013;&#25991;',
    '%uD83D%uDE00' => '&#128512;',
) as $query => $terms) {
    set_error_handler(function ($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });
    $decoded = u5SearchUnicodeEntities($query);
    restore_error_handler();
    legacyAssert($decoded === $terms, 'Legacy Unicode link decodes without PHP diagnostics for ' . $query);
    legacyFind($query);
    legacyAssert($foundNames === array('unicode-links'), 'Legacy Unicode link reaches the stored page for ' . $query);
    legacyAssert($foundTerms === $terms, 'Legacy Unicode link keeps decoded search terms for ' . $query);
}
foreach (array('Interngeheim','Passwortgeheim','Unsichtbar','Geloescht','Codegeheim','Bildgeheim') as $word) {
    legacyFind($word);
    legacyAssert(!in_array('!intranet',$foundNames,true) && !in_array('protected',$foundNames,true) && !in_array('hidden',$foundNames,true) && !in_array('deleted',$foundNames,true) && !in_array('code',$foundNames,true) && !in_array('excluded',$foundNames,true), 'Keep all configured exclusions for ' . $word);
}
$nointranet='';$doesfindpasswordprotectedcontent='yes';
legacyFind('Interngehem');
legacyAssert($foundNames === array('!intranet'), 'Authenticated/intranet scope still searches its own vocabulary');
$nointranet="AND name NOT LIKE '!%'";$doesfindpasswordprotectedcontent='no';
mysql_query('DELETE FROM resources');
legacyInsert('one',array('Informatik'));legacyInsert('two',array('Sicherheit'));
legacyFind('Informtik Sicherhet');
sort($foundNames);
legacyAssert($foundNames === array('one','two'), 'Keep corrected-query OR fallback when words occur on separate pages');
mysql_query('DELETE FROM resources');
$output=legacyFind('anything');
legacyAssert($foundNames === array() && strpos($output,'No/partial results')!==false, 'Empty database returns a visible no-result message without recursion');
legacyInsert('punctuation',array('under_score'));
legacyFind('underscor');
legacyAssert($foundNames === array('punctuation'), 'Wildcard transformation cannot trigger repeated correction scans');
echo json_encode(array('status'=>'PASS','entry'=>$entry,'checks'=>$checks,'runtime'=>PHP_VERSION,'database'=>mysql_get_server_info())) . "\n";
