<?php
if ($ignoremanualfullpaths != 'yes') {
$sql_a = "UPDATE resources SET content_1=REPLACE(content_1,'c=".$search."\\\\','c=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE resources SET content_2=REPLACE(content_2,'c=".$search."\\\\','c=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE resources SET content_3=REPLACE(content_3,'c=".$search."\\\\','c=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE resources SET content_4=REPLACE(content_4,'c=".$search."\\\\','c=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE resources SET content_5=REPLACE(content_5,'c=".$search."\\\\','c=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE resources SET content_1=REPLACE(content_1,'n=".$search."\\\\','n=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE resources SET content_2=REPLACE(content_2,'n=".$search."\\\\','n=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE resources SET content_3=REPLACE(content_3,'n=".$search."\\\\','n=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE resources SET content_4=REPLACE(content_4,'n=".$search."\\\\','n=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE resources SET content_5=REPLACE(content_5,'n=".$search."\\\\','n=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

}
?>