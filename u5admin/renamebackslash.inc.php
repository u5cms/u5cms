<?php
if ($ignoremanualfullpaths != 'yes') {
$sql_a = "UPDATE resources SET content_d=REPLACE(content_d,'c=".$search."\\\\','c=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE resources SET content_e=REPLACE(content_e,'c=".$search."\\\\','c=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE resources SET content_f=REPLACE(content_f,'c=".$search."\\\\','c=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE resources SET content_d=REPLACE(content_d,'n=".$search."\\\\','n=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE resources SET content_e=REPLACE(content_e,'n=".$search."\\\\','n=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE resources SET content_f=REPLACE(content_f,'n=".$search."\\\\','n=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
}
?>