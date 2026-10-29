<?php
require_once __DIR__ . '/search-core.inc.php';
require_once __DIR__ . '/search-suggestions.inc.php';

/* Database eligibility and previews keep the existing installation policies. */
function u5SearchPolicy($intranet = false) {
    return array(
        'findProtected' => ($GLOBALS['doesfindpasswordprotectedcontent'] ?? 'no') === 'yes',
        'previewProtected' => ($GLOBALS['doesshowpreviewofsuchcontent'] ?? 'no') === 'yes',
        'hideIntranet' => ($GLOBALS['doeshideintranetcontenttopublic'] ?? 'yes') === 'yes',
        'intranet' => $intranet,
        'openIntranet' => $intranet && ($GLOBALS['automaticallyskipintranetlogin'] ?? 'no') === 'yes',
    );
}

function u5SearchWhere($slot, $policy) {
    /* NULL is not indexed yet; an indexed empty body can still match its title. */
    $where = "deleted!=1 AND hidden=0 AND typ!='c' AND search_" . (int) $slot . " IS NOT NULL";
    if ($policy['hideIntranet']) $where .= " AND name NOT LIKE '!%'";
    if (!$policy['findProtected']) $where .= !empty($policy['openIntranet'])
        ? " AND (logins NOT LIKE '%:%' OR name LIKE '!%')" : " AND logins NOT LIKE '%:%'";
    $excluded = isset($GLOBALS['excludedobjecttypesinsearchresults']) ? (string) $GLOBALS['excludedobjecttypesinsearchresults'] : '';
    foreach (array('p', 'i', 'f', 'a', 'd', 'v', 'y', 'e') as $type) {
        if (strpos($excluded, $type) !== false) $where .= " AND typ!='" . $type . "'";
    }
    /* This is trusted, administrator-supplied SQL, as in the previous engine. */
    if (!empty($GLOBALS['searchenginesqladditionalbooland'])) $where .= ' ' . $GLOBALS['searchenginesqladditionalbooland'];
    return $where;
}

function u5SearchRowProtected($row, $policy = array()) {
    /* Match login.inc.php: the opt-out only opens intranet-prefixed resources. */
    if (!empty($policy['openIntranet']) && strpos((string) ($row['name'] ?? ''), '!') === 0) return false;
    return strpos((string) ($row['logins'] ?? ''), ':') !== false
        || (isset($row['name']) && is_file(__DIR__ . '/r/' . $row['name'] . '/.htaccess'));
}

