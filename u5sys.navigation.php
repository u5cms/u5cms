<?php

$debug = ($_GET['debug']) ?? false;
$naviecho = '';

require_once('connect.inc.php');
require_once('render.inc.php');
require_once('u5sys.navigation_helper.php');

$sql_b    = "SELECT * FROM inserts WHERE source1 NOT like '[' AND source1 NOT like '[h:]' ORDER BY length(source1) DESC";
$result_b = mysql_query($sql_b);
if ($result_b == false) {
    $naviecho .= 'SQL_b-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_b . '</font><p>';
}
$num_b = mysql_num_rows($result_b);
if (!isset($_GET['c']) || $_GET['c'] == '') {
    $sql_a    = "SELECT * FROM resources WHERE deleted!=1 AND ishomepage=1";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) {
        $naviecho .= 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
    }
    $row_a     = mysql_fetch_array($result_a);
    $_GET['c'] = $row_a['name'];
}
$sql_a    = "SELECT * FROM resources WHERE deleted!=1 AND name='navigation'";
$result_a = mysql_query($sql_a);
if ($result_a == false) {
    $naviecho .= 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
}
$row_a  = mysql_fetch_array($result_a);

////////////////////////////////////////////////////////////////////////////////////////////
$string = def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5']);

if ($debug) echo "<pre>", var_dump($string), "</pre>";

$matches = array();
$res = preg_match_all('/#*\[.*:.*\]/', $string, $matches);
$navitems = $matches[0];

if ($debug) echo "<pre>\n", var_dump($navitems), "</pre>\n";

$z1 = $z2 = $z3 = $z4 = 0;
$pack1 = $pack2 = $pack3 = $pack4 = array();

foreach ($navitems as $navitem) {
    // Inspect current navigation item
    $currentLevel = navGetLevel($navitem);
    $pageId = navGetPageId($navitem);

    if ($currentLevel == 1) { // if level 1
        $z1++;
        $z2 = $z3 = $z4 = 0;

        $pack1[$z1] = ',' . $pageId . ',';
    } elseif ($currentLevel == 2) { // if level 2
        $z2++;
        $z3 = $z4 = 0;

        $pack1[$z1] .= ',' . $pageId . ',';
        $pack2[$z1 . $z2] = ',' . $pageId . ',';
    } elseif ($currentLevel == 3) { // if level 3
        $z3++;
        $z4 = 0;

        $pack1[$z1] .= ',' . $pageId . ',';
        $pack2[$z1 . $z2] .= ',' . $pageId . ',';
        $pack3[$z1 . $z2 . $z3] = ',' . $pageId . ',';
    } elseif ($currentLevel = 4) { // if level 4
        $z4++;

        $pack1[$z1] .= ',' . $pageId . ',';
        $pack2[$z1 . $z2] .= ',' . $pageId . ',';
        $pack3[$z1 . $z2 . $z3] .= ',' . $pageId . ',';
        $pack4[$z1 . $z2 . $z3 . $z4] = ',' . $pageId . ',';
    }
}

if ($debug) echo "<pre>\n", var_dump($pack1), var_dump($pack2), var_dump($pack3), var_dump($pack4), "</pre>\n";

////////////////////////////////////////////////////////////////////////////////////////////
// $string       = def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5']);
// $string       = explode('<br />', nl2br($string));
$z1 = $z2 = $z3 = $z4 = 0;
$oldlevel = 0;
$oldz1 = $oldz2 = $oldz3  = 0;
$context = '';

// for ($i = 0; $i < tnuoc($string); $i++) {
$naviecho = "";
foreach ($navitems as $navitem) {
    // Inspect current navigation item
    $currentLevel = navGetLevel($navitem);
    $pageId = navGetPageId($navitem);
    $pageTitle = navGetPageTitle($navitem);

    if ($debug) echo "Working on " . $pageId . " (" . $pageTitle . ") " . $currentLevel . " | ". $oldlevel . "\n";

    // when we go down, close previous start a new unordered list(s)
    // visibility dependent on current list item context
    if ($currentLevel > $oldlevel) {
        for ($i=$currentLevel-$oldlevel; $i>0; $i--) {
            $naviecho .= "\n<ul" . navGetVisibilityAttrib($context) . ">\n";
        }
    } elseif ($currentLevel < $oldlevel) {
        // when we go up again, close last list item and close unordered list(s)
        for ($i=$oldlevel-$currentLevel; $i>0; $i--) {
            $naviecho .= "</li>\n</ul>\n</li>\n";
        }
    } else {
        // if on same level, just close last list item but not on the first run, when
        $naviecho .= "</li>\n";
    }

    if ($currentLevel == 1) {
        $z1++;
        $z2 = $z3 = $z4 = 0;
        $oldz2 = $oldz3 = 0;
        $context = $pack1[$z1];

        $isActive = navIsActiveLine($context);
        $activeitem = $isActive ? ' class="active"' : ' class="inactive"';

        $naviecho .= '<li' . $activeitem . '>';
        $naviecho .= navRenderLink($pageId, $pageTitle, $isActive );
        // list item get closed at the top of the foreach

    } elseif ($currentLevel == 2) {
        $z2++;
        $z3 = $z4 = 0;
        $context = $pack1[$z1];

        $naviecho .= '<li' . navGetVisibilityAttrib($context) . '>';
        $naviecho .= navRenderLink($pageId, $pageTitle, navIsActiveLine($pack2[$z1 . $z2]));
        // list item get closed at the top of the foreach
        $oldz1 = $z1;

    } elseif ($currentLevel == 3) {
        $z3++;
        $z4 = 0;
        $context = $pack2[$z1 . $z2];

        $naviecho .= '<li' . navGetVisibilityAttrib($context) . '>';
        $naviecho .= navRenderLink($pageId, $pageTitle, navIsActiveLine($pack3[$z1 . $z2 . $z3]));
        // list item get closed at the top of the foreach
        $oldz2 = $z2;

    } elseif ($currentLevel == 4) {
        $z4++;
        $context = $pack3[$z1 . $z2 . $z3];

        $naviecho .= '<li' . navGetVisibilityAttrib($context) . '>';
        $naviecho .= navRenderLink($pageId, $pageTitle, navIsActiveLine($pack4[$z1 . $z2 . $z3 . $z4]));
        // list item get closed at the top of the foreach
        $oldz3 = $z3;
    }

    $oldlevel = $currentLevel;
}

// Add finally close last list item and last unsorted list tag
$naviecho .= "</li>\n</ul>\n";

echo $naviecho;
