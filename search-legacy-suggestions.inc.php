<?php
/* Legacy search (Windows-1252): one unbuffered pass, no site-wide word array.
 * $sql and $field are supplied by the existing, trusted search entry point.
 * Returns null when no correction is possible, false on a database failure.
 */
function u5LegacySearchClosest($terms, $sql, $field) {
    if (!is_array($terms) || !$terms || count($terms) > 16) return null;
    $inputs = array();
    $closest = array();
    $distances = array();
    foreach ($terms as $term) {
        if (!is_string($term) || trim($term) === '' || strlen($term) > 255) return null;
        $inputs[] = strtolower($term);
        $closest[] = null;
        $distances[] = PHP_INT_MAX;
    }
    $result = mysql_unbuffered_query($sql);
    if ($result === false) return false;
    $remaining = count($inputs);
    $delimiters = " \n\r\t()<>,.:!?'\xB4[]=/";
    try {
        while ($remaining > 0 && ($row = mysql_fetch_assoc($result))) {
            $text = (string) ($row[$field] ?? '');
            $length = strlen($text);
            for ($offset = 0; $offset < $length && $remaining > 0;) {
                $offset += strspn($text, $delimiters, $offset);
                if ($offset >= $length) break;
                $size = strcspn($text, $delimiters, $offset);
                $start = $offset;
                $offset += $size;
                // Never materialize an oversized token or feed it to old PHP's
                // byte-based levenshtein(), which accepts at most 255 bytes.
                if ($size > 255) continue;
                $word = substr($text, $start, $size);
                $folded = strtolower($word);
                foreach ($inputs as $i => $input) {
                    if ($distances[$i] === 0 || abs(strlen($input) - $size) > $distances[$i]) continue;
                    $distance = levenshtein($input, $folded);
                    if ($distance >= 0 && $distance <= $distances[$i]) {
                        $closest[$i] = $word;
                        $distances[$i] = $distance;
                        if ($distance === 0) $remaining--;
                    }
                }
            }
        }
    } finally {
        // Finish/release the stream before any corrected-query lookup.
        mysql_free_result($result);
    }
    foreach ($closest as $word) if ($word === null) return null;
    return implode(' ', $closest);
}
