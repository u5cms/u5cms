<?php
require_once('san.inc.php');
/**
 * Sample URLs for testing purposes:
 *
 * mean.php?m=5%21*%21education%21%2C%210%21*%21engineering%21%2C%210%21*%21medical%21%2C%210%21*%21other%21%2C%215!*!education!,!0!*!engineering!,!0!*!medical!,!0!*!other!,!
 * mean.php?m=1%21*%21+0%21%2C%210%21*%211%21%2C%212%21*%212%21%2C%210%21*%213%21%2C%210%21*%214%21%2C%210%21*%215%21%2C%210%21*%219%21%2C%21
 * mean.php?m=1%21*%210%21%2C%210%21*%211%21%2C%212%21*%212%21%2C%216%21*%213%21%2C%210%21*%214%21%2C%212%21*%215%21%2C%213%21*%218%21%2C%2110%21*%219%21%2C%21
 * mean.php?m=1%21*%210%21%2C%210%21*%211%21%2C%212%21*%212%21%2C%216%21*%213%21%2C%210%21*%214%21%2C%212%21*%215%21%2C%213%21*%217%21%2C%2110%21*%219%21%2C%21
 * mean.php?m=1%21*%2110%21%2C%212%21*%2120%21%2C%211%21*%2130%21%2C%210%21*%2140%21%2C%210%21*%2150%21%2C%212%21*%2190%21%2C%21
*/
require_once 'myfunctions.inc.php';
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);

$debug = $_GET['debug'] ?? false;

$tuples = explode ('!,!',$_GET['m']);
array_pop($tuples);
$doStats = true;
$nominations = array();
$items = array();
$values = array();

$stepsize=null;
$previous=null;
$locked=false;
foreach ($tuples as $tuple) {
    list($nom, $item) = explode('!*!', $tuple);
    $nom = (int) trim($nom);
    $item = trim($item);
    // check of item is a number; we don't do stats for string values
    if ($item != (int) $item) $doStats = false;
    $nominations[] = $nom;
    $items[] = $item;
    // Consider stepsize between the first two items
    $item = (int) $item;

    // Calculate stepsize if needed
    if (is_null($stepsize) && !is_null($previous)) {
        $stepsize = $item - $previous;
    }

    // As long as the values array is not yet locked, save item as many times
    // it was nominated if item has the same stepsize as the calculated
    // stepsize or if stepsize is not yet available
    if (!$locked) {
        if (is_null($stepsize) || $item - $previous == $stepsize) {
            for ($i=0;$i<$nom;$i++) {
                $values[] = (int) $item;
            }
        } else {
            // stepsize violation occred; lock the values array
            $locked = true;
        }
    }

    $previous = (int) $item;
}

$totaln = array_sum($nominations);
$validn = count($values);

if ($debug) {
    echo '<pre>';
    var_dump($_GET['m']);
    var_dump($tuples);
    echo '$doStats: '; var_dump($doStats);
    echo '$stepsize: '; var_dump($stepsize);
    echo '$nominations: '; var_dump($nominations);
    echo '$totaln: '; var_dump($totaln);
    echo '$validn: '; var_dump($validn);
    echo '$items: '; var_dump($items);
    echo '$values: '; var_dump($values);
    echo '$sum_values: '; var_dump(array_sum($values));
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" style="font-family:Arial, Helvetica, sans-serif;font-size:60%;color:#666666;">

<table cellspacing="1" cellpadding="0" border="0">
<tr>
<td>n*</td>
<td>=</td>
<td><b><?= $validn ?></b></td>
</tr>
<tr>
<td>&micro;</td>
<td>=</td>
<td><b><?= $doStats ? calc_mean($values) : '-' ?></b></td>
</tr>
<tr>
<td>&sigma;</td>
<td>=</td>
<td><b><?= $doStats ? calc_sd($values) : '-' ?></b></td>
</tr>
<tr>
<td>&micro;<sub>&frac12;</sub></td>
<td>=</td>
<td><b><?= $doStats ? calc_median($values) : '-' ?></b></td>
</tr>
<tr>
<td colspan="3" align="right">*<span style="font-size:90%"> (<?= $totaln ?> &minus; <?= $totaln - $validn ?>)</span></td>
</tr>
</table>

</body>
</html>
<?php

/**
* Calculates the mean or avarage of an array of numbers
*
* @param   array   Array of values
* @param   int     The precision to apply; defaults to 2 digits after the comma
* @return  float   The mean or average of the numbers in thee given array
*/
function calc_mean(array $values, int $precision = 2) {
    if (count($values) == 0) {
        return 0;
    }

    $format = '%.' . $precision . 'F';
    return sprintf($format, array_sum($values)/count($values)) ;
}

/**
* Calculates the standard deviation of an array of numbers
*
* @param   array   Array of values
* @param   int     The precision to apply; defaults to 2 digits after the comma
* @return  float   The standard deviation of the numbers in the given array
*/
function calc_sd(array $values, int $precision = 2) {
    $size = count($values);

    if ($size < 2) {
        return 0;
    }

    $mean = calc_mean($values, 8);
    $squares = array_map(function ($x) use ($mean) {
        return pow($x - $mean, 2);
    }, $values);

    $sd = sqrt(array_sum($squares) / ($size - 1));

    $format = '%.' . $precision . 'F';
    return sprintf($format, $sd);
}

/**
* Calculates the median of an arrayy of numbers
*
* @param   array   Array of values
* @param   int     The precision to apply; defaults to 2 digits after the comma
* @return  float   The median of the numbers in thee given array
*/
function calc_median(array $values, int $precision = 2) {
    $size = count($values); //total numbers in array
    if ($size == 0) {
        return 0;
    }

    sort($values);
    $midpos = floor(($size-1)/2); // find the position of the center
    if($size % 2) { // odd number, value at the center position is the median
        $median = $values[$midpos];
    } else { // even number, calculate avg between the two values around the center
        $low = $values[$midpos];
        $high = $values[$midpos+1];
        $median = (($low+$high)/2);
    }
    $format = '%.' . $precision . 'F';
    return sprintf($format, $median);
}
