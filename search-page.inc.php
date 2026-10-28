<?php
require_once __DIR__ . '/connect.inc.php';
require_once __DIR__ . '/search-ui.inc.php';
require_once __DIR__ . '/search-service.inc.php';

$query = u5SearchRequest();
$scope = !empty($u5SearchIntranet) ? '_searchsi' : '_search';
u5SearchForm(true, $scope);
if (is_file(__DIR__ . '/indexupdate.inc.php')) include __DIR__ . '/indexupdate.inc.php';
echo '<section class="u5-search-results" aria-label="' . u5SearchHtml(u5SearchLabel('results_label')) . '">';
if ($query['error'] !== null) {
    if ($query['error'] !== 'empty') echo '<p role="alert">' . u5SearchHtml(u5SearchLabel($query['error'])) . '</p>';
    echo '</section>';
    return;
}
$pageNumber = isset($_GET['sp']) && is_string($_GET['sp']) && ctype_digit($_GET['sp']) ? min(1000000, (int) $_GET['sp']) : 1;
$pageSize = isset($searchresultspagesize) ? (int) $searchresultspagesize : 25;
$found = u5SearchFind($query, u5SearchLanguageSlot(), u5SearchPolicy(!empty($u5SearchIntranet)), $pageNumber, $pageSize);
if ($found['error'] !== null) {
    echo '<p role="alert">' . u5SearchHtml(u5SearchLabel('unavailable')) . '</p></section>';
    return;
}
if ($found['partial']) {
    echo '<h2>' . u5SearchHtml(u5SearchLabel('no_results')) . ' <span id="terms">' . u5SearchHtml($query['raw']) . '</span></h2>';
    echo '<h3>' . u5SearchHtml(u5SearchLabel('partial')) . ' (' . $found['total'] . ')</h3>';
    echo '<p>' . u5SearchHtml(u5SearchLabel('partial_help')) . '</p>';
} elseif ($found['total'] > 0) {
    echo '<h2><strong>' . $found['total'] . '</strong> ' . u5SearchHtml(u5SearchLabel('results')) . ' <span id="terms">' . u5SearchHtml($query['raw']) . '</span></h2>';
} else {
    echo '<h2>' . u5SearchHtml(u5SearchLabel('no_results')) . ' <span id="terms">' . u5SearchHtml($query['raw']) . '</span></h2>';
}
if ($found['suggestions']) {
    echo '<p>' . u5SearchHtml(u5SearchLabel('suggestions')) . ' ';
    $links = array();
    foreach ($found['suggestions'] as $suggestion) $links[] = '<a href="' . u5SearchHtml(u5SearchUrl($scope, $suggestion)) . '">' . u5SearchHtml($suggestion) . '</a>';
    echo implode(' &middot; ', $links) . '</p>';
}
if ($found['exactTotal'] === 0) {
    $hasPhrase = false;
    foreach ($query['terms'] as $term) $hasPhrase = $hasPhrase || $term['phrase'];
    if ($hasPhrase) echo '<p><a href="' . u5SearchHtml(u5SearchUrl($scope, u5SearchSerializeTerms($query['terms'], false))) . '">' . u5SearchHtml(u5SearchLabel('without_quotes')) . '</a></p>';
}
foreach ($found['rows'] as $hit) {
    $url = u5SearchUrl($hit['name'], $query['raw']);
    $type = '';
    if ($hit['typ'] === 'd') {
        $row_a = array('name' => $hit['name']);
        $fbfile = '';
        include __DIR__ . '/getfile.inc.php';
        $download = def($file_1, $file_2, $file_3, $file_4, $file_5);
        if ($download !== '' && $download[0] !== '.' && is_file(__DIR__ . '/r/' . $hit['name'] . '/' . $download)) {
            $url = 'f.php?' . http_build_query(array('f' => 'r/' . $hit['name'] . '/' . $download), '', '&', PHP_QUERY_RFC3986);
            $type = strtoupper(pathinfo($download, PATHINFO_EXTENSION));
        }
    }
    $titleLimit = isset($resulttitlemaxlength) && (int) $resulttitlemaxlength > 0 ? (int) $resulttitlemaxlength : 60;
    $displayTitle = mb_substr($hit['title'], 0, $titleLimit, 'UTF-8');
    $titleHtml = u5SearchSnippet($displayTitle, $query['terms'], mb_strlen($displayTitle, 'UTF-8'), 1);
    if ($titleHtml === '') $titleHtml = u5SearchHtml($displayTitle);
    if ($displayTitle !== $hit['title']) $titleHtml .= '&hellip;';
    echo '<article class="u5-search-result"><h3><a href="' . u5SearchHtml($url) . '">';
    if ($type !== '') echo '<small>' . u5SearchHtml($type) . '</small> ';
    echo $titleHtml . '</a></h3>';
    if (!$hit['preview']) {
        echo '<p class="u5-search-protected">' . u5SearchHtml(u5SearchLabel('protected')) . '</p>';
    } else {
        $snippet = u5SearchSnippet($hit['body'], $query['terms']);
        if ($snippet !== '') echo '<p class="u5-search-excerpt">' . $snippet . '</p>';
        else {
            $titleSnippet = u5SearchSnippet($hit['title'], $query['terms']);
            if ($titleSnippet !== '') echo '<p class="u5-search-excerpt">' . u5SearchHtml(u5SearchLabel('title_only')) . ' ' . $titleSnippet . '</p>';
        }
    }
    echo '</article>';
}
if ($found['pages'] > 1) {
    echo '<nav class="u5-search-pagination" aria-label="' . u5SearchHtml(u5SearchLabel('page')) . '">';
    if ($found['page'] > 1) echo '<a rel="prev" href="' . u5SearchHtml(u5SearchUrl($scope, $query['raw'], $found['page'] - 1)) . '">' . u5SearchHtml(u5SearchLabel('previous')) . '</a> ';
    echo '<span aria-current="page">' . u5SearchHtml(u5SearchLabel('page')) . ' ' . $found['page'] . ' ' . u5SearchHtml(u5SearchLabel('of')) . ' ' . $found['pages'] . '</span>';
    if ($found['page'] < $found['pages']) echo ' <a rel="next" href="' . u5SearchHtml(u5SearchUrl($scope, $query['raw'], $found['page'] + 1)) . '">' . u5SearchHtml(u5SearchLabel('next')) . '</a>';
    echo '</nav>';
}
echo '</section>';
