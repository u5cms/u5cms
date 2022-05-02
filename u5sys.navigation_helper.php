<?php

function navGetLevel(string $navitem) {
    $matches = array();
    $res = preg_match('/(#+)\[/', $navitem, $matches);

    if ($res === false) {
        die('Not a navitem: ' . $navitem);
    }

    return strlen($matches[1]);
}

function navGetPageId(string $navitem) { $matches = array();
    $res = preg_match('/:\s*([^:]*)\s*]/', $navitem, $matches);

    if ($res === false) {
        die('Not a navitem: ' . $navitem);
    }

    return $matches[1];
}

function navGetPageTitle(string $navitem) {
    $matches = array();
    $res = preg_match('/\[\s*(.*)\s*:/', $navitem, $matches);

    if ($res === false) {
        die('Not a navitem: ' . $navitem);
    }

    return $matches[1];
}

function navIsActiveLine($context) {
    $needle = ',' . $_GET['c'] . ',';

    return (strpos($context, $needle) === false) ? false : true;
}

function navGetVisibilityAttrib($context) {
    if (empty($context)) {
        return '';
    }
    return (navIsActiveLine($context)) ? '' : ' style="display:none"';
}

function navRenderLink($id, $title, $isActive = false) {
    // either id matches current page or isActive overriden from outside
    // the later is a specialty of layer 1 of the navigation
    $class = ($_GET['c'] == $id || $isActive) ? ' class="activeItem"' : '';
    $link = '<a' . $class . ' href="index.php?c=' . $id . '&amp;l=' . $_GET['l'] . '">';
    $link.= render($title);
    $link.= '</a>';

    return $link;
}


