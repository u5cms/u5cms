<?php
require_once ('connect.inc.php');
require_once ('render.inc.php');


$sql_a = "SELECT * FROM resources WHERE deleted!=1 AND name='slogan'";
$result_a = mysql_query($sql_a);

if ($result_a == false) {

  echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
}
$row_a = mysql_fetch_array($result_a);
$sql_b = "SELECT * FROM inserts WHERE source1 NOT like '[' AND source1 NOT like '[h:]' ORDER BY length(source1) DESC";
$result_b = mysql_query($sql_b);

if ($result_b == false) {

  echo 'SQL_b-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_b . '</font><p>';
}
$num_b = mysql_num_rows($result_b);


echo render(def($row_a['content_1'],$row_a['content_2'],$row_a['content_3'],$row_a['content_4'],$row_a['content_5']));


?>
