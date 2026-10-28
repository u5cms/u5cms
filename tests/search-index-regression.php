<?php
/* CLI regression tests. Set U5CMS_TEST_PHP when using a PHP runtime wrapper. */
require_once dirname(__DIR__) . '/search-index.inc.php';

if (($argv[1] ?? '') === '--probe-lock') {
    $run = u5SearchIndexAcquire($argv[2]);
    echo $run === false ? 'busy' : 'acquired';
    u5SearchIndexRelease($run);
    exit;
}

$checks = 0;
function indexCheck($condition, $message) {
    global $checks;
    $checks++;
    if (!$condition) throw new RuntimeException('CHECK FAILED: ' . $message);
}
function indexSame($actual, $expected, $message) {
    indexCheck($actual === $expected, $message . ' (' . var_export($actual, true) . ')');
}
function indexProcess($file, $arguments = array(), $environment = array(), $directory = null) {
    $command = array_merge(array(getenv('U5CMS_TEST_PHP') ?: PHP_BINARY, $file), $arguments);
    $process = proc_open($command, array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes, $directory, array_merge(getenv(), $environment));
    if (!is_resource($process)) throw new RuntimeException('Cannot start the index test process.');
    fclose($pipes[0]);
    $out = stream_get_contents($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    return array(proc_close($process), $out, $error);
}
function indexRemoveTree($directory) {
    foreach (scandir($directory) as $name) {
        if ($name === '.' || $name === '..') continue;
        $path = $directory . '/' . $name;
        if (is_dir($path)) indexRemoveTree($path); else unlink($path);
    }
    rmdir($directory);
}

$emoji = u5SearchCodepoint(0x1f600);
$euro = u5SearchCodepoint(0x20ac);
$oe = u5SearchCodepoint(0x152);
$cases = array(
    array('Such<b>begriff</b>', 'Suchbegriff'),
    array('<span title="a > b">Such</span><em>begriff</em>', 'Suchbegriff'),
    array('<p>Alpha</p><p>Beta</p>', 'Alpha Beta'),
    array('Alpha<br/>Beta<hr>Gamma', 'Alpha Beta Gamma'),
    array('<table><tr><td>A</td><td>B</td></tr><tr><td>C</td></tr></table>', 'A B C'),
    array('A<!-- hidden </p> -->B<style>.bad { content: ">"; }</style>C', 'ABC'),
    array('A<script data-test=">">bad()</script>B', 'AB'),
    array('<span title="<script>">Visible</span>', 'Visible'),
    array('<span data-code="<?php">Visible</span>', 'Visible'),
    array('A<script><!-- hidden </script>B', 'AB'),
    array('A<!-- unfinished comment', 'A'),
    array('Before<script>unfinished', 'Before'),
    array('A<?php echo "never"; ?>B', 'AB'),
    array('&lt;b&gt;literal&lt;/b&gt;', '<b>literal</b>'),
    array('&amp;#128512;', '&#128512;'),
    array('&#128512;', $emoji),
    array('A&nbsp;B&#8239;C', 'A B C'),
    array("Preis \x80; \x8Cuvre", 'Preis ' . $euro . '; ' . $oe . 'uvre'),
    array('<b>one</b> <i>two</i>', 'one two')
);
foreach ($cases as $case) indexSame(u5SearchIndexVisibleText($case[0]), $case[1], 'visible text: ' . $case[0]);

foreach (array('A & B', '&#128512;', '&euro;', '<literal>', $emoji, $euro, $oe, "\u{00c3}\u{00a9}", "\u{00e9}", 'A (B C) D') as $text) {
    $stored = u5SearchIndexEncodeLegacy($text);
    indexSame(u5SearchFromLegacy($stored), $text, 'index storage roundtrip');
    indexSame(u5SearchIndexEncodeLegacy(u5SearchFromLegacy($stored)), $stored, 'idempotent storage');
}
indexSame(u5SearchIndexEncodeLegacy('&#128512;'), '&amp;#128512;', 'literal entity is distinguishable');
indexSame(u5SearchIndexEncodeLegacy($emoji), '&#128512;', 'actual emoji remains a numeric reference');
indexSame(u5SearchIndexEncodeLegacy('A & B', false), 'A & B', 'auto-title legacy representation is retained');

$root = sys_get_temp_dir() . '/u5-search-index-' . bin2hex(random_bytes(6));
mkdir($root, 0700, true);
try {
    $state = $root . '/state'; mkdir($state);
    file_put_contents($state . '/lastsave.txt', '100');
    file_put_contents($state . '/lastindex.txt', '120');
    indexCheck(u5SearchIndexNeedsUpdate($state), 'missing format marker requests an index even without a new save');
    $run = u5SearchIndexAcquire($state, 200);
    indexCheck(is_array($run), 'first full run acquires lock');
    indexCheck(u5SearchIndexIsRunning($state), 'running status uses the live lock');
    indexSame(file_get_contents($state . '/lastindex.txt'), '120', 'starting does not publish a checkpoint');
    $probe = indexProcess(__FILE__, array('--probe-lock', $state));
    indexSame($probe[0], 0, 'parallel lock probe exits successfully');
    indexSame($probe[1], 'busy', 'parallel process cannot run a second full index');
    u5SearchIndexRelease($run);
    indexCheck(!u5SearchIndexIsRunning($state), 'aborted run releases the lock');
    indexSame(file_get_contents($state . '/lastindex.txt'), '120', 'aborted run retains the old checkpoint');
    indexCheck(!u5SearchIndexFormatCurrent($state), 'aborted run does not bless the new format');
    $run = u5SearchIndexAcquire($state, 201);
    u5SearchIndexPublish($run);
    u5SearchIndexRelease($run);
    indexSame(file_get_contents($state . '/lastindex.txt'), '200', 'successful checkpoint excludes the start second');
    indexCheck(!u5SearchIndexNeedsUpdate($state), 'successful complete run is current');
    file_put_contents($state . '/lastsave.txt', '201');
    indexCheck(u5SearchIndexNeedsUpdate($state), 'save in the start second remains pending');
    file_put_contents($state . '/indexerrunning.txt', '9999999999');
    indexCheck(!u5SearchIndexIsRunning($state), 'orphaned timestamps do not prevent retry');

    $fixture = $root . '/fixture'; mkdir($fixture); mkdir($fixture . '/fileversions');
    foreach (array('indexer.php', 'search-index.inc.php', 'search-core.inc.php', 'htaccessandindexer.inc.php', 'indexupdate.inc.php') as $file) {
        copy(dirname(__DIR__) . '/' . $file, $fixture . '/' . $file);
    }
    file_put_contents($fixture . '/getinserts.inc.php', '<?php $result_b=true; $num_b=0;');
    file_put_contents($fixture . '/render.inc.php', <<<'PHP'
<?php
function render($text) {
    $mode = getenv('U5_SEARCH_INDEX_TEST_MODE');
    if ($mode === 'render-error') throw new RuntimeException('Deliberate fixture render error.');
    if ($mode === 'render-exit') exit('Deliberate fixture rendering exit.');
    if ($mode === 'concurrent-save') file_put_contents(__DIR__ . '/fileversions/lastsave.txt', (string) time());
    return $text;
}
PHP
    );
    file_put_contents($fixture . '/connect.inc.php', <<<'PHP'
<?php
$lan1na='de'; $lan2na='fr'; $lan3na='it'; $lan4na='rm'; $lan5na='en';
$maxwordsindocumenttitle=7;
class SearchIndexFixtureResult { public $rows; public $offset=0; function __construct($rows) { $this->rows=$rows; } }
function mysql_query($sql) {
    $mode=getenv('U5_SEARCH_INDEX_TEST_MODE');
    if (strpos($sql, 'SELECT * FROM resources') === 0) {
        if ($mode === 'select-error') return false;
        $rows=array();
        foreach (array('sample','retained') as $name) {
            $row=array('name'=>$name,'typ'=>'p');
            for ($i=1; $i<=5; $i++) {
                $row['content_'.$i]=$name==='sample' ? 'Such<b>begriff</b> &amp; &#128512;' : 'DoNotAutoIndexIfThisStringIsFoundInFinalPageContentLiterally REPLACEMENT';
                $row['title_'.$i]=$name==='sample' ? '' : 'Retained title';
                $row['search_'.$i]='MANUAL &#128512; & raw';
            }
            $rows[]=$row;
        }
        return new SearchIndexFixtureResult(isset($_GET['n']) ? array($rows[0]) : $rows);
    }
    if (strpos($sql, 'UPDATE resources') === 0) {
        if ($mode === 'update-error') return false;
        file_put_contents(__DIR__.'/updates.log',$sql."\n--QUERY--\n",FILE_APPEND);
        return true;
    }
    throw new RuntimeException('Unexpected fixture SQL.');
}
function mysql_num_rows($result) { return count($result->rows); }
function mysql_fetch_array($result) { return $result->rows[$result->offset++]; }
function mysql_real_escape_string($value) { return addslashes($value); }
PHP
    );
    file_put_contents($fixture . '/run.php', <<<'PHP'
<?php
chdir(__DIR__);
$_GET=getenv('U5_SEARCH_INDEX_TEST_ITEM') ? array('n'=>'sample') : array();
$_COOKIE=array();
if (getenv('U5_SEARCH_INDEX_TEST_MODE') === 'refresh') {
    $blockautoincluderefresherforhtaccessandindex='no'; $waitsecondstillindexingsincelastsave=0;
    require 'htaccessandindexer.inc.php';
} elseif (getenv('U5_SEARCH_INDEX_TEST_MODE') === 'status') {
    require 'indexupdate.inc.php';
} else require 'indexer.php';
PHP
    );
    $reset = function () use ($fixture) {
        file_put_contents($fixture . '/fileversions/lastsave.txt', '100');
        file_put_contents($fixture . '/fileversions/lastindex.txt', '120');
        file_put_contents($fixture . '/fileversions/lasthtaccess.txt', '120');
        @unlink($fixture . '/fileversions/search-index-v2.txt');
        @unlink($fixture . '/fileversions/search-index-pending.txt');
        @unlink($fixture . '/updates.log');
    };
    foreach (array('select-error', 'update-error', 'render-error', 'render-exit') as $mode) {
        $reset();
        $result = indexProcess($fixture . '/run.php', array(), array('U5_SEARCH_INDEX_TEST_MODE'=>$mode));
        indexSame($result[0], 0, $mode . ' is handled without a PHP crash');
        indexSame(file_get_contents($fixture . '/fileversions/lastindex.txt'), '120', $mode . ' retains checkpoint');
        indexCheck(!u5SearchIndexFormatCurrent($fixture . '/fileversions'), $mode . ' cannot publish full format');
        indexCheck(!u5SearchIndexIsRunning($fixture . '/fileversions'), $mode . ' releases lock');
        indexSame(file_get_contents($fixture . '/fileversions/indexerrunning.txt'), '0', $mode . ' clears running marker');
        indexCheck(u5SearchIndexNeedsUpdate($fixture . '/fileversions'), $mode . ' remains pending for automatic retry');
    }
    $reset();
    file_put_contents($fixture . '/fileversions/search-index-v2.txt', '2');
    indexCheck(!u5SearchIndexNeedsUpdate($fixture . '/fileversions'), 'manual full-run fixture starts current');
    $result = indexProcess($fixture . '/run.php', array(), array('U5_SEARCH_INDEX_TEST_MODE'=>'update-error'));
    indexCheck(u5SearchIndexNeedsUpdate($fixture . '/fileversions'), 'failed manual full run becomes pending even without a content edit');
    indexSame(file_get_contents($fixture . '/fileversions/lastindex.txt'), '120', 'failed manual full run retains known checkpoint');
    $reset();
    $result = indexProcess($fixture . '/run.php', array(), array('U5_SEARCH_INDEX_TEST_MODE'=>'refresh'));
    indexCheck(strpos($result[1], 'src="indexer.php"') !== false, 'format upgrade schedules full indexing without content edits');
    $result = indexProcess($fixture . '/run.php', array(), array('U5_SEARCH_INDEX_TEST_MODE'=>'status'));
    indexCheck(strpos($result[1], 'not yet fully up to date') !== false, 'status reports missing index upgrade');
    $result = indexProcess($fixture . '/run.php', array(), array('U5_SEARCH_INDEX_TEST_MODE'=>'normal','U5_SEARCH_INDEX_TEST_ITEM'=>'1'));
    indexSame($result[0], 0, 'single resource indexing succeeds');
    indexSame(file_get_contents($fixture . '/fileversions/lastindex.txt'), '120', 'single resource does not publish full checkpoint');
    indexCheck(!u5SearchIndexFormatCurrent($fixture . '/fileversions'), 'single resource cannot bless full format');
    $reset();
    $result = indexProcess($fixture . '/run.php', array(), array('U5_SEARCH_INDEX_TEST_MODE'=>'normal','U5_SEARCH_INDEX_TEST_ITEM'=>''));
    indexSame($result[0], 0, 'complete indexer executes successfully');
    indexCheck(strpos($result[1], 'doneaudio') !== false, 'completion UI appears only after success');
    indexCheck(u5SearchIndexFormatCurrent($fixture . '/fileversions'), 'successful full run publishes format marker');
    $sql = file_get_contents($fixture . '/updates.log');
    indexCheck(strpos($sql, "search_1='Suchbegriff &amp; &#128512;'") !== false, 'real indexer stores visible inline word and escaped ampersand');
    indexCheck(strpos($sql, "search_1='MANUAL &#128512; &amp; raw'") !== false, 'format upgrade preserves opted-out manual index text');
    indexCheck(strpos($sql, 'REPLACEMENT') === false, 'opted-out content is not regenerated');
    @unlink($fixture . '/updates.log');
    $result = indexProcess($fixture . '/run.php', array(), array('U5_SEARCH_INDEX_TEST_MODE'=>'normal','U5_SEARCH_INDEX_TEST_ITEM'=>''));
    $queries = explode('--QUERY--', file_get_contents($fixture . '/updates.log'));
    foreach ($queries as $query) if (strpos($query, "name='retained'") !== false) {
        indexCheck(strpos($query, 'search_1=') === false, 'manual index reserialization is limited to format upgrade');
    }
    $reset();
    $result = indexProcess($fixture . '/run.php', array(), array('U5_SEARCH_INDEX_TEST_MODE'=>'concurrent-save','U5_SEARCH_INDEX_TEST_ITEM'=>''));
    indexCheck(u5SearchIndexFormatCurrent($fixture . '/fileversions'), 'completed concurrent-save run publishes format');
    indexCheck(u5SearchIndexNeedsUpdate($fixture . '/fileversions'), 'save during actual indexing is not swallowed');
} finally {
    indexRemoveTree($root);
}
echo "Search index checks: $checks passed.\n";
