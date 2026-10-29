<?php
/*
 * Bounded, resource-local spelling suggestions. All arguments are decoded UTF-8.
 * The caller applies finding AND preview policy before supplying any text, and
 * rechecks candidate witnesses before display. No database access or recursion.
 * u5SearchEditDistance() is supplied by search-service.inc.php.
 */
require_once __DIR__ . '/search-core.inc.php';

function u5SearchSuggestionPlan($term) {
    $folded = u5SearchNormalize($term['text'], false);
    $leading = strlen($folded) - strlen(ltrim($folded, ' '));
    $normalized = trim($folded, ' ');
    preg_match_all('/\p{L}[\p{L}\p{M}]*/u', $normalized, $matches, PREG_OFFSET_CAPTURE);
    $words = array();
    $occurrences = array();
    foreach ($matches[0] as $index => $match) {
        $length = mb_strlen($match[0], 'UTF-8');
        $words[] = array('normalized' => $match[0], 'start' => $match[1], 'end' => $match[1] + strlen($match[0]),
            'length' => $length, 'limit' => $length >= 3 && $length <= 40 ? ($length < 5 ? 1 : 2) : 0);
        $occurrences[] = array($leading + $match[1], $leading + $match[1] + strlen($match[0]), $index);
    }
    $correctable = false;
    foreach (u5SearchResolveOccurrences($term['text'], $occurrences) as $range) {
        $index = $range[4];
        $words[$index]['sourceStart'] = $range[0];
        $words[$index]['sourceEnd'] = $range[1];
        // A compatibility glyph may expand to several words. Do not replace a
        // fragment of one source glyph as if it were an independent word.
        if (u5SearchNormalize(substr($term['text'], $range[0], $range[1] - $range[0])) !== $words[$index]['normalized']) $words[$index]['limit'] = 0;
        $correctable = $correctable || $words[$index]['limit'] > 0;
    }
    $gaps = array();
    for ($i = 1; $i < count($words); $i++) $gaps[] = substr($normalized, $words[$i - 1]['end'], $words[$i]['start'] - $words[$i - 1]['end']);
    return array('term' => $term, 'words' => $words, 'correctable' => $correctable, 'gaps' => $gaps,
        'prefix' => $words ? substr($normalized, 0, $words[0]['start']) : '',
        'suffix' => $words ? substr($normalized, $words[count($words) - 1]['end']) : '');
}

function u5SearchSuggestionState($query) {
    $state = array('query' => $query, 'plans' => array(), 'active' => false, 'exact' => false, 'truncated' => false,
        'remainingBytes' => 4 * 1024 * 1024, 'remainingNormalizedBytes' => 4 * 1024 * 1024,
        'remainingTokens' => 100000, 'remainingComparisons' => 20000, 'distanceCache' => array(), 'candidates' => array());
    if (!is_array($query) || ($query['error'] ?? null) !== null || empty($query['terms'])) return $state;
    foreach ($query['terms'] as $term) {
        $plan = u5SearchSuggestionPlan($term);
        $state['plans'][] = $plan;
        $state['active'] = $state['active'] || $plan['correctable'];
    }
    return $state;
}

function u5SearchSuggestionCanScan($state) {
    return $state['active'] && !$state['exact'] && $state['remainingBytes'] > 0 && $state['remainingNormalizedBytes'] > 0
        && $state['remainingTokens'] > 0 && $state['remainingComparisons'] > 0;
}

function u5SearchSuggestionField(&$state, $text) {
    $allowed = min(strlen($text), $state['remainingBytes']);
    $truncated = $allowed < strlen($text);
    $source = mb_strcut($text, 0, $allowed, 'UTF-8');
    $state['remainingBytes'] -= $allowed;
    $normalized = '';
    // Chunk normalization prevents compatibility expansion of a bounded source
    // field from allocating an unbounded folded string. Adjacent spaces still
    // collapse exactly as in u5SearchNormalize($source, false).
    for ($offset = 0, $length = strlen($source); $offset < $length;) {
        $chunk = mb_strcut($source, $offset, 8192, 'UTF-8');
        $offset += strlen($chunk);
        $piece = u5SearchNormalize($chunk, false);
        if (substr($normalized, -1) === ' ' && substr($piece, 0, 1) === ' ') $piece = substr($piece, 1);
        if (strlen($piece) > $state['remainingNormalizedBytes']) {
            $piece = mb_strcut($piece, 0, $state['remainingNormalizedBytes'], 'UTF-8');
            $truncated = true;
            $state['remainingNormalizedBytes'] = 0;
            $normalized .= $piece;
            break;
        }
        $state['remainingNormalizedBytes'] -= strlen($piece);
        $normalized .= $piece;
    }
    if ($truncated) {
        // The final folded word may continue beyond either byte bound. It must
        // never become a fictitious complete word in a spelling suggestion.
        $normalized = preg_replace('/[\p{L}\p{M}\p{N}]+$/u', '', $normalized);
        $state['truncated'] = true;
    }
    return array('source' => $source, 'normalized' => $normalized);
}

