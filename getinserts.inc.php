<?php 
require_once ('connect.inc.php');

$sql_b = "SELECT * FROM inserts WHERE source1 NOT like '[' AND source1 NOT like '[h:]' ORDER BY length(source1) DESC";
$result_b = mysql_query($sql_b);

if ($result_b == false) {
echo 'SQL_b-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_b . '</font><p>';
}
$num_b = mysql_num_rows($result_b);

?>