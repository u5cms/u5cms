<?php
if (!function_exists('u5qCodepointEscape')) {
function u5qCodepointEscape($cp) {
    $cp = (int) $cp;

    if ($cp < 0 || $cp > 0x10FFFF || ($cp >= 0xD800 && $cp <= 0xDFFF)) {
        return null;
    }

    if ($cp <= 0xFF) {
        return chr($cp);
    }

    if ($cp <= 0xFFFF) {
        return sprintf('%%u%04X', $cp);
    }

    $cp -= 0x10000;
    $high = 0xD800 + ($cp >> 10);
    $low = 0xDC00 + ($cp & 0x3FF);

    return sprintf('%%u%04X%%u%04X', $high, $low);
}
}

if (!function_exists('u5qNumericEntitiesToUtf8')) {
function u5qNumericEntitiesToUtf8($value) {
    return preg_replace_callback(
        '/&(?:(?:amp;)+)?#(x[0-9a-fA-F]+|[0-9]+);/i',
        function($match) {
            $number = $match[1];
            $cp = (strtolower($number[0]) === 'x')
                ? hexdec(substr($number, 1))
                : (int) $number;

            if ($cp < 0 || $cp > 0x10FFFF || ($cp >= 0xD800 && $cp <= 0xDFFF)) {
                return $match[0];
            }

            return mb_convert_encoding(pack('N', $cp), 'UTF-8', 'UCS-4BE');
        },
        $value
    );
}
}

if (!function_exists('u5qCanonicalize')) {
function u5qCanonicalize($value) {
    if (!is_string($value) || $value === '') {
        return is_string($value) ? $value : '';
    }

    $utf8 = mb_check_encoding($value, 'UTF-8')
        ? $value
        : mb_convert_encoding($value, 'UTF-8', 'WINDOWS-1252');

    // q may arrive as a URL-encoded numeric HTML character reference,
    // for example %26%2313056%3B -> &#13056;. Normalize only numeric
    // references; named HTML entities and ordinary ampersands are untouched.
    $utf8 = u5qNumericEntitiesToUtf8($utf8);

    $chars = preg_split('//u', $utf8, -1, PREG_SPLIT_NO_EMPTY);
    if ($chars === false) {
        return $value;
    }

    $out = '';
    foreach ($chars as $ch) {
        $cp = unpack('N', mb_convert_encoding($ch, 'UCS-4BE', 'UTF-8'))[1];
        $escaped = u5qCodepointEscape($cp);
        $out .= ($escaped === null) ? $ch : $escaped;
    }

    return $out;
}
}

$_GET['q'] = isset($_GET['q']) ? u5qCanonicalize($_GET['q']) : '';

function u5stz($that) {
    return htmlspecialchars($that, ENT_COMPAT | ENT_HTML401, 'WINDOWS-1252');
}
function u5map($v) {
    return is_string($v) ? u5stz($v) : $v;
}
$filterChars = array('\r', '\n', ';', '<', '>', '(', ')');
if (isset($_GET['q']) && is_string($_GET['q'])) {
    $_GET['q'] = str_replace($filterChars, '', $_GET['q']);
}
$_GET = array_map("u5map", $_GET);
$_COOKIE = array_map("u5map", $_COOKIE);

$skipPost = false;
foreach (get_included_files() as $f) {
    if (in_array(basename($f), ['formsave.php','delformsave.php','editformsave.php'])) {
        $skipPost = true;
        break;
    }
}
$keys = ['c','n','l','typ','name','id'];
foreach ($keys as $k) {
    $pattern = ($k === 'n') ? '/[^a-z0-9_! ]/i' : '/[^a-z0-9_!]/i';

    if (isset($_GET[$k]) && is_string($_GET[$k])) {
        $_GET[$k] = preg_replace($pattern, '', $_GET[$k]);
    }
    if (!$skipPost && isset($_POST[$k]) && is_string($_POST[$k])) {
        $_POST[$k] = preg_replace($pattern, '', $_POST[$k]);
    }
}
