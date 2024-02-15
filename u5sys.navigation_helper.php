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

function navGetVisibilityAttrib($context, $activeUl = null) {
    if (empty($context)) {
        return '';
    }
    if (!is_null($activeUl) && !$activeUl) {
        return ' style="display:none"';
    }
    return (navIsActiveLine($context)) ? '' : ' style="display:none"';
}

function navRenderLink($id, $title, $isActive = false) {
    global $wasActive;

    // either id matches current page or isActive overriden from outside
    // the later is a specialty of layer 1 of the navigation
    $class = ($_GET['c'] == $id || $isActive) ? ' class="activeItem"' : '';
    $wasActive = ($class == '') ? false : true;
    $link = '<a' . $class . ' href="index.php?c=' . $id . '&amp;l=' . $_GET['l'] . '">';
    $link.= render($title);
    $link.= '</a>';

    return $link;
}