function u5SearchSuggestionDistance(&$state, $word, $candidate) {
    if ($word['normalized'] === $candidate['normalized']) return 0;
    if ($word['limit'] === 0 || abs($word['length'] - $candidate['length']) > $word['limit']) return false;
    $key = $word['normalized'] . "\0" . $candidate['normalized'];
    if (array_key_exists($key, $state['distanceCache'])) return $state['distanceCache'][$key];
    if ($state['remainingComparisons'] <= 0) { $state['truncated'] = true; return false; }
    $state['remainingComparisons']--;
    $distance = u5SearchEditDistance($word['normalized'], $candidate['normalized'], $word['limit']);
    $state['distanceCache'][$key] = $distance <= $word['limit'] ? $distance : false;
    return $state['distanceCache'][$key];
}

function u5SearchSuggestionSourceRange($source, $start, $end, &$cursor) {
    // Advance monotonically through source codepoints alongside folded tokens.
    // Only one expansion segment is retained, even if a glyph folds to several
    // words. This also rejects oversized spellings before candidate deduplication.
    $map = u5SearchNormalizationMap();
    $range = null;
    $sourceLength = strlen($source);
    while (true) {
        if ($cursor['segment'] === null) {
            if ($cursor['source'] >= $sourceLength) break;
            $sourceStart = $cursor['source'];
            $leading = ord($source[$sourceStart]);
            $length = $leading < 0x80 ? 1 : ($leading < 0xe0 ? 2 : ($leading < 0xf0 ? 3 : 4));
            $character = substr($source, $sourceStart, $length);
            $replacement = array_key_exists($character, $map) ? $map[$character] : $character;
            if (strpos($replacement, ' ') !== false) {
                $replacement = preg_replace('/ +/', ' ', $replacement);
                if ($cursor['space'] && substr($replacement, 0, 1) === ' ') $replacement = substr($replacement, 1);
            }
            $normalStart = $cursor['normalized'];
            $cursor['normalized'] += strlen($replacement);
            if ($replacement !== '') $cursor['space'] = substr($replacement, -1) === ' ';
            $cursor['source'] += $length;
            $sourceCharacter = $cursor['characters']++;
            $cursor['segment'] = array($normalStart, $cursor['normalized'], $sourceStart, $cursor['source'], $sourceCharacter, $cursor['characters']);
        }
        $segment = $cursor['segment'];
        if ($segment[0] === $segment[1]) {
            if ($range !== null && $segment[0] <= $end) { $range[1] = $segment[3]; $range[3] = $segment[5]; }
            $cursor['segment'] = null;
            continue;
        }
        if ($segment[1] <= $start) { $cursor['segment'] = null; continue; }
        if ($segment[0] >= $end) break;
        if ($range === null) $range = array($segment[2], $segment[3], $segment[4], $segment[5]);
        else { $range[1] = $segment[3]; $range[3] = $segment[5]; }
        if ($segment[1] > $end) break;
        $cursor['segment'] = null;
    }
    return $range;
}

function u5SearchSuggestionCompare($a, $b) {
    foreach (array('cost', 'changes', 'changedTerms') as $key) {
        if (($a[$key] ?? 0) !== ($b[$key] ?? 0)) return ($a[$key] ?? 0) <=> ($b[$key] ?? 0);
    }
    if (($a['score'] ?? 0) !== ($b['score'] ?? 0)) return ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
    return strcmp($a['key'], $b['key']);
}

