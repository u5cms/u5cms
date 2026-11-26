<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

// Execute the real sanitiser, redirect guards, SAML URL expressions and final
// return-target construction. No authentication, SQL, cookies or mail is used.
$root = getenv('U5CMS_SOURCE_ROOT') ?: dirname(__DIR__);
$_GET = $_POST = $_COOKIE = array();
require $root . '/san.inc.php';

function returnUrlAssert($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}
function redirectGuard($source) {
    $start = strpos($source, "if (isset(\$_GET['u'])) {");
    returnUrlAssert($start !== false, 'Missing redirect guard.');
    $block = ''; $depth = 0; $started = false;
    foreach (token_get_all('<?php ' . substr($source, $start)) as $token) {
        if (is_array($token) && $token[0] === T_OPEN_TAG) continue;
        $block .= is_array($token) ? $token[1] : $token;
        if ($token === '{') { $depth++; $started = true; }
        elseif ($token === '}' && --$depth === 0 && $started) break;
    }
    returnUrlAssert($started && $depth === 0, 'Incomplete redirect guard.');
    // A rejected guard throws in this isolated process, rather than ending all
    // cases. Its predicates and error messages are executed unchanged.
    return preg_replace('/\bdie\(/', 'throw new RuntimeException(', $block);
}
function runGuard($guard) { eval($guard); }
function expressionUrl($expression) {
    $html = eval('return ' . $expression . ';');
    returnUrlAssert(preg_match('/location\.href="([^"]+)"/', $html, $m) === 1, 'Missing SAML transition.');
    return $m[1];
}
function requestQuery($url) {
    parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $result);
    return $result;
}

$saml = file_get_contents($root . '/saml.inc.php');
$samlLogin = file_get_contents($root . '/saml/login.php');
returnUrlAssert(preg_match('/die\((.*?)\);\n/s', $saml, $toSaml) === 1, 'Missing SAML entry expression.');
returnUrlAssert(preg_match('/else echo (.*?);\n/s', $samlLogin, $fromSaml) === 1, 'Missing SAML return expression.');
$samlGuard = redirectGuard($samlLogin);
$requests = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
$roundTrips = 0; $denials = 0;
foreach (array('loginsave.php', 'u5admin/loginsave.php') as $path) {
    $source = file_get_contents($root . '/' . $path);
    $guard = redirectGuard($source);
    $start = strpos($source, '$u=explode(\'#\',');
    $end = strpos($source, 'header("Location: ".$u[0]);', $start);
    returnUrlAssert($start !== false && $end !== false, 'Missing final redirect construction in ' . $path);
    $tail = substr($source, $start, $end - $start);
    foreach ($requests as $request) {
        $origin = parse_url($request['login']);
        $_SERVER['HTTP_HOST'] = $origin['host'];
        $_SERVER['SERVER_PORT'] = $origin['port'] ?? ($origin['scheme'] === 'https' ? 443 : 80);
        // First loginsave request: connect.inc.php applies san.inc.php.
        $_GET = array_map('u5map', requestQuery($request['login']));
        runGuard($guard);
        $_GET = requestQuery(expressionUrl($toSaml[1]));
        runGuard($samlGuard);
        $_GET = requestQuery(expressionUrl($fromSaml[1]));
        // Second loginsave request after authentication applies it again.
        $_GET = array_map('u5map', $_GET);
        runGuard($guard);
        eval($tail);
        $parts = explode('#', $u[0], 2);
        returnUrlAssert(preg_match('/[?&][0-9]+$/', $parts[0], $cachebuster) === 1, 'Missing login cachebuster.');
        $separator = parse_url($request['target'], PHP_URL_QUERY) !== null ? '&' : '?';
        returnUrlAssert($cachebuster[0][0] === $separator, $path . ' merged the cachebuster into a query value.');
        $actual = substr($parts[0], 0, -strlen($cachebuster[0])) . (isset($parts[1]) ? '#' . $parts[1] : '');
        returnUrlAssert($actual === $request['target'], $path . ' changed return URL: ' . $actual);
        $roundTrips++;
    }
    $_SERVER['HTTP_HOST'] = 'cms.example';
    $_SERVER['SERVER_PORT'] = 443;
    foreach (array('', '//other.example/path', 'https://other.example/path', 'https://cms.example:444/path',
        'https://user:password@cms.example/path', 'javascript:alert(1)', 'data:text/html,test',
        '/path/../file', '/path/./file', '/path\\file', '/path%0d%0aLocation:evil',
        '/path%250d%250aLocation:evil', array('invalid')) as $bad) {
        $_GET = array_map('u5map', array('u' => $bad));
        try { runGuard($guard); }
        catch (RuntimeException $error) { $denials++; continue; }
        throw new RuntimeException($path . ' accepted an invalid return target.');
    }
}
echo json_encode(array('round_trips' => $roundTrips, 'denials' => $denials), JSON_THROW_ON_ERROR);
