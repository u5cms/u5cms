<?php
/** Explicit, request-local presentation. Standalone render() callers stay legacy. */
function u5RenderUseTemplate($template) {
    $responsive = false;
    // Consume complete tags before inspecting names/attributes. A body example
    // inside another attribute, raw-text element or inert template is not a body.
    // Possessive, disjoint tokens also handle large custom attributes without JIT
    // recursion proportional to their byte length.
    $pattern = '~<!--(?:[^-]++|-(?!->))*+(?:-->|$)|<(?:"[^"]*+"|\'[^\']*+\'|[^\'">]++)*+>~s';
    $raw = ''; $templates = 0;
    if (preg_match_all($pattern, $template ?? '', $tags) !== false) foreach ($tags[0] as $tag) {
        if (substr($tag, 0, 4) === '<!--') continue;
        if (!preg_match('~^<(/?)([a-z][a-z0-9:-]*)(?=[\s/>])~i', $tag, $name)) continue;
        $element = strtolower($name[2]); $closing = $name[1] === '/';
        if ($raw !== '') {
            if ($closing && $element === $raw) $raw = '';
            continue;
        }
        if (!$closing && in_array($element, array('script','style','textarea','title','xmp','iframe','noembed','noframes','noscript','plaintext'), true)) { $raw = $element; continue; }
        if ($element === 'template') { $templates = max(0, $templates + ($closing ? -1 : 1)); continue; }
        if ($templates || $closing || $element !== 'body') continue;
        preg_match_all('~\s+([^\s=/>]+)(?:\s*=\s*(?:"([^"]*+)"|\'([^\']*+)\'|([^\s>]+)))?~', substr($tag, strlen($name[0])), $attributes, PREG_SET_ORDER);
        foreach ($attributes as $attribute) {
            if (strtolower($attribute[1]) !== 'data-u5-layout') continue;
            $value = ($attribute[2] ?? '') . ($attribute[3] ?? '') . ($attribute[4] ?? '');
            $responsive = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'Windows-1252') === 'responsive';
            break;
        }
        break;
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

/** Responsive F-object geometry; preserve authored ratio and keep captions in flow. */
function u5RenderFreeImageStyles($width, $height, $path) {
    $legacy = (is_numeric($width) ? 'width:'.$width.'px;' : '').(is_numeric($height) ? 'height:'.$height.'px;' : '');
    if (!u5RenderIsResponsive()) return array($legacy, $legacy);
    // Explicit zero/negative/invalid numeric sizes keep their historical meaning.
    foreach (array($width, $height) as $size) if (is_numeric($size) && (!is_finite((float)$size) || $size <= 0)) return array($legacy, $legacy);
    $w = is_numeric($width) ? (float)$width : null;
    $h = is_numeric($height) ? (float)$height : null;
    if ($w === null && $h !== null) {
        $native = @getimagesize($path);
        if ($native && $native[0] > 0 && $native[1] > 0) $w = $h * $native[0] / $native[1];
    }
    $box = $w !== null ? 'width:min(100%,'.$w.'px);height:auto;' : 'width:fit-content;height:auto;';
    if ($w !== null && $h !== null) $image = u5RenderMediaStyle($w, $h, 'image');
    elseif ($w !== null) $image = 'width:min(100%,'.$w.'px);height:auto;';
    else $image = 'max-width:100%;height:auto;'.($h !== null ? 'max-height:'.$h.'px;' : '');
    return array($box, $image);
}
