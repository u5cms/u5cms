<?php
/*
 * Pure search engine. All public text arguments are UTF-8 unless explicitly
 * named Legacy. No database, session, request mutation or output side effects.
 *
 * u5SearchDecodeRequest($raw, $version): UTF-8; qv=2 is literal, older links
 *   additionally decode legacy %u escapes/entities and comma separators.
 * u5SearchParseQuery($query): raw, terms[{text, normalized, phrase}], error.
 * u5SearchNormalize($text, $trim=true): shared, literal character folding.
 * u5SearchMatch($text, $terms, $withRanges=false): matched, count, ranges.
 *   Range pairs are half-open UTF-8 BYTE offsets in the original text.
 * u5SearchEvaluate($title, $body, $terms): AND across title/body, never across
 *   field boundaries inside a phrase; deterministic relevance score.
 * u5SearchSnippet($text, $terms, $context=65, $maxFragments=3): escaped ASCII
 *   HTML with balanced span.hitshilite elements; empty if nothing matches.
 * u5SearchFromLegacy($bytes, $encoding='WINDOWS-1252'): decode entities ONCE.
 * u5SearchHtml / u5SearchJson: safe output for the existing CP1252 pages.
 *
 * PHP 7.4+. The same committed normalization map is consumed by JavaScript.
 */

function u5SearchUtf8($text) {
    if (!is_string($text)) return '';
    if (preg_match('//u', $text)) return $text;
    return mb_convert_encoding($text, 'UTF-8', 'WINDOWS-1252');
}

