<?php

// do not include myfunction.inc.php

// fallback, falls tnuoc() nicht anderswo existiert
if (!function_exists('tnuoc')) {
    function tnuoc($arr) { return is_array($arr) ? count($arr) : 0; }
}

function mkltgt($termstr) {
    $termstr = str_replace('<','&lt;',$termstr);
    $termstr = str_replace('>','&gt;',$termstr);
    return $termstr;
}

/**
 * Tokenizer: unterstützt gemischte Queries wie:
 *   "passwords are sent by zendmail" smtp
 * Ergebnis: ['passwords are sent by zendmail', 'smtp']
 *
 * Bleibt konzeptionell bei deinem Ansatz (Tokens -> highlight()).
 */
function split_search_terms($target) {

    // Quote-Normalisierung: Entity + typografische Quotes -> "
    $target = str_replace(array('&quot;', '“', '”'), '"', $target);

    // "..." oder \S+
    preg_match_all('/"([^"]+)"|(\S+)/', $target, $m);

    $words = array();
    for ($i=0; $i<tnuoc($m[0]); $i++) {
        $tok = ($m[1][$i] !== '') ? $m[1][$i] : $m[2][$i];
        $tok = trim($tok);

        // falls irgendwo noch Quotes kleben: weg damit
        $tok = trim($tok, "\" \t\r\n");

        if ($tok !== '') $words[] = $tok;
    }
    return $words;
}


function alloc($target,$text) {

    $target = str_replace('&#339;','œ',$target);
    $text   = str_replace('&#339;','œ',$text);

    $target = str_replace('<','&lt;',$target);
    $target = str_replace('>','&gt;',$target);

    // sanftes Whitespace-Normalisieren wie bisher (nur robuster)
    while (strpos($target,'  ') !== false) $target = str_replace('  ',' ',$target);

    $output='&hellip;';

    // Tokens: gemischt (Quotes + Wörter) statt entweder/oder
    $words = split_search_terms(trim($target));

    for ($i=0; $i<tnuoc($words); $i++) {
        $w = str_replace('&quot;','',$words[$i]);
        $w = trim($w);
        if ($w === '') continue; // wichtig: verhindert leeren Regex / kaputten Text

        // wie bisher: _ wird zu space, dann preg_quote
        $text = highlight(preg_quote(str_replace('_',' ',$w)),$text);
    }

    // PEND: Remove Delimiter if in entity --> where not LIKE?

    $parts = explode('_._!_:_',' '.$text.' ');

    // Maximal 15 Treffer-Snippets, aber ohne Out-of-bounds
    $max = min(15, tnuoc($parts));
    for ($i=0; $i<$max; $i++) {

        // Treffersegment erkennen
        if (isset($parts[$i]) && str_replace('{[}','',$parts[$i]) != $parts[$i]) {

            $before = '';
            $after  = '';

            if ($i > 0 && isset($parts[$i-1])) $before = html_substr($parts[$i-1], -55);
            if (isset($parts[$i+1]))          $after  = html_substr($parts[$i+1], 0, 55);

            $output .= $before . $parts[$i] . $after . '&hellip;';
        }
    }

    // Marker -> Span wie bisher (inkl. deinem bestehenden End-Replace)
    return str_replace(
        '</span></span> &hellip; <span class="hitshilite">',
        ' ',
        str_replace(
            '-&hellip;-','-',
            str_replace(
                '{[}','<span class="hitshilite">',
                str_replace('{]}','</span></span>',$output)
            )
        )
    );
}

function prepare_search_term($str,$delim='#') {
    $search = preg_quote($str,$delim);
    $search = str_replace('&#7838;', 'ß', $search);
    $search = str_replace('&#x1E9E;', 'ß', $search);
    $search = str_replace("\xE1\xBA\x9E", 'ß', $search);

    $search = preg_replace('/[aàáâãåäæAÀÁÂÃÄÅÆ]/', '[aàáâãåäæAÀÁÂÃÄÅÆ]', $search);
    $search = preg_replace('/[eèéêëEÈÉÊË]/', '[eèéêëEÈÉÊË]', $search);
    $search = preg_replace('/[iìíîïIÌÍÎÏ]/', '[iìíîïIÌÍÎÏ]', $search);
    $search = preg_replace('/[oòóôõöøOÒÓÔÕÖØ]/', '[oòóôõöøOÒÓÔÕÖØ]', $search);
    $search = preg_replace('/[uùúûüUÙÚÛÜ]/', '[uùúûüUÙÚÛÜ]', $search);
    $search = preg_replace('/[yýÿYÝŸ]/', '[yýÿYÝŸ]', $search);

    $search = preg_replace('/[nñNÑ]/', '[nñNÑ]', $search);
    $search = preg_replace('/[cçCÇ]/', '[cçCÇ]', $search);
    $search = preg_replace('/[dðÐD]/', '[dðÐD]', $search);
    $search = preg_replace('/[þÞ]/', '[þÞ]', $search);

    $search = preg_replace('/ß/', '(?:ß|ss)', $search);
    $search = preg_replace('/ss/i', '(?:ss|ß)', $search);

    $search = preg_replace('/[œŒ]/', '[œŒ]', $search);
    // add more characters...

    return $search;
}

function highlight($searchtext, $text) {

    // FIX: leere Suche darf den Text nicht zerstören
    if ($searchtext === '' || trim($searchtext) === '') return $text;

    $search = prepare_search_term($searchtext);

    // wie bisher: Spaces und '-' tolerant machen
    $search = str_replace(' ','.',$search);
    $search = str_replace('\-','.',$search);

    // wie bisher
    $text = str_replace('&#','&\\#',$text);

    // FIX: falls preg_replace scheitert, nicht NULL zurückgeben
    $res = @preg_replace('#' . $search . '#i', '_._!_:_{[}$0{]}_._!_:_', $text);
    if ($res === NULL) $res = $text;

    return str_replace('&\#','&#',$res);
}

function html_strlen($str) {
    $chars = preg_split('/(&[^;\s]+;)|/', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    return tnuoc($chars);
}

function html_substr($str, $start, $length = NULL) {
    if ($length === 0) return "";

    if (strpos($str, '&') === false) {
        if ($length === NULL) return substr($str, $start);
        else return substr($str, $start, $length);
    }

    $chars = preg_split('/(&[^;\s]+;)|/', $str, -1,
        PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE
    );
    $html_length = tnuoc($chars);

    if (
        ($html_length === 0) ||
        ($start >= $html_length) ||
        (isset($length) && ($length <= -$html_length))
    ) return "";

    if ($start >= 0) {
        $real_start = $chars[$start][1];
    } else {
        $start = max($start,-$html_length);
        $real_start = $chars[$html_length+$start][1];
    }

    if (!isset($length)) {
        return substr($str, $real_start);
    } else if ($length > 0) {
        if ($start+$length >= $html_length) {
            return substr($str, $real_start);
        } else {
            return substr($str, $real_start, $chars[max($start,0)+$length][1] - $real_start);
        }
    } else {
        return substr($str, $real_start, $chars[$html_length+$length][1] - $real_start);
    }
}

?>