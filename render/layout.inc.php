<?php
/** Explicit, request-local presentation. Standalone render() callers stay legacy. */
function u5RenderUseTemplate($template) {
    $template = preg_replace('/<!--.*?-->/s', '', $template);
    $responsive = preg_match('/<body\b[^>]*\bdata-u5-layout\s*=\s*([\'"])responsive\1/i', $template) === 1;
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