function u5SearchFromLegacy($text, $encoding = 'WINDOWS-1252') {
    if (!is_string($text)) return '';
    $utf8 = strtoupper($encoding) === 'UTF-8'
        ? u5SearchUtf8($text)
        : mb_convert_encoding($text, 'UTF-8', $encoding);
    return html_entity_decode($utf8, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function u5SearchCodepoint($cp) {
    if ($cp < 0 || $cp > 0x10ffff || ($cp >= 0xd800 && $cp <= 0xdfff)) return "\u{fffd}";
    if ($cp < 0x80) return chr($cp);
    if ($cp < 0x800) return chr(0xc0 | ($cp >> 6)) . chr(0x80 | ($cp & 0x3f));
    if ($cp < 0x10000) return chr(0xe0 | ($cp >> 12)) . chr(0x80 | (($cp >> 6) & 0x3f)) . chr(0x80 | ($cp & 0x3f));
    return chr(0xf0 | ($cp >> 18)) . chr(0x80 | (($cp >> 12) & 0x3f)) . chr(0x80 | (($cp >> 6) & 0x3f)) . chr(0x80 | ($cp & 0x3f));
}

function u5SearchDecodeRequest($raw, $version = null) {
    $query = u5SearchUtf8($raw);
    if ($version === '2' || $version === 2) return $query;
    $query = preg_replace_callback(
        '/%u([dD][89aAbB][0-9a-fA-F]{2})%u([dD][c-fC-F][0-9a-fA-F]{2})|%u([0-9a-fA-F]{4})/',
        function ($m) {
            if ($m[1] !== '') return u5SearchCodepoint(0x10000 + ((hexdec($m[1]) - 0xd800) << 10) + hexdec($m[2]) - 0xdc00);
            return u5SearchCodepoint(hexdec($m[3]));
        },
        $query
    );
    $query = html_entity_decode($query, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return str_replace(',', ' ', $query);
}

function u5SearchNormalizationMap() {
    static $map = null;
    if ($map === null) {
        $json = file_get_contents(__DIR__ . '/search-normalization.json');
        $data = json_decode($json, true);
        if (!is_array($data) || !isset($data['version'], $data['map']) || $data['version'] !== 1 || !is_array($data['map'])) {
            throw new RuntimeException('Search normalization data is unavailable.');
        }
        $map = $data['map'];
    }
    return $map;
}

function u5SearchNormalize($text, $trim = true) {
    $folded = strtr(u5SearchUtf8($text), u5SearchNormalizationMap());
    $folded = preg_replace('/ +/', ' ', $folded);
    return $trim ? trim($folded, ' ') : $folded;
}

function u5SearchParseQuery($query) {
    $query = u5SearchUtf8($query);
    $result = array('raw' => $query, 'terms' => array(), 'error' => null);
    if (mb_strlen($query, 'UTF-8') > 512) {
        $result['error'] = 'too_long';
        return $result;
    }
    if (preg_match('/[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]/', $query)) {
        $result['error'] = 'invalid_control';
        return $result;
    }
    $quoteCharacters = array('"', "\u{201c}", "\u{201d}", "\u{201e}", "\u{00ab}", "\u{00bb}");
    $characters = preg_split('//u', $query, -1, PREG_SPLIT_NO_EMPTY);
    $buffer = '';
    $quoted = false;
    $tokenCount = 0;
    $seen = array();
    $append = function ($text, $phrase) use (&$result, &$tokenCount, &$seen) {
        $normalized = u5SearchNormalize($text);
        if ($normalized === '') {
            if ($phrase) $result['error'] = 'empty_phrase';
            return;
        }
        $tokenCount++;
        if ($tokenCount > 16) {
            $result['error'] = 'too_many_terms';
            return;
        }
        $key = ($phrase ? 'p:' : 'w:') . $normalized;
        if (!isset($seen[$key])) {
            $result['terms'][] = array('text' => $text, 'normalized' => $normalized, 'phrase' => $phrase);
            $seen[$key] = true;
        }
    };
    for ($characterIndex = 0; $characterIndex < count($characters); $characterIndex++) {
        $character = $characters[$characterIndex];
        if ($character === '\\' && isset($characters[$characterIndex + 1]) &&
            (in_array($characters[$characterIndex + 1], $quoteCharacters, true) || $characters[$characterIndex + 1] === '\\')) {
            $buffer .= $characters[++$characterIndex];
            continue;
        }
        if (in_array($character, $quoteCharacters, true)) {
            if ($quoted) $append($buffer, true);
            elseif ($buffer !== '') $append($buffer, false);
            $buffer = '';
            $quoted = !$quoted;
        } elseif (!$quoted && preg_match('/[\s\p{Z}]/u', $character)) {
            if ($buffer !== '') $append($buffer, false);
            $buffer = '';
        } else {
            $buffer .= $character;
        }
        if ($result['error'] !== null) return $result;
    }
    if ($quoted) $result['error'] = 'unclosed_quote';
    elseif ($buffer !== '') $append($buffer, false);
    if ($result['error'] === null && !$result['terms']) $result['error'] = 'empty';
    return $result;
}

function u5SearchTerms($terms) {
    if (isset($terms['terms'])) return $terms['terms'];
    return is_array($terms) ? $terms : array();
}

function u5SearchSerializeTerms($terms, $preservePhrases = true) {
    $quoteCharacters = array('"', "\u{201c}", "\u{201d}", "\u{201e}", "\u{00ab}", "\u{00bb}");
    $pieces = array();
    foreach (u5SearchTerms($terms) as $term) {
        $text = str_replace('\\', '\\\\', $term['text']);
        foreach ($quoteCharacters as $quote) $text = str_replace($quote, '\\' . $quote, $text);
        $pieces[] = $preservePhrases && !empty($term['phrase']) ? '"' . $text . '"' : $text;
    }
    return implode(' ', $pieces);
}

function u5SearchNormalizeWithMap($text) {
    $text = u5SearchUtf8($text);
    $map = u5SearchNormalizationMap();
    $characters = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_OFFSET_CAPTURE);
    $folded = '';
    $segments = array();
    $length = 0;
    $previousSpace = false;
    foreach ($characters as $pair) {
        $character = $pair[0];
        $start = $pair[1];
        $end = $start + strlen($character);
        $replacement = array_key_exists($character, $map) ? $map[$character] : $character;
        if ($replacement === '') {
            if ($segments) $segments[count($segments) - 1][3] = $end;
            continue;
        }
        foreach (preg_split('//u', $replacement, -1, PREG_SPLIT_NO_EMPTY) as $piece) {
            if ($piece === ' ' && $previousSpace) {
                if ($segments) $segments[count($segments) - 1][3] = $end;
                continue;
            }
            $pieceLength = strlen($piece);
            $segments[] = array($length, $length + $pieceLength, $start, $end);
            $folded .= $piece;
            $length += $pieceLength;
            $previousSpace = $piece === ' ';
        }
    }
    return array('text' => $folded, 'segments' => $segments);
}

function u5SearchMapPosition($segments, $position) {
    $low = 0;
    $high = count($segments);
    while ($low < $high) {
        $mid = (int) floor(($low + $high) / 2);
        if ($segments[$mid][1] <= $position) $low = $mid + 1;
        else $high = $mid;
    }
    return min($low, count($segments) - 1);
}

function u5SearchMergeRanges($ranges) {
    usort($ranges, function ($a, $b) { return $a[0] === $b[0] ? $a[1] <=> $b[1] : $a[0] <=> $b[0]; });
    $merged = array();
    foreach ($ranges as $range) {
        $last = count($merged) - 1;
        if ($last >= 0 && $range[0] < $merged[$last][1]) $merged[$last][1] = max($merged[$last][1], $range[1]);
        else $merged[] = $range;
    }
    return $merged;
}

function u5SearchResolveRanges($text, $normalizedRanges) {
    // Keep memory proportional to the bounded match list, not document length.
    // A megabyte text must not become a million PHP offset-array objects.
    $normalizedRanges = u5SearchMergeRanges($normalizedRanges);
    $map = u5SearchNormalizationMap();
    $resolved = array();
    $nextRange = 0;
    $rangeCount = count($normalizedRanges);
    $normalizedOffset = 0;
    $previousSpace = false;
    $lastTouched = array();
    $textLength = strlen($text);
    for ($sourceStart = 0; $sourceStart < $textLength; $sourceStart += $characterLength) {
        $leadingByte = ord($text[$sourceStart]);
        $characterLength = $leadingByte < 0x80 ? 1 : ($leadingByte < 0xe0 ? 2 : ($leadingByte < 0xf0 ? 3 : 4));
        $character = substr($text, $sourceStart, $characterLength);
        $sourceEnd = $sourceStart + $characterLength;
        $replacement = array_key_exists($character, $map) ? $map[$character] : $character;
        if (strpos($replacement, ' ') !== false) {
            $replacement = preg_replace('/ +/', ' ', $replacement);
            if ($previousSpace && substr($replacement, 0, 1) === ' ') $replacement = substr($replacement, 1);
        }
        if ($replacement === '') {
            foreach ($lastTouched as $index) $resolved[$index][1] = $sourceEnd;
            continue;
        }
        if ($nextRange >= $rangeCount) break;
        $previousSpace = substr($replacement, -1) === ' ';
        $normalizedEnd = $normalizedOffset + strlen($replacement);
        $lastTouched = array();
        while ($nextRange < $rangeCount && $normalizedRanges[$nextRange][0] < $normalizedEnd) {
            if ($normalizedRanges[$nextRange][1] > $normalizedOffset) {
                if (!isset($resolved[$nextRange])) $resolved[$nextRange] = array($sourceStart, $sourceEnd);
                else $resolved[$nextRange][1] = $sourceEnd;
                $lastTouched[] = $nextRange;
            }
            if ($normalizedRanges[$nextRange][1] <= $normalizedEnd) $nextRange++;
            else break;
        }
        $normalizedOffset = $normalizedEnd;
    }
    return u5SearchMergeRanges(array_values($resolved));
}

function u5SearchMatch($text, $terms, $withRanges = false) {
    $text = u5SearchUtf8($text);
    $terms = u5SearchTerms($terms);
    $normalized = u5SearchNormalize($text, false);
    $matchedTerms = 0;
    $normalizedRanges = array();
    $perTermLimit = max(1, (int) floor(1000 / max(1, count($terms))));
    foreach ($terms as $term) {
        $needle = isset($term['normalized']) ? $term['normalized'] : u5SearchNormalize($term['text']);
        if ($needle === '') continue;
        $position = strpos($normalized, $needle);
        if ($position === false) continue;
        $matchedTerms++;
        if (!$withRanges) continue;
        $occurrences = 0;
        do {
            $normalizedRanges[] = array($position, $position + strlen($needle));
            $occurrences++;
            $position = strpos($normalized, $needle, $position + 1);
        } while ($position !== false && $occurrences < $perTermLimit);
    }
    $ranges = array();
    if ($normalizedRanges) {
        $ranges = u5SearchResolveRanges($text, $normalizedRanges);
    }
    return array('matched' => count($terms) > 0 && $matchedTerms === count($terms), 'count' => $matchedTerms, 'ranges' => $ranges);
}

function u5SearchEvaluate($title, $body, $terms) {
    $terms = u5SearchTerms($terms);
    $title = u5SearchNormalize($title);
    $body = u5SearchNormalize($body);
    $titleMatches = 0;
    $bodyMatches = 0;
    $matchedTerms = 0;
    $score = 0;
    foreach ($terms as $term) {
        $needle = $term['normalized'];
        if ($needle === '') continue;
        $inTitle = strpos($title, $needle) !== false;
        $inBody = strpos($body, $needle) !== false;
        if ($inTitle) $titleMatches++;
        if ($inBody) $bodyMatches++;
        if ($inTitle || $inBody) {
            $matchedTerms++;
            $score += ($inTitle ? 30 : 10) + (!empty($term['phrase']) ? 8 : 0);
            if ($title === $needle) $score += 60;
        }
    }
    if ($terms && $titleMatches === count($terms)) $score += 100;
    return array('matched' => count($terms) > 0 && $matchedTerms === count($terms), 'score' => $score, 'titleMatches' => $titleMatches, 'bodyMatches' => $bodyMatches, 'matchedTerms' => $matchedTerms, 'totalTerms' => count($terms));
}

function u5SearchHtml($text) {
    $text = htmlspecialchars(u5SearchUtf8($text), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    return mb_encode_numericentity($text, array(0x80, 0x10ffff, 0, 0x1fffff), 'UTF-8');
}

function u5SearchJson($value) {
    $encoded = json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE);
    return $encoded === false ? 'null' : $encoded;
}

function u5SearchResolveOccurrences($text, $occurrences) {
    // Preserve individual anchors. Merging overlapping matches here could turn
    // many short phrases into an unbounded, document-sized snippet anchor.
    $starts = array();
    $ends = array();
    foreach ($occurrences as $id => $occurrence) {
        $starts[] = array($occurrence[0], $id);
        $ends[] = array($occurrence[1], $id);
    }
    $sort = function ($a, $b) { return $a[0] === $b[0] ? $a[1] <=> $b[1] : $a[0] <=> $b[0]; };
    usort($starts, $sort);
    usort($ends, $sort);
    $map = u5SearchNormalizationMap();
    $resolved = array();
    $nextStart = 0;
    $nextEnd = 0;
    $count = count($occurrences);
    $normalizedOffset = 0;
    $previousSpace = false;
    $lastEnded = array();
    $textLength = strlen($text);
    for ($sourceStart = 0, $sourceChar = 0; $sourceStart < $textLength; $sourceStart += $characterLength, $sourceChar++) {
        $leadingByte = ord($text[$sourceStart]);
        $characterLength = $leadingByte < 0x80 ? 1 : ($leadingByte < 0xe0 ? 2 : ($leadingByte < 0xf0 ? 3 : 4));
        $character = substr($text, $sourceStart, $characterLength);
        $sourceEnd = $sourceStart + $characterLength;
        $replacement = array_key_exists($character, $map) ? $map[$character] : $character;
        if (strpos($replacement, ' ') !== false) {
            $replacement = preg_replace('/ +/', ' ', $replacement);
            if ($previousSpace && substr($replacement, 0, 1) === ' ') $replacement = substr($replacement, 1);
        }
        if ($replacement === '') {
            // Include the full original grapheme/space run at an anchor end.
            foreach ($lastEnded as $id) {
                $resolved[$id][1] = $sourceEnd;
                $resolved[$id][3] = $sourceChar + 1;
            }
            continue;
        }
        if ($nextEnd >= $count) break;
        $previousSpace = substr($replacement, -1) === ' ';
        $normalizedEnd = $normalizedOffset + strlen($replacement);
        while ($nextStart < $count && $starts[$nextStart][0] < $normalizedEnd) {
            $id = $starts[$nextStart++][1];
            $resolved[$id] = array($sourceStart, $sourceEnd, $sourceChar, $sourceChar + 1, $occurrences[$id][2]);
        }
        $lastEnded = array();
        while ($nextEnd < $count && $ends[$nextEnd][0] <= $normalizedEnd) {
            $id = $ends[$nextEnd++][1];
            $resolved[$id][1] = $sourceEnd;
            $resolved[$id][3] = $sourceChar + 1;
            $lastEnded[] = $id;
        }
        $normalizedOffset = $normalizedEnd;
    }
    return array_values($resolved);
}

function u5SearchSnippet($text, $terms, $context = 65, $maxFragments = 3) {
    $text = u5SearchUtf8($text);
    $terms = array_values(u5SearchTerms($terms));
    if (!$terms || $maxFragments < 1) return '';
    $context = min(512, max(0, (int) $context));
    $maxFragments = min(5, (int) $maxFragments);
    $normalized = u5SearchNormalize($text, false);
    $occurrences = array();
    $longestTerm = 0;
    foreach ($terms as $id => $term) {
        $needle = isset($term['normalized']) ? $term['normalized'] : u5SearchNormalize($term['text']);
        if ($needle === '') continue;
        $longestTerm = max($longestTerm, mb_strlen($term['text'], 'UTF-8'));
        $position = strpos($normalized, $needle);
        // Reserve anchors for every term, including one that first occurs far
        // beyond a frequently repeated word. Never scan source prefixes per hit.
        for ($count = 0; $position !== false && $count < 32; $count++) {
            $occurrences[] = array($position, $position + strlen($needle), $id);
            $position = strpos($normalized, $needle, $position + 1);
        }
    }
    unset($normalized);
    if (!$occurrences) return '';
    $occurrences = u5SearchResolveOccurrences($text, $occurrences);
    $textLength = mb_strlen($text, 'UTF-8');
    $windowLimit = min(4096, max(256, 2 * $context + 4 * $longestTerm));
    $candidates = array();
    $uncovered = array();
    foreach ($occurrences as $occurrence) {
        $anchorLength = $occurrence[3] - $occurrence[2];
        // A short normalized phrase may contain huge original whitespace or
        // invisible runs. Do not fabricate a complete quote by clipping it.
        if ($anchorLength > $windowLimit) continue;
        $padding = min($context, (int) floor(($windowLimit - $anchorLength) / 2));
        $left = max(0, $occurrence[2] - $padding);
        $right = min($textLength, $occurrence[3] + $padding);
        $candidates[$left . ':' . $right] = array($left, $right);
        $uncovered[$occurrence[4]] = true;
    }
    if (!$candidates) return '';
    $windows = array();
    while ($uncovered && count($windows) < $maxFragments) {
        $best = null;
        $bestCoverage = array();
        foreach ($candidates as $candidate) {
            $coverage = array();
            foreach ($occurrences as $occurrence) {
                if (isset($uncovered[$occurrence[4]]) && $occurrence[2] >= $candidate[0] && $occurrence[3] <= $candidate[1]) {
                    $coverage[$occurrence[4]] = true;
                }
            }
            if (count($coverage) > count($bestCoverage) ||
                (count($coverage) > 0 && count($coverage) === count($bestCoverage) && ($best === null || $candidate[0] < $best[0]))) {
                $best = $candidate;
                $bestCoverage = $coverage;
            }
        }
        if ($best === null) break;
        $windows[] = $best;
        foreach ($bestCoverage as $id => $unused) unset($uncovered[$id]);
    }
    usort($windows, function ($a, $b) { return $a[0] <=> $b[0]; });
    $boundedWindows = array();
    foreach ($windows as $window) {
        $last = count($boundedWindows) - 1;
        if ($last >= 0 && $window[0] <= $boundedWindows[$last][1] &&
            max($window[1], $boundedWindows[$last][1]) - $boundedWindows[$last][0] <= $windowLimit) {
            $boundedWindows[$last][1] = max($window[1], $boundedWindows[$last][1]);
        } else $boundedWindows[] = $window;
    }
    $html = $boundedWindows[0][0] > 0 ? '&hellip;' : '';
    foreach ($boundedWindows as $windowIndex => $window) {
        if ($windowIndex > 0) $html .= ' &hellip; ';
        $fragment = mb_substr($text, $window[0], $window[1] - $window[0], 'UTF-8');
        // Match inside this bounded fragment: only complete occurrences are
        // highlighted, even if another occurrence crosses its context boundary.
        $match = u5SearchMatch($fragment, $terms, true);
        $cursor = 0;
        foreach ($match['ranges'] as $range) {
            $html .= u5SearchHtml(substr($fragment, $cursor, $range[0] - $cursor));
            $html .= '<span class="hitshilite">' . u5SearchHtml(substr($fragment, $range[0], $range[1] - $range[0])) . '</span>';
            $cursor = $range[1];
        }
        $html .= u5SearchHtml(substr($fragment, $cursor));
    }
    if ($boundedWindows[count($boundedWindows) - 1][1] < $textLength) $html .= '&hellip;';
    return $html;
}
