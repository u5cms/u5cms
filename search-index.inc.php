<?php
/* Search index text and completion state. PHP 7.4+, no database side effects. */
require_once __DIR__ . '/search-core.inc.php';

function u5SearchIndexVisibleText($legacyHtml) {
    $html = mb_convert_encoding((string) $legacyHtml, 'UTF-8', 'WINDOWS-1252');
    // Respect quoted attribute values: an attribute's > is not a tag boundary.
    $tail = '(?:"[^"]*"|\'[^\']*\'|[^\'">])*';
    $tokens = '~<!--[\s\S]*?(?:-->|$)|<\?[\s\S]*?(?:\?>|$)|</?([a-z][a-z0-9:-]*)\b' . $tail . '>~i';
    $blocks = array_flip(explode(' ', 'address article aside blockquote body br caption dd details dialog div dl dt fieldset figcaption figure footer form h1 h2 h3 h4 h5 h6 header hgroup hr html legend li main menu nav ol p pre section summary table tbody td tfoot th thead tr ul'));
    $visible = '';
    $offset = 0;
    while (preg_match($tokens, $html, $match, PREG_OFFSET_CAPTURE, $offset)) {
        $visible .= substr($html, $offset, $match[0][1] - $offset);
        $offset = $match[0][1] + strlen($match[0][0]);
        if (!isset($match[1]) || $match[1][1] < 0 || $match[1][0] === '') continue;
        $name = strtolower($match[1][0]);
        if (($name === 'script' || $name === 'style') && substr($match[0][0], 0, 2) !== '</') {
            // Raw-text elements end at their closing tag, even inside a JS comment.
            if (!preg_match('~</' . $name . '\b' . $tail . '>~i', $html, $closing, PREG_OFFSET_CAPTURE, $offset)) {
                $offset = strlen($html);
                break;
            }
            $offset = $closing[0][1] + strlen($closing[0][0]);
        } elseif (isset($blocks[$name])) {
            $visible .= ' ';
        }
    }
    $visible .= substr($html, $offset);
    // Strip malformed/incomplete residual markup without interpreting encoded <.
    $text = u5SearchFromLegacy(strip_tags($visible), 'UTF-8');
    return trim(preg_replace('/[\p{Z}\s]+/u', ' ', $text));
}

function u5SearchIndexEncodeLegacy($utf8, $escapeAmpersands = true) {
    if (!preg_match('//u', $utf8)) throw new RuntimeException('Invalid UTF-8 search index text.');
    if ($escapeAmpersands) $utf8 = str_replace('&', '&amp;', $utf8);
    // Keep ASCII in the fast path; do not allocate one array item per character.
    return preg_replace_callback('/[^\x00-\x7f]/u', function ($match) {
        $char = $match[0];
        $legacy = mb_convert_encoding($char, 'WINDOWS-1252', 'UTF-8');
        if (mb_convert_encoding($legacy, 'UTF-8', 'WINDOWS-1252') === $char) {
            return $legacy;
        }
        return '&#' . mb_ord($char, 'UTF-8') . ';';
    }, $utf8);
}

function u5SearchIndexFileTime($path) {
    $value = @file_get_contents($path);
    return $value === false ? 0 : max(0, (int) trim($value));
}

function u5SearchIndexFormatCurrent($directory = null) {
    if ($directory === null) $directory = __DIR__ . '/fileversions';
    $value = @file_get_contents($directory . '/search-index-v2.txt');
    return $value !== false && trim($value) === '2';
}

function u5SearchIndexNeedsUpdate($directory = null) {
    if ($directory === null) $directory = __DIR__ . '/fileversions';
    return is_file($directory . '/search-index-pending.txt')
        || !u5SearchIndexFormatCurrent($directory)
        || u5SearchIndexFileTime($directory . '/lastsave.txt') > u5SearchIndexFileTime($directory . '/lastindex.txt');
}

function u5SearchIndexIsRunning($directory = null) {
    if ($directory === null) $directory = __DIR__ . '/fileversions';
    $handle = @fopen($directory . '/indexerrunning.txt', 'r');
    if ($handle === false) return false;
    $available = @flock($handle, LOCK_EX | LOCK_NB);
    if ($available) flock($handle, LOCK_UN);
    fclose($handle);
    return !$available;
}

function u5SearchIndexAcquire($directory, $started = null) {
    $handle = @fopen($directory . '/indexerrunning.txt', 'c+');
    if ($handle === false) throw new RuntimeException('Cannot open the search index lock.');
    if (!flock($handle, LOCK_EX | LOCK_NB)) {
        fclose($handle);
        return false;
    }
    if ($started === null) $started = time();
    $started = (int) $started;
    if (!ftruncate($handle, 0) || !rewind($handle) || fwrite($handle, (string) $started) === false || !fflush($handle)) {
        flock($handle, LOCK_UN);
        fclose($handle);
        throw new RuntimeException('Cannot record the search index run.');
    }
    return array('handle' => $handle, 'directory' => $directory, 'started' => $started,
        // lastsave has second resolution. A save in this same second must remain pending.
        'watermark' => max(0, $started - 1));
}

function u5SearchIndexAtomicWrite($path, $value) {
    $temp = @tempnam(dirname($path), '.search-index-');
    if ($temp === false) throw new RuntimeException('Cannot prepare the search index checkpoint.');
    $written = @file_put_contents($temp, $value);
    if ($written !== strlen($value)) {
        @unlink($temp);
        throw new RuntimeException('Cannot write the search index checkpoint.');
    }
    @chmod($temp, is_file($path) ? (fileperms($path) & 0777) : 0644);
    if (!@rename($temp, $path)) {
        @unlink($temp);
        throw new RuntimeException('Cannot publish the search index checkpoint.');
    }
}

function u5SearchIndexPublish($run) {
    if (!is_array($run) || !isset($run['handle']) || !is_resource($run['handle'])) {
        throw new RuntimeException('No active full search index run.');
    }
    u5SearchIndexAtomicWrite($run['directory'] . '/lastindex.txt', (string) $run['watermark']);
    u5SearchIndexAtomicWrite($run['directory'] . '/search-index-v2.txt', '2');
    $pending = $run['directory'] . '/search-index-pending.txt';
    if (is_file($pending) && !@unlink($pending)) throw new RuntimeException('Cannot finish the search index checkpoint.');
}

function u5SearchIndexMarkPending($run) {
    if (!is_array($run) || !isset($run['handle']) || !is_resource($run['handle'])) {
        throw new RuntimeException('No active full search index run.');
    }
    u5SearchIndexAtomicWrite($run['directory'] . '/search-index-pending.txt', (string) $run['started']);
}

function u5SearchIndexRelease(&$run) {
    if (is_array($run) && isset($run['handle']) && is_resource($run['handle'])) {
        @ftruncate($run['handle'], 0);
        @rewind($run['handle']);
        @fwrite($run['handle'], '0');
        @fflush($run['handle']);
        @flock($run['handle'], LOCK_UN);
        @fclose($run['handle']);
    }
    $run = null;
}