function u5SearchRowTitle($row, $slot) {
    $value = (string) ($row['title_' . $slot] ?? '');
    if (trim($value) === '') {
        for ($i = 1; $i <= 5; $i++) {
            if (trim((string) ($row['title_' . $i] ?? '')) !== '') {
                $value = (string) $row['title_' . $i];
                break;
            }
        }
    }
    if (trim($value) === '') return (string) $row['name'];
    /* Titles follow ehtml() in u5sys.meta.php: only numeric references are
       interpreted; named entity-looking strings are visible literal text. */
    $title = mb_convert_encoding($value, 'UTF-8', 'WINDOWS-1252');
    return preg_replace_callback('/&#(?:[xX][0-9a-fA-F]+|[0-9]+);/', function ($match) {
        return html_entity_decode($match[0], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }, $title);
}

function u5SearchSortResults(&$rows) {
    usort($rows, function ($a, $b) {
        if ($a['matchedTerms'] !== $b['matchedTerms']) return $b['matchedTerms'] <=> $a['matchedTerms'];
        if ($a['score'] !== $b['score']) return $b['score'] <=> $a['score'];
        if ($a['lastmut'] !== $b['lastmut']) return $b['lastmut'] <=> $a['lastmut'];
        return strcmp($a['name'], $b['name']);
    });
}

function u5SearchFind($query, $slot, $policy, $page = 1, $pageSize = 25, $refreshed = false) {
    $empty = array('error' => null, 'total' => 0, 'exactTotal' => 0, 'partial' => false, 'page' => 1, 'pages' => 1, 'rows' => array(), 'suggestions' => array());
    if ($query['error'] !== null || !$query['terms']) return $empty;
    $where = u5SearchWhere($slot, $policy);
    $sql = 'SELECT name,typ,logins,lastmut,title_1,title_2,title_3,title_4,title_5,search_' . (int) $slot . ' AS search_text FROM resources WHERE ' . $where;
    $result = mysql_unbuffered_query($sql);
    if ($result === false) { $empty['error'] = 'unavailable'; return $empty; }
    $exact = array();
    $partial = array();
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $protected = u5SearchRowProtected($row, $policy);
        if ($protected && !$policy['findProtected']) continue;
        $title = u5SearchRowTitle($row, $slot);
        $body = u5SearchFromLegacy((string) $row['search_text']);
        $match = u5SearchEvaluate($title, $body, $query['terms']);
        if ($match['matchedTerms'] === 0) continue;
        /* Retain only small result metadata; bodies are fetched for one page. */
        $hit = array(
            'name' => $row['name'], 'typ' => $row['typ'], 'title' => $title,
            'protected' => $protected, 'preview' => !$protected || $policy['previewProtected'],
            'score' => $match['score'], 'lastmut' => (int) $row['lastmut'],
            'matchedTerms' => $match['matchedTerms'], 'bodyMatches' => $match['bodyMatches'],
        );
        if ($match['matched']) $exact[] = $hit;
        else $partial[] = $hit;
    }
    mysql_free_result($result);
    $empty['exactTotal'] = count($exact);
    $rows = $exact ? $exact : $partial;
    $empty['partial'] = !$exact && count($partial) > 0;
    u5SearchSortResults($rows);
    $empty['total'] = count($rows);
    $pageSize = max(5, min(100, (int) $pageSize));
    $empty['pages'] = max(1, (int) ceil(count($rows) / $pageSize));
    $empty['page'] = max(1, min($empty['pages'], (int) $page));
    $selected = array_slice($rows, ($empty['page'] - 1) * $pageSize, $pageSize);
    $eligibilityChanged = false;
    if ($selected) {
        $names = array();
        foreach ($selected as $hit) $names[] = "'" . mysql_real_escape_string($hit['name']) . "'";
        $bodies = array();
        if ($names) {
            /* Repeat the policy in case a resource changed during this request. */
            $details = mysql_query('SELECT name,logins,search_' . (int) $slot . ' AS search_text FROM resources WHERE ' . $where . ' AND name IN (' . implode(',', $names) . ')');
            if ($details === false) { $empty['error'] = 'unavailable'; return $empty; }
            while ($row = mysql_fetch_array($details, MYSQL_ASSOC)) {
                $protected = u5SearchRowProtected($row, $policy);
                if ($protected && !$policy['findProtected']) continue;
                $canPreview = !$protected || $policy['previewProtected'];
                $bodies[$row['name']] = array('protected' => $protected, 'preview' => $canPreview, 'body' => $canPreview ? u5SearchFromLegacy((string) $row['search_text']) : '');
            }
            mysql_free_result($details);
        }
        $verified = array();
        foreach ($selected as $hit) {
            $detail = $bodies[$hit['name']] ?? null;
            if ($detail === null) continue;
            $hit['protected'] = $detail['protected'];
            $hit['preview'] = $detail['preview'];
            $hit['body'] = $hit['preview'] ? $detail['body'] : '';
            $verified[] = $hit;
        }
        if (count($verified) !== count($selected)) {
            /* Refresh counts and pagination once after a concurrent eligibility change. */
            if (!$refreshed) return u5SearchFind($query, $slot, $policy, $page, $pageSize, true);
            $eligibilityChanged = true;
            $removed = count($selected) - count($verified);
            $empty['total'] -= $removed;
            if (!$empty['partial']) $empty['exactTotal'] -= $removed;
            $empty['pages'] = max(1, (int) ceil($empty['total'] / $pageSize));
            $empty['page'] = min($empty['page'], $empty['pages']);
        }
        $selected = $verified;
    }
    $empty['rows'] = $selected;
    if ($empty['exactTotal'] === 0 && !$eligibilityChanged) $empty['suggestions'] = u5SearchSuggest($query, $slot, $policy, $where);
    return $empty;
}

/* Unicode optimal-string-alignment distance: an adjacent transposition is one
   typing error, including in short words. Only the band within the edit limit
   is evaluated, avoiding full quadratic work for every candidate word. */
function u5SearchEditDistance($left, $right, $limit) {
    $a = preg_split('//u', $left, -1, PREG_SPLIT_NO_EMPTY);
    $b = preg_split('//u', $right, -1, PREG_SPLIT_NO_EMPTY);
    $aLength = count($a);
    $bLength = count($b);
    $outside = $limit + 1;
    if (abs($aLength - $bLength) > $limit) return $outside;
    $previous = range(0, min($bLength, $limit));
    $beforePrevious = array();
    for ($i = 1; $i <= $aLength; $i++) {
        $current = array(0 => $i <= $limit ? $i : $outside);
        $minimum = $current[0];
        $end = min($bLength, $i + $limit);
        for ($j = max(1, $i - $limit); $j <= $end; $j++) {
            $current[$j] = min(($current[$j - 1] ?? $outside) + 1, ($previous[$j] ?? $outside) + 1,
                ($previous[$j - 1] ?? $outside) + ($a[$i - 1] === $b[$j - 1] ? 0 : 1));
            if ($i > 1 && $j > 1 && $a[$i - 1] === $b[$j - 2] && $a[$i - 2] === $b[$j - 1]) {
                $current[$j] = min($current[$j], ($beforePrevious[$j - 2] ?? $outside) + 1);
            }
            $minimum = min($minimum, $current[$j]);
        }
        if ($minimum > $limit) return $outside;
        $beforePrevious = $previous;
        $previous = $current;
    }
    return $previous[$bLength] ?? $outside;
}

