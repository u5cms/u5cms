<?php
/** Explicit, request-local presentation. Standalone render() callers stay legacy. */
function u5RenderUseTemplate($template) {
    $responsive = false;
    // Ignore examples in comments/scripts and tokenize attributes, so a quoted
    // attribute value or data-example-data-u5-layout never becomes an opt-in.
    $template = preg_replace('~<!--.*?-->|<(script|style)\b[^>]*>.*?</\1\s*>~is', '', $template ?? '');
    if ($template !== null && preg_match('~<body\b((?:[^>\'"]|"[^"]*"|\'[^\']*\')*)>~i', $template, $body) === 1) {
        preg_match_all('~\s+([^\s=/>]+)(?:\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+)))?~', $body[1], $attributes, PREG_SET_ORDER);
        foreach ($attributes as $attribute) {
            if (strtolower($attribute[1]) !== 'data-u5-layout') continue;
            $responsive = ($attribute[2] ?? '') === 'responsive' || ($attribute[3] ?? '') === 'responsive' || ($attribute[4] ?? '') === 'responsive';
            break;
        }
    }
    $GLOBALS['u5RenderResponsive'] = $responsive;
    return $responsive;
}

function u5RenderIsResponsive() {
    return ($GLOBALS['u5RenderResponsive'] ?? false) === true;
}

/** Preserve configured dimensions as preferred sizes; only shrink to fit. */
function u5RenderMediaStyle($width, $height, $kind) {
    $legacy = 'width:'.$width.'px;height:'.$height.'px';
    if (!u5RenderIsResponsive() || !is_numeric($width) || !is_numeric($height) || $width <= 0 || $height <= 0) return $legacy;
    $style = 'width:min(100%,'.(float)$width.'px);';
    if ($kind === 'audio') return $style.'height:'.(float)$height.'px';
    return $style.'height:auto;aspect-ratio:'.(float)$width.'/'.(float)$height;
}

/** Only renderer-owned video frames get a ratio; arbitrary [h:] HTML is untouched. */
function u5RenderFrameAttributes($width, $height) {
    if (!u5RenderIsResponsive() || !is_numeric($width) || !is_numeric($height) || $width <= 0 || $height <= 0) return '';
    return ' class="u5-video-frame" style="'.u5RenderMediaStyle($width, $height, 'video').'"';
}
