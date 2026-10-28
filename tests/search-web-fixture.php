<?php
/* Disposable MariaDB fixture for the real frontend search regression suite. */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$port = (int) (getenv('U5CMS_TEST_PORT') ?: 33361);
$connection = new mysqli('127.0.0.1', getenv('U5CMS_TEST_USER') ?: 'root', getenv('U5CMS_TEST_PASSWORD') ?: '', '', $port);
$action = $argv[1] ?? '';
if (in_array($action, array('cleanup', 'empty', 'restore', 'add-intranet'), true)) {
    $name = $argv[2] ?? '';
    if (!preg_match('/^u5search_web_[a-f0-9]+$/D', $name)) throw new RuntimeException('Invalid disposable database name.');
    if ($action === 'cleanup') $connection->query('DROP DATABASE IF EXISTS `' . $name . '`');
    else {
        $connection->select_db($name);
        if ($action === 'add-intranet') {
            $columns = array('name', 'typ', 'logins', 'lastmut');
            $values = array('!fixture', 'p', '?tester@example.invalid:unused-inline-password;', '150');
            for ($slot = 1; $slot <= 5; $slot++) {
                $columns[] = 'content_' . $slot;
                $values[] = '[h:]<p>IntranetNeedle intranetpayload</p>[:h]';
                $columns[] = 'title_' . $slot;
                $values[] = 'Intranet sample';
            }
            $quoted = array();
            foreach ($values as $value) $quoted[] = "'" . $connection->real_escape_string($value) . "'";
            $connection->query('INSERT INTO resources (`' . implode('`,`', $columns) . '`) VALUES (' . implode(',', $quoted) . ')');
        } else {
            $connection->query($action === 'empty' ? 'UPDATE resources SET hidden=1' : "UPDATE resources SET hidden=IF(name='hidden',1,0)");
        }
    }
    exit;
}
$root = realpath($argv[1] ?? '');
if ($root === false || !is_file($root . '/index.php')) throw new RuntimeException('Missing disposable site directory.');
$name = 'u5search_web_' . bin2hex(random_bytes(6));
$connection->query('CREATE DATABASE `' . $name . '` CHARACTER SET utf8mb4');
$connection->select_db($name);
try {
    $schema = file_get_contents(dirname(__DIR__) . '/u5standard.sql');
    // Client sandbox directives in a dump are not SQL server statements.
    $schema = preg_replace('/^\/\*!999999.*\R/', '', $schema);
    $connection->multi_query($schema);
    do {
        if ($result = $connection->store_result()) $result->free();
    } while ($connection->more_results() && $connection->next_result());
    $connection->query('SET NAMES latin1');
    $connection->query('DELETE FROM resources');
    $connection->query('DELETE FROM accounts');
    $connection->query("UPDATE languages SET lan1na='de',lan2na='fr',lan3na='it',lan4na='rm',lan5na='en'");
    $account = $connection->prepare('INSERT INTO accounts(email,pw,accadmin) VALUES (?,?,1)');
    $user = 'tester@example.invalid';
    $password = '{SHA}' . base64_encode(sha1('search-test-password', true));
    $account->bind_param('ss', $user, $password); $account->execute();
    $seed = function ($resource, $title, $html, $options = array()) use ($connection) {
        $columns = array('name', 'typ', 'deleted', 'hidden', 'logins', 'lastmut', 'ishomepage');
        $values = array($resource, $options['typ'] ?? 'p', (string) ($options['deleted'] ?? 0), (string) ($options['hidden'] ?? 0), $options['logins'] ?? '', (string) ($options['lastmut'] ?? 100), $resource === 'phrase' ? '1' : '0');
        for ($slot = 1; $slot <= 5; $slot++) {
            $body = is_array($html) ? $html[$slot - 1] : $html;
            $columns[] = 'content_' . $slot;
            // Encode fixture Unicode independently of the production index serializer.
            $values[] = '[h:]' . mb_encode_numericentity($body, array(0x80, 0x10ffff, 0, 0x1fffff), 'UTF-8') . '[:h]';
            $columns[] = 'title_' . $slot;
            $values[] = mb_encode_numericentity($title, array(0x80, 0x10ffff, 0, 0x1fffff), 'UTF-8');
        }
        $quoted = array();
        foreach ($values as $value) $quoted[] = "'" . $connection->real_escape_string($value) . "'";
        $connection->query('INSERT INTO resources (`' . implode('`,`', $columns) . '`) VALUES (' . implode(',', $quoted) . ')');
    };
    $seed('phrase', 'Parenthesis phrase', '<div style="height:1200px"></div><p id="phrase-text">Wort <em>(Wort</em> Wort) Wort</p>');
    $seed('flatphrase', 'Phrase without parentheses', '<p>Wort Wort Wort Wort</p>');
    $seed('ordered', 'Ordered words', '<p>alpha beta gamma</p>');
    $seed('reordered', 'Reordered words', '<p>gamma alpha beta</p>');
    $seed('andall', 'Complete terms', '<p>red green blue</p>');
    $seed('andpartial', 'Some terms', '<p>red green</p>');
    $seed('punctuation', 'Literal punctuation', '<p>C++ 100% foo_bar (round) a.b [square] x+y a&amp;b</p><p>Literal &amp;#128512; notation.</p>');
    $seed('punctuationdecoy', 'Other punctuation', '<p>C 1000 fooXbar ab</p>');
    $seed('unicode', 'Unicode text', "<p>Caf\u{00e9} Stra\u{00df}e \u{0152}uvre \u{20ac} \u{1f600} e\u{0301}cole</p>");
    $seed('inline', 'Inline formatting', '<p>Such<b>begriff</b></p>');
    $seed('titleonly', 'TitleNeedle', '<p>ordinary body</p>');
    $seed('emptybody', 'EmptyBodyNeedle', '');
    $seed('downloadfixture', 'Download document', '<p>RealDownloadFixture readable description</p>', array('typ'=>'d'));
    $seed('missingdownload', 'Missing document', '<p>AbsentDownloadFixture description</p>', array('typ'=>'d'));
    mkdir($root . '/r/downloadfixture');
    file_put_contents($root . '/r/downloadfixture/downloadfixture_de.txt', 'Actual download fixture payload.');
    mkdir($root . '/r/missingdownload');
    file_put_contents($root . '/r/missingdownload/.htaccess', 'Require valid-user');
    $seed('languages', 'Five language fields', array('<p>deutschmarker</p>', '<p>francaismarker</p>', '<p>italianomarker</p>', '<p>rumantschmarker</p>', '<p>englishmarker</p>'));
    $seed('protected', 'Protected sample', '<p>SecretNeedle privatepayload</p>', array('logins'=>'?tester@example.invalid:unused-inline-password;'));
    $seed('hidden', 'Hidden item', '<p>HiddenNeverDisplayed</p>', array('hidden'=>1));
    $seed('deleted', 'Deleted item', '<p>DeletedNeverDisplayed</p>', array('deleted'=>1));
    $seed('codefixture', 'Code item', '<p>CodeNeverDisplayed</p>', array('typ'=>'c'));
    $seed('suggestion', 'Suggestions', '<p>sunflower</p>');
    for ($i = 1; $i <= 7; $i++) $seed('pagination' . $i, 'Pagination ' . $i, '<p>PaginateMarker</p>', array('lastmut'=>100+$i));

    $config = file_get_contents($root . '/config.php.sample');
    $settings = array('host'=>'127.0.0.1', 'username'=>getenv('U5CMS_TEST_USER') ?: 'root', 'password'=>getenv('U5CMS_TEST_PASSWORD') ?: '',
        'db'=>$name, 'mymail'=>'fixture@example.invalid', 'usesessioninsteadofbasicauth'=>'no', 'autoresolvefastcgiproblems'=>'no', 'automaticallyskipintranetlogin'=>'no', 'executephp'=>'nowhere');
    foreach ($settings as $variable=>$value) {
        $config = preg_replace_callback('/\$' . preg_quote($variable, '/') . '\s*=\s*[\'\"][^\'\"]*[\'\"]\s*;/', function () use ($variable, $value) { return '$' . $variable . '=' . var_export($value, true) . ';'; }, $config, 1);
    }
    $config .= "\n\$blockautoincluderefresherforhtaccessandindex='yes';\n\$backup_send_mail=false;\n\$searchresultspagesize=5;\n";
    file_put_contents($root . '/config.php', $config);
    $template = '<!doctype html><html><head><meta charset="windows-1252"><title>Search fixture</title><link rel="icon" href="data:,"><style>body{font:16px/24px Arial;margin:0}header{position:sticky;top:0;background:white;z-index:2;padding:12px}main{padding:20px}.hitshilite{background:#ffeb7d}</style></head><body><header>{{{search}}}</header><main id="content">{{{content}}}</main></body></html>';
    file_put_contents($root . '/r/htmltemplate.css', $template);
    file_put_contents($root . '/r/htmltemplate.html', $template);
    file_put_contents($root . '/fileversions/lastsave.txt', '100');
    file_put_contents($root . '/fileversions/lastindex.txt', '0');
    @unlink($root . '/fileversions/search-index-v2.txt');
    @unlink($root . '/fileversions/search-index-pending.txt');
    echo json_encode(array('database'=>$name, 'username'=>$user, 'password'=>'search-test-password'));
} catch (Throwable $error) {
    $connection->query('DROP DATABASE IF EXISTS `' . $name . '`');
    throw $error;
}
