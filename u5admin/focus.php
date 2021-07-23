<?php
setcookie('f9focus', $_GET['c'], time() + 3600 * 24 * 365 * 10, '/');
setcookie('pvs_f', 'x', time() + 3600 * 24 * 365 * 10, '/');
setcookie('dgf', '', time() + 3600 * 24 * 365 * 10, '/');

require_once('connect.inc.php');

$sql_a = "SELECT * FROM resources WHERE name='" . mysql_real_escape_string($_GET['c']) . "' AND deleted!=1";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
$num_a = mysql_num_rows($result_a);
if ($num_a == 0) echo '<script>alert("not found");history.go(-1);</script>';
$row_a = mysql_fetch_array($result_a);

if ($row_a['deleted'] == 2) setcookie('shrchv', 1, time() + 3600 * 24 * 365 * 10, '/');
else setcookie('shrchv', 0, time() + 3600 * 24 * 365 * 10, '/');

if ($row_a['typ'] == 'p' && $row_a['name'][0] != '!') header("Location: p1.php");
else if ($row_a['typ'] == 'p' && $row_a['name'][0] == '!') header("Location: p2.php");
else if ($row_a['typ'] == 'i') header("Location: i1.php");
else if ($row_a['typ'] == 'a') header("Location: i1.php");
else if ($row_a['typ'] == 'f') header("Location: f.php");
else if ($row_a['typ'] == 'd') header("Location: d.php");
else if ($row_a['typ'] == 'v') header("Location: v1.php");
else if ($row_a['typ'] == 'y') header("Location: y.php");
else if ($row_a['typ'] == 'c') header("Location: c.php");
else if ($row_a['typ'] == 'e') header("Location: e.php");
else if ($row_a['typ'] == 'x') header("Location: x.php");
?>