<?php
require_once __DIR__.'/request.inc.php';
require_once __DIR__.'/render/layout.inc.php';
require_once __DIR__.'/render/markup.inc.php';
require_once __DIR__.'/render/resources.inc.php';
require_once __DIR__.'/render/output.inc.php';
require_once __DIR__.'/render/dat.inc.php';

/**
 * Stable public API for frontend, previews, forms, mail and custom integrations.
 * Presentation is selected by the page template, never by screen/UA detection.
 * The syntax and dat stages deliberately share one implementation in both modes.
 */
function render($stringa) {
    global $mymail, $scripturi;
    $stringa = trim($stringa ?? '');
    require __DIR__.'/render.superglobals.inc.php';
    return u5RenderOutput(u5RenderResources(u5RenderMarkup($stringa)));
}