function u5SearchSuggestionWindow(&$state, $plan, $buffer, $position, $capacity, $normalized, $field) {
    $count = count($plan['words']);
    if ($position + 1 < $count) return null;
    $first = $buffer[($position - $count + 1) % $capacity];
    $last = $buffer[$position % $capacity];
    if ($first === null || $last === null) return null;
    $prefixLength = strlen($plan['prefix']);
    $suffixLength = strlen($plan['suffix']);
    if ($first['start'] < $prefixLength || substr($normalized, $first['start'] - $prefixLength, $prefixLength) !== $plan['prefix']
        || substr($normalized, $last['end'], $suffixLength) !== $plan['suffix']) return null;
    $cost = 0;
    $spellingCost = 0;
    $replacements = array();
    $previous = null;
    foreach ($plan['words'] as $i => $word) {
        $candidate = $buffer[($position - $count + 1 + $i) % $capacity];
        if ($candidate === null) return null;
        if ($previous !== null) {
            $gap = $plan['gaps'][$i - 1];
            if ($candidate['start'] - $previous['end'] !== strlen($gap) || substr($normalized, $previous['end'], strlen($gap)) !== $gap) return null;
        }
        $distance = u5SearchSuggestionDistance($state, $word, $candidate);
        if ($distance === false) return null;
        if ($distance > 0) {
            if ($candidate['spelling'] === null) return null;
            $cost += $distance;
            $spellingCost += $candidate['spellingCost'];
            $replacements[] = array('word' => $i, 'spelling' => $candidate['spelling']);
        }
        $previous = $candidate;
    }
    if (!$replacements) return null;
    $key = substr($normalized, $first['start'] - $prefixLength, $last['end'] + $suffixLength - $first['start'] + $prefixLength);
    return array('key' => $key, 'cost' => $cost, 'changes' => count($replacements), 'spellingCost' => $spellingCost, 'field' => $field, 'replacements' => $replacements);
}

function u5SearchSuggestionAlternatives(&$state, $plans, $fields) {
    $alternatives = array();
    $capacity = 1;
    $maxWordBytes = 168;
    foreach ($plans as $id => $plan) {
        $alternatives[$id] = array();
        $capacity = max($capacity, count($plan['words']));
        foreach ($plan['words'] as $word) $maxWordBytes = max($maxWordBytes, strlen($word['normalized']) + 8);
    }
    foreach ($fields as $field => $data) {
        $buffer = array();
        $offset = 0;
        $position = 0;
        $cursor = array('source' => 0, 'characters' => 0, 'normalized' => 0, 'space' => false, 'segment' => null);
        while ($state['remainingTokens'] > 0 && $state['remainingComparisons'] > 0
            && preg_match('/\p{L}[\p{L}\p{M}]*/u', $data['normalized'], $match, PREG_OFFSET_CAPTURE, $offset)) {
            $start = $match[0][1];
            $offset = $start + strlen($match[0][0]);
            $state['remainingTokens']--;
            $range = u5SearchSuggestionSourceRange($data['source'], $start, $offset, $cursor);
            $spelling = null;
            // Preserve source case/accents, but never materialize a megabyte
            // invisible run as a replacement for a short normalized word.
            if ($range !== null && $range[1] - $range[0] <= 2048 && $range[3] - $range[2] <= 512) {
                $spelling = substr($data['source'], $range[0], $range[1] - $range[0]);
                if (u5SearchNormalize($spelling) !== $match[0][0]) $spelling = null;
            }
            $wordLength = mb_strlen($match[0][0], 'UTF-8');
            $spellingCost = $range === null ? 0 : max(0, $range[3] - $range[2] - 3 * $wordLength - 4);
            $buffer[$position % $capacity] = strlen($match[0][0]) > $maxWordBytes ? null
                : array('normalized' => $match[0][0], 'length' => $wordLength, 'start' => $start, 'end' => $offset, 'spelling' => $spelling, 'spellingCost' => $spellingCost);
            foreach ($plans as $id => $plan) {
                $candidate = u5SearchSuggestionWindow($state, $plan, $buffer, $position, $capacity, $data['normalized'], $field);
                if ($candidate === null) continue;
                // Keep ordinary original accents/case. If equivalent spellings
                // differ by an excessive invisible/combining run, prefer the
                // bounded ordinary spelling instead of shadowing it.
                if (isset($alternatives[$id][$candidate['key']]) && $alternatives[$id][$candidate['key']]['spellingCost'] <= $candidate['spellingCost']) continue;
                $alternatives[$id][$candidate['key']] = $candidate;
                uasort($alternatives[$id], 'u5SearchSuggestionCompare');
                $alternatives[$id] = array_slice($alternatives[$id], 0, 4, true);
            }
            $position++;
        }
    }
    if ($state['remainingTokens'] <= 0 || $state['remainingComparisons'] <= 0) $state['truncated'] = true;
    $result = array();
    foreach ($alternatives as $id => $choices) {
        $result[$id] = array();
        foreach ($choices as $choice) {
            $term = $plans[$id]['term'];
            foreach (array_reverse($choice['replacements']) as $replacement) {
                $word = $plans[$id]['words'][$replacement['word']];
                $term['text'] = substr_replace($term['text'], $replacement['spelling'], $word['sourceStart'], $word['sourceEnd'] - $word['sourceStart']);
            }
            $term['normalized'] = u5SearchNormalize($term['text']);
            if ($term['normalized'] !== $choice['key']) continue;
            $result[$id][] = array('term' => $term, 'cost' => $choice['cost'], 'changes' => $choice['changes'], 'key' => $choice['key']);
        }
    }
    return $result;
}

