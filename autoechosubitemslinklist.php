<?php

$debug = ($_GET['debug']) ?? false;
$subnaviecho = '';

require_once('connect.inc.php');
require_once('render.inc.php');
require_once('u5sys.navigation_helper.php');

if (!isset($_GET['c']) || $_GET['c'] == '') {
    $sql_a    = "SELECT * FROM resources WHERE deleted!=1 AND ishomepage=1";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) {
        $subnaviecho .= 'SQL_a-Query failed!...!<p>';
    }
    $row_a     = mysql_fetch_array($result_a);
    $_GET['c'] = $row_a['name'];
}

$sql_a    = "SELECT * FROM resources WHERE deleted!=1 AND name='navigation'";
$result_a = mysql_query($sql_a);
if ($result_a == false) {
    $subnaviecho .= 'SQL_a-Query failed!...!<p>';
}
$row_a  = mysql_fetch_array($result_a);

$string = def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5']);

if ($debug) {
    echo "<pre>", var_dump($string), "</pre>";
}

$matches = [];
$res = preg_match_all('/#*\[.*:.*\]/', $string, $matches);
$navitems = $matches[0];

if ($debug) {
    echo "<pre>\n", var_dump($navitems), "</pre>\n";
}

$currentIndex = null;
$currentLevel = null;

foreach ($navitems as $index => $navitem) {
    if (preg_match('/:\\s*' . preg_quote($_GET['c'], '/') . '\\s*\\]\\s*$/', $navitem)) {
        $currentIndex = $index;
        $currentLevel = navGetLevel($navitem);
        break;
    }
}

if ($currentIndex === null) {
    echo '';
    return;
}

$targetLevel = $currentLevel + 1;
$children = [];

for ($i = $currentIndex + 1; $i < count($navitems); $i++) {
    $navitem = $navitems[$i];
    $level = navGetLevel($navitem);

    if ($level <= $currentLevel) {
        break;
    }

    if ($level == $targetLevel) {
        $children[] = [
            'id' => navGetPageId($navitem),
            'title' => navGetPageTitle($navitem),
        ];
    }
}

if (empty($children)) {
    echo '';
    return;
}

$listitems = '';
foreach ($children as $child) {
    if (!empty(trim($child['title']))) {
        $listitems .= '<li>';
        $listitems .= navRenderLink($child['id'], $child['title']);
        $listitems .= "</li>\n";
    }
}

if (!empty($listitems)) {
    $subnaviecho .= "<div class=\"autoechosubitemslinklist\">\n<ul>\n";
    $subnaviecho .= $listitems;
    $subnaviecho .= "</ul>\n</div>\n";
}

echo $subnaviecho;