/* Stream tokens within a shared scan budget, including repeated words. */
function u5SearchSuggestionWords($text, &$remainingBytes, &$remainingTokens) {
    if ($remainingBytes <= 0 || $remainingTokens <= 0) return;
    $bytes = strlen($text);
    if ($bytes > $remainingBytes) {
        $text = mb_strcut($text, 0, $remainingBytes, 'UTF-8');
        /* A cut-off word is not a spelling suggestion. */
        $text = preg_replace('/[\p{L}\p{M}\p{N}_]+$/u', '', $text);
    }
    $remainingBytes -= min($bytes, $remainingBytes);
    $offset = 0;
    while ($remainingTokens > 0 && preg_match('/[\p{L}\p{N}][\p{L}\p{M}\p{N}_]*/u', $text, $match, PREG_OFFSET_CAPTURE, $offset)) {
        $offset = $match[0][1] + strlen($match[0][0]);
        $remainingTokens--;
        yield $match[0][0];
    }
}

function u5SearchSuggest($query, $slot, $policy, $where) {
    $state = u5SearchSuggestionState($query);
    if (!u5SearchSuggestionCanScan($state)) return array();
    $result = mysql_unbuffered_query('SELECT name,logins,title_1,title_2,title_3,title_4,title_5,search_' . (int) $slot . ' AS search_text FROM resources WHERE ' . $where . ' ORDER BY name ASC');
    if ($result === false) return array();
    while (u5SearchSuggestionCanScan($state) && ($row = mysql_fetch_array($result, MYSQL_ASSOC))) {
        if (u5SearchRowProtected($row, $policy) && (!$policy['findProtected'] || !$policy['previewProtected'])) continue;
        u5SearchSuggestionCollect($state, u5SearchRowTitle($row, $slot), u5SearchFromLegacy((string) $row['search_text']), (string) $row['name']);
    }
    mysql_free_result($result);
    $candidates = u5SearchSuggestionCandidates($state);
    if (!$candidates) return array();

    /* Recheck the bounded set of witness resources before showing their words.
       A vocabulary scan must not expose a resource made private in the meantime.
       Streaming this lookup keeps memory independent of the witness body sizes. */
    $witnesses = array();
    $parsed = array();
    foreach ($candidates as $index => $candidate) {
        $parsed[$index] = u5SearchParseQuery($candidate['query']);
        if ($parsed[$index]['error'] !== null) continue;
        foreach ($candidate['sources'] as $name) {
            if ($name === '') continue;
            $witnesses[$name][] = $index;
        }
    }
    if (!$witnesses) return array();
    $names = array();
    foreach ($witnesses as $name => $unused) $names[] = "'" . mysql_real_escape_string((string) $name) . "'";
    $verified = array();
    $details = mysql_unbuffered_query('SELECT name,logins,title_1,title_2,title_3,title_4,title_5,search_' . (int) $slot . ' AS search_text FROM resources WHERE ' . $where . ' AND name IN (' . implode(',', $names) . ')');
    if ($details === false) return array();
    while ($row = mysql_fetch_array($details, MYSQL_ASSOC)) {
        $name = (string) $row['name'];
        if (!isset($witnesses[$name])) continue;
        clearstatcache(true, __DIR__ . '/r/' . $name . '/.htaccess');
        if (u5SearchRowProtected($row, $policy) && (!$policy['findProtected'] || !$policy['previewProtected'])) continue;
        $title = u5SearchRowTitle($row, $slot);
        $body = u5SearchFromLegacy((string) $row['search_text']);
        foreach ($witnesses[$name] as $index) {
            if (!isset($verified[$index]) && u5SearchEvaluate($title, $body, $parsed[$index]['terms'])['matched']) $verified[$index] = true;
        }
    }
    mysql_free_result($details);
    $suggestions = array();
    foreach ($candidates as $index => $candidate) {
        if (!isset($verified[$index])) continue;
        $suggestions[] = $candidate['query'];
        if (count($suggestions) === 3) break;
    }
    return $suggestions;
}