function u5SearchSuggestionCollect(&$state, $title, $body, $resource = '') {
    if (!u5SearchSuggestionCanScan($state)) return;
    $fields = array(u5SearchSuggestionField($state, $title), u5SearchSuggestionField($state, $body));
    $missing = array();
    foreach ($state['plans'] as $id => $plan) {
        $needle = $plan['term']['normalized'];
        if (strpos($fields[0]['normalized'], $needle) === false && strpos($fields[1]['normalized'], $needle) === false) $missing[$id] = $plan;
    }
    if (!$missing) { $state['exact'] = true; $state['candidates'] = array(); return; }
    foreach ($missing as $plan) if (!$plan['correctable']) return;
    $alternatives = u5SearchSuggestionAlternatives($state, $missing, $fields);
    foreach ($alternatives as $choices) if (!$choices) return;
    $beam = array(array('terms' => array(), 'cost' => 0, 'changes' => 0, 'changedTerms' => 0, 'key' => ''));
    foreach ($state['plans'] as $id => $plan) {
        $choices = $alternatives[$id] ?? array(array('term' => $plan['term'], 'cost' => 0, 'changes' => 0, 'key' => $plan['term']['normalized']));
        $next = array();
        foreach ($beam as $candidate) foreach ($choices as $choice) {
            $copy = $candidate;
            $copy['terms'][] = $choice['term'];
            $copy['cost'] += $choice['cost'];
            $copy['changes'] += $choice['changes'];
            $copy['changedTerms'] += $choice['cost'] > 0 ? 1 : 0;
            $copy['key'] .= ($choice['term']['phrase'] ? 'p:' : 'w:') . $choice['key'] . "\0";
            $next[] = $copy;
        }
        usort($next, 'u5SearchSuggestionCompare');
        $beam = array_slice($next, 0, 8);
    }
    foreach ($beam as $candidate) {
        $queryText = u5SearchSerializeTerms($candidate['terms']);
        $parsed = u5SearchParseQuery($queryText);
        if ($parsed['error'] !== null || $candidate['cost'] <= 0) continue;
        // The normalized bounded fields are fixed points of the shared fold.
        // Reusing them avoids expanding huge compatibility-heavy input twice.
        $verified = u5SearchEvaluate($fields[0]['normalized'], $fields[1]['normalized'], $parsed['terms']);
        if (!$verified['matched']) continue;
        $candidate['query'] = $queryText;
        $candidate['score'] = $verified['score'];
        $candidate['sources'] = $resource === '' ? array() : array($resource);
        unset($candidate['terms']);
        $key = $candidate['key'];
        if (isset($state['candidates'][$key])) {
            $existing = $state['candidates'][$key];
            $sources = array_slice(array_values(array_unique(array_merge($existing['sources'], $candidate['sources']))), 0, 3);
            if (u5SearchSuggestionCompare($candidate, $existing) < 0) $state['candidates'][$key] = $candidate;
            $state['candidates'][$key]['sources'] = $sources;
        } else $state['candidates'][$key] = $candidate;
        uasort($state['candidates'], 'u5SearchSuggestionCompare');
        $state['candidates'] = array_slice($state['candidates'], 0, 12, true);
    }
}

function u5SearchSuggestionCandidates($state) {
    return array_values($state['candidates']);
}

function u5SearchSuggestionResults($state) {
    return array_column(array_slice(u5SearchSuggestionCandidates($state), 0, 3), 'query');
}
