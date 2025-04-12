<?php header('Content-Type: text/html; charset=ISO-8859-1'); ?><?php require_once('connect.inc.php'); ?><!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<style>
tr{background:#f8faf8}
</style>
<title>diff</title>
<?php require('backendcss.php'); ?></head>
<body>

<?php
function normalizeNewlines($text) {
    return str_replace(["\r\n", "\r"], "\n", $text);
}

function splitLines($text) {
    return explode("\n", $text);
}

function encodeToken($token) {
    // HTML-Entitäten durchlassen, sonst escapen
    if (preg_match('/^&[#a-zA-Z0-9]+;$/', $token)) {
        return $token;
    }
    return htmlspecialchars($token, ENT_NOQUOTES, 'ISO-8859-1');
}

function tokenizeForHtml($str) {
    // Trennt in vollständige HTML-Entitäten und sonst einzelne Zeichen
    preg_match_all('/(&[a-zA-Z#0-9]+;|.)/', $str, $matches);
    return $matches[0];
}

function formatLine($line) {
    return $line === '' ? '<span style="color:#bbb;">&#8629;</span>' : encodeToken($line);
}

function diffTokens($old, $new) {
    $oldTokens = tokenizeForHtml($old);
    $newTokens = tokenizeForHtml($new);

    $outOld = '';
    $outNew = '';
    $max = max(count($oldTokens), count($newTokens));

    for ($i = 0; $i < $max; $i++) {
        $o = $oldTokens[$i] ?? null;
        $n = $newTokens[$i] ?? null;

        if ($o === $n) {
            $outOld .= encodeToken($o);
            $outNew .= encodeToken($n);
        } else {
            if ($o !== null) {
                $outOld .= '<span style="background:#99dd99;">' . encodeToken($o) . '</span>';
            }
            if ($n !== null) {
                $outNew .= '<span style="background:#dd9999;">' . encodeToken($n) . '</span>';
            }
        }
    }

    return [$outOld, $outNew];
}

function diffText($before, $after) {
    $before = normalizeNewlines($before);
    $after = normalizeNewlines($after);

    $beforeLines = splitLines($before);
    $afterLines = splitLines($after);

    $maxLines = max(count($beforeLines), count($afterLines));
    $html = '<table style="width:100%; font-family:monospace;">';

    for ($i = 0; $i < $maxLines; $i++) {
        $lineNumber = $i + 1;

        $leftExists = array_key_exists($i, $beforeLines);
        $rightExists = array_key_exists($i, $afterLines);

        $leftRaw = $leftExists ? $beforeLines[$i] : null;
        $rightRaw = $rightExists ? $afterLines[$i] : null;

        $leftHtml = '';
        $rightHtml = '';
        $bgLeft = '';
        $bgRight = '';

        if ($leftRaw === $rightRaw) {
            $content = $leftRaw === '' ? '<span style="color:#bbb;">&#8629;</span>' : encodeToken($leftRaw);
            $leftHtml = $rightHtml = $content;
        } else {
            if ($leftRaw !== null && $rightRaw !== null) {
                [$leftHtml, $rightHtml] = diffTokens($leftRaw, $rightRaw);
                $bgLeft = $leftRaw === '' ? '#f5f5f5' : '#eaffea';
                $bgRight = $rightRaw === '' ? '#f5f5f5' : '#ffecec';
            } elseif ($leftRaw !== null) {
                $leftHtml = $leftRaw === '' ? '<span style="color:#bbb;">&#8629;</span>' : encodeToken($leftRaw);
                $bgLeft = '#eaffea';
                $bgRight = '#f5f5f5';
                $rightHtml = '';
            } elseif ($rightRaw !== null) {
                $rightHtml = $rightRaw === '' ? '<span style="color:#bbb;">&#8629;</span>' : encodeToken($rightRaw);
                $bgRight = '#ffecec';
                $bgLeft = '#f5f5f5';
                $leftHtml = '';
            }
        }

        $html .= "<tr>
            <td style='text-align:right; color:#888; vertical-align:top;'>$lineNumber</td>
            <td style='width:50%; padding:2px; background:$bgLeft;'>$leftHtml</td>
            <td style='text-align:right; color:#888; vertical-align:top;'>$lineNumber</td>
            <td style='width:50%; padding:2px; background:$bgRight;'>$rightHtml</td>
        </tr>";
    }



    $html .= '</table>';
    return $html;
}

echo diffText($_POST['TL'], $_POST['TR']);



?>


</body>
</html>