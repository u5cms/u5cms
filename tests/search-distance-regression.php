<?php
/* Independent full-matrix oracle for the bounded Unicode distance algorithm. */
require_once dirname(__DIR__) . '/search-service.inc.php';

function distanceReference($left, $right) {
    $left = preg_split('//u', $left, -1, PREG_SPLIT_NO_EMPTY);
    $right = preg_split('//u', $right, -1, PREG_SPLIT_NO_EMPTY);
    $matrix = array();
    for ($i = 0; $i <= count($left); $i++) $matrix[$i] = array($i);
    for ($j = 1; $j <= count($right); $j++) $matrix[0][$j] = $j;
    for ($i = 1; $i <= count($left); $i++) {
        for ($j = 1; $j <= count($right); $j++) {
            $matrix[$i][$j] = min($matrix[$i - 1][$j] + 1, $matrix[$i][$j - 1] + 1,
                $matrix[$i - 1][$j - 1] + ($left[$i - 1] === $right[$j - 1] ? 0 : 1));
            if ($i > 1 && $j > 1 && $left[$i - 1] === $right[$j - 2] && $left[$i - 2] === $right[$j - 1]) {
                $matrix[$i][$j] = min($matrix[$i][$j], $matrix[$i - 2][$j - 2] + 1);
            }
        }
    }
    return $matrix[count($left)][count($right)];
}

$checks = 0;
function distanceCheck($left, $right) {
    global $checks;
    $expected = distanceReference($left, $right);
    foreach (array(0, 1, 2) as $limit) {
        $actual = u5SearchEditDistance($left, $right, $limit);
        if (($expected <= $limit && $actual !== $expected) || ($expected > $limit && $actual <= $limit)) {
            throw new RuntimeException('Distance disagreement: ' . json_encode(array($left, $right, $limit, $expected, $actual)));
        }
        $checks++;
    }
}

// Exhaust all short inputs, including empty strings and overlapping swaps.
$words = array('');
$frontier = array('');
for ($length = 1; $length <= 5; $length++) {
    $next = array();
    foreach ($frontier as $word) foreach (array('a', 'b') as $letter) $next[] = $word . $letter;
    $words = array_merge($words, $next);
    $frontier = $next;
}
foreach ($words as $left) foreach ($words as $right) distanceCheck($left, $right);

// Deterministic Unicode samples exercise the full supported word-length range.
mt_srand(23817);
$alphabet = array('a', 'b', 'c', "\u{e9}", "\u{df}", "\u{44f}");
for ($trial = 0; $trial < 3000; $trial++) {
    $left = '';
    for ($i = 0, $length = mt_rand(0, 40); $i < $length; $i++) $left .= $alphabet[mt_rand(0, count($alphabet) - 1)];
    $characters = preg_split('//u', $left, -1, PREG_SPLIT_NO_EMPTY);
    for ($edit = 0, $edits = mt_rand(0, 4); $edit < $edits; $edit++) {
        $index = mt_rand(0, count($characters));
        $letter = $alphabet[mt_rand(0, count($alphabet) - 1)];
        switch (mt_rand(0, 3)) {
            case 0: array_splice($characters, $index, 0, array($letter)); break;
            case 1: array_splice($characters, $index, 1); break;
            case 2: if (isset($characters[$index])) $characters[$index] = $letter; break;
            case 3:
                if (isset($characters[$index + 1])) {
                    $temporary = $characters[$index];
                    $characters[$index] = $characters[$index + 1];
                    $characters[$index + 1] = $temporary;
                }
                break;
        }
    }
    distanceCheck($left, implode('', $characters));
}
echo 'Search distance reference checks: ' . $checks . " passed.\n";
