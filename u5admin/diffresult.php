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
    if (preg_match('/^&(#\d+|#x[0-9a-fA-F]+|[a-zA-Z0-9]+);$/', $token)) {
        return $token;
    }
    return htmlspecialchars($token, ENT_NOQUOTES, 'ISO-8859-1');
}

function tokenizeForHtml($str) {
    preg_match_all('/(&(#\d+|#x[0-9a-fA-F]+|[a-zA-Z0-9]+);|.)/', $str, $matches);
    return $matches[0];
}

function formatLine($line) {
    return $line === '' ? '<span style="color:#bbb;">&#8629;</span>' : encodeToken($line);
}

function diffTokens($old, $new) {
    $oldTokens = tokenizeForHtml($old);
    $newTokens = tokenizeForHtml($new);

    $m = count($oldTokens);
    $n = count($newTokens);

    $lcs = array_fill(0, $m + 1, array_fill(0, $n + 1, 0));

    for ($i = $m - 1; $i >= 0; $i--) {
        for ($j = $n - 1; $j >= 0; $j--) {
            if ($oldTokens[$i] === $newTokens[$j]) {
                $lcs[$i][$j] = $lcs[$i + 1][$j + 1] + 1;
            } else {
                $lcs[$i][$j] = max($lcs[$i + 1][$j], $lcs[$i][$j + 1]);
            }
        }
    }

    $outOld = '';
    $outNew = '';
    $i = $j = 0;

    while ($i < $m && $j < $n) {
        if ($oldTokens[$i] === $newTokens[$j]) {
            $enc = encodeToken($oldTokens[$i]);
            $outOld .= $enc;
            $outNew .= $enc;
            $i++; $j++;
        } elseif ($lcs[$i + 1][$j] >= $lcs[$i][$j + 1]) {
            $outOld .= '<span style="background:#99dd99;">' . encodeToken($oldTokens[$i]) . '</span>';
            $i++;
        } else {
            $outNew .= '<span style="background:#dd9999;">' . encodeToken($newTokens[$j]) . '</span>';
            $j++;
        }
    }

    while ($i < $m) {
        $outOld .= '<span style="background:#99dd99;">' . encodeToken($oldTokens[$i]) . '</span>';
        $i++;
    }
    while ($j < $n) {
        $outNew .= '<span style="background:#dd9999;">' . encodeToken($newTokens[$j]) . '</span>';
        $j++;
    }

    return [$outOld, $outNew];
}

function findMovedLines($beforeLines, $afterLines) {
    $beforeCount = array_count_values($beforeLines);
    $afterCount = array_count_values($afterLines);

    $moved = [];

    foreach ($beforeLines as $i => $line) {
        if (!isset($afterCount[$line]) || $afterCount[$line] === 0) continue;

        foreach ($afterLines as $j => $line2) {
            if ($line === $line2 && $i !== $j && !isset($moved[$i]) && !isset($moved["after_$j"])) {
                $moved[$i] = $j;
                $moved["after_$j"] = $i;
                break;
            }
        }
    }

    return $moved;
}

function diffText($before, $after) {
    $before = normalizeNewlines($before);
    $after = normalizeNewlines($after);

    $beforeLines = splitLines($before);
    $afterLines = splitLines($after);

    $movedLines = findMovedLines($beforeLines, $afterLines);

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
            $content = formatLine($leftRaw);
            $leftHtml = $rightHtml = $content;
        } elseif (
            isset($movedLines[$i]) && $leftRaw === $afterLines[$movedLines[$i]] &&
            isset($movedLines["after_$i"]) && $rightRaw === $beforeLines[$movedLines["after_$i"]]
        ) {
            $leftHtml = formatLine($leftRaw);
            $rightHtml = formatLine($rightRaw);
            $bgLeft = $bgRight = '#f9f9e5';
        } elseif (
            isset($movedLines[$i]) && $leftRaw === $afterLines[$movedLines[$i]]
        ) {
            $leftHtml = formatLine($leftRaw);
            $rightHtml = formatLine($rightRaw ?? '');
            $bgLeft = '#f9f9e5';
            $bgRight = $rightRaw !== null ? '#ffecec' : '#f5f5f5';
        } elseif (
            isset($movedLines["after_$i"]) && $rightRaw === $beforeLines[$movedLines["after_$i"]]
        ) {
            $leftHtml = formatLine($leftRaw ?? '');
            $rightHtml = formatLine($rightRaw);
            $bgRight = '#f9f9e5';
            $bgLeft = $leftRaw !== null ? '#eaffea' : '#f5f5f5';
        } else {
            if ($leftRaw !== null && $rightRaw !== null) {
                [$leftHtml, $rightHtml] = diffTokens($leftRaw, $rightRaw);
                $bgLeft = '#eaffea';
                $bgRight = '#ffecec';
            } elseif ($leftRaw !== null) {
                $leftHtml = formatLine($leftRaw);
                $bgLeft = '#eaffea';
                $bgRight = '#f5f5f5';
                $rightHtml = '';
            } elseif ($rightRaw !== null) {
                $rightHtml = formatLine($rightRaw);
                $bgRight = '#ffecec';
                $bgLeft = '#f5f5f5';
                $leftHtml = '';
            }
        }

        $html .= "<tr>
            <td style='text-align:right; color:#888; vertical-align:top;'>$lineNumber</td>
            <td style='width:50%; padding:2px; background:$bgLeft;'>".decodeAmpBeforeHtmlEntities($leftHtml)."</td>
            <td style='text-align:right; color:#888; vertical-align:top;'>$lineNumber</td>
            <td style='width:50%; padding:2px; background:$bgRight;'>".decodeAmpBeforeHtmlEntities($rightHtml)."</td>
        </tr>";
    }

    $html .= '</table>';
    return $html;
}

echo diffText($_POST['TL'], $_POST['TR']);

function decodeAmpBeforeHtmlEntities($text) {
    return preg_replace_callback(
        '/&amp;(?=(#\d+|#x[0-9a-fA-F]+|[a-zA-Z][a-zA-Z0-9]+);)/',
        function ($match) {
            $entity = '&' . $match[1] . ';';
            if (html_entity_decode($entity, ENT_QUOTES | ENT_HTML5, 'UTF-8') !== $entity) {
                return '&';
            }
            return $match[0];
        },
        $text
    );
}
?>

</body>
</html>
